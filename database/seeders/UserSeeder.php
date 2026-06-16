<?php

namespace Database\Seeders;

use App\Models\User;
use Illuminate\Database\Seeder;

/**
 * Useri admin Filament din config/seed.php (parole din .env). Idempotent
 * (updateOrCreate după email). Parola e cast 'hashed' pe model → se hash-uiește automat.
 */
class UserSeeder extends Seeder
{
    public function run(): void
    {
        foreach (config('seed.admins', []) as $admin) {
            User::updateOrCreate(
                ['email' => $admin['email']],
                ['name' => $admin['name'], 'password' => $admin['password']],
            );
        }

        $this->command?->info('UserSeeder: '.count(config('seed.admins', [])).' useri admin.');
    }
}
