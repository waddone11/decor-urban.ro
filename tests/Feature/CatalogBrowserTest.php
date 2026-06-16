<?php

namespace Tests\Feature;

use App\Livewire\CatalogBrowser;
use App\Models\Category;
use App\Models\Product;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Livewire\Livewire;
use Tests\TestCase;

class CatalogBrowserTest extends TestCase
{
    use RefreshDatabase;

    private function seedCatalog(): array
    {
        $banci = Category::create(['name' => 'Bănci', 'slug' => 'banci-sezut', 'sort_order' => 1, 'is_active' => true]);
        $cosuri = Category::create(['name' => 'Coșuri', 'slug' => 'cosuri-de-gunoi', 'sort_order' => 2, 'is_active' => true]);

        $banca = Product::create(['name' => 'Bancă B201', 'slug' => 'banca-b201', 'code' => '#B201', 'price_on_request' => true, 'is_active' => true, 'sort_order' => 1]);
        $banca->categories()->attach($banci, ['is_primary' => true]);

        $cos = Product::create(['name' => 'Coș stradal C120', 'slug' => 'cos-c120', 'code' => '#C120', 'price_on_request' => true, 'is_active' => true, 'sort_order' => 1]);
        $cos->categories()->attach($cosuri, ['is_primary' => true]);

        $hidden = Product::create(['name' => 'Ascuns', 'slug' => 'ascuns', 'price_on_request' => true, 'is_active' => false, 'sort_order' => 1]);
        $hidden->categories()->attach($banci, ['is_primary' => true]);

        return compact('banci', 'cosuri', 'banca', 'cos', 'hidden');
    }

    public function test_lists_only_active_products(): void
    {
        $this->seedCatalog();

        Livewire::test(CatalogBrowser::class)
            ->assertSee('Bancă B201')
            ->assertSee('Coș stradal C120')
            ->assertDontSee('Ascuns');
    }

    public function test_filter_by_category(): void
    {
        $this->seedCatalog();

        Livewire::test(CatalogBrowser::class)
            ->set('cat', 'banci-sezut')
            ->assertSee('Bancă B201')
            ->assertDontSee('Coș stradal C120');
    }

    public function test_search_by_name_and_code(): void
    {
        $this->seedCatalog();

        Livewire::test(CatalogBrowser::class)
            ->set('q', 'C120')
            ->assertSee('Coș stradal C120')
            ->assertDontSee('Bancă B201');
    }

    public function test_url_query_string_is_synced(): void
    {
        $this->seedCatalog();

        // cat e marcat #[Url] => prezent în query string al componentei.
        Livewire::withQueryParams(['cat' => 'cosuri-de-gunoi'])
            ->test(CatalogBrowser::class)
            ->assertSee('Coș stradal C120')
            ->assertDontSee('Bancă B201');
    }

    public function test_catalog_page_renders_component(): void
    {
        $this->seedCatalog();

        $this->get('/catalog')
            ->assertOk()
            ->assertSeeLivewire(CatalogBrowser::class);
    }
}
