<?php

namespace App\Http\Controllers;

use App\Models\Category;
use App\Models\Product;

class StorefrontController extends Controller
{
    /**
     * Pagina categorie: produsele active din categorie, sortabil + paginat.
     */
    public function category(Category $category)
    {
        $sort = request('sort', 'recomandate');

        $query = $category->products()
            ->where('is_active', true)
            ->with(['images', 'categories']);

        $this->applySort($query, $sort);

        $products = $query->paginate(24)->withQueryString();

        $count = $category->products()->where('is_active', true)->count();

        return view('storefront.category', compact('category', 'products', 'sort', 'count'));
    }

    /**
     * Pagina produs: galerie, info, CTA WhatsApp, produse similare.
     */
    public function product(Product $product)
    {
        $product->load(['images', 'categories']);

        $primaryCategory = $product->primaryCategory() ?? $product->categories->first();

        $similar = collect();
        if ($primaryCategory) {
            $similar = $primaryCategory->products()
                ->where('is_active', true)
                ->where('products.id', '!=', $product->id)
                ->with('images')
                ->ordered()
                ->take(4)
                ->get();
        }

        return view('storefront.product', compact('product', 'primaryCategory', 'similar'));
    }

    private function applySort($query, string $sort): void
    {
        match ($sort) {
            'nume' => $query->orderBy('name'),
            'cod' => $query->orderByRaw('code IS NULL, code'),
            default => $query->orderBy('sort_order')->orderBy('name'),
        };
    }
}
