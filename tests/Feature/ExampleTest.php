<?php

namespace Tests\Feature;

use Database\Seeders\CategorySeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class ExampleTest extends TestCase
{
    use RefreshDatabase;

    public function test_homepage_renders_with_categories(): void
    {
        $this->seed(CategorySeeder::class);

        $response = $this->get('/');

        $response->assertStatus(200);
        $response->assertSee('Mobilier stradal care durează.');
        $response->assertSee('Explorează pe categorii');
        // Categoriile reale din DB apar în header/secțiunea de categorii.
        $response->assertSee('Bănci &amp; șezut', false);
        $response->assertSee('Sport &amp; stadion', false);
    }

    public function test_homepage_renders_with_empty_database(): void
    {
        // Fără date (fără categorii/produse) pagina nu trebuie să crape.
        $this->get('/')->assertStatus(200);
    }
}
