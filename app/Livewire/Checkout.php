<?php

namespace App\Livewire;

use Livewire\Component;

class Checkout extends Component
{
    // Implementat complet în Partea 2 (formular + creare comandă).
    public function render()
    {
        return view('livewire.checkout')
            ->layout('components.layouts.storefront', ['title' => 'Finalizează comanda']);
    }
}
