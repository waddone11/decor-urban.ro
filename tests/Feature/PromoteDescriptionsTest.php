<?php

namespace Tests\Feature;

use App\Models\Product;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Artisan;
use Tests\TestCase;

class PromoteDescriptionsTest extends TestCase
{
    use RefreshDatabase;

    private function product(): Product
    {
        return Product::create([
            'name' => 'Bancă B202', 'slug' => 'banca-b202', 'code' => '#B202',
            'description' => 'Descriere veche scrapată.',
            'description_draft' => 'Descriere îmbogățită AI, grounded.',
            'description_source' => 'legacy',
            'price_on_request' => true, 'is_active' => true, 'sort_order' => 1,
        ]);
    }

    public function test_promote_copies_draft_and_backs_up_legacy(): void
    {
        $p = $this->product();

        Artisan::call('catalog:promote-descriptions');

        $p->refresh();
        $this->assertSame('Descriere îmbogățită AI, grounded.', $p->description);
        $this->assertSame('Descriere veche scrapată.', $p->legacy_description);
        $this->assertSame('ai', $p->description_source);
    }

    public function test_promote_is_idempotent_and_preserves_original_legacy(): void
    {
        $p = $this->product();

        Artisan::call('catalog:promote-descriptions');
        // A doua rulare nu trebuie să suprascrie legacy cu descrierea deja promovată.
        Artisan::call('catalog:promote-descriptions');

        $p->refresh();
        $this->assertSame('Descriere veche scrapată.', $p->legacy_description, 'legacy original păstrat');
        $this->assertSame('Descriere îmbogățită AI, grounded.', $p->description);
    }

    public function test_revert_restores_legacy(): void
    {
        $p = $this->product();
        Artisan::call('catalog:promote-descriptions');

        Artisan::call('catalog:revert-descriptions');

        $p->refresh();
        $this->assertSame('Descriere veche scrapată.', $p->description);
        $this->assertSame('legacy', $p->description_source);
        // Draftul rămâne pentru re-promovare.
        $this->assertSame('Descriere îmbogățită AI, grounded.', $p->description_draft);
    }

    public function test_only_flag_targets_single_product(): void
    {
        $a = $this->product();
        $b = Product::create([
            'name' => 'Coș C120', 'slug' => 'cos-c120', 'description' => 'Veche B',
            'description_draft' => 'Nouă B', 'description_source' => 'legacy',
            'price_on_request' => true, 'is_active' => true, 'sort_order' => 2,
        ]);

        Artisan::call('catalog:promote-descriptions', ['--only' => 'banca-b202']);

        $this->assertSame('ai', $a->refresh()->description_source);
        $this->assertSame('legacy', $b->refresh()->description_source, 'celălalt produs neatins');
    }
}
