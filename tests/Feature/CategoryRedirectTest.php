<?php

namespace Tests\Feature;

use App\Support\LegacyRedirects;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class CategoryRedirectTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();
        LegacyRedirects::flush();
    }

    public function test_nested_old_category_url_redirects_by_last_segment(): void
    {
        // Forma nested: parent vechi + segment categorie.
        $this->get('/mobilier-stradal-foarte-lung-pergole/banci-stradale-si-mobilier-urban')
            ->assertStatus(301)
            ->assertRedirect('/categorie/banci-sezut');
    }

    public function test_flat_old_category_url_redirects(): void
    {
        $this->get('/cosuri-de-gunoi-stradale-si-mobilier-stradal')
            ->assertStatus(301)
            ->assertRedirect('/categorie/cosuri-de-gunoi');
    }

    public function test_multiple_old_categories_map_to_same_new_slug(): void
    {
        $this->get('/totemuri-panouri-cu-mesaje-permanente')
            ->assertStatus(301)
            ->assertRedirect('/categorie/placute-totemuri');

        $this->get('/placute-numere-casa')
            ->assertStatus(301)
            ->assertRedirect('/categorie/placute-totemuri');
    }

    public function test_old_parent_pages_redirect(): void
    {
        $this->get('/mobilier-stradal-si-mobilier-urban')
            ->assertStatus(301)
            ->assertRedirect('/catalog');

        $this->get('/producator-mobilier-stradal')
            ->assertStatus(301)
            ->assertRedirect('/despre');
    }

    public function test_unknown_path_still_404(): void
    {
        $this->get('/habar-n-am-ce-i-asta')->assertNotFound();
    }

    public function test_static_pages_in_sitemap(): void
    {
        $res = $this->get('/sitemap.xml')->assertOk();
        $res->assertSee(route('despre'), false);
        $res->assertSee(route('contact'), false);
        $res->assertSee(route('confidentialitate'), false);
        $res->assertSee(route('termeni'), false);
        $res->assertSee(route('politica-cookies'), false);
    }
}
