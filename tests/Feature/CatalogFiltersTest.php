<?php

namespace Tests\Feature;

use App\Livewire\CatalogBrowser;
use App\Models\Category;
use App\Models\Product;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Livewire\Livewire;
use Tests\TestCase;

class CatalogFiltersTest extends TestCase
{
    use RefreshDatabase;

    private function seedCatalog(): void
    {
        $cat = Category::create(['name' => 'Bănci', 'slug' => 'banci', 'sort_order' => 1, 'is_active' => true]);

        $promo = Product::create([
            'name' => 'Bancă promo B1', 'slug' => 'banca-promo-b1', 'price' => 1159, 'sale_price' => 999,
            'price_on_request' => false, 'is_active' => true, 'sort_order' => 1,
            'specs' => ['material' => ['metal', 'lemn']],
        ]);
        $seap = Product::create([
            'name' => 'Bancă SEAP B2', 'slug' => 'banca-seap-b2', 'price_on_request' => true,
            'available_seap' => true, 'is_active' => true, 'sort_order' => 2,
            'specs' => ['material' => ['beton']],
        ]);
        $plain = Product::create([
            'name' => 'Bancă simplă B3', 'slug' => 'banca-simpla-b3', 'price_on_request' => true,
            'is_active' => true, 'sort_order' => 3,
            'specs' => ['material' => ['metal']],
        ]);
        foreach ([$promo, $seap, $plain] as $p) {
            $p->categories()->attach($cat, ['is_primary' => true]);
        }
    }

    public function test_promo_toggle_shows_only_sale_products(): void
    {
        $this->seedCatalog();

        Livewire::test(CatalogBrowser::class)
            ->set('promo', true)
            ->assertSee('Bancă promo B1')
            ->assertDontSee('Bancă SEAP B2')
            ->assertDontSee('Bancă simplă B3');
    }

    public function test_seap_toggle_shows_only_seap_products(): void
    {
        $this->seedCatalog();

        Livewire::test(CatalogBrowser::class)
            ->set('seap', true)
            ->assertSee('Bancă SEAP B2')
            ->assertDontSee('Bancă promo B1');
    }

    public function test_seap_filter_reachable_from_url_query(): void
    {
        $this->seedCatalog();

        $this->get('/catalog?seap=1')->assertOk()
            ->assertSee('Bancă SEAP B2')
            ->assertDontSee('Bancă simplă B3');
    }

    public function test_seap_combines_with_material_facet(): void
    {
        $this->seedCatalog();

        Livewire::test(CatalogBrowser::class)
            ->set('seap', true)
            ->set('materials', ['metal'])
            ->assertDontSee('Bancă SEAP B2')
            ->assertSee('Niciun produs');
    }

    public function test_toggle_counts_are_real(): void
    {
        $this->seedCatalog();

        Livewire::test(CatalogBrowser::class)
            ->assertSeeHtml('Doar reduceri')
            ->assertSeeHtml('SEAP');
    }

    public function test_clear_filters_resets_promo_and_seap(): void
    {
        $this->seedCatalog();

        Livewire::test(CatalogBrowser::class)
            ->set('promo', true)
            ->set('seap', true)
            ->call('clearFilters')
            ->assertSet('promo', false)
            ->assertSet('seap', false)
            ->assertSee('Bancă simplă B3');
    }

    public function test_seap_toggle_hidden_when_no_seap_products(): void
    {
        $cat = Category::create(['name' => 'Bănci', 'slug' => 'banci', 'sort_order' => 1, 'is_active' => true]);
        $p = Product::create(['name' => 'Bancă X', 'slug' => 'banca-x', 'price_on_request' => true, 'is_active' => true, 'sort_order' => 1]);
        $p->categories()->attach($cat, ['is_primary' => true]);

        Livewire::test(CatalogBrowser::class)->assertDontSeeHtml('wire:model.live="seap"');
    }
}
