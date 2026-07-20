<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class OrderItem extends Model
{
    protected $fillable = [
        'order_id', 'product_id', 'product_name', 'product_code', 'quantity', 'unit_price', 'line_note',
    ];

    protected $casts = [
        'quantity' => 'int',
        'unit_price' => 'decimal:2',
    ];

    /** Totalul liniei (unit_price snapshot × cantitate); null la „la cerere". */
    public function lineTotal(): ?float
    {
        return $this->unit_price === null ? null : (float) $this->unit_price * $this->quantity;
    }

    /** „999,00 lei" sau „La cerere" — pentru email/coș/WhatsApp. */
    public function priceLabel(): string
    {
        return $this->unit_price === null ? 'La cerere' : Product::formatLei((float) $this->unit_price);
    }

    public function order(): BelongsTo
    {
        return $this->belongsTo(Order::class);
    }

    public function product(): BelongsTo
    {
        return $this->belongsTo(Product::class);
    }
}
