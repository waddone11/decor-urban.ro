<?php

namespace App\Http\Controllers;

use App\Models\Category;
use App\Models\Product;
use App\Support\JsonLd;
use Illuminate\Support\Facades\Storage;

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

        $ogImagePath = $category->representativeImagePath();
        $ogImage = $ogImagePath ? Storage::disk('public')->url($ogImagePath) : null;

        $jsonLd = [
            JsonLd::breadcrumb([
                ['name' => 'Acasă', 'url' => url('/')],
                ['name' => 'Catalog', 'url' => route('catalog')],
                ['name' => $category->name, 'url' => route('category', $category->slug)],
            ]),
            JsonLd::itemList($products->getCollection(), $category->name),
        ];

        return view('storefront.category', compact('category', 'products', 'sort', 'count', 'ogImage', 'jsonLd'));
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

        // CTA WhatsApp: mesaj precompletat, URL-encoded corect (diacritice).
        $code = $product->code ? ltrim($product->code, '#') : null;
        $waText = 'Bună ziua, doresc o ofertă pentru: '.$product->name
            .($code ? " (cod {$code})" : '')
            .' — '.route('product', $product->slug);
        $whatsappUrl = 'https://wa.me/'.config('contact.whatsapp').'?text='.rawurlencode($waText);

        $jsonLd = [
            JsonLd::product($product),
            JsonLd::breadcrumb(array_values(array_filter([
                ['name' => 'Acasă', 'url' => url('/')],
                ['name' => 'Catalog', 'url' => route('catalog')],
                $primaryCategory ? ['name' => $primaryCategory->name, 'url' => route('category', $primaryCategory->slug)] : null,
                ['name' => $product->name, 'url' => route('product', $product->slug)],
            ]))),
        ];

        return view('storefront.product', compact('product', 'primaryCategory', 'similar', 'whatsappUrl', 'jsonLd'));
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
