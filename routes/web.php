<?php

use App\Http\Controllers\CommandController;
use App\Http\Controllers\StorefrontController;
use App\Http\Middleware\VerifySecretKey;
use App\Livewire\CartPage;
use App\Livewire\CatalogBrowser;
use App\Livewire\Checkout;
use App\Livewire\OrderSuccess;
use App\Models\Category;
use App\Models\Product;
use App\Support\LegacyRedirects;
use Illuminate\Foundation\Http\Middleware\PreventRequestForgery;
use Illuminate\Http\Request;
use Illuminate\Session\Middleware\StartSession;
use Illuminate\Support\Facades\Route;
use Illuminate\View\Middleware\ShareErrorsFromSession;

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

// Proiectele noastre — gestionate din Filament (doar cele publicate sunt vizibile).
Route::get('/proiecte', [StorefrontController::class, 'projects'])->name('proiecte');
Route::get('/proiecte/{project:slug}', [StorefrontController::class, 'projectShow'])->name('project.show');

// ── Storefront ────────────────────────────────────────────────────────────
// /catalog = componentă Livewire full-page (filtre + search + sort + paginare).
Route::get('/catalog', CatalogBrowser::class)->name('catalog');

Route::get('/categorie/{category:slug}', [StorefrontController::class, 'category'])->name('category');
Route::get('/produs/{product:slug}', [StorefrontController::class, 'product'])->name('product');

// ── Coș & comandă (guest) ───────────────────────────────────────────────────
Route::get('/cos', CartPage::class)->name('cart');
Route::get('/checkout', Checkout::class)->name('checkout');
Route::get('/comanda/{number}', OrderSuccess::class)->name('order.success');

// ── Pagini statice / legale ─────────────────────────────────────────────────
Route::view('/despre', 'static.despre')->name('despre');
Route::view('/institutii', 'static.institutii')->name('institutii');
Route::view('/contact', 'static.contact')->name('contact');
Route::view('/confidentialitate', 'static.confidentialitate')->name('confidentialitate');
Route::view('/termeni', 'static.termeni')->name('termeni');
Route::view('/politica-cookies', 'static.politica-cookies')->name('politica-cookies');

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
        'Disallow: /commands',
        '',
        'Sitemap: '.url('/sitemap.xml'),
        '',
    ];

    return response(implode("\n", $lines), 200, ['Content-Type' => 'text/plain']);
})->name('robots');

// ── /commands: helper artisan din URL (o singură cheie `secret`) ─────────────
// Fără sesiune/CSRF → merg și pe DB proaspătă/goală (SESSION_DRIVER=database ar crăpa
// înainte de migrate). 404 la secret lipsă/greșit (VerifySecretKey).
Route::middleware([VerifySecretKey::class, 'throttle:'.config('commands.rate_limit', 30).',1'])
    ->withoutMiddleware([StartSession::class, ShareErrorsFromSession::class, PreventRequestForgery::class])
    ->prefix('commands')
    ->group(function () {
        Route::get('/', [CommandController::class, 'index'])->name('commands.index');
        Route::get('/clear-cache', [CommandController::class, 'clearCache'])->name('commands.clearCache');
        Route::get('/optimize-clear', [CommandController::class, 'optimizeClear'])->name('commands.optimizeClear');
        Route::get('/optimize', [CommandController::class, 'optimize'])->name('commands.optimize');
        Route::get('/create-storage-link', [CommandController::class, 'createStorageLink'])->name('commands.createStorageLink');
        Route::get('/create-sitemap', [CommandController::class, 'createSitemap'])->name('commands.createSitemap');
        Route::get('/migrate', [CommandController::class, 'migrate'])->name('commands.migrate');
        Route::get('/migrate-fresh-seed', [CommandController::class, 'migrateFreshSeed'])->name('commands.migrateFreshSeed');
        Route::get('/migrate-status', [CommandController::class, 'migrateStatus'])->name('commands.migrateStatus');
        Route::get('/about', [CommandController::class, 'about'])->name('commands.about');
        Route::get('/catalog-summary', [CommandController::class, 'catalogSummary'])->name('commands.catalogSummary');
        Route::get('/queue-restart', [CommandController::class, 'queueRestart'])->name('commands.queueRestart');
        Route::get('/trigger-queue/{queue?}', [CommandController::class, 'triggerQueue'])->name('commands.triggerQueue');
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
