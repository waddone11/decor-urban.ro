<?php

namespace Database\Seeders;

use App\Models\Category;
use Illuminate\Database\Seeder;

class CategorySeeder extends Seeder
{
    /**
     * Cele 9 categorii noi ale catalogului. Idempotent (updateOrCreate după slug).
     */
    public const CATEGORIES = [
        ['name' => 'Bănci & șezut',        'slug' => 'banci-sezut',        'sort_order' => 1],
        ['name' => 'Coșuri de gunoi',      'slug' => 'cosuri-de-gunoi',    'sort_order' => 2],
        ['name' => 'Jardiniere',           'slug' => 'jardiniere',         'sort_order' => 3],
        ['name' => 'Pergole & foișoare',   'slug' => 'pergole-foisoare',   'sort_order' => 4],
        ['name' => 'Locuri de joacă',      'slug' => 'locuri-de-joaca',    'sort_order' => 5],
        ['name' => 'Suporturi biciclete',  'slug' => 'suporturi-biciclete', 'sort_order' => 6],
        ['name' => 'Stații de autobuz',    'slug' => 'statii-autobuz',     'sort_order' => 7],
        ['name' => 'Plăcuțe & totemuri',   'slug' => 'placute-totemuri',   'sort_order' => 8],
        ['name' => 'Diverse & custom',     'slug' => 'diverse-custom',     'sort_order' => 9],
        ['name' => 'Sport & stadion',      'slug' => 'sport-stadion',      'sort_order' => 10],
        ['name' => 'Tarabe & piață',       'slug' => 'tarabe-piata',       'sort_order' => 11],
    ];

    public function run(): void
    {
        foreach (self::CATEGORIES as $cat) {
            Category::updateOrCreate(
                ['slug' => $cat['slug']],
                [
                    'name' => $cat['name'],
                    'sort_order' => $cat['sort_order'],
                    'is_active' => true,
                ],
            );
        }
    }
}
