<?php

namespace Tests\Feature;

use App\Models\Category;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class MobileNavTest extends TestCase
{
    use RefreshDatabase;

    public function test_mobile_menu_renders_categories_grid_and_sticky_cta(): void
    {
        Category::create(['name' => 'Bănci', 'slug' => 'banci-sezut', 'sort_order' => 1, 'is_active' => true]);
        Category::create(['name' => 'Coșuri', 'slug' => 'cosuri-de-gunoi', 'sort_order' => 2, 'is_active' => true]);

        $html = $this->get('/')->assertOk()->getContent();

        // Panou scrollabil + grilă 2 coloane + ambele categorii linkate.
        $this->assertStringContainsString('id="mobile-menu"', $html);
        $this->assertStringContainsString('grid-cols-2', $html);
        $this->assertStringContainsString('overflow-y-auto', $html);
        $this->assertStringContainsString('max-h-[calc(100dvh-4rem)]', $html);
        $this->assertStringContainsString(route('category', 'banci-sezut'), $html);
        $this->assertStringContainsString(route('category', 'cosuri-de-gunoi'), $html);
        // CTA WhatsApp prezent în panou.
        $this->assertStringContainsString('Cere ofertă pe WhatsApp', $html);
    }
}
