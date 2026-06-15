@props([
    'slug' => null,
])

{{--
    Dispatcher iconițe categorii — un singur loc de adevăr.
    Primește slug-ul categoriei și randează componenta SVG line-art potrivită.
    Refolosibil: grilă homepage, mega-menu și (în 4b) carduri categorie + breadcrumb.
    Formele sunt portate din prototipul `design/decor-urban-iconite-categorii.html`.
--}}
@php
    $map = [
        'banci-sezut' => 'banci',
        'cosuri-de-gunoi' => 'cosuri',
        'jardiniere' => 'jardiniere',
        'pergole-foisoare' => 'pergole',
        'locuri-de-joaca' => 'locuri-joaca',
        'suporturi-biciclete' => 'suporturi',
        'statii-autobuz' => 'statii',
        'placute-totemuri' => 'placute',
        'diverse-custom' => 'diverse',
        'sport-stadion' => 'sport',
        'tarabe-piata' => 'tarabe',
    ];
    $name = $map[$slug] ?? 'diverse';
@endphp

<x-dynamic-component :component="'icons.category.'.$name" :attributes="$attributes" />
