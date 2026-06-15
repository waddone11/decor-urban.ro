<?php

namespace Tests\Feature;

use App\Models\Category;
use App\Models\Product;
use App\Models\ProductImage;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class HomepageTest extends TestCase
{
    use RefreshDatabase;

    private function seedCatalog(): Category
    {
        $banci = Category::create([
            'name' => 'Bănci & șezut', 'slug' => 'banci-sezut', 'sort_order' => 1, 'is_active' => true,
        ]);
        Category::create([
            'name' => 'Coșuri de gunoi', 'slug' => 'cosuri-de-gunoi', 'sort_order' => 2, 'is_active' => true,
        ]);

        $product = Product::create([
            'name' => 'Bancă stradală model B201', 'slug' => 'banca-b201', 'code' => '#B201',
            'price_on_request' => true, 'is_active' => true, 'sort_order' => 1,
        ]);
        $product->categories()->attach($banci, ['is_primary' => true]);
        ProductImage::create([
            'product_id' => $product->id, 'path' => 'products/banca-b201/1.jpg',
            'sort_order' => 1, 'is_primary' => true,
        ]);

        return $banci;
    }

    public function test_homepage_renders_ok(): void
    {
        $this->seedCatalog();

        $this->get('/')->assertOk();
    }

    public function test_homepage_renders_new_sales_sections(): void
    {
        $this->seedCatalog();

        $res = $this->get('/');

        // Secțiunile noi orientate pe vânzare (copy real).
        $res->assertSee('Ofertăm pentru achiziții publice și licitații');
        $res->assertSee('Documentație tehnică pentru caietul de sarcini');
        $res->assertSee('Cum lucrăm');
        $res->assertSee('Făcut să stea'); // titlul are „afară” într-un span (underline animat)
        $res->assertSee('ani la rând');
        $res->assertSee('Producem la comanda ta');
        $res->assertSee('Cere ofertă custom');
        $res->assertSee('Întrebări frecvente');
        $res->assertSee('Cum cumpăr prin SEAP/SICAP?');

        // Ancorele de secțiune folosite în nav.
        $res->assertSee('id="institutii"', false);
        $res->assertSee('id="categorii"', false);
        $res->assertSee('id="proces"', false);
        $res->assertSee('id="faq"', false);
    }

    public function test_homepage_has_category_icons_in_grid_and_megamenu(): void
    {
        $this->seedCatalog();

        $res = $this->get('/');

        // Iconițele de categorie (componente refolosibile) sunt randate.
        $res->assertSee('cat-icon', false);
        $res->assertSee('data-draw-on', false);
        // Forma „bănci" din prototip (un path caracteristic).
        $res->assertSee('M9 22 V27', false);
    }

    public function test_homepage_has_animated_process_and_material_icons(): void
    {
        $this->seedCatalog();

        $res = $this->get('/');

        // „Cum lucrăm" — timeline animat cu iconițe pas + punct călător.
        $res->assertSee('data-proces', false);
        $res->assertSee('step-icon', false);
        $res->assertSee('data-point-h', false);
        // „Calitate & materiale" — iconițe material + sweep.
        $res->assertSee('data-quality', false);
        $res->assertSee('material-icon', false);
        $res->assertSee('data-quality-sweep', false);
    }

    public function test_homepage_emits_structured_data(): void
    {
        $this->seedCatalog();

        $res = $this->get('/');

        $res->assertSee('application/ld+json', false);
        $res->assertSee('"@type":"Organization"', false);
        $res->assertSee('"@type":"FAQPage"', false);
        $res->assertSee('"@type":"BreadcrumbList"', false);
        $res->assertSee('"@type":"Product"', false);
    }

    public function test_content_is_complete_without_js_for_reduced_motion(): void
    {
        $this->seedCatalog();

        $res = $this->get('/');

        // Fallback fără JS / reduced-motion: numerele contoarelor sunt în HTML (nu doar animate),
        // iar răspunsul FAQ e prezent în markup (nu injectat de JS).
        $res->assertSee('data-count-to="2"', false); // 2 categorii seedate
        $res->assertSee('Da, factură fiscală și garanție; livrare în toată țara.');
    }
}
