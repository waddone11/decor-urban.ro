<?php

namespace App\Livewire;

use App\Support\Cart;
use Livewire\Attributes\On;
use Livewire\Component;

class CartCounter extends Component
{
    public int $count = 0;

    public function mount(): void
    {
        $this->count = Cart::count();
    }

    #[On('cart-updated')]
    public function refresh(): void
    {
        $this->count = Cart::count();
    }

    public function render()
    {
        return view('livewire.cart-counter');
    }
}
