<?php

return [
    /*
    | Calea snapshot-ului de catalog (categorii + produse + pivot + imagini).
    | Scris de `catalog:export-snapshot`, citit de CatalogSeeder. Commis în git
    | (e mic, fără binare). Fișierele imagine NU sunt în git — se urcă separat.
    */
    'snapshot_path' => database_path('data/catalog.json'),
];
