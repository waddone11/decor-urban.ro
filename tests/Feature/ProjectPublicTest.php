<?php

namespace Tests\Feature;

use App\Models\Project;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class ProjectPublicTest extends TestCase
{
    use RefreshDatabase;

    public function test_proiecte_empty_state_when_no_published(): void
    {
        Project::create(['title' => 'Nepublicat', 'slug' => 'nepublicat', 'is_published' => false, 'sort_order' => 1]);

        $this->get('/proiecte')
            ->assertOk()
            ->assertSee('Adăugăm în curând')
            ->assertDontSee('Nepublicat');
    }

    public function test_proiecte_grid_shows_published(): void
    {
        $pub = Project::create(['title' => 'Parc Slatina', 'slug' => 'parc-slatina', 'location' => 'Primăria Slatina', 'is_published' => true, 'sort_order' => 1]);
        $draft = Project::create(['title' => 'Secret', 'slug' => 'secret', 'is_published' => false, 'sort_order' => 2]);

        $this->get('/proiecte')
            ->assertOk()
            ->assertSee('Parc Slatina')
            ->assertSee('Primăria Slatina')
            ->assertDontSee('Secret');
    }

    public function test_published_project_detail_renders(): void
    {
        $p = Project::create(['title' => 'Parc Slatina', 'slug' => 'parc-slatina', 'body' => '<p>Am montat 20 bănci.</p>', 'is_published' => true, 'sort_order' => 1]);

        $this->get('/proiecte/parc-slatina')
            ->assertOk()
            ->assertSee('Parc Slatina')
            ->assertSee('Am montat 20 bănci', false);
    }

    public function test_unpublished_and_unknown_project_404(): void
    {
        Project::create(['title' => 'Draft', 'slug' => 'draft', 'is_published' => false, 'sort_order' => 1]);

        $this->get('/proiecte/draft')->assertNotFound();
        $this->get('/proiecte/nu-exista')->assertNotFound();
    }

    public function test_published_projects_in_sitemap(): void
    {
        Project::create(['title' => 'Parc Slatina', 'slug' => 'parc-slatina', 'is_published' => true, 'sort_order' => 1]);
        Project::create(['title' => 'Draft', 'slug' => 'draft', 'is_published' => false, 'sort_order' => 2]);

        $this->get('/sitemap.xml')->assertOk()->assertSee(url('/sitemaps/pages.xml'), false);
        $res = $this->get('/sitemaps/pages.xml')->assertOk();
        $res->assertSee(route('project.show', 'parc-slatina'), false);
        $res->assertDontSee(route('project.show', 'draft'), false);
        $res->assertSee(route('institutii'), false);
    }
}
