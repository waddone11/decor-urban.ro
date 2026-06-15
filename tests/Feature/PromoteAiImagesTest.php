<?php

namespace Tests\Feature;

use App\Models\Product;
use App\Models\ProductImage;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\File;
use Illuminate\Support\Facades\Storage;
use Tests\TestCase;

class PromoteAiImagesTest extends TestCase
{
    use RefreshDatabase;

    private string $slug = 'test-promote-banca-ai';

    private function stagingDir(): string
    {
        return storage_path('scrape/images-ai/'.$this->slug);
    }

    protected function tearDown(): void
    {
        // curăță fișierele de staging reale create de test
        File::deleteDirectory(storage_path('scrape/images-ai/'.$this->slug));
        parent::tearDown();
    }

    private function makeProductWithImage(string $file = '1.jpg'): ProductImage
    {
        $product = Product::create(['slug' => $this->slug, 'name' => 'Bancă test']);

        return ProductImage::create([
            'product_id' => $product->id,
            'path' => "products/{$this->slug}/{$file}",
            'alt' => 'Bancă test',
            'sort_order' => 0,
            'is_primary' => true,
        ]);
    }

    private function makeStagingFile(string $file, string $bytes): void
    {
        File::ensureDirectoryExists($this->stagingDir());
        File::put($this->stagingDir().'/'.$file, $bytes);
    }

    public function test_promote_copies_to_public_disk_and_marks_row_ai(): void
    {
        Storage::fake('public');
        $image = $this->makeProductWithImage('1.jpg');
        $this->makeStagingFile('1.jpg', 'AI-BYTES');

        $this->assertSame('legacy', $image->fresh()->source, 'default DB = legacy');
        $this->assertNull($image->fresh()->enhanced_at);

        $exit = Artisan::call('images:promote-ai', ['--only' => $this->slug]);

        $this->assertSame(0, $exit);
        Storage::disk('public')->assertExists("products/{$this->slug}/1.jpg");
        $this->assertSame('AI-BYTES', Storage::disk('public')->get("products/{$this->slug}/1.jpg"));

        $image->refresh();
        $this->assertSame('ai', $image->source);
        $this->assertNotNull($image->enhanced_at);
    }

    public function test_promote_is_idempotent(): void
    {
        Storage::fake('public');
        $image = $this->makeProductWithImage('1.jpg');
        $this->makeStagingFile('1.jpg', 'AI-BYTES');

        Artisan::call('images:promote-ai', ['--only' => $this->slug]);
        Artisan::call('images:promote-ai', ['--only' => $this->slug]);

        $image->refresh();
        $this->assertSame('ai', $image->source);
        $this->assertSame(1, ProductImage::where('source', 'ai')->count());
    }

    public function test_dry_run_does_not_write_or_update(): void
    {
        Storage::fake('public');
        $image = $this->makeProductWithImage('1.jpg');
        $this->makeStagingFile('1.jpg', 'AI-BYTES');

        $exit = Artisan::call('images:promote-ai', ['--only' => $this->slug, '--dry-run' => true]);

        $this->assertSame(0, $exit);
        Storage::disk('public')->assertMissing("products/{$this->slug}/1.jpg");
        $image->refresh();
        $this->assertSame('legacy', $image->source);
        $this->assertNull($image->enhanced_at);
    }

    public function test_only_targets_a_single_slug(): void
    {
        Storage::fake('public');
        $this->makeProductWithImage('1.jpg');
        $this->makeStagingFile('1.jpg', 'AI-BYTES');

        // alt slug în staging, dar nu trebuie promovat
        $other = Product::create(['slug' => 'alt-slug-x', 'name' => 'Alt']);
        ProductImage::create([
            'product_id' => $other->id,
            'path' => 'products/alt-slug-x/1.jpg',
            'sort_order' => 0,
        ]);
        File::ensureDirectoryExists(storage_path('scrape/images-ai/alt-slug-x'));
        File::put(storage_path('scrape/images-ai/alt-slug-x/1.jpg'), 'OTHER');

        Artisan::call('images:promote-ai', ['--only' => $this->slug]);

        $this->assertSame('ai', ProductImage::where('path', "products/{$this->slug}/1.jpg")->value('source'));
        $this->assertSame('legacy', ProductImage::where('path', 'products/alt-slug-x/1.jpg')->value('source'));

        File::deleteDirectory(storage_path('scrape/images-ai/alt-slug-x'));
    }
}
