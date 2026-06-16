<?php

namespace Tests\Feature;

use App\Models\Product;
use App\Models\ProductImage;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\File;
use Illuminate\Support\Facades\Storage;
use Tests\TestCase;

class RevertAiImagesTest extends TestCase
{
    use RefreshDatabase;

    private string $slug = 'test-revert-banca-ai';

    protected function tearDown(): void
    {
        File::deleteDirectory(storage_path('scrape/images/'.$this->slug));
        parent::tearDown();
    }

    private function makeAiImage(string $file = '1.jpg'): ProductImage
    {
        $product = Product::create(['slug' => $this->slug, 'name' => 'Bancă test']);

        return ProductImage::create([
            'product_id' => $product->id,
            'path' => "products/{$this->slug}/{$file}",
            'sort_order' => 0,
            'source' => 'ai',
            'enhanced_at' => Carbon::now(),
        ]);
    }

    private function makePristineSource(string $file, string $bytes): void
    {
        $dir = storage_path('scrape/images/'.$this->slug);
        File::ensureDirectoryExists($dir);
        File::put($dir.'/'.$file, $bytes);
    }

    public function test_revert_restores_pristine_original_and_marks_legacy(): void
    {
        Storage::fake('public');
        $image = $this->makeAiImage('1.jpg');
        Storage::disk('public')->put("products/{$this->slug}/1.jpg", 'AI-BYTES');
        $this->makePristineSource('1.jpg', 'ORIGINAL-BYTES');

        $exit = Artisan::call('images:revert-ai', ['--only' => $this->slug]);

        $this->assertSame(0, $exit);
        $this->assertSame('ORIGINAL-BYTES', Storage::disk('public')->get("products/{$this->slug}/1.jpg"));
        $image->refresh();
        $this->assertSame('legacy', $image->source);
        $this->assertNull($image->enhanced_at);
    }

    public function test_revert_skips_when_no_pristine_source(): void
    {
        Storage::fake('public');
        $image = $this->makeAiImage('1.jpg');
        Storage::disk('public')->put("products/{$this->slug}/1.jpg", 'AI-BYTES');
        // fără sursă pristină → rândul rămâne neschimbat

        Artisan::call('images:revert-ai', ['--only' => $this->slug]);

        $image->refresh();
        $this->assertSame('ai', $image->source, 'fără original pristin, nu revertim');
        $this->assertSame('AI-BYTES', Storage::disk('public')->get("products/{$this->slug}/1.jpg"));
    }

    public function test_dry_run_does_not_change_anything(): void
    {
        Storage::fake('public');
        $image = $this->makeAiImage('1.jpg');
        Storage::disk('public')->put("products/{$this->slug}/1.jpg", 'AI-BYTES');
        $this->makePristineSource('1.jpg', 'ORIGINAL-BYTES');

        Artisan::call('images:revert-ai', ['--only' => $this->slug, '--dry-run' => true]);

        $image->refresh();
        $this->assertSame('ai', $image->source);
        $this->assertSame('AI-BYTES', Storage::disk('public')->get("products/{$this->slug}/1.jpg"));
    }
}
