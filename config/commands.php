<?php

/*
|--------------------------------------------------------------------------
| Rute helper artisan din URL (prod fără terminal) — o singură cheie `secret`
|--------------------------------------------------------------------------
| Securizate cu cheia `SECRET` din .env (lungă, random, NEcomisă). Orice request
| fără ?secret= corect (sau header X-Command-Secret) → 404 (nu dezvăluim ruta).
| Rutele rulează fără sesiune/CSRF → merg și pe DB proaspătă/goală (înainte de migrate).
*/

return [
    'secret' => env('SECRET'),

    // Limită requesturi/minut pe IP pentru grupul /commands.
    'rate_limit' => (int) env('COMMAND_RATE_LIMIT', 30),
];
