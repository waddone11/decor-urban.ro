<?php

namespace App\Livewire;

use App\Models\Order;
use Livewire\Component;

class OrderSuccess extends Component
{
    public Order $order;

    public function mount(string $number): void
    {
        $this->order = Order::with('items')->where('number', $number)->firstOrFail();
    }

    public function render()
    {
        return view('livewire.order-success')
            ->layout('components.layouts.storefront', ['title' => 'Comanda '.$this->order->number]);
    }
}
