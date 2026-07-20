<?php

namespace Tests\Feature;

use App\Filament\Resources\Products\Pages\ListProducts;
use App\Models\Category;
use App\Models\Product;
use App\Models\User;
use Database\Seeders\CatalogSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Artisan;
use Livewire\Livewire;
use Tests\TestCase;

class SeapCpvTest extends TestCase
{
    use RefreshDatabase;

    private function makeProduct(array $attrs = []): Product
    {
        $cat = Category::firstOrCreate(
            ['slug' => 'banci'],
            ['name' => 'Bănci', 'sort_order' => 1, 'is_active' => true]
        );
        $product = Product::create(array_merge([
            'name' => 'Bancă B1', 'slug' => 'banca-b1', 'price_on_request' => true,
            'is_active' => true, 'sort_order' => 1,
        ], $attrs));
        $product->categories()->attach($cat, ['is_primary' => true]);

        return $product;
    }

    // ── Câmpuri: default-uri sigure ──────────────────────────────────────────

    public function test_available_seap_defaults_to_false_and_cpv_to_null(): void
    {
        $p = $this->makeProduct();

        $this->assertFalse($p->fresh()->available_seap);
        $this->assertNull($p->fresh()->cpv_code);
    }

    // ── Bulk action Filament ─────────────────────────────────────────────────

    public function test_bulk_action_marks_selection_as_seap(): void
    {
        $this->actingAs(User::factory()->create());
        $a = $this->makeProduct(['slug' => 'banca-a']);
        $b = $this->makeProduct(['slug' => 'banca-b']);

        Livewire::test(ListProducts::class)
            ->callTableBulkAction('seap_on', [$a->id, $b->id]);

        $this->assertTrue($a->fresh()->available_seap);
        $this->assertTrue($b->fresh()->available_seap);
    }

    public function test_bulk_action_unmarks_selection(): void
    {
        $this->actingAs(User::factory()->create());
        $a = $this->makeProduct(['available_seap' => true]);

        Livewire::test(ListProducts::class)
            ->callTableBulkAction('seap_off', [$a->id]);

        $this->assertFalse($a->fresh()->available_seap);
    }

    // ── Afișare ──────────────────────────────────────────────────────────────

    public function test_product_page_shows_seap_badge_and_cpv_when_set(): void
    {
        $this->makeProduct(['available_seap' => true, 'cpv_code' => '34928400-2']);

        $res = $this->get('/produs/banca-b1')->assertOk();

        $res->assertSee('SEAP');
        $res->assertSee('Cod CPV: 34928400-2');
    }

    public function test_product_page_hides_cpv_without_seap_or_code(): void
    {
        $this->makeProduct();

        $this->get('/produs/banca-b1')->assertOk()
            ->assertDontSee('Cod CPV');
    }

    public function test_card_shows_seap_badge_only_when_available(): void
    {
        $this->makeProduct(['available_seap' => true]);
        $this->makeProduct(['slug' => 'banca-b2', 'name' => 'Bancă B2']);

        $res = $this->get('/categorie/banci')->assertOk();

        $this->assertSame(1, substr_count($res->getContent(), 'SEAP/SICAP'));
    }

    // ── /institutii ──────────────────────────────────────────────────────────

    public function test_institutii_links_to_seap_filtered_catalog(): void
    {
        $this->get('/institutii')->assertOk()
            ->assertSee('/catalog?seap=1', false);
    }

    // ── Snapshot round-trip ──────────────────────────────────────────────────

    public function test_snapshot_round_trips_seap_and_cpv(): void
    {
        $tmp = sys_get_temp_dir().'/snapshot-seap-test.json';
        config(['catalog.snapshot_path' => $tmp]);
        $this->makeProduct(['available_seap' => true, 'cpv_code' => '34928400-2']);

        Artisan::call('catalog:export-snapshot');
        $data = json_decode((string) file_get_contents($tmp), true);
        $this->assertTrue($data['products'][0]['available_seap']);
        $this->assertSame('34928400-2', $data['products'][0]['cpv_code']);

        Product::query()->delete();
        $this->seed(CatalogSeeder::class);

        $restored = Product::where('slug', 'banca-b1')->firstOrFail();
        $this->assertTrue($restored->available_seap);
        $this->assertSame('34928400-2', $restored->cpv_code);
        @unlink($tmp);
    }
}
