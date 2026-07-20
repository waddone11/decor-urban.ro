<?php

/*
|--------------------------------------------------------------------------
| Date de contact / brand — storefront public
|--------------------------------------------------------------------------
| TODO(owner): completează cu datele reale ale brandului nou Decor Urban.
| Pot diferi de site-ul vechi. Setează-le în .env (vezi cheile de mai jos).
| Atât timp cât rămân placeholdere, sunt marcate vizibil în footer/header.
*/

return [
    'brand' => env('CONTACT_BRAND', env('BUSINESS_NAME', 'Decor Urban')),

    'phone' => env('CONTACT_PHONE', env('BUSINESS_PHONE', '+40 758 522 227')),
    'whatsapp' => env('CONTACT_WHATSAPP', env('BUSINESS_WHATSAPP_DIGITS', '40756222260')),
    'email' => env('CONTACT_EMAIL', env('BUSINESS_EMAIL', 'contact@decor-urban.ro')),

    // Adresă / oraș (opțional, pentru footer).
    'city' => env('CONTACT_CITY', 'Scornicești, Olt'),

    // Social (opțional — gol = ascuns).
    'facebook' => env('CONTACT_FACEBOOK', env('BUSINESS_FACEBOOK', 'https://www.facebook.com/profile.php?id=61592205237734')),
    'instagram' => env('CONTACT_INSTAGRAM', env('BUSINESS_INSTAGRAM', 'https://www.instagram.com/decor.urban.ro')),

    // True cât timp datele sunt placeholdere (afișează un mic avertisment în footer).
    'is_placeholder' => env('CONTACT_IS_PLACEHOLDER', false),
];
