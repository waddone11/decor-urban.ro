<?php

use Livewire\Component;

new class extends Component
{
    public int $count = 0;

    public function increment(): void
    {
        $this->count++;
    }
};
?>

<div class="flex min-h-screen flex-col items-center justify-center gap-8 bg-neutral-950 px-6 text-center text-neutral-100">
    <div class="flex flex-col items-center gap-3">
        <span class="rounded-full border border-emerald-500/40 bg-emerald-500/10 px-3 py-1 text-xs font-medium uppercase tracking-widest text-emerald-400">
            Faza 0 — stack ready
        </span>
        <h1 class="text-4xl font-semibold tracking-tight sm:text-5xl">
            Decor Urban
        </h1>
        <p class="max-w-md text-sm text-neutral-400">
            Mobilier stradal &amp; urban. Acesta este un placeholder de infrastructură —
            Laravel 13, Filament 5, Livewire 4 și Tailwind 4 rulează corect.
        </p>
    </div>

    <div class="flex flex-col items-center gap-3">
        <button
            type="button"
            wire:click="increment"
            class="rounded-lg bg-emerald-500 px-5 py-2.5 text-sm font-medium text-neutral-950 transition hover:bg-emerald-400 focus:outline-none focus-visible:ring-2 focus-visible:ring-emerald-300"
        >
            Livewire test — apăsat de {{ $count }} ori
        </button>
        <span class="text-xs text-neutral-500" data-testid="count">{{ $count }}</span>
    </div>

    <a
        href="/admin"
        class="text-sm font-medium text-emerald-400 underline-offset-4 hover:underline"
    >
        Mergi la panoul de administrare →
    </a>
</div>
