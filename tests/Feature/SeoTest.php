<?php

namespace Tests\Feature;

use App\Models\Category;
use App\Models\Product;
use App\Models\ProductImage;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class SeoTest extends TestCase
{
    use RefreshDatabase;

    private function seedProduct(): Product
    {
        $cat = Category::create(['name' => 'Coșuri de gunoi', 'slug' => 'cosuri-de-gunoi', 'sort_order' => 1, 'is_active' => true]);
        $product = Product::create([
            'name' => 'Coș stradal C120', 'slug' => 'cos-c120', 'code' => '#C120',
            'description' => 'Coș rezistent.', 'price_on_request' => true, 'is_active' => true, 'sort_order' => 1,
        ]);
        $product->categories()->attach($cat, ['is_primary' => true]);
        ProductImage::create(['product_id' => $product->id, 'path' => 'products/cos-c120/1.jpg', 'sort_order' => 1, 'is_primary' => true]);

        return $product;
    }

    /** @return array<int, array<string, mixed>> toate blocurile JSON-LD din pagină */
    private function jsonLdBlocks(string $html): array
    {
        preg_match_all('#<script type="application/ld\+json">(.*?)</script>#s', $html, $m);

        return array_map(function (string $json) {
            $decoded = json_decode($json, true);
            $this->assertSame(JSON_ERROR_NONE, json_last_error(), "JSON-LD invalid: {$json}");

            return $decoded;
        }, $m[1]);
    }

    public function test_product_page_meta_and_canonical(): void
    {
        $this->seedProduct();
        $html = $this->get('/produs/cos-c120')->assertOk()->getContent();

        $this->assertStringContainsString('<link rel="canonical" href="'.route('product', 'cos-c120').'">', $html);
        $this->assertStringContainsString('og:image', $html);
        $this->assertSame(1, substr_count($html, '<h1'), 'Trebuie un singur h1 per pagină');
    }

    public function test_product_jsonld_is_valid_and_has_no_fake_price(): void
    {
        $this->seedProduct();
        $html = $this->get('/produs/cos-c120')->getContent();
        $blocks = $this->jsonLdBlocks($html);

        $types = array_column($blocks, '@type');
        $this->assertContains('Product', $types);
        $this->assertContains('BreadcrumbList', $types);

        $product = collect($blocks)->firstWhere('@type', 'Product');
        $this->assertSame('C120', $product['sku']);
        $this->assertSame(config('contact.brand'), $product['brand']['name']);
        $this->assertSame('https://schema.org/PreOrder', $product['offers']['availability']);
        $this->assertArrayNotHasKey('price', $product['offers'], 'Niciun preț fals la „la cerere"');
    }

    public function test_category_page_has_itemlist_and_breadcrumb(): void
    {
        $this->seedProduct();
        $html = $this->get('/categorie/cosuri-de-gunoi')->assertOk()->getContent();
        $types = array_column($this->jsonLdBlocks($html), '@type');

        $this->assertContains('ItemList', $types);
        $this->assertContains('BreadcrumbList', $types);
        $this->assertStringContainsString('<link rel="canonical" href="'.route('category', 'cosuri-de-gunoi').'">', $html);
    }

    public function test_catalog_page_has_single_h1_and_itemlist(): void
    {
        $this->seedProduct();
        $html = $this->get('/catalog')->assertOk()->getContent();

        $this->assertSame(1, substr_count($html, '<h1'), 'Catalog: un singur h1');
        $types = array_column($this->jsonLdBlocks($html), '@type');
        $this->assertContains('ItemList', $types);
    }

    public function test_sitemap_lists_active_categories_and_products(): void
    {
        $this->seedProduct();
        Product::create(['name' => 'Inactiv', 'slug' => 'inactiv', 'price_on_request' => true, 'is_active' => false, 'sort_order' => 9]);

        $res = $this->get('/sitemap.xml')->assertOk();
        $res->assertHeader('Content-Type', 'application/xml');
        $res->assertSee(route('product', 'cos-c120'), false);
        $res->assertSee(route('category', 'cosuri-de-gunoi'), false);
        $res->assertDontSee(route('product', 'inactiv'), false);
    }

    public function test_robots_blocks_admin_and_ops_and_points_to_sitemap(): void
    {
        $this->get('/robots.txt')
            ->assertOk()
            ->assertSee('Disallow: /admin')
            ->assertSee('Disallow: /ops')
            ->assertSee('Sitemap: '.url('/sitemap.xml'));
    }
}
