@props([
    'title',
    'description' => null,
    'updated' => null,
])

<x-layouts.storefront :title="$title" :description="$description">
    <x-seo.jsonld :data="\App\Support\JsonLd::breadcrumb([
        ['name' => 'Acasă', 'url' => url('/')],
        ['name' => $title, 'url' => url()->current()],
    ])" />

    <div class="mx-auto max-w-7xl px-4 py-8 sm:px-6 lg:px-8">
        <x-storefront.breadcrumb :items="[
            ['label' => 'Acasă', 'url' => url('/')],
            ['label' => $title],
        ]" class="mb-6" />

        <h1 class="text-3xl font-bold text-ink sm:text-4xl">{{ $title }}</h1>

        {{-- Avertisment vizibil: șablon de verificat juridic, nu consultanță. --}}
        <div class="mt-6 rounded-card border border-amber-300 bg-amber-50 p-4 text-sm text-amber-900">
            ⚠️ <strong>Document model, de verificat de un specialist înainte de lansare.</strong>
            Nu constituie consultanță juridică. Se completează cu datele reale ale firmei
            (<code>config/company.php</code>) și se adaptează la activitatea reală.
        </div>

        <div class="mt-8 space-y-6 text-ink-soft leading-relaxed [&_h2]:text-xl [&_h2]:font-bold [&_h2]:text-ink [&_h2]:mt-8 [&_p]:mt-2 [&_ul]:mt-2 [&_ul]:list-disc [&_ul]:pl-6 [&_a]:text-accent [&_a]:underline">
            {{ $slot }}
        </div>

        @if ($updated)
            <p class="mt-10 text-xs text-ink-muted">Ultima actualizare: {{ $updated }}</p>
        @endif
    </div>
</x-layouts.storefront>
