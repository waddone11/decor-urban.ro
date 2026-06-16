<?php

return [
    /*
    | Useri admin (Filament) seedați la `db:seed`. Parolele vin din .env (NU se comit
    | în git). Pe prod setează aceleași chei în .env. updateOrCreate după email (idempotent).
    */
    'admins' => array_values(array_filter([
        [
            'name' => env('SEED_ADMIN_1_NAME', 'Admin'),
            'email' => env('SEED_ADMIN_1_EMAIL'),
            'password' => env('SEED_ADMIN_1_PASSWORD'),
        ],
        [
            'name' => env('SEED_ADMIN_2_NAME', 'Admin'),
            'email' => env('SEED_ADMIN_2_EMAIL'),
            'password' => env('SEED_ADMIN_2_PASSWORD'),
        ],
    ], fn (array $a): bool => ! empty($a['email']) && ! empty($a['password']))),
];
