<?php

namespace Tests\Feature;

use App\Models\Category;
use App\Models\Product;
use App\Models\ProductImage;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class ProductPageTest extends TestCase
{
    use RefreshDatabase;

    private function seedProduct(): Product
    {
        $cat = Category::create(['name' => 'Coșuri de gunoi', 'slug' => 'cosuri-de-gunoi', 'sort_order' => 1, 'is_active' => true]);

        $product = Product::create([
            'name' => 'Coș stradal C120', 'slug' => 'cos-c120', 'code' => '#C120',
            'description' => 'Coș rezistent din metal.', 'availability' => 'la comandă',
            'price_on_request' => true, 'is_active' => true, 'sort_order' => 1,
        ]);
        $product->categories()->attach($cat, ['is_primary' => true]);

        ProductImage::create(['product_id' => $product->id, 'path' => 'products/cos-c120/2.jpg', 'sort_order' => 2, 'is_primary' => false]);
        ProductImage::create(['product_id' => $product->id, 'path' => 'products/cos-c120/1.jpg', 'sort_order' => 1, 'is_primary' => true]);

        // Produs similar din aceeași categorie.
        $other = Product::create(['name' => 'Coș stradal C130', 'slug' => 'cos-c130', 'code' => '#C130', 'price_on_request' => true, 'is_active' => true, 'sort_order' => 2]);
        $other->categories()->attach($cat, ['is_primary' => true]);

        return $product;
    }

    public function test_product_page_shows_core_info(): void
    {
        $this->seedProduct();

        $this->get('/produs/cos-c120')
            ->assertOk()
            ->assertSee('Coș stradal C120')
            ->assertSee('C120')
            ->assertSee('Coș rezistent din metal.')
            ->assertSee('la comandă')
            ->assertSee('Preț la cerere');
    }

    public function test_product_page_has_prefilled_whatsapp_cta(): void
    {
        config(['contact.whatsapp' => '40712345678']);
        $this->seedProduct();

        $res = $this->get('/produs/cos-c120')->assertOk();

        $expectedText = rawurlencode('Bună ziua, doresc o ofertă pentru: Coș stradal C120 (cod C120) — '.route('product', 'cos-c120'));
        $res->assertSee('https://wa.me/40712345678?text='.$expectedText, false);
    }

    public function test_product_page_links_category_and_similar(): void
    {
        $this->seedProduct();

        $this->get('/produs/cos-c120')
            ->assertSee(route('category', 'cosuri-de-gunoi'))
            ->assertSee('Produse similare')
            ->assertSee('Coș stradal C130');
    }

    public function test_price_shown_when_not_on_request(): void
    {
        $cat = Category::create(['name' => 'Bănci', 'slug' => 'banci-sezut', 'sort_order' => 1, 'is_active' => true]);
        $product = Product::create([
            'name' => 'Bancă cu preț', 'slug' => 'banca-pret', 'code' => '#B1',
            'price' => 1234.50, 'price_on_request' => false, 'is_active' => true, 'sort_order' => 1,
        ]);
        $product->categories()->attach($cat, ['is_primary' => true]);

        $this->get('/produs/banca-pret')
            ->assertOk()
            ->assertSee('1.234,50 lei')
            ->assertDontSee('Preț la cerere');
    }
}
