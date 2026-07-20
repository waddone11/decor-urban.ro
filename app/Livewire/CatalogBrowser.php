<?php

namespace App\Livewire;

use App\Models\Category;
use App\Models\Product;
use App\Support\JsonLd;
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

    /** Facete material selectate (lemn/metal/inox/...). Sincronizat în URL. */
    #[Url(as: 'mat')]
    public array $materials = [];

    /** Doar produsele cu preț promoțional (hasSalePrice). */
    #[Url(as: 'promo')]
    public bool $promo = false;

    /** Doar produsele marcate manual „disponibil pe SEAP/SICAP". */
    #[Url(as: 'seap')]
    public bool $seap = false;

    /** Ordinea facetelor de material (canonice, ca în SpecsExtractor). */
    public const MATERIAL_FACETS = ['lemn', 'metal', 'inox', 'beton', 'alucobond', 'policarbonat'];

    public function updatedMaterials(): void
    {
        $this->resetPage();
    }

    public function updatedPromo(): void
    {
        $this->resetPage();
    }

    public function updatedSeap(): void
    {
        $this->resetPage();
    }

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
        $this->reset(['cat', 'q', 'sort', 'materials', 'promo', 'seap']);
        $this->resetPage();
    }

    public function render()
    {
        $categories = Category::query()->active()->ordered()->withCount(['products as products_count' => function ($q) {
            $q->where('is_active', true);
        }])->get();

        $totalCount = Product::query()->active()->count();

        // Filtrele de bază (categorie + search) — aplicate și la facete, și la rezultate.
        $applyBase = function ($q) {
            if ($this->cat) {
                $q->whereHas('categories', fn ($c) => $c->where('slug', $this->cat));
            }
            if (trim($this->q) !== '') {
                $term = '%'.trim($this->q).'%';
                $q->where(fn ($w) => $w->where('name', 'like', $term)->orWhere('code', 'like', $term));
            }
        };

        // Facete: din specs + coloane (cat+search aplicat, FĂRĂ propriul filtru), cu count.
        $facetQuery = Product::query()->active();
        $applyBase($facetQuery);
        $facetRows = $facetQuery->get(['id', 'specs', 'price', 'sale_price', 'price_on_request', 'available_seap']);

        $materialFacets = [];
        foreach (self::MATERIAL_FACETS as $m) {
            $c = $facetRows->filter(fn ($p) => in_array($m, (array) ($p->specs['material'] ?? []), true))->count();
            if ($c > 0) {
                $materialFacets[$m] = $c;
            }
        }

        $promoCount = $facetRows->filter(fn ($p) => $p->hasSalePrice())->count();
        $seapCount = $facetRows->filter(fn ($p) => $p->available_seap)->count();

        $query = Product::query()->active()->with(['images', 'categories']);
        $applyBase($query);

        // Filtru material (DB-agnostic: derivăm ID-urile din specs, apoi whereIn).
        if (! empty($this->materials)) {
            $ids = $facetRows
                ->filter(fn ($p) => array_intersect($this->materials, (array) ($p->specs['material'] ?? [])))
                ->pluck('id');
            $query->whereIn('id', $ids);
        }

        if ($this->promo) {
            $query->whereIn('id', $facetRows->filter(fn ($p) => $p->hasSalePrice())->pluck('id'));
        }

        if ($this->seap) {
            $query->where('available_seap', true);
        }

        match ($this->sort) {
            'nume' => $query->orderBy('name'),
            'cod' => $query->orderByRaw('code IS NULL, code'),
            default => $query->orderBy('sort_order')->orderBy('name'),
        };

        $products = $query->paginate(24);

        $activeCategory = $this->cat ? $categories->firstWhere('slug', $this->cat) : null;

        $title = $activeCategory ? $activeCategory->name : 'Catalog produse';
        $description = $activeCategory
            ? $activeCategory->seoDescription()
            : 'Catalogul complet de mobilier urban și stradal: bănci, coșuri de gunoi, jardiniere, locuri de joacă și altele. '.config('contact.brand').'.';

        $itemListLd = JsonLd::itemList($products->getCollection(), $title);

        return view('livewire.catalog-browser', compact('categories', 'products', 'totalCount', 'activeCategory', 'itemListLd', 'materialFacets', 'promoCount', 'seapCount'))
            ->layout('components.layouts.storefront', ['title' => $title, 'description' => $description]);
    }
}
