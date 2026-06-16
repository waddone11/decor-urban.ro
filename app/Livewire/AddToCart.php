<?php

namespace App\Livewire;

use App\Support\Cart;
use Livewire\Component;

class AddToCart extends Component
{
    public int $productId;

    public int $qty = 1;

    public bool $added = false;

    public function increment(): void
    {
        $this->qty++;
    }

    public function decrement(): void
    {
        $this->qty = max(1, $this->qty - 1);
    }

    public function updatedQty(): void
    {
        $this->qty = max(1, (int) $this->qty);
    }

    public function add(): void
    {
        Cart::add($this->productId, max(1, (int) $this->qty));

        $this->added = true;
        $this->qty = 1;

        $this->dispatch('cart-updated');
    }

    public function render()
    {
        return view('livewire.add-to-cart');
    }
}
