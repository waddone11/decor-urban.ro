<?php

namespace Tests\Feature;

use App\Models\Category;
use App\Models\Product;
use App\Models\ProductImage;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class StorefrontRoutesTest extends TestCase
{
    use RefreshDatabase;

    private function seedCatalog(): array
    {
        $banci = Category::create([
            'name' => 'Bănci & șezut', 'slug' => 'banci-sezut', 'sort_order' => 1, 'is_active' => true,
        ]);
        $inactiveCat = Category::create([
            'name' => 'Categorie ascunsă', 'slug' => 'categorie-ascunsa', 'sort_order' => 9, 'is_active' => false,
        ]);

        $product = Product::create([
            'name' => 'Bancă stradală B201', 'slug' => 'banca-b201', 'code' => '#B201',
            'description' => 'O bancă rezistentă.', 'price_on_request' => true,
            'availability' => 'la comandă', 'is_active' => true, 'sort_order' => 1,
        ]);
        $product->categories()->attach($banci, ['is_primary' => true]);
        ProductImage::create([
            'product_id' => $product->id, 'path' => 'products/banca-b201/1.jpg',
            'sort_order' => 1, 'is_primary' => true,
        ]);

        $inactiveProduct = Product::create([
            'name' => 'Produs ascuns', 'slug' => 'produs-ascuns',
            'price_on_request' => true, 'is_active' => false, 'sort_order' => 2,
        ]);
        $inactiveProduct->categories()->attach($banci, ['is_primary' => true]);

        return compact('banci', 'inactiveCat', 'product', 'inactiveProduct');
    }

    public function test_catalog_route_renders_ok(): void
    {
        $this->seedCatalog();

        $this->get('/catalog')->assertOk();
    }

    public function test_active_category_route_renders_ok(): void
    {
        $this->seedCatalog();

        $this->get('/categorie/banci-sezut')
            ->assertOk()
            ->assertSee('Bănci & șezut');
    }

    public function test_active_product_route_renders_ok(): void
    {
        $this->seedCatalog();

        $this->get('/produs/banca-b201')
            ->assertOk()
            ->assertSee('Bancă stradală B201');
    }

    public function test_inactive_category_returns_404(): void
    {
        $this->seedCatalog();

        $this->get('/categorie/categorie-ascunsa')->assertNotFound();
    }

    public function test_inactive_product_returns_404(): void
    {
        $this->seedCatalog();

        $this->get('/produs/produs-ascuns')->assertNotFound();
    }

    public function test_unknown_slugs_return_404(): void
    {
        $this->seedCatalog();

        $this->get('/categorie/nu-exista')->assertNotFound();
        $this->get('/produs/nu-exista')->assertNotFound();
    }
}
