<?php

namespace Database\Seeders;

use App\Models\User;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

class DatabaseSeeder extends Seeder
{
    use WithoutModelEvents;

    /**
     * Seed the application's database.
     */
    public function run(): void
    {
        // Catalogul: cele 11 categorii canonice + produse/pivot/imagini din snapshot.
        $this->call([
            CategorySeeder::class,
            CatalogSeeder::class,
        ]);

        // User de test DOAR local/testing — niciodată pe prod (migrate:fresh --seed via ops).
        if (app()->environment('local', 'testing')) {
            User::factory()->create([
                'name' => 'Test User',
                'email' => 'test@example.com',
            ]);
        }
    }
}
