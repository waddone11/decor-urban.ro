<?php

namespace Tests\Feature;

use App\Models\Product;
use App\Support\SpecsExtractor;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Artisan;
use Tests\TestCase;

class ExtractSpecsTest extends TestCase
{
    use RefreshDatabase;

    public function test_extracts_dimensions_and_materials_from_real_shape(): void
    {
        $specs = SpecsExtractor::fromText('Banca de parc cu picioare din teava rotunda Φ26,9 x 2,6mm si elemente din lemn de rasinoasa 1800x90x45mm.');

        $this->assertContains('1800x90x45mm', $specs['dimensiuni']);
        $this->assertContains('Φ26,9x2,6mm', $specs['dimensiuni']);
        $this->assertContains('lemn', $specs['material']);
        $this->assertContains('metal', $specs['material']);
    }

    public function test_detects_finisaj_electrostatic_and_lacuit(): void
    {
        $specs = SpecsExtractor::fromText('Coș metalic vopsit electrostatic, lemn lăcuit.');
        $this->assertContains('vopsit electrostatic', $specs['finisaj']);
        $this->assertContains('lăcuit', $specs['finisaj']);
    }

    public function test_invents_nothing_when_source_is_thin(): void
    {
        $specs = SpecsExtractor::fromText('Produs frumos pentru oraș.');

        // Niciun spec — fără fabricare.
        $this->assertArrayNotHasKey('dimensiuni', $specs);
        $this->assertArrayNotHasKey('material', $specs);
        $this->assertSame([], $specs);
    }

    public function test_command_populates_specs_and_leaves_thin_null(): void
    {
        $rich = Product::create([
            'name' => 'Bancă B202', 'slug' => 'banca-b202', 'code' => '#B202',
            'description' => 'Picioare din teava rotunda Φ26,9 x 2,6mm si lemn de rasinoasa 1800x90x45mm.',
            'price_on_request' => true, 'is_active' => true, 'sort_order' => 1,
        ]);
        $thin = Product::create([
            'name' => 'Produs vag', 'slug' => 'produs-vag',
            'description' => 'Ceva drăguț.', 'price_on_request' => true, 'is_active' => true, 'sort_order' => 2,
        ]);

        Artisan::call('catalog:extract-specs');

        $rich->refresh();
        $this->assertNotNull($rich->specs);
        $this->assertContains('lemn', $rich->specs['material']);
        $this->assertContains('1800x90x45mm', $rich->specs['dimensiuni']);

        $this->assertNull($thin->refresh()->specs, 'Sursă subțire → specs null, nu fabricat');
    }
}
