<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Support\Facades\DB;

class Order extends Model
{
    public const STATUSES = ['noua', 'in_lucru', 'ofertata', 'confirmata', 'livrata', 'anulata'];

    public const STATUS_LABELS = [
        'noua' => 'Nouă',
        'in_lucru' => 'În lucru',
        'ofertata' => 'Ofertată',
        'confirmata' => 'Confirmată',
        'livrata' => 'Livrată',
        'anulata' => 'Anulată',
    ];

    public const PAYMENT_METHODS = [
        'ramburs' => 'Ramburs la livrare',
        'whatsapp' => 'Stabilim pe WhatsApp',
    ];

    protected $fillable = [
        'number', 'customer_name', 'company', 'cui', 'phone', 'email',
        'county', 'city', 'address', 'payment_method', 'notes', 'status', 'total',
    ];

    protected $casts = [
        'total' => 'decimal:2',
    ];

    protected $attributes = [
        'status' => 'noua',
    ];

    public function items(): HasMany
    {
        return $this->hasMany(OrderItem::class);
    }

    public function getRouteKeyName(): string
    {
        return 'number';
    }

    public function statusLabel(): string
    {
        return self::STATUS_LABELS[$this->status] ?? $this->status;
    }

    public function paymentMethodLabel(): string
    {
        return self::PAYMENT_METHODS[$this->payment_method] ?? $this->payment_method;
    }

    /**
     * Următorul număr de comandă lizibil: DU-{an}-{secvență 4 cifre}.
     * Secvențial pe an, sub lock pentru a evita coliziunile.
     */
    public static function generateNumber(?int $year = null): string
    {
        $year ??= (int) date('Y');
        $prefix = "DU-{$year}-";

        $last = static::query()
            ->where('number', 'like', $prefix.'%')
            ->lockForUpdate()
            ->orderByDesc('number')
            ->value('number');

        $seq = $last ? ((int) substr($last, strlen($prefix))) + 1 : 1;

        return $prefix.str_pad((string) $seq, 4, '0', STR_PAD_LEFT);
    }

    /**
     * Creează o comandă cu număr unic generat atomic (în tranzacție).
     *
     * @param  array<string, mixed>  $attributes
     */
    public static function createWithNumber(array $attributes): self
    {
        return DB::transaction(function () use ($attributes) {
            $attributes['number'] = static::generateNumber();

            return static::create($attributes);
        });
    }
}
