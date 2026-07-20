<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

/**
 * Recenzie REALĂ de la un vizitator/client, prin formularul public.
 * Owner-ul doar moderează (approve/reject) — nu creează și nu editează conținut.
 */
class ProductReview extends Model
{
    public const STATUSES = ['pending', 'approved', 'rejected'];

    public const STATUS_LABELS = [
        'pending' => 'În așteptare',
        'approved' => 'Aprobată',
        'rejected' => 'Respinsă',
    ];

    protected $fillable = [
        'product_id', 'author_name', 'author_email', 'rating', 'title', 'body',
        'status', 'verified_purchase',
    ];

    protected $casts = [
        'rating' => 'int',
        'verified_purchase' => 'bool',
    ];

    public function product(): BelongsTo
    {
        return $this->belongsTo(Product::class);
    }

    public function scopeApproved(Builder $query): Builder
    {
        return $query->where('status', 'approved');
    }
}
