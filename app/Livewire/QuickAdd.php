<?php

namespace App\Livewire;

use App\Support\Cart;
use Livewire\Component;

/**
 * Buton „adaugă în coș" rapid (cantitate 1) pentru cardul de produs — refolosit
 * în grile (catalog, categorie, similare, homepage). Refolosește acțiunea din 4c.
 */
class QuickAdd extends Component
{
    public int $productId;

    public bool $added = false;

    public function add(): void
    {
        Cart::add($this->productId, 1);
        $this->added = true;
        $this->dispatch('cart-updated');
    }

    public function render()
    {
        return view('livewire.quick-add');
    }
}
