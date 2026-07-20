<?php

namespace Tests\Feature;

use App\Models\Category;
use App\Models\Product;
use App\Support\SafeHtml;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class DescriptionHtmlTest extends TestCase
{
    use RefreshDatabase;

    private function makeProduct(string $description): Product
    {
        $cat = Category::create(['name' => 'Bănci', 'slug' => 'banci', 'sort_order' => 1, 'is_active' => true]);

        $product = Product::create([
            'name' => 'Bancă stradală B1', 'slug' => 'banca-b1', 'code' => '#B1',
            'description' => $description,
            'price_on_request' => true, 'is_active' => true, 'sort_order' => 1,
        ]);
        $product->categories()->attach($cat, ['is_primary' => true]);

        return $product;
    }

    // ── SafeHtml (unit) ──────────────────────────────────────────────────────

    public function test_plain_text_is_escaped_and_gets_br_for_newlines(): void
    {
        $html = (string) SafeHtml::render("Primul paragraf.\n\nAl doilea paragraf.");

        $this->assertStringContainsString('<br', $html);
        $this->assertStringNotContainsString('<p>', $html);
    }

    public function test_html_description_keeps_whitelisted_tags(): void
    {
        $html = (string) SafeHtml::render('<p>Un <strong>paragraf</strong>.</p><ul><li>una</li></ul>');

        $this->assertStringContainsString('<p>Un <strong>paragraf</strong>.</p>', $html);
        $this->assertStringContainsString('<li>una</li>', $html);
    }

    public function test_script_and_disallowed_tags_are_removed(): void
    {
        $html = (string) SafeHtml::render('<p>Text</p><script>alert(1)</script><img src=x onerror=alert(1)>');

        $this->assertStringNotContainsString('<script', $html);
        $this->assertStringNotContainsString('<img', $html);
        $this->assertStringNotContainsString('onerror', $html);
    }

    public function test_attributes_are_stripped_from_allowed_tags(): void
    {
        $html = (string) SafeHtml::render('<p onclick="alert(1)" style="color:red">Text</p>');

        $this->assertSame('<p>Text</p>', $html);
    }

    public function test_empty_description_renders_null(): void
    {
        $this->assertNull(SafeHtml::render(null));
        $this->assertNull(SafeHtml::render('   '));
    }

    // ── Pagina produs (integrare) ────────────────────────────────────────────

    public function test_product_page_renders_html_description_not_literal_tags(): void
    {
        $this->makeProduct('<p>Primul paragraf.</p><p>Al doilea paragraf.</p>');

        $res = $this->get('/produs/banca-b1')->assertOk();

        $res->assertSee('<p>Primul paragraf.</p>', false);
        $res->assertDontSee('&lt;p&gt;', false);
    }

    public function test_product_page_still_renders_plain_text_descriptions(): void
    {
        $this->makeProduct("Primul paragraf.\n\nAl doilea paragraf.");

        $res = $this->get('/produs/banca-b1')->assertOk();

        $res->assertSee('Primul paragraf.');
        $res->assertSee('<br', false);
        $res->assertDontSee('&lt;', false);
    }

    public function test_product_page_never_renders_script_from_description(): void
    {
        $this->makeProduct('<p>Ok</p><script>alert(1)</script>');

        $this->get('/produs/banca-b1')->assertOk()->assertDontSee('<script>alert(1)</script>', false);
    }

    // ── Meta / SEO: fără tag-uri ─────────────────────────────────────────────

    public function test_seo_description_strips_html_tags(): void
    {
        $product = $this->makeProduct('<p>Un <strong>paragraf</strong> descriptiv.</p>');

        $this->assertStringNotContainsString('<', $product->seoDescription());
        $this->assertStringContainsString('Un paragraf descriptiv.', $product->seoDescription());
    }

    public function test_meta_description_on_page_has_no_tags(): void
    {
        $this->makeProduct('<p>Un paragraf descriptiv pentru meta.</p>');

        $res = $this->get('/produs/banca-b1')->assertOk();

        $res->assertDontSee('content="&lt;p&gt;', false);
        $res->assertSee('Un paragraf descriptiv pentru meta.', false);
    }
}
