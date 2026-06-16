<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Casts\Attribute;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Product extends Model
{
    protected $fillable = [
        'slug',
        'name',
        'code',
        'description',
        'specs',
        'legacy_description',
        'description_draft',
        'description_source',
        'price',
        'price_on_request',
        'availability',
        'is_active',
        'sort_order',
        'legacy_urls',
        'legacy_categories',
        'meta_title',
        'meta_description',
        'meta_keywords',
    ];

    protected $casts = [
        'price' => 'decimal:2',
        'price_on_request' => 'bool',
        'is_active' => 'bool',
        'sort_order' => 'int',
        'legacy_urls' => 'array',
        'legacy_categories' => 'array',
        'specs' => 'array',
    ];

    /**
     * Binding scopat: în storefront `{product:slug}` rezolvă DOAR produse active
     * (inactiv → 404). Filament leagă pe id (field null), deci nu e afectat.
     */
    public function resolveRouteBinding($value, $field = null)
    {
        $field ??= $this->getRouteKeyName();
        $query = $this->where($field, $value);

        if ($field === 'slug') {
            $query->where('is_active', true);
        }

        return $query->first();
    }

    public function scopeActive(Builder $query): Builder
    {
        return $query->where('is_active', true);
    }

    public function scopeOrdered(Builder $query): Builder
    {
        return $query->orderBy('sort_order')->orderBy('name');
    }

    public function categories(): BelongsToMany
    {
        return $this->belongsToMany(Category::class)
            ->withPivot('is_primary');
    }

    public function images(): HasMany
    {
        return $this->hasMany(ProductImage::class)->orderBy('sort_order');
    }

    public function primaryCategory(): ?Category
    {
        return $this->categories->firstWhere('pivot.is_primary', true);
    }

    public function primaryImage(): ?ProductImage
    {
        return $this->images->firstWhere('is_primary', true)
            ?? $this->images->sortBy('sort_order')->first();
    }

    /**
     * Imaginile pentru galeria paginii produs: primary prima, apoi după sort_order.
     * Dacă există coloana `source` (vine cu workstream-ul poze-ai) și produsul are
     * imagini source='ai', folosește-le pe acelea. Altfel toate imaginile.
     * Defensiv: merge și înainte ca migrația poze-ai să fie aplicată.
     */
    public function galleryImages(): \Illuminate\Support\Collection
    {
        $images = $this->images;

        if ($this->productImagesHaveSourceColumn()) {
            $ai = $images->where('source', 'ai');
            if ($ai->isNotEmpty()) {
                $images = $ai;
            }
        }

        return $images->sortByDesc('is_primary')->values();
    }

    private function productImagesHaveSourceColumn(): bool
    {
        static $has = null;

        return $has ??= \Illuminate\Support\Facades\Schema::hasColumn('product_images', 'source');
    }

    // ── SEO ────────────────────────────────────────────────────────────────

    public function seoTitle(): string
    {
        return $this->meta_title ?: $this->name;
    }

    public function seoDescription(): string
    {
        if ($this->meta_description) {
            return $this->meta_description;
        }

        if ($this->description) {
            return (string) \Illuminate\Support\Str::of($this->description)->stripTags()->squish()->limit(155);
        }

        $cat = $this->primaryCategory() ?? $this->categories->first();

        return trim($this->name
            .($cat ? ' — '.$cat->name : '')
            .'. '.ucfirst(config('company.supplier_label')).' · '.config('contact.brand').'. Cere ofertă.');
    }

    public function ogImageUrl(): ?string
    {
        return $this->primaryImage()?->url();
    }

    // ── Specs (afișare; doar ce există — fără fabricare) ─────────────────────

    /** Etichetă material pentru chip/card, ex. „Lemn + metal". Null dacă lipsește. */
    public function materialLabel(): ?string
    {
        $materials = $this->specs['material'] ?? null;
        if (empty($materials)) {
            return null;
        }

        return ucfirst(implode(' + ', (array) $materials));
    }

    /**
     * Specs pentru tabelul de pe pagina produs — DOAR câmpurile prezente, în ordine.
     *
     * @return array<string, string> label → value
     */
    public function displaySpecs(): array
    {
        $specs = (array) $this->specs;
        $out = [];

        if (! empty($specs['material'])) {
            $out['Material'] = ucfirst(implode(', ', (array) $specs['material']));
        }
        if (! empty($specs['dimensiuni'])) {
            $out['Dimensiuni'] = implode(' · ', (array) $specs['dimensiuni']);
        }
        if (! empty($specs['montaj'])) {
            $out['Montaj'] = is_array($specs['montaj']) ? implode(', ', $specs['montaj']) : $specs['montaj'];
        }
        if (! empty($specs['finisaj'])) {
            $out['Finisaj'] = implode(', ', array_map('ucfirst', (array) $specs['finisaj']));
        }

        return $out;
    }

    /**
     * Calea imaginii primary (is_primary, fallback prima după sort_order),
     * relativă la disk-ul public. Pentru ImageColumn în tabelul Filament.
     */
    protected function primaryImagePath(): Attribute
    {
        return Attribute::get(fn (): ?string => $this->primaryImage()?->path);
    }
}
