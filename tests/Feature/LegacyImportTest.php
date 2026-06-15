<?php

namespace Tests\Feature;

use App\Models\Category;
use App\Models\Product;
use App\Models\ProductImage;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;
use Tests\TestCase;

class LegacyImportTest extends TestCase
{
    use RefreshDatabase;

    public function test_import_loads_full_catalog_with_correct_counts(): void
    {
        Artisan::call('import:legacy');

        $this->assertSame(9, Category::count(), '9 categorii noi');
        $this->assertSame(127, Product::count(), '127 produse unice');
        $this->assertSame(195, ProductImage::count(), '195 imagini');
        $this->assertSame(136, DB::table('category_product')->count(), '136 rânduri pivot (118×1 + 9×2)');
    }

    public function test_cross_listed_product_belongs_to_two_categories_with_one_primary(): void
    {
        Artisan::call('import:legacy');

        $sm200 = Product::where('code', '#SM200')->sole();
        $slugs = $sm200->categories()->pluck('slug')->sort()->values()->all();
        $this->assertSame(['banci-sezut', 'cosuri-de-gunoi'], $slugs);

        $primaryCount = DB::table('category_product')
            ->where('product_id', $sm200->id)
            ->where('is_primary', true)
            ->count();
        $this->assertSame(1, $primaryCount, 'exact o categorie principală');
    }

    public function test_duplicate_codes_keep_distinct_products(): void
    {
        Artisan::call('import:legacy');

        // Codul nu e unic: ambele produse distincte trebuie să existe.
        $b201 = Product::where('code', '#B201')->pluck('slug');
        $this->assertCount(2, $b201);
        $this->assertSame(2, $b201->unique()->count(), '#B201 pe 2 sluguri distincte');

        $ps100 = Product::where('code', '#PS100')->pluck('slug');
        $this->assertCount(2, $ps100);
        $this->assertSame(2, $ps100->unique()->count(), '#PS100 pe 2 sluguri distincte');
    }

    public function test_products_without_description_import_as_null(): void
    {
        Artisan::call('import:legacy');

        $this->assertSame(5, Product::whereNull('description')->count(), '5 produse fără descriere = null');
    }

    public function test_all_products_are_price_on_request(): void
    {
        Artisan::call('import:legacy');

        $this->assertSame(
            127,
            Product::where('price_on_request', true)->whereNull('price')->count(),
            'toate produsele: price=null, price_on_request=true'
        );
    }

    public function test_sample_product_images_exist_on_public_disk(): void
    {
        Artisan::call('import:legacy');

        $product = Product::where('slug', 'set-mobilier-stradal-banca-si-cos-gunoi')->sole();
        $this->assertGreaterThan(0, $product->images()->count());

        foreach ($product->images as $image) {
            $this->assertStringStartsWith('products/', $image->path);
            $this->assertTrue(
                Storage::disk('public')->exists($image->path),
                "Fișierul {$image->path} trebuie să existe fizic pe disk-ul public"
            );
        }
    }

    public function test_import_is_idempotent(): void
    {
        Artisan::call('import:legacy');
        Artisan::call('import:legacy');

        $this->assertSame(127, Product::count());
        $this->assertSame(195, ProductImage::count());
        $this->assertSame(136, DB::table('category_product')->count());
    }

    public function test_catalog_summary_runs_and_reports_counts(): void
    {
        Artisan::call('import:legacy');

        $exit = Artisan::call('catalog:summary');
        $output = Artisan::output();

        $this->assertSame(0, $exit);
        $this->assertStringContainsString('Produse total:        127', $output);
        $this->assertStringContainsString('Imagini total:        195', $output);
        $this->assertStringContainsString('Diverse & custom', $output);
    }
}
