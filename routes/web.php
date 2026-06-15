<?php

use App\Models\Category;
use App\Models\Product;
use Illuminate\Support\Facades\Route;

Route::get('/', function () {
    $categories = Category::query()->active()->ordered()->withCount('products')->get();

    // Hero: o bancă reprezentativă, cu imagine.
    $hero = Product::query()
        ->where('is_active', true)
        ->whereHas('categories', fn ($q) => $q->where('slug', 'banci-sezut'))
        ->whereHas('images')
        ->with('images')
        ->first();

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

    return view('home', compact('categories', 'hero', 'featured'));
})->name('home');
