<?php

namespace App\Livewire;

use App\Models\Category;
use App\Models\Product;
use Livewire\Attributes\Url;
use Livewire\Component;
use Livewire\WithPagination;

class CatalogBrowser extends Component
{
    use WithPagination;

    /** Slug categorie selectată (null = toate). Sincronizat în URL pentru linkuri SEO-friendly. */
    #[Url(as: 'cat')]
    public ?string $cat = null;

    /** Căutare după nume sau cod. */
    #[Url(as: 'q')]
    public string $q = '';

    /** Sortare: recomandate | nume | cod. */
    #[Url(as: 'sort')]
    public string $sort = 'recomandate';

    public function updatedCat(): void
    {
        $this->resetPage();
    }

    public function updatedQ(): void
    {
        $this->resetPage();
    }

    public function updatedSort(): void
    {
        $this->resetPage();
    }

    public function clearFilters(): void
    {
        $this->reset(['cat', 'q', 'sort']);
        $this->resetPage();
    }

    public function render()
    {
        $categories = Category::query()->active()->ordered()->withCount(['products as products_count' => function ($q) {
            $q->where('is_active', true);
        }])->get();

        $totalCount = Product::query()->active()->count();

        $query = Product::query()->active()->with(['images', 'categories']);

        if ($this->cat) {
            $query->whereHas('categories', fn ($q) => $q->where('slug', $this->cat));
        }

        if (trim($this->q) !== '') {
            $term = '%'.trim($this->q).'%';
            $query->where(fn ($q) => $q->where('name', 'like', $term)->orWhere('code', 'like', $term));
        }

        match ($this->sort) {
            'nume' => $query->orderBy('name'),
            'cod' => $query->orderByRaw('code IS NULL, code'),
            default => $query->orderBy('sort_order')->orderBy('name'),
        };

        $products = $query->paginate(24);

        $activeCategory = $this->cat ? $categories->firstWhere('slug', $this->cat) : null;

        return view('livewire.catalog-browser', compact('categories', 'products', 'totalCount', 'activeCategory'))
            ->layout('components.layouts.storefront', ['title' => 'Catalog produse']);
    }
}
