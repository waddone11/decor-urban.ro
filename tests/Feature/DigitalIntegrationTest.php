<?php

namespace Tests\Feature;

use App\Models\Category;
use App\Models\Product;
use App\Models\ProductImage;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class DigitalIntegrationTest extends TestCase
{
    use RefreshDatabase;

    private function product(array $overrides = []): Product
    {
        $category = Category::firstOrCreate(
            ['slug' => 'banci'],
            ['name' => 'Bănci', 'is_active' => true, 'sort_order' => 1]
        );

        $product = Product::create(array_merge([
            'name' => 'Bancă stradală B100',
            'slug' => 'banca-b100',
            'code' => '#B100',
            'description' => 'Bancă stradală cu structură metalică.',
            'price' => null,
            'price_on_request' => true,
            'quote_only' => true,
            'feed_enabled' => false,
            'availability' => 'in stock',
            'is_active' => true,
            'sort_order' => 1,
        ], $overrides));

        $product->categories()->attach($category, ['is_primary' => true]);

        if (($overrides['with_image'] ?? true) !== false) {
            ProductImage::create([
                'product_id' => $product->id,
                'path' => 'products/'.$product->slug.'/1.jpg',
                'alt' => $product->name,
                'is_primary' => true,
                'sort_order' => 1,
            ]);
        }

        return $product;
    }

    public function test_social_links_and_nap_render_consistently(): void
    {
        $html = $this->get('/contact')->assertOk()->getContent();

        $this->assertStringContainsString('https://www.facebook.com/profile.php?id=61592205237734', $html);
        $this->assertStringContainsString('https://www.instagram.com/decor.urban.ro', $html);
        $this->assertStringContainsString('https://www.tiktok.com/@decor.urban.ro', $html);
        $this->assertStringContainsString('https://share.google/sWYL0KoX1P7j3O06B', $html);
        $this->assertStringContainsString('+40 758 522 227', $html);
        $this->assertStringContainsString('+40 756 222 260', $html);
        $this->assertStringContainsString('Str. Băltați nr. 149, Sat Băltați, Oraș Scornicești, Județul Olt, România', $html);
        $this->assertStringContainsString('outbound_social_click', $html);
    }

    public function test_sitemaps_are_valid_xml_and_exclude_inactive_products(): void
    {
        $this->product();
        Product::create(['name' => 'Draft', 'slug' => 'draft', 'is_active' => false, 'price_on_request' => true]);

        foreach (['/sitemap.xml', '/sitemaps/pages.xml', '/sitemaps/categories.xml', '/sitemaps/products.xml', '/sitemaps/images.xml'] as $path) {
            $xml = $this->get($path)->assertOk()->getContent();
            $this->assertNotFalse(simplexml_load_string($xml), $path.' nu este XML valid');
        }

        $products = $this->get('/sitemaps/products.xml')->getContent();
        $this->assertStringContainsString(route('product', 'banca-b100'), $products);
        $this->assertStringNotContainsString(route('product', 'draft'), $products);
    }

    public function test_feeds_exclude_quote_only_and_include_only_eligible_products(): void
    {
        $this->product();
        $this->product([
            'name' => 'Coș C200',
            'slug' => 'cos-c200',
            'code' => '#C200',
            'price' => 399.99,
            'price_on_request' => false,
            'quote_only' => false,
            'feed_enabled' => true,
            'availability' => 'in stock',
            'mpn' => 'C200',
        ]);

        $google = $this->get('/feeds/google-merchant.xml')->assertOk()->getContent();
        $this->assertNotFalse(simplexml_load_string($google));
        $this->assertStringContainsString('<g:id>cos-c200</g:id>', $google);
        $this->assertStringContainsString('<g:mpn>C200</g:mpn>', $google);
        $this->assertStringNotContainsString('<g:id>banca-b100</g:id>', $google);
        $this->assertStringContainsString('<g:price>399.99 RON</g:price>', $google);

        $meta = $this->get('/feeds/meta-catalog.csv')->assertOk()->getContent();
        $this->assertStringContainsString('id,title,description,availability,condition,price,link,image_link', $meta);
        $this->assertStringContainsString('cos-c200', $meta);
        $this->assertStringNotContainsString('banca-b100', $meta);
    }

    public function test_product_jsonld_and_share_controls(): void
    {
        $this->product();
        $html = $this->get('/produs/banca-b100')->assertOk()->getContent();

        $this->assertStringContainsString('BreadcrumbList', $html);
        $this->assertStringContainsString('"@type":"Product"', $html);
        $this->assertStringNotContainsString('"offers"', $html);
        $this->assertStringContainsString('facebook.com/sharer/sharer.php', $html);
        $this->assertStringContainsString('Copiază link', $html);
    }

    public function test_pixels_are_loaded_only_after_consent_code_path(): void
    {
        config([
            'business.tracking.ga4_measurement_id' => 'G-TEST',
            'business.tracking.meta_pixel_id' => '123',
            'business.tracking.tiktok_pixel_id' => 'TT',
        ]);

        $html = $this->get('/despre')->assertOk()->getContent();

        $this->assertStringContainsString("analytics_storage: 'denied'", $html);
        $this->assertStringContainsString('applyConsent(consent)', $html);
        $this->assertStringContainsString('loadMarketing()', $html);
        $this->assertStringContainsString('connect.facebook.net', $html);
    }
}
