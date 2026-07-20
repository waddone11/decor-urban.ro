<?php

namespace Tests\Feature;

use App\Models\Product;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class NormalizeDescriptionsTest extends TestCase
{
    use RefreshDatabase;

    private function makeProduct(array $attrs): Product
    {
        return Product::create(array_merge([
            'name' => 'Produs', 'slug' => 'produs-'.uniqid(), 'price_on_request' => true,
            'is_active' => true, 'sort_order' => 1,
        ], $attrs));
    }

    public function test_plain_text_paragraphs_become_p_blocks(): void
    {
        $p = $this->makeProduct(['description' => "Primul paragraf.\n\nAl doilea paragraf."]);

        $this->artisan('catalog:normalize-descriptions')->assertSuccessful();

        $this->assertSame('<p>Primul paragraf.</p><p>Al doilea paragraf.</p>', $p->fresh()->description);
    }

    public function test_collapsed_richeditor_html_is_rebuilt_from_draft(): void
    {
        // RichEditor a lipit paragrafele draftului într-un singur <p> fără spații.
        $p = $this->makeProduct([
            'description' => '<p>Primul paragraf.Al doilea paragraf.</p>',
            'description_draft' => "Primul paragraf.\n\nAl doilea paragraf.",
            'description_source' => 'ai',
        ]);

        $this->artisan('catalog:normalize-descriptions')->assertSuccessful();

        $this->assertSame('<p>Primul paragraf.</p><p>Al doilea paragraf.</p>', $p->fresh()->description);
    }

    public function test_html_edited_by_admin_is_left_untouched(): void
    {
        // Textul din description NU mai corespunde draftului → a fost editat manual.
        $edited = '<p>Text modificat manual de admin.</p>';
        $p = $this->makeProduct([
            'description' => $edited,
            'description_draft' => "Alt text.\n\nCu totul diferit.",
            'description_source' => 'ai',
        ]);

        $this->artisan('catalog:normalize-descriptions')->assertSuccessful();

        $this->assertSame($edited, $p->fresh()->description);
    }

    public function test_single_paragraph_plain_text_is_wrapped(): void
    {
        $p = $this->makeProduct(['description' => 'O singură propoziție.']);

        $this->artisan('catalog:normalize-descriptions')->assertSuccessful();

        $this->assertSame('<p>O singură propoziție.</p>', $p->fresh()->description);
    }

    public function test_dry_run_changes_nothing(): void
    {
        $p = $this->makeProduct(['description' => "Unu.\n\nDoi."]);

        $this->artisan('catalog:normalize-descriptions --dry-run')->assertSuccessful();

        $this->assertSame("Unu.\n\nDoi.", $p->fresh()->description);
    }

    public function test_empty_descriptions_are_skipped(): void
    {
        $p = $this->makeProduct(['description' => null]);

        $this->artisan('catalog:normalize-descriptions')->assertSuccessful();

        $this->assertNull($p->fresh()->description);
    }
}
