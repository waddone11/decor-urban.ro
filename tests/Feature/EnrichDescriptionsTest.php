<?php

namespace Tests\Feature;

use App\Models\Product;
use App\Support\GeminiText;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\Http;
use Tests\TestCase;

class EnrichDescriptionsTest extends TestCase
{
    use RefreshDatabase;

    private function fakeGemini(string $text): void
    {
        Http::fake([
            'generativelanguage.googleapis.com/*' => Http::response([
                'candidates' => [['content' => ['parts' => [['text' => $text]]], 'finishReason' => 'STOP']],
            ], 200),
        ]);
    }

    public function test_client_returns_generated_text(): void
    {
        $this->fakeGemini('Text generat.');
        $this->assertSame('Text generat.', (new GeminiText('k', 'gemini-3.5-flash'))->generate('prompt'));
    }

    public function test_writes_draft_not_live_description(): void
    {
        config(['services.gemini.key' => 'test-key']);
        $this->fakeGemini('Descriere îmbogățită de test pentru bancă.');

        $p = Product::create([
            'name' => 'Bancă B202', 'slug' => 'banca-b202', 'code' => '#B202',
            'description' => 'Descriere veche.', 'price_on_request' => true, 'is_active' => true, 'sort_order' => 1,
        ]);

        Artisan::call('catalog:enrich-descriptions', ['--only' => 'banca-b202']);

        $p->refresh();
        $this->assertSame('Descriere îmbogățită de test pentru bancă.', $p->description_draft);
        $this->assertSame('Descriere veche.', $p->description, 'description live NU se atinge');
        $this->assertSame('legacy', $p->description_source);

        $manifest = json_decode((string) file_get_contents(storage_path('enrich/manifest.json')), true);
        $this->assertSame('ok', $manifest['items']['banca-b202']['status']);
    }

    public function test_generic_marker_is_stripped_and_flagged(): void
    {
        config(['services.gemini.key' => 'test-key']);
        $this->fakeGemini('[GENERIC] Descriere generică de categorie.');

        $p = Product::create([
            'name' => 'Produs vag', 'slug' => 'produs-vag',
            'description' => '', 'price_on_request' => true, 'is_active' => true, 'sort_order' => 1,
        ]);

        Artisan::call('catalog:enrich-descriptions', ['--only' => 'produs-vag']);

        $p->refresh();
        $this->assertSame('Descriere generică de categorie.', $p->description_draft, 'marcajul [GENERIC] e curățat');

        $manifest = json_decode((string) file_get_contents(storage_path('enrich/manifest.json')), true);
        $this->assertTrue($manifest['items']['produs-vag']['generic']);
        $this->assertTrue($manifest['items']['produs-vag']['thin']);
    }

    protected function tearDown(): void
    {
        @unlink(storage_path('enrich/manifest.json'));
        parent::tearDown();
    }
}
