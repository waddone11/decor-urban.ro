<?php

namespace App\Models;

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
    ];

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
     * Calea imaginii primary (is_primary, fallback prima după sort_order),
     * relativă la disk-ul public. Pentru ImageColumn în tabelul Filament.
     */
    protected function primaryImagePath(): Attribute
    {
        return Attribute::get(fn (): ?string => $this->primaryImage()?->path);
    }
}
