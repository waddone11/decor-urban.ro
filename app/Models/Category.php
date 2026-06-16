<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Category extends Model
{
    protected $fillable = [
        'name',
        'slug',
        'description',
        'intro',
        'parent_id',
        'sort_order',
        'is_active',
    ];

    protected $casts = [
        'is_active' => 'bool',
        'sort_order' => 'int',
    ];

    /**
     * Binding scopat: în storefront `{category:slug}` rezolvă DOAR categorii active
     * (inactivă → 404). Filament leagă pe id (field null), deci nu e afectat.
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

    public function parent(): BelongsTo
    {
        return $this->belongsTo(self::class, 'parent_id');
    }

    public function children(): HasMany
    {
        return $this->hasMany(self::class, 'parent_id');
    }

    public function products(): BelongsToMany
    {
        return $this->belongsToMany(Product::class)
            ->withPivot('is_primary');
    }

    public function scopeActive(Builder $query): Builder
    {
        return $query->where('is_active', true);
    }

    public function scopeOrdered(Builder $query): Builder
    {
        return $query->orderBy('sort_order')->orderBy('name');
    }

    // ── SEO ────────────────────────────────────────────────────────────────

    public function seoTitle(): string
    {
        return $this->name;
    }

    public function seoDescription(): string
    {
        if ($this->description) {
            return (string) \Illuminate\Support\Str::of($this->description)->stripTags()->squish()->limit(155);
        }

        return $this->name.' — '.ucfirst(config('company.supplier_label')).' de mobilier urban și stradal. '
            .config('contact.brand').'. Cere ofertă pentru proiectul tău.';
    }

    /**
     * Calea unei imagini reprezentative (prima imagine a primului produs activ
     * din categorie), relativă la disk-ul public. Pentru cardurile de categorie.
     */
    public function representativeImagePath(): ?string
    {
        $product = $this->products()
            ->where('is_active', true)
            ->whereHas('images')
            ->with('images')
            ->first();

        return $product?->primaryImage()?->path;
    }
}
