<?php

namespace Tests\Feature;

use App\Models\Product;
use App\Support\LegacyRedirects;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class LegacyRedirectTest extends TestCase
{
    use RefreshDatabase;

    public function test_known_legacy_product_url_redirects_301(): void
    {
        Product::create([
            'name' => 'Coș stradal C120', 'slug' => 'cos-c120', 'code' => '#C120',
            'price_on_request' => true, 'is_active' => true, 'sort_order' => 1,
            'legacy_urls' => ['https://www.mobilier-stradal.ro/cos-de-gunoi-stradal-c120'],
        ]);
        LegacyRedirects::flush();

        $this->get('/cos-de-gunoi-stradal-c120')
            ->assertStatus(301)
            ->assertRedirect('/produs/cos-c120');
    }

    public function test_legacy_redirect_matches_path_only(): void
    {
        Product::create([
            'name' => 'Bancă B201', 'slug' => 'banca-b201',
            'price_on_request' => true, 'is_active' => true, 'sort_order' => 1,
            'legacy_urls' => ['/produse/banca-stradala-b201/'],
        ]);
        LegacyRedirects::flush();

        // Aceeași cale, cu/ fără slash final, indiferent de domeniu.
        $this->get('/produse/banca-stradala-b201')
            ->assertStatus(301)
            ->assertRedirect('/produs/banca-b201');
    }

    public function test_inactive_product_legacy_url_is_not_redirected(): void
    {
        Product::create([
            'name' => 'Ascuns', 'slug' => 'ascuns',
            'price_on_request' => true, 'is_active' => false, 'sort_order' => 1,
            'legacy_urls' => ['/produs-vechi-ascuns'],
        ]);
        LegacyRedirects::flush();

        $this->get('/produs-vechi-ascuns')->assertNotFound();
    }

    public function test_category_redirect_from_config_map(): void
    {
        config(['legacy_category_map.redirect_url_map' => ['/banci-stradale' => 'banci-sezut']]);
        LegacyRedirects::flush();

        $this->get('/banci-stradale')
            ->assertStatus(301)
            ->assertRedirect('/categorie/banci-sezut');
    }

    public function test_unknown_path_returns_404(): void
    {
        LegacyRedirects::flush();

        $this->get('/chiar-nu-exista-nimic-aici')->assertNotFound();
    }
}
