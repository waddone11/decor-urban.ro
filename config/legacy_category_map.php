<?php

/*
|--------------------------------------------------------------------------
| Mapare categorii sursă (site vechi mobilier-stradal.ro) → categorii noi
|--------------------------------------------------------------------------
|
| Cheia = numele categoriei sursă (exact cum apare în storage/scrape/products.json,
| câmpul source_categories[].name). Valoarea = listă de slug-uri de categorii noi.
| Un produs poate mapa la mai multe categorii noi; slug-urile se deduplică la sync.
|
| Editabil — ajustează aici dacă spargem ulterior „Diverse & custom".
*/

return [
    'Banci stradale'          => ['banci-sezut'],
    'Cosuri de gunoi'         => ['cosuri-de-gunoi'],
    'Jardiniere'              => ['jardiniere'],
    'Pergole'                 => ['pergole-umbrare'],
    'Placute denumiri strazi' => ['placute-totemuri'],
    'Placute numere casa'     => ['placute-totemuri'],
    'Statii de autobuz'       => ['statii-autobuz'],
    'Suporturi biciclete'     => ['suporturi-biciclete'],
    'Echipamente de joaca'    => ['locuri-de-joaca'],
    'Totemuri'                => ['placute-totemuri'],
    'Diverse produse'         => ['diverse-custom'],
];
