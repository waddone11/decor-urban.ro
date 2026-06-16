<?php

use App\Http\Controllers\StorefrontController;
use App\Livewire\CatalogBrowser;
use App\Models\Category;
use App\Models\Product;
use App\Support\LegacyRedirects;
use Illuminate\Http\Request;
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

// Proiectele noastre — stub onest până avem lucrări reale (poze/nume) de afișat.
// Citește o listă opțională din config/company.php (gol acum → mesaj „în curând”).
Route::get('/proiecte', function () {
    $projects = config('company.projects_list', []);

    return view('proiecte', compact('projects'));
})->name('proiecte');

// ── Storefront ────────────────────────────────────────────────────────────
// /catalog = componentă Livewire full-page (filtre + search + sort + paginare).
Route::get('/catalog', CatalogBrowser::class)->name('catalog');

Route::get('/categorie/{category:slug}', [StorefrontController::class, 'category'])->name('category');
Route::get('/produs/{product:slug}', [StorefrontController::class, 'product'])->name('product');

// ── SEO: sitemap + robots ───────────────────────────────────────────────────
// /sitemap.xml: dinamic (dev/test); pe prod `sitemap:generate` scrie un fișier
// static în public/ care e servit direct de webserver (mai rapid).
Route::get('/sitemap.xml', function () {
    return response(\App\Support\Sitemap::xml(), 200, ['Content-Type' => 'application/xml']);
})->name('sitemap');

Route::get('/robots.txt', function () {
    $lines = [
        'User-agent: *',
        'Disallow: /admin',
        'Disallow: /ops',
        '',
        'Sitemap: '.url('/sitemap.xml'),
        '',
    ];

    return response(implode("\n", $lines), 200, ['Content-Type' => 'text/plain']);
})->name('robots');

// ── Ops web-runner (hosting fără SSH) — 404-gated pe token + whitelist ───────
Route::middleware(['ops', 'throttle:'.config('ops.rate_limit', 20).',1'])
    ->prefix('ops')
    ->group(function () {
        Route::get('/', [\App\Http\Controllers\OpsController::class, 'index'])->name('ops.index');
        Route::get('/{command}', [\App\Http\Controllers\OpsController::class, 'run'])->name('ops.run');
    });

// ── 301 din URL-urile vechi ─────────────────────────────────────────────────
// Rulează DOAR când nicio rută cunoscută nu prinde requestul (prioritate joasă).
// Caută calea într-o hartă cache-uită (legacy_urls produse + categorii vechi);
// 301 către canonical dacă există, altfel 404 frumos.
Route::fallback(function (Request $request) {
    if ($target = LegacyRedirects::lookup($request->path())) {
        return redirect($target, 301);
    }

    abort(404);
});
