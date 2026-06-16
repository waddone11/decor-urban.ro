<?php

namespace Tests\Feature;

use App\Models\Category;
use App\Models\Product;
use App\Models\ProductImage;
use Database\Seeders\DatabaseSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

/**
 * Verifică faptul că snapshot-ul COMMIS (database/data/catalog.json) reconstruiește
 * tot catalogul — echivalentul lui `migrate:fresh --seed` pe prod.
 */
class CommittedSnapshotTest extends TestCase
{
    use RefreshDatabase;

    public function test_committed_snapshot_rebuilds_full_catalog(): void
    {
        // Folosește calea reală din config (snapshot-ul commis), nu un temp.
        $this->seed(DatabaseSeeder::class);

        $this->assertSame(11, Category::count(), '11 categorii din snapshot');
        $this->assertSame(127, Product::count(), '127 produse din snapshot');
        $this->assertGreaterThanOrEqual(127, ProductImage::count());

        // Spot-check: integritate pivot (fiecare produs are cel puțin o categorie).
        $orphans = Product::query()->doesntHave('categories')->count();
        $this->assertSame(0, $orphans, 'Niciun produs fără categorie');
    }
}
