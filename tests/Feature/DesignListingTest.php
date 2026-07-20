<?php

namespace Tests\Feature;

use App\Livewire\CatalogBrowser;
use App\Models\Category;
use App\Models\Product;
use App\Models\ProductImage;
use App\Support\JsonLd;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Livewire\Livewire;
use Tests\TestCase;

class DesignListingTest extends TestCase
{
    use RefreshDatabase;

    private function product(string $slug, array $specs = [], ?string $name = null): Product
    {
        $p = Product::create([
            'name' => $name ?? ('Produs '.$slug), 'slug' => $slug, 'code' => '#'.strtoupper($slug),
            'description' => 'Descriere îmbogățită.', 'specs' => $specs ?: null,
            'price_on_request' => true, 'is_active' => true, 'sort_order' => 1,
        ]);
        ProductImage::create(['product_id' => $p->id, 'path' => "products/{$slug}/1.jpg", 'is_primary' => true, 'sort_order' => 1]);

        return $p;
    }

    public function test_category_shows_intro(): void
    {
        $cat = Category::create(['name' => 'Bănci', 'slug' => 'banci-sezut', 'intro' => 'Bănci stradale rezistente pentru parcuri și primării.', 'sort_order' => 1, 'is_active' => true]);
        $this->product('banca-1', ['material' => ['lemn', 'metal']])->categories()->attach($cat, ['is_primary' => true]);

        $this->get('/categorie/banci-sezut')
            ->assertOk()
            ->assertSee('Bănci stradale rezistente pentru parcuri și primării.');
    }

    public function test_card_shows_material_chip(): void
    {
        $cat = Category::create(['name' => 'Bănci', 'slug' => 'banci-sezut', 'sort_order' => 1, 'is_active' => true]);
        $this->product('banca-lm', ['material' => ['lemn', 'metal']])->categories()->attach($cat, ['is_primary' => true]);

        $this->get('/categorie/banci-sezut')
            ->assertOk()
            ->assertSee('Lemn + metal');
    }

    public function test_catalog_material_filter(): void
    {
        $cat = Category::create(['name' => 'Cat', 'slug' => 'cat', 'sort_order' => 1, 'is_active' => true]);
        $lemn = $this->product('p-lemn', ['material' => ['lemn']]);
        $inox = $this->product('p-inox', ['material' => ['metal', 'inox']]);
        $lemn->categories()->attach($cat);
        $inox->categories()->attach($cat);

        Livewire::test(CatalogBrowser::class)
            ->set('materials', ['inox'])
            ->assertSee('Produs p-inox')
            ->assertDontSee('Produs p-lemn');
    }

    public function test_product_specs_table_only_present_fields(): void
    {
        $cat = Category::create(['name' => 'Bănci', 'slug' => 'banci-sezut', 'sort_order' => 1, 'is_active' => true]);
        $p = $this->product('banca-specs', ['material' => ['lemn', 'metal'], 'dimensiuni' => ['1800x90x45mm']]);
        $p->categories()->attach($cat, ['is_primary' => true]);

        $html = $this->get('/produs/banca-specs')->assertOk()
            ->assertSee('Specificații')
            ->assertSee('Dimensiuni')
            ->assertSee('1800x90x45mm')
            ->getContent();

        // Montaj/Finisaj absente (nu există în specs) — fără fabricare.
        $this->assertStringNotContainsString('<dt class="w-32 shrink-0 font-medium text-ink-muted">Montaj</dt>', $html);
    }

    public function test_product_jsonld_has_material_and_additional_property(): void
    {
        $cat = Category::create(['name' => 'Bănci', 'slug' => 'banci-sezut', 'sort_order' => 1, 'is_active' => true]);
        $p = $this->product('banca-jsonld', ['material' => ['lemn', 'metal'], 'dimensiuni' => ['1800x90x45mm'], 'finisaj' => ['vopsit electrostatic']]);
        $p->categories()->attach($cat, ['is_primary' => true]);

        $ld = JsonLd::product($p->fresh());

        $this->assertSame('Lemn + metal', $ld['material']);
        $this->assertNotEmpty($ld['additionalProperty']);
        $names = array_column($ld['additionalProperty'], 'name');
        $this->assertContains('Material', $names);
        $this->assertContains('Dimensiuni', $names);
        $this->assertContains('Finisaj', $names);
        $this->assertArrayNotHasKey('offers', $ld); // fără Offer / preț fals
    }
}
