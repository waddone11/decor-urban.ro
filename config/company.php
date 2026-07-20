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
    // Identitate legală — afișată ca semnal de încredere pentru instituții.
    'legal_name' => env('COMPANY_LEGAL_NAME', env('BUSINESS_LEGAL_NAME', 'MOBILIER-STRADAL RO 2026 S.R.L.')),
    'brand' => env('COMPANY_BRAND', env('BUSINESS_NAME', 'Decor Urban')),
    'euid' => env('COMPANY_EUID', ''),             // identificator unic european
    'address' => env('COMPANY_ADDRESS', env('BUSINESS_ADDRESS', 'Str. Băltați nr. 149, Sat Băltați, Oraș Scornicești, Județul Olt, România')),
    'caen' => env('COMPANY_CAEN', ''),             // cod CAEN principal
    'founded' => env('COMPANY_FOUNDED', ''),       // data înființării (Y-m-d)

    // Cum ne descriem (configurabil — chestiune de CAEN, ajustabil fără cod).
    'supplier_label' => env('COMPANY_SUPPLIER_LABEL', 'producător / furnizor direct'),

    // Identitate fiscală — afișate doar dacă sunt completate.
    'cui' => env('COMPANY_CUI', env('BUSINESS_VAT_NUMBER', '54295156')),
    'reg_com' => env('COMPANY_REG_COM', env('BUSINESS_REGISTRATION_NUMBER', 'J2026018529009')),

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

    // Proiecte livrate (pagina /proiecte). Listă separată prin „|” în .env, ex.
    // „Primăria X — 20 bănci|Școala Y — locuri de joacă”. Gol = pagina arată „în curând”.
    // NU inventa proiecte — se completează cu lucrări reale când există.
    'projects_list' => array_values(array_filter(array_map(
        'trim',
        explode('|', (string) env('COMPANY_PROJECTS_LIST', ''))
    ))),

    // Standarde / certificări (ex. EN 1176 pentru echipamente de joacă).
    // NU le afirma ca fapt până nu sunt confirmate — gol = chip-ul nu apare.
    'standards' => array_values(array_filter(array_map(
        'trim',
        explode('|', (string) env('COMPANY_STANDARDS', ''))
    ))),

    // True cât timp datele de mai sus sunt necompletate / provizorii.
    'is_placeholder' => env('COMPANY_IS_PLACEHOLDER', false),
];
