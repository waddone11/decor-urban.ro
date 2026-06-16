<?php

namespace App\Models;

use App\Models\Concerns\HasImageThumbnails;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Support\Facades\Storage;

class ProductImage extends Model
{
    use HasImageThumbnails;

    protected $fillable = [
        'product_id',
        'path',
        'alt',
        'sort_order',
        'is_primary',
        'source',
        'enhanced_at',
    ];

    protected $casts = [
        'is_primary' => 'bool',
        'sort_order' => 'int',
        'enhanced_at' => 'datetime',
    ];

    public function product(): BelongsTo
    {
        return $this->belongsTo(Product::class);
    }

    public function url(): string
    {
        return Storage::disk('public')->url($this->path);
    }
}
