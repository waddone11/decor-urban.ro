<?php

namespace Tests\Feature\Filament;

use App\Filament\Resources\Categories\Pages\ListCategories;
use App\Filament\Resources\Products\Pages\CreateProduct;
use App\Filament\Resources\Products\Pages\EditProduct;
use App\Filament\Resources\Products\Pages\ListProducts;
use App\Filament\Resources\Products\RelationManagers\ImagesRelationManager;
use App\Models\Category;
use App\Models\Product;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\Storage;
use Livewire\Livewire;
use Tests\TestCase;

class CatalogAdminTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();
        Artisan::call('import:legacy');
        $this->actingAs(User::factory()->create());
    }

    public function test_products_list_shows_all_127_records(): void
    {
        Livewire::test(ListProducts::class)
            ->assertCountTableRecords(127)
            ->assertCanRenderTableColumn('primary_image_path')
            ->assertCanRenderTableColumn('categories.name')
            ->assertCanRenderTableColumn('price_status');
    }

    public function test_categories_list_shows_11_with_product_counts(): void
    {
        Livewire::test(ListCategories::class)
            ->assertCountTableRecords(11)
            ->assertCanRenderTableColumn('products_count');

        // products_count corect prin relația pivot.
        $this->assertSame(47, Category::where('slug', 'banci-sezut')->sole()->products()->count());
        $this->assertSame(22, Category::where('slug', 'cosuri-de-gunoi')->sole()->products()->count());
        $this->assertSame(8, Category::where('slug', 'diverse-custom')->sole()->products()->count());
    }

    public function test_edit_product_persists_changes(): void
    {
        $product = Product::where('slug', 'set-mobilier-stradal-banca-si-cos-gunoi')->sole();

        Livewire::test(EditProduct::class, ['record' => $product->getRouteKey()])
            ->fillForm([
                'name' => 'Set mobilier stradal EDITAT',
                'code' => '#SM200X',
            ])
            ->call('save')
            ->assertHasNoFormErrors();

        $product->refresh();
        $this->assertSame('Set mobilier stradal EDITAT', $product->name);
        $this->assertSame('#SM200X', $product->code);
    }

    public function test_price_toggle_hides_price_field_and_price_saves_when_off(): void
    {
        $product = Product::where('slug', 'set-mobilier-stradal-banca-si-cos-gunoi')->sole();

        // price_on_request on => câmpul price e ascuns.
        Livewire::test(EditProduct::class, ['record' => $product->getRouteKey()])
            ->fillForm(['price_on_request' => true])
            ->assertFormFieldIsHidden('price')
            ->fillForm(['price_on_request' => false])
            ->assertFormFieldIsVisible('price')
            ->fillForm(['price' => 1234.56])
            ->call('save')
            ->assertHasNoFormErrors();

        $product->refresh();
        $this->assertFalse($product->price_on_request);
        $this->assertSame('1234.56', $product->price);
    }

    public function test_assigning_categories_syncs_pivot(): void
    {
        $product = Product::where('slug', 'cos-de-gunoi-stradal-c120')->sole();
        $banci = Category::where('slug', 'banci-sezut')->sole();
        $jardiniere = Category::where('slug', 'jardiniere')->sole();

        Livewire::test(EditProduct::class, ['record' => $product->getRouteKey()])
            ->fillForm(['categories' => [$banci->id, $jardiniere->id]])
            ->call('save')
            ->assertHasNoFormErrors();

        $product->refresh();
        $this->assertEqualsCanonicalizing(
            ['banci-sezut', 'jardiniere'],
            $product->categories->pluck('slug')->all()
        );
    }

    public function test_create_product_with_auto_slug_defaults_to_price_on_request(): void
    {
        Livewire::test(CreateProduct::class)
            ->fillForm([
                'name' => 'Produs nou de test',
            ])
            ->call('create')
            ->assertHasNoFormErrors();

        $product = Product::where('name', 'Produs nou de test')->sole();
        $this->assertSame('produs-nou-de-test', $product->slug);
        $this->assertTrue($product->price_on_request);
        $this->assertNull($product->price);
    }

    public function test_search_by_code_and_filter_by_category(): void
    {
        $c120 = Product::where('slug', 'cos-de-gunoi-stradal-c120')->sole();
        $banca = Product::where('slug', 'banca-stradala-b202-si-mobilier-stradal')->sole();

        // Search după cod (1 rezultat → vizibil pe prima pagină).
        Livewire::test(ListProducts::class)
            ->searchTable('#C120')
            ->assertCountTableRecords(1)
            ->assertCanSeeTableRecords([$c120])
            ->assertCanNotSeeTableRecords([$banca]);

        // Filtru după categorie (22 produse în cosuri; banca nu apare).
        $cosuri = Category::where('slug', 'cosuri-de-gunoi')->sole();
        Livewire::test(ListProducts::class)
            ->filterTable('categories', [$cosuri->id])
            ->assertCountTableRecords(22)
            ->assertCanNotSeeTableRecords([$banca]);
    }

    public function test_images_relation_manager_lists_existing_images(): void
    {
        $product = Product::where('slug', 'banca-stradala-b202-si-mobilier-stradal')->sole();
        $this->assertSame(2, $product->images()->count());

        Livewire::test(ImagesRelationManager::class, [
            'ownerRecord' => $product,
            'pageClass' => EditProduct::class,
        ])
            ->assertCountTableRecords(2)
            ->assertCanRenderTableColumn('path')
            ->assertCanRenderTableColumn('is_primary');
    }

    public function test_uploading_image_stores_file_on_public_disk(): void
    {
        Storage::fake('public');
        $product = Product::where('slug', 'cos-de-gunoi-stradal-c120')->sole();

        Livewire::test(ImagesRelationManager::class, [
            'ownerRecord' => $product,
            'pageClass' => EditProduct::class,
        ])
            ->callTableAction('create', data: [
                'path' => UploadedFile::fake()->image('noua.jpg', 200, 200),
                'is_primary' => true,
            ])
            ->assertHasNoTableActionErrors();

        $image = $product->images()->latest('id')->first();
        $this->assertNotNull($image);
        $this->assertStringStartsWith("products/{$product->slug}/", $image->path);
        Storage::disk('public')->assertExists($image->path);
    }
}
