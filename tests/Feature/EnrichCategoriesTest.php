<?php

namespace Tests\Feature;

use App\Models\Category;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\Http;
use Tests\TestCase;

class EnrichCategoriesTest extends TestCase
{
    use RefreshDatabase;

    public function test_generates_intro_for_category(): void
    {
        config(['services.gemini.key' => 'test-key']);
        Http::fake([
            'generativelanguage.googleapis.com/*' => Http::response([
                'candidates' => [['content' => ['parts' => [['text' => 'Bănci stradale pentru parcuri și spații publice, cu dimensiuni la cerere.']]], 'finishReason' => 'STOP']],
            ], 200),
        ]);

        $cat = Category::create(['name' => 'Bănci & șezut', 'slug' => 'banci-sezut', 'sort_order' => 1, 'is_active' => true]);

        Artisan::call('catalog:enrich-categories', ['--only' => 'banci-sezut']);

        $this->assertStringContainsString('parcuri', $cat->refresh()->intro);
    }

    public function test_skips_when_intro_exists_without_force(): void
    {
        config(['services.gemini.key' => 'test-key']);
        Http::fake(); // orice apel HTTP ar eșua testul dacă s-ar face

        $cat = Category::create(['name' => 'Coșuri', 'slug' => 'cosuri-de-gunoi', 'intro' => 'Intro existent.', 'sort_order' => 2, 'is_active' => true]);

        Artisan::call('catalog:enrich-categories', ['--only' => 'cosuri-de-gunoi']);

        $this->assertSame('Intro existent.', $cat->refresh()->intro);
        Http::assertNothingSent();
    }
}
