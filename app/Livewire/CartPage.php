<?php

namespace App\Livewire;

use App\Support\Cart;
use Livewire\Component;

class CartPage extends Component
{
    public function updateQty(int $productId, int $qty): void
    {
        Cart::setQty($productId, max(1, $qty));
        $this->dispatch('cart-updated');
    }

    public function increment(int $productId): void
    {
        $line = Cart::lines()->firstWhere('product.id', $productId);
        Cart::setQty($productId, ($line['qty'] ?? 0) + 1);
        $this->dispatch('cart-updated');
    }

    public function decrement(int $productId): void
    {
        $line = Cart::lines()->firstWhere('product.id', $productId);
        Cart::setQty($productId, max(1, ($line['qty'] ?? 1) - 1));
        $this->dispatch('cart-updated');
    }

    public function remove(int $productId): void
    {
        Cart::remove($productId);
        $this->dispatch('cart-updated');
    }

    public function render()
    {
        return view('livewire.cart-page', [
            'lines' => Cart::lines(),
            'count' => Cart::count(),
        ])->layout('components.layouts.storefront', ['title' => 'Coșul tău']);
    }
}
