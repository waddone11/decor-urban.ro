<?php

namespace Tests\Feature;

use App\Models\Category;
use App\Models\Product;
use App\Support\Feeds\ProductFeed;
use App\Support\JsonLd;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class PriceDisplayTest extends TestCase
{
    use RefreshDatabase;

    private function makeProduct(array $attrs = []): Product
    {
        $cat = Category::firstOrCreate(
            ['slug' => 'banci'],
            ['name' => 'Bănci', 'sort_order' => 1, 'is_active' => true]
        );

        $product = Product::create(array_merge([
            'name' => 'Bancă stradală B1', 'slug' => 'banca-b1', 'code' => '#B1',
            'description' => 'Bancă rezistentă.', 'availability' => 'la comandă',
            'price_on_request' => true, 'is_active' => true, 'sort_order' => 1,
        ], $attrs));
        $product->categories()->attach($cat, ['is_primary' => true]);

        return $product;
    }

    // ── Model ────────────────────────────────────────────────────────────────

    public function test_price_on_request_wins_even_with_price_set(): void
    {
        $p = $this->makeProduct(['price' => 100, 'price_on_request' => true]);

        $this->assertTrue($p->isPriceOnRequest());
        $this->assertNull($p->currentPrice());
    }

    public function test_current_price_prefers_valid_sale_price(): void
    {
        $p = $this->makeProduct(['price' => 1250, 'sale_price' => 990, 'price_on_request' => false]);

        $this->assertFalse($p->isPriceOnRequest());
        $this->assertTrue($p->hasSalePrice());
        $this->assertSame(990.0, $p->currentPrice());
        $this->assertSame(21, $p->discountPercent());
    }

    public function test_sale_price_equal_or_above_price_is_ignored(): void
    {
        $p = $this->makeProduct(['price' => 100, 'sale_price' => 100, 'price_on_request' => false]);

        $this->assertFalse($p->hasSalePrice());
        $this->assertSame(100.0, $p->currentPrice());
        $this->assertNull($p->discountPercent());
    }

    // ── Pagina produs ────────────────────────────────────────────────────────

    public function test_product_page_shows_normal_price(): void
    {
        $this->makeProduct(['price' => 1250, 'price_on_request' => false]);

        $this->get('/produs/banca-b1')->assertOk()
            ->assertSee('1.250,00 lei')
            ->assertDontSee('Preț la cerere');
    }

    public function test_product_page_shows_sale_price_with_strikethrough_old_price(): void
    {
        $this->makeProduct(['price' => 1250, 'sale_price' => 990, 'price_on_request' => false]);

        $res = $this->get('/produs/banca-b1')->assertOk();

        $res->assertSee('990,00 lei');
        $res->assertSee('<s', false);
        $res->assertSee('1.250,00 lei');
        $res->assertSee('-21%');
    }

    public function test_product_page_keeps_price_on_request(): void
    {
        $this->makeProduct(['price_on_request' => true]);

        $this->get('/produs/banca-b1')->assertOk()->assertSee('Preț la cerere');
    }

    // ── Card (listare categorie) ─────────────────────────────────────────────

    public function test_category_card_shows_price(): void
    {
        $this->makeProduct(['price' => 1250, 'price_on_request' => false]);

        $this->get('/categorie/banci')->assertOk()
            ->assertSee('1.250,00 lei')
            ->assertDontSee('La cerere');
    }

    public function test_category_card_shows_sale_price_with_old_price(): void
    {
        $this->makeProduct(['price' => 1250, 'sale_price' => 990, 'price_on_request' => false]);

        $res = $this->get('/categorie/banci')->assertOk();

        $res->assertSee('990,00 lei');
        $res->assertSee('1.250,00 lei');
    }

    public function test_category_card_keeps_la_cerere_for_on_request_products(): void
    {
        $this->makeProduct(['price_on_request' => true]);

        $this->get('/categorie/banci')->assertOk()->assertSee('La cerere');
    }

    // ── JSON-LD Offer ────────────────────────────────────────────────────────

    public function test_jsonld_has_offer_with_current_price_for_priced_product(): void
    {
        $p = $this->makeProduct(['price' => 1250, 'sale_price' => 990, 'price_on_request' => false]);

        $ld = JsonLd::product($p);

        $this->assertSame('Offer', $ld['offers']['@type']);
        $this->assertSame('990.00', $ld['offers']['price']);
        $this->assertSame('RON', $ld['offers']['priceCurrency']);
    }

    public function test_jsonld_has_no_offer_for_price_on_request(): void
    {
        $p = $this->makeProduct(['price_on_request' => true]);

        $this->assertArrayNotHasKey('offers', JsonLd::product($p));
    }

    // ── Feed ─────────────────────────────────────────────────────────────────

    public function test_priced_feed_enabled_product_appears_in_google_feed(): void
    {
        $p = $this->makeProduct([
            'price' => 1250, 'sale_price' => 990, 'price_on_request' => false,
            'quote_only' => false, 'feed_enabled' => true,
        ]);
        \App\Models\ProductImage::create(['product_id' => $p->id, 'path' => 'products/banca-b1/1.jpg', 'sort_order' => 1, 'is_primary' => true]);

        ProductFeed::forgetCache();
        $xml = ProductFeed::googleXml();

        $this->assertStringContainsString('banca-b1', $xml);
        $this->assertStringContainsString('1250.00 RON', $xml);
        $this->assertStringContainsString('990.00 RON', $xml);
    }
}
