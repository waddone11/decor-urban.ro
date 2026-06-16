<?php

namespace Tests\Feature;

use App\Models\Category;
use App\Models\Product;
use App\Models\ProductImage;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class HomepageLinksTest extends TestCase
{
    use RefreshDatabase;

    private function seedCatalog(): void
    {
        $cat = Category::create(['name' => 'Bănci', 'slug' => 'banci-sezut', 'sort_order' => 1, 'is_active' => true]);
        $p = Product::create(['name' => 'Bancă B201', 'slug' => 'banca-b201', 'code' => '#B201', 'price_on_request' => true, 'is_active' => true, 'sort_order' => 1]);
        $p->categories()->attach($cat, ['is_primary' => true]);
        ProductImage::create(['product_id' => $p->id, 'path' => 'products/banca-b201/1.jpg', 'sort_order' => 1, 'is_primary' => true]);
    }

    public function test_homepage_has_no_dead_hash_links(): void
    {
        $this->seedCatalog();
        $html = $this->get('/')->assertOk()->getContent();

        $this->assertStringNotContainsString('href="#"', $html);
        $this->assertStringNotContainsString('href="#categorii"', $html);
    }

    public function test_homepage_links_to_real_routes(): void
    {
        $this->seedCatalog();
        $res = $this->get('/')->assertOk();

        $res->assertSee(route('catalog'), false);
        $res->assertSee(route('category', 'banci-sezut'), false);
        $res->assertSee(route('product', 'banca-b201'), false);
        $res->assertSee(route('despre'), false);
        $res->assertSee(route('institutii'), false);
        $res->assertSee(route('contact'), false);
        $res->assertSee(route('proiecte'), false);
    }

    public function test_footer_links_to_legal_pages(): void
    {
        $this->seedCatalog();
        $res = $this->get('/')->assertOk();

        $res->assertSee(route('confidentialitate'), false);
        $res->assertSee(route('termeni'), false);
        $res->assertSee(route('politica-cookies'), false);
    }
}
