<?php

namespace App\Support;

use App\Models\Product;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Session;

/**
 * Coș guest pe sesiune. Prețuri „la cerere" → coșul e o cerere de ofertă,
 * fără total. Made-to-order → fără stoc/limită; cantitatea = cât cere clientul.
 *
 * Stocare: ['cart' => [productId => qty]].
 */
class Cart
{
    public const SESSION_KEY = 'cart';

    /** @return array<int, int> [productId => qty] */
    public static function raw(): array
    {
        return Session::get(self::SESSION_KEY, []);
    }

    public static function add(int $productId, int $qty = 1): void
    {
        $qty = max(1, $qty);
        $items = self::raw();
        $items[$productId] = ($items[$productId] ?? 0) + $qty;
        self::persist($items);
    }

    public static function setQty(int $productId, int $qty): void
    {
        $items = self::raw();

        if ($qty < 1) {
            unset($items[$productId]);
        } else {
            $items[$productId] = $qty;
        }

        self::persist($items);
    }

    public static function remove(int $productId): void
    {
        $items = self::raw();
        unset($items[$productId]);
        self::persist($items);
    }

    public static function clear(): void
    {
        Session::forget(self::SESSION_KEY);
    }

    /** Suma cantităților (badge header). */
    public static function count(): int
    {
        return array_sum(self::raw());
    }

    public static function isEmpty(): bool
    {
        return self::count() === 0;
    }

    /**
     * Liniile coșului cu produsele încărcate (doar produse active existente).
     * Curăță automat produsele dispărute/inactive din sesiune.
     *
     * @return Collection<int, array{product: Product, qty: int}>
     */
    public static function lines(): Collection
    {
        $items = self::raw();
        if ($items === []) {
            return collect();
        }

        $products = Product::query()
            ->whereIn('id', array_keys($items))
            ->where('is_active', true)
            ->with('images')
            ->get()
            ->keyBy('id');

        // Sincronizează sesiunea dacă unele produse nu mai există/sunt inactive.
        $cleaned = array_intersect_key($items, $products->all());
        if (count($cleaned) !== count($items)) {
            self::persist($cleaned);
        }

        return collect($cleaned)->map(fn (int $qty, int $id) => [
            'product' => $products[$id],
            'qty' => $qty,
        ])->values();
    }

    /** @param array<int, int> $items */
    private static function persist(array $items): void
    {
        Session::put(self::SESSION_KEY, $items);
    }
}
