<?php

namespace Tests\Feature;

use Database\Seeders\CategorySeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class ExampleTest extends TestCase
{
    use RefreshDatabase;

    public function test_homepage_renders_animated_hero(): void
    {
        $this->seed(CategorySeeder::class);

        $response = $this->get('/');
        $content = $response->getContent();

        $response->assertStatus(200);

        // Hero: container cu aria-label + ilustrația SVG (draw-on) cu aria-hidden.
        $response->assertSee('id="hero"', false);
        $response->assertSee('aria-label=', false);
        $response->assertSee('hero-draw', false);          // path-uri desenabile
        $response->assertSee('aria-hidden="true"', false); // SVG decorativ
        $response->assertSee('durează.');                  // ultimul cuvânt din H1

        // Stat line trage numerele reale din DB (11 categorii seedate; 0 produse → fallback 127).
        $response->assertSee('11 categorii · 127 produse · livrare în toată țara');

        // Modulul GSAP e enqueued (dev: resources/js/app.js, build: build/assets/app-*.js).
        $this->assertTrue(
            str_contains($content, 'resources/js/app.js') || str_contains($content, 'build/assets/app'),
            'Bundle-ul JS (cu GSAP) trebuie să fie enqueued în pagină.'
        );

        // Mecanismul reduced-motion: clasa .js (gating) + clasele hero-reveal sunt prezente.
        $response->assertSee("classList.add('js')", false);
        $response->assertSee('hero-reveal', false);
    }

    public function test_homepage_renders_with_empty_database(): void
    {
        // Fără date pagina nu trebuie să crape (hero e ilustrație, nu depinde de produse).
        $this->get('/')->assertStatus(200);
    }
}
