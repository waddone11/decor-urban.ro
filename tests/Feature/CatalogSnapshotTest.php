<?php

namespace Tests\Feature;

use App\Models\Category;
use App\Models\Product;
use App\Models\ProductImage;
use Database\Seeders\CatalogSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Artisan;
use Tests\TestCase;

class CatalogSnapshotTest extends TestCase
{
    use RefreshDatabase;

    private string $tmp;

    protected function setUp(): void
    {
        parent::setUp();
        $this->tmp = sys_get_temp_dir().'/catalog-test-'.getmypid().'.json';
        config(['catalog.snapshot_path' => $this->tmp]);
    }

    protected function tearDown(): void
    {
        @unlink($this->tmp);
        parent::tearDown();
    }

    private function seedSource(): void
    {
        $banci = Category::create(['name' => 'Bănci', 'slug' => 'banci-sezut', 'description' => 'Bănci stradale.', 'sort_order' => 1, 'is_active' => true]);
        $cosuri = Category::create(['name' => 'Coșuri', 'slug' => 'cosuri-de-gunoi', 'sort_order' => 2, 'is_active' => true]);

        $p = Product::create([
            'name' => 'Coș C120', 'slug' => 'cos-c120', 'code' => '#C120', 'description' => 'Rezistent.',
            'price_on_request' => true, 'availability' => 'la comandă', 'is_active' => true, 'sort_order' => 1,
            'legacy_urls' => ['/cos-vechi-c120'],
        ]);
        $p->categories()->attach($cosuri, ['is_primary' => true]);
        $p->categories()->attach($banci, ['is_primary' => false]);
        ProductImage::create(['product_id' => $p->id, 'path' => 'products/cos-c120/1.jpg', 'sort_order' => 1, 'is_primary' => true]);
        ProductImage::create(['product_id' => $p->id, 'path' => 'products/cos-c120/2.jpg', 'sort_order' => 2, 'is_primary' => false]);
    }

    public function test_export_writes_snapshot_file(): void
    {
        $this->seedSource();

        Artisan::call('catalog:export-snapshot');

        $this->assertFileExists($this->tmp);
        $data = json_decode(file_get_contents($this->tmp), true);
        $this->assertSame(2, $data['counts']['categories']);
        $this->assertSame(1, $data['counts']['products']);
        $this->assertSame(2, $data['counts']['images']);
    }

    public function test_seeder_reconstructs_catalog_from_snapshot(): void
    {
        $this->seedSource();
        Artisan::call('catalog:export-snapshot');

        // Șterge tot, apoi reconstruiește din snapshot.
        ProductImage::query()->delete();
        \DB::table('category_product')->delete();
        Product::query()->delete();
        Category::query()->delete();

        $this->seed(CatalogSeeder::class);

        $this->assertSame(2, Category::count());
        $this->assertSame(1, Product::count());
        $this->assertSame(2, ProductImage::count());

        $p = Product::where('slug', 'cos-c120')->first();
        $this->assertSame('#C120', $p->code);
        $this->assertSame(['/cos-vechi-c120'], $p->legacy_urls);
        $this->assertSame('cosuri-de-gunoi', $p->primaryCategory()->slug);
        $this->assertTrue($p->primaryImage()->is_primary);
        $this->assertSame(2, $p->categories()->count());
    }

    public function test_snapshot_round_trips_thumb_paths(): void
    {
        $this->seedSource();
        // Setează căile thumb pe imagini (ca după images:thumbnails).
        ProductImage::where('path', 'products/cos-c120/1.jpg')->update([
            'thumb_sm_path' => 'products/cos-c120/1-400.webp',
            'thumb_md_path' => 'products/cos-c120/1-800.webp',
        ]);

        Artisan::call('catalog:export-snapshot');

        $data = json_decode(file_get_contents($this->tmp), true);
        $img = collect($data['products'][0]['images'])->firstWhere('path', 'products/cos-c120/1.jpg');
        $this->assertSame('products/cos-c120/1-400.webp', $img['thumb_sm_path']);
        $this->assertSame('products/cos-c120/1-800.webp', $img['thumb_md_path']);

        // Wipe + reseed → căile thumb supraviețuiesc.
        ProductImage::query()->delete();
        \DB::table('category_product')->delete();
        Product::query()->delete();
        Category::query()->delete();
        $this->seed(CatalogSeeder::class);

        $restored = ProductImage::where('path', 'products/cos-c120/1.jpg')->first();
        $this->assertSame('products/cos-c120/1-400.webp', $restored->thumb_sm_path);
        $this->assertSame('products/cos-c120/1-800.webp', $restored->thumb_md_path);
    }

    public function test_seeder_is_idempotent(): void
    {
        $this->seedSource();
        Artisan::call('catalog:export-snapshot');

        $this->seed(CatalogSeeder::class);
        $this->seed(CatalogSeeder::class);

        // Fără dubluri după rulări repetate.
        $this->assertSame(2, Category::count());
        $this->assertSame(1, Product::count());
        $this->assertSame(2, ProductImage::count());
        $this->assertSame(2, \DB::table('category_product')->count());
    }
}
