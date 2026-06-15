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
    'brand' => env('CONTACT_BRAND', 'Decor Urban'),

    // TODO(owner): telefon afișabil + WhatsApp în format internațional pentru wa.me.
    'phone' => env('CONTACT_PHONE', '+40 7XX XXX XXX'),          // TODO
    'whatsapp' => env('CONTACT_WHATSAPP', '40700000000'),         // TODO — doar cifre, ex. 40712345678
    'email' => env('CONTACT_EMAIL', 'contact@decor-urban.ro'),   // TODO de confirmat

    // Adresă / oraș (opțional, pentru footer).
    'city' => env('CONTACT_CITY', 'România'),

    // Social (opțional — gol = ascuns).
    'facebook' => env('CONTACT_FACEBOOK', ''),
    'instagram' => env('CONTACT_INSTAGRAM', ''),

    // True cât timp datele sunt placeholdere (afișează un mic avertisment în footer).
    'is_placeholder' => env('CONTACT_IS_PLACEHOLDER', true),
];
