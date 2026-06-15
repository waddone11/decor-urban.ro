<?php

/*
|--------------------------------------------------------------------------
| Mapare categorii sursă (site vechi mobilier-stradal.ro) → categorii noi
|--------------------------------------------------------------------------
|
| 'source_map':  cheia = numele categoriei sursă (exact ca în products.json,
|                source_categories[].name); valoarea = listă slug-uri noi.
|
| 'product_overrides': cheia = SLUG-ul produsului (NU code — codul nu e unic,
|                #PS100 e pe 2 produse distincte); valoarea = lista FINALĂ de
|                slug-uri. Override-ul ÎNLOCUIEȘTE complet maparea pe sursă
|                pentru acel produs. is_primary = prima categorie din listă.
|
| Editabil — ajustează aici dacă spargem mai departe „Diverse & custom".
*/

return [

    'source_map' => [
        'Banci stradale'          => ['banci-sezut'],
        'Cosuri de gunoi'         => ['cosuri-de-gunoi'],
        'Jardiniere'              => ['jardiniere'],
        'Pergole'                 => ['pergole-foisoare'],
        'Placute denumiri strazi' => ['placute-totemuri'],
        'Placute numere casa'     => ['placute-totemuri'],
        'Statii de autobuz'       => ['statii-autobuz'],
        'Suporturi biciclete'     => ['suporturi-biciclete'],
        'Echipamente de joaca'    => ['locuri-de-joaca'],
        'Totemuri'                => ['placute-totemuri'],
        'Diverse produse'         => ['diverse-custom'],
    ],

    // Re-clasare produse din „Diverse produse" în categorii coerente, după slug.
    'product_overrides' => [

        // → Sport & stadion: tribune + peluză stadion (NU panoul alcobond).
        'tribuna-stadion-tr381' => ['sport-stadion'],
        'tribuna-stadion-tr382' => ['sport-stadion'],
        'tribuna-stadion-tr383' => ['sport-stadion'],
        'tribuna-stadion-tr384' => ['sport-stadion'],
        'peluza-stadion-ps100'  => ['sport-stadion'],

        // → Pergole & foișoare: foișoarele F200–F203.
        'foisor-din-lemn-si-teava-rotunda-f200-si-mobilier-stradal' => ['pergole-foisoare'],
        'foisor-din-lemn-f201'                                      => ['pergole-foisoare'],
        'foisor-metalic-pentru-exterior-f202'                      => ['pergole-foisoare'],
        'foisor-din-lemn-si-teava-rotunda-f203'                    => ['pergole-foisoare'],

        // → Tarabe & piață: TP391–TP394.
        'taraba-piata-tp391' => ['tarabe-piata'],
        'taraba-piata-tp392' => ['tarabe-piata'],
        'taraba-piata-tp393' => ['tarabe-piata'],
        'taraba-piata-tp394' => ['tarabe-piata'],

        // → Plăcuțe & totemuri: signalistica rămasă în diverse (panou + plăcuțe numere).
        'panou-stradal-din-alcobond-ps-100'                       => ['placute-totemuri'],
        'placuta-numar-casa-pc-100'                               => ['placute-totemuri'],
        'placuta-numar-inmatriculare-utilaj-agricol-moped-caruta' => ['placute-totemuri'],
        'numere-pentru-utilaje-agricole-n100'                     => ['placute-totemuri'],
    ],
];
