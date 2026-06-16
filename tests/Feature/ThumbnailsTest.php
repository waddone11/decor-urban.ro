<?php

namespace Tests\Feature;

use App\Models\Product;
use App\Models\ProductImage;
use App\Models\Project;
use App\Models\ProjectImage;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\Storage;
use Tests\TestCase;

class ThumbnailsTest extends TestCase
{
    use RefreshDatabase;

    /** Scrie un PNG real (non-pătrat) în disk-ul public fake. */
    private function putPng(string $rel, int $w = 300, int $h = 200): void
    {
        $abs = Storage::disk('public')->path($rel);
        @mkdir(dirname($abs), 0775, true);
        $im = imagecreatetruecolor($w, $h);
        imagefilledrectangle($im, 0, 0, $w, $h, imagecolorallocate($im, 200, 30, 30));
        imagepng($im, $abs);
        imagedestroy($im);
    }

    public function test_generates_square_webp_variants_for_products_and_projects(): void
    {
        Storage::fake('public');

        $product = Product::create(['slug' => 'banca-x', 'name' => 'Bancă X']);
        ProductImage::create(['product_id' => $product->id, 'path' => 'products/banca-x/1.png', 'sort_order' => 0, 'is_primary' => true]);
        $this->putPng('products/banca-x/1.png');

        $project = Project::create(['slug' => 'parc-y', 'title' => 'Parc Y']);
        ProjectImage::create(['project_id' => $project->id, 'path' => 'projects/parc-y/1.png', 'sort_order' => 0, 'is_primary' => true]);
        $this->putPng('projects/parc-y/1.png', 400, 300);

        $exit = Artisan::call('images:thumbnails');
        $this->assertSame(0, $exit, Artisan::output());

        foreach (['products/banca-x/1', 'projects/parc-y/1'] as $base) {
            Storage::disk('public')->assertExists("{$base}-400.webp");
            Storage::disk('public')->assertExists("{$base}-800.webp");

            [$w4, $h4, $t4] = getimagesize(Storage::disk('public')->path("{$base}-400.webp"));
            $this->assertSame([400, 400, IMAGETYPE_WEBP], [$w4, $h4, $t4], 'varianta 400 e pătrată webp');

            [$w8, $h8, $t8] = getimagesize(Storage::disk('public')->path("{$base}-800.webp"));
            $this->assertSame([800, 800, IMAGETYPE_WEBP], [$w8, $h8, $t8], 'varianta 800 e pătrată webp');
        }

        // Nu atinge originalul.
        Storage::disk('public')->assertExists('products/banca-x/1.png');
    }

    public function test_is_idempotent_skips_existing_without_force(): void
    {
        Storage::fake('public');
        $product = Product::create(['slug' => 'banca-x', 'name' => 'Bancă X']);
        ProductImage::create(['product_id' => $product->id, 'path' => 'products/banca-x/1.png', 'sort_order' => 0, 'is_primary' => true]);
        $this->putPng('products/banca-x/1.png');

        Artisan::call('images:thumbnails');
        Artisan::call('images:thumbnails');
        $second = Artisan::output();

        $this->assertStringContainsString('Variante generate: 0', $second);
        $this->assertStringContainsString('Sărite (existau):  2', $second);
    }

    public function test_thumb_url_falls_back_to_original_when_variant_missing(): void
    {
        Storage::fake('public');
        $product = Product::create(['slug' => 'banca-x', 'name' => 'Bancă X']);
        $image = ProductImage::create(['product_id' => $product->id, 'path' => 'products/banca-x/1.png', 'sort_order' => 0, 'is_primary' => true]);
        $this->putPng('products/banca-x/1.png');

        // Fără variantă pe disk → fallback la original.
        $this->assertSame($image->url(), $image->thumbUrl(400));
        $this->assertStringContainsString('1.png', $image->thumbUrl(400));

        // După generare → întoarce varianta.
        Artisan::call('images:thumbnails');
        $this->assertStringContainsString('1-400.webp', $image->thumbUrl(400));
        $this->assertStringContainsString('1-800.webp', $image->thumbUrl(800));
    }
}
