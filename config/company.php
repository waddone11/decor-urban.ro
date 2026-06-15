<?php

/*
|--------------------------------------------------------------------------
| Date despre companie — vânzare B2B / instituții (SEAP, licitații)
|--------------------------------------------------------------------------
| TODO(owner): completează cu datele reale. Cât timp o valoare e goală,
| chip-ul / secțiunea care depinde de ea NU se afișează (vezi homepage),
| iar footer-ul arată un mic „TODO" cât timp `is_placeholder` e true.
| Setează-le în .env (vezi cheile de mai jos). Nu inventa date.
*/

return [
    // Identitate fiscală — afișate doar dacă sunt completate.
    'cui' => env('COMPANY_CUI', ''),            // ex. RO12345678
    'reg_com' => env('COMPANY_REG_COM', ''),    // ex. J40/1234/2010

    // Achiziții publice.
    'cpv' => env('COMPANY_CPV', ''),            // cod CPV principal, ex. 34928400-2
    'seap_present' => env('COMPANY_SEAP_PRESENT', false), // true = afișează badge „prezenți în SEAP/SICAP"

    // Social proof (contoare homepage). Numerele de produse/categorii vin din DB.
    'years' => (int) env('COMPANY_YEARS', 0),       // ani de experiență — 0 = ascuns
    'projects' => (int) env('COMPANY_PROJECTS', 0), // proiecte livrate — 0 = ascuns

    // Referințe (nume clienți). Listă separată prin „|” în .env, ex. „Primăria X|Școala Y”.
    // Gol = se afișează doar contoarele, fără listă de nume.
    'references' => array_values(array_filter(array_map(
        'trim',
        explode('|', (string) env('COMPANY_REFERENCES', ''))
    ))),

    // Standarde / certificări (ex. EN 1176 pentru echipamente de joacă).
    // NU le afirma ca fapt până nu sunt confirmate — gol = chip-ul nu apare.
    'standards' => array_values(array_filter(array_map(
        'trim',
        explode('|', (string) env('COMPANY_STANDARDS', ''))
    ))),

    // True cât timp datele de mai sus sunt necompletate / provizorii.
    'is_placeholder' => env('COMPANY_IS_PLACEHOLDER', true),
];
