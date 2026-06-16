<?php

/*
|--------------------------------------------------------------------------
| Ops web-runner — comenzi artisan din URL (hosting fără SSH)
|--------------------------------------------------------------------------
| PLASĂ pentru hosting fără terminal. NU înlocuiește un deploy real.
| Periculos prin natură → 404-gated pe token, whitelist strictă, confirm la
| comenzi distructive, logare. Ține-l dezactivat (OPS_ENABLED=false) când nu-l
| folosești și rotește/șterge OPS_TOKEN. Token-ul NU se comite (e în .env).
*/

return [
    // Master switch. Orice request către /ops* → 404 cât timp e false.
    'enabled' => env('OPS_ENABLED', false),

    // Token secret lung, generat (ex. `php artisan str:random 48`). Gol → tot 404.
    'token' => env('OPS_TOKEN', ''),

    // Limită requesturi/minut pe IP pentru grupul /ops.
    'rate_limit' => env('OPS_RATE_LIMIT', 20),

    /*
    | Whitelist strict: cheie din URL → comandă artisan (cu argumente).
    | NIMIC din afara acestei hărți nu rulează. `--force` pentru prod non-interactiv.
    */
    'commands' => [
        'migrate' => 'migrate --force',
        'fresh' => 'migrate:fresh --seed --force',
        'seed' => 'db:seed --force',
        'storage-link' => 'storage:link',
        'optimize' => 'optimize',
        'optimize-clear' => 'optimize:clear',
        'config-cache' => 'config:cache',
        'config-clear' => 'config:clear',
        'route-cache' => 'route:cache',
        'route-clear' => 'route:clear',
        'view-cache' => 'view:cache',
        'view-clear' => 'view:clear',
        'cache-clear' => 'cache:clear',
        'migrate-status' => 'migrate:status',
        'catalog-summary' => 'catalog:summary',
        'sitemap' => 'sitemap:generate',
        'about' => 'about',
    ],

    // Comenzi distructive — cer în plus &confirm=YES în URL.
    'destructive' => [
        'fresh',
    ],
];
