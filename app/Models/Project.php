<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Casts\Attribute;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Project extends Model
{
    public const CLIENT_TYPES = [
        'primarie' => 'Primărie',
        'scoala' => 'Școală',
        'firma' => 'Firmă',
        'alt' => 'Altele',
    ];

    protected $fillable = [
        'title', 'slug', 'location', 'client_type', 'summary', 'body', 'year', 'is_published', 'sort_order',
    ];

    protected $casts = [
        'is_published' => 'bool',
        'sort_order' => 'int',
    ];

    public function images(): HasMany
    {
        return $this->hasMany(ProjectImage::class)->orderBy('sort_order');
    }

    public function primaryImage(): ?ProjectImage
    {
        return $this->images->firstWhere('is_primary', true)
            ?? $this->images->sortBy('sort_order')->first();
    }

    public function clientTypeLabel(): ?string
    {
        return $this->client_type ? (self::CLIENT_TYPES[$this->client_type] ?? $this->client_type) : null;
    }

    /** Calea imaginii primary, pentru ImageColumn în Filament. */
    protected function primaryImagePath(): Attribute
    {
        return Attribute::get(fn (): ?string => $this->primaryImage()?->path);
    }

    public function scopePublished(Builder $query): Builder
    {
        return $query->where('is_published', true);
    }

    public function scopeOrdered(Builder $query): Builder
    {
        return $query->orderBy('sort_order')->orderByDesc('year')->orderByDesc('id');
    }

    /**
     * Binding scopat: în storefront `{project:slug}` rezolvă DOAR proiecte publicate
     * (nepublicat/inexistent → 404). Filament leagă pe id (field null), neafectat.
     */
    public function resolveRouteBinding($value, $field = null)
    {
        $field ??= $this->getRouteKeyName();
        $query = $this->where($field, $value);

        if ($field === 'slug') {
            $query->where('is_published', true);
        }

        return $query->first();
    }
}
