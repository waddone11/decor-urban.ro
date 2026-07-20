@props(['title' => 'Ce spun clienții'])

@php
    $reviewsData = \App\Support\GoogleReviews::cached();
    $reviews = collect($reviewsData['reviews'] ?? [])->take(5);
@endphp

@if ($reviews->isNotEmpty())
    <section {{ $attributes->merge(['class' => 'border-t border-line pt-10']) }}>
        <div class="flex flex-col gap-3 sm:flex-row sm:items-end sm:justify-between">
            <div>
                <h2 class="text-xl font-bold text-ink">{{ $title }}</h2>
                <p class="mt-1 text-sm text-ink-soft">
                    Recenzii reale afișate din Google.
                    @if ($reviewsData['rating'])
                        Rating {{ number_format((float) $reviewsData['rating'], 1, ',', '.') }}/5
                        @if ($reviewsData['user_ratings_total'])
                            din {{ $reviewsData['user_ratings_total'] }} recenzii
                        @endif
                    @endif
                </p>
            </div>
            <a href="{{ config('business.google_maps_url') }}" target="_blank" rel="noopener noreferrer"
               class="text-sm font-semibold text-accent hover:text-accent-hover">Vezi pe Google</a>
        </div>

        <div class="mt-6 grid gap-4 md:grid-cols-2 lg:grid-cols-3">
            @foreach ($reviews as $review)
                <article class="rounded-card border border-line bg-surface-card p-5">
                    <div class="flex items-center gap-3">
                        @if ($review['profile_photo_url'])
                            <img src="{{ $review['profile_photo_url'] }}" alt="{{ $review['author_name'] }}" width="40" height="40" loading="lazy" class="h-10 w-10 rounded-full">
                        @endif
                        <div>
                            <h3 class="text-sm font-semibold text-ink">{{ $review['author_name'] }}</h3>
                            <a href="{{ $review['author_url'] ?: config('business.google_maps_url') }}" target="_blank" rel="noopener noreferrer" class="text-xs text-ink-muted hover:text-accent">pe Google</a>
                        </div>
                    </div>
                    <div class="mt-3 flex items-center gap-1 text-accent" aria-label="{{ $review['rating'] }} din 5 stele">
                        @for ($i = 1; $i <= 5; $i++)
                            <svg class="h-4 w-4 {{ $i <= (int) $review['rating'] ? 'fill-current' : 'fill-none' }}" viewBox="0 0 20 20" stroke="currentColor" aria-hidden="true"><path d="m10 1.7 2.5 5.1 5.6.8-4 3.9.9 5.5-5-2.6-5 2.6.9-5.5-4-3.9 5.6-.8L10 1.7Z"/></svg>
                        @endfor
                        @if ($review['relative_time_description'])
                            <span class="ml-2 text-xs text-ink-muted">{{ $review['relative_time_description'] }}</span>
                        @endif
                    </div>
                    @if ($review['text'])
                        <p class="mt-3 text-sm leading-relaxed text-ink-soft">{{ $review['text'] }}</p>
                    @endif
                </article>
            @endforeach
        </div>
    </section>
@elseif (config('business.google_review_url'))
    <section {{ $attributes->merge(['class' => 'border-t border-line pt-10']) }}>
        <div class="rounded-card border border-line bg-tint-sky p-5">
            <h2 class="text-lg font-bold text-ink">{{ $title }}</h2>
            <p class="mt-1 text-sm text-ink-soft">Recenziile reale vor apărea aici după sincronizarea cu Google.</p>
            <x-storefront.google-review-cta class="mt-4" />
        </div>
    </section>
@endif
