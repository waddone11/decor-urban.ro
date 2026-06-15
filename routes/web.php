<?php

use App\Models\Category;
use App\Models\Product;
use Illuminate\Support\Facades\Route;

Route::get('/', function () {
    $categories = Category::query()->active()->ordered()->withCount('products')->get();

    // Produse featured: câte un produs cu imagine din primele categorii (variație).
    $featured = $categories
        ->take(8)
        ->map(fn (Category $c) => $c->products()
            ->where('is_active', true)
            ->whereHas('images')
            ->with('images')
            ->first())
        ->filter()
        ->values();

    $stats = [
        'categories' => Category::count() ?: 11,
        'products' => Product::count() ?: 127,
    ];

    // FAQ — folosit și în secțiunea acordeon, și în JSON-LD (FAQPage). Un singur loc.
    $faqs = [
        [
            'q' => 'Cum cumpăr prin SEAP/SICAP?',
            'a' => 'Pregătim o ofertă cu specificații și coduri CPV și trimitem documentația pentru caietul de sarcini. Ne contactați și vă ghidăm prin proces.',
        ],
        [
            'q' => 'Faceți dimensiuni și culori custom?',
            'a' => 'Da, producem la comandă: dimensiuni și culori RAL la alegere, plus personalizare cu stema localității sau logo.',
        ],
        [
            'q' => 'Care e termenul de livrare?',
            'a' => 'Variază după produs și cantitate; îl confirmăm în ofertă, în scris.',
        ],
        [
            'q' => 'Oferiți factură și garanție?',
            'a' => 'Da, factură fiscală și garanție; livrare în toată țara.',
        ],
        [
            'q' => 'Cum se plătește?',
            'a' => 'Ramburs la livrare sau prin transfer bancar; pentru instituții, cu factură.',
        ],
    ];

    return view('home', compact('categories', 'featured', 'stats', 'faqs'));
})->name('home');
