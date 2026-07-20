<section class="mt-10 border-t border-line pt-8" aria-labelledby="recenzii-titlu">
    <div class="flex flex-wrap items-center justify-between gap-3">
        <h2 id="recenzii-titlu" class="text-xl font-bold text-ink">Recenzii</h2>
        @if ($stats->cnt > 0)
            <p class="flex items-center gap-2 text-sm text-ink-soft">
                <span class="flex items-center gap-0.5" aria-hidden="true">
                    @for ($i = 1; $i <= 5; $i++)
                        <svg class="h-4 w-4 {{ $i <= round($stats->avg_rating) ? 'text-accent-warm' : 'text-line' }}" viewBox="0 0 20 20" fill="currentColor"><path d="M9.049 2.927c.3-.921 1.603-.921 1.902 0l1.519 4.674a1 1 0 0 0 .95.69h4.915c.969 0 1.371 1.24.588 1.81l-3.976 2.888a1 1 0 0 0-.363 1.118l1.518 4.674c.3.922-.755 1.688-1.538 1.118l-3.976-2.888a1 1 0 0 0-1.176 0l-3.976 2.888c-.783.57-1.838-.196-1.538-1.118l1.518-4.674a1 1 0 0 0-.363-1.118L2.077 10.1c-.783-.57-.38-1.81.588-1.81h4.914a1 1 0 0 0 .951-.69z"/></svg>
                    @endfor
                </span>
                <strong class="text-ink">{{ number_format((float) $stats->avg_rating, 1, ',', '') }}</strong>
                din {{ $stats->cnt }} {{ $stats->cnt === 1 ? 'recenzie' : 'recenzii' }}
            </p>
        @endif
    </div>

    {{-- Lista recenziilor aprobate --}}
    @if ($reviews->isEmpty())
        <p class="mt-4 text-sm text-ink-soft">Fii primul care lasă o recenzie pentru acest produs.</p>
    @else
        <ul class="mt-5 space-y-5">
            @foreach ($reviews as $review)
                <li class="rounded-card border border-line bg-surface-card p-4">
                    <div class="flex flex-wrap items-center gap-x-3 gap-y-1">
                        <span class="flex items-center gap-0.5" role="img" aria-label="{{ $review->rating }} din 5 stele">
                            @for ($i = 1; $i <= 5; $i++)
                                <svg class="h-4 w-4 {{ $i <= $review->rating ? 'text-accent-warm' : 'text-line' }}" viewBox="0 0 20 20" fill="currentColor" aria-hidden="true"><path d="M9.049 2.927c.3-.921 1.603-.921 1.902 0l1.519 4.674a1 1 0 0 0 .95.69h4.915c.969 0 1.371 1.24.588 1.81l-3.976 2.888a1 1 0 0 0-.363 1.118l1.518 4.674c.3.922-.755 1.688-1.538 1.118l-3.976-2.888a1 1 0 0 0-1.176 0l-3.976 2.888c-.783.57-1.838-.196-1.538-1.118l1.518-4.674a1 1 0 0 0-.363-1.118L2.077 10.1c-.783-.57-.38-1.81.588-1.81h4.914a1 1 0 0 0 .951-.69z"/></svg>
                            @endfor
                        </span>
                        <span class="font-semibold text-ink">{{ $review->author_name }}</span>
                        @if ($review->verified_purchase)
                            <span class="inline-flex items-center gap-1 rounded-full bg-accent-soft px-2 py-0.5 text-xs font-medium text-accent">
                                <svg class="h-3 w-3" viewBox="0 0 20 20" fill="currentColor" aria-hidden="true"><path fill-rule="evenodd" d="M16.704 4.153a.75.75 0 0 1 .143 1.052l-8 10.5a.75.75 0 0 1-1.127.075l-4.5-4.5a.75.75 0 0 1 1.06-1.06l3.894 3.893 7.48-9.817a.75.75 0 0 1 1.05-.143z" clip-rule="evenodd"/></svg>
                                Achiziție verificată
                            </span>
                        @endif
                        <time class="text-xs text-ink-muted" datetime="{{ $review->created_at->toDateString() }}">{{ $review->created_at->format('d.m.Y') }}</time>
                    </div>
                    @if ($review->title)
                        <p class="mt-2 font-semibold text-ink">{{ $review->title }}</p>
                    @endif
                    <p class="mt-1 text-sm leading-relaxed text-ink-soft">{{ $review->body }}</p>
                </li>
            @endforeach
        </ul>
    @endif

    {{-- Formular recenzie nouă --}}
    <div class="mt-8 rounded-card border border-line bg-surface-card p-5">
        @if ($submitted)
            <p class="rounded-lg bg-accent-soft px-4 py-3 text-sm font-medium text-accent" role="status">
                Mulțumim! Recenzia va apărea după verificare.
            </p>
        @else
            <h3 class="text-base font-semibold text-ink">Lasă o recenzie</h3>
            <form wire:submit="submit" class="mt-4 grid gap-4 sm:grid-cols-2">
                {{-- Honeypot (invizibil pentru oameni) --}}
                <div class="hidden" aria-hidden="true">
                    <label>Website <input type="text" wire:model="website" tabindex="-1" autocomplete="off"></label>
                </div>

                <div>
                    <label for="review-name" class="block text-sm font-medium text-ink">Nume</label>
                    <input id="review-name" type="text" wire:model="author_name" required
                           class="mt-1 w-full rounded-button border-line text-sm focus:border-accent focus:ring-accent">
                    @error('author_name')<p class="mt-1 text-xs text-red-600">{{ $message }}</p>@enderror
                </div>

                <div>
                    <label for="review-email" class="block text-sm font-medium text-ink">Email <span class="font-normal text-ink-muted">(nu se publică)</span></label>
                    <input id="review-email" type="email" wire:model="author_email" required
                           class="mt-1 w-full rounded-button border-line text-sm focus:border-accent focus:ring-accent">
                    @error('author_email')<p class="mt-1 text-xs text-red-600">{{ $message }}</p>@enderror
                </div>

                <fieldset class="sm:col-span-2">
                    <legend class="text-sm font-medium text-ink">Nota ta</legend>
                    <div class="mt-1 flex gap-1">
                        @for ($i = 1; $i <= 5; $i++)
                            <label class="cursor-pointer">
                                <input type="radio" wire:model.live="rating" value="{{ $i }}" class="sr-only" aria-label="{{ $i }} {{ $i === 1 ? 'stea' : 'stele' }}">
                                <svg class="h-7 w-7 transition-colors {{ (int) $rating >= $i ? 'text-accent-warm' : 'text-line hover:text-accent-warm/60' }}" viewBox="0 0 20 20" fill="currentColor" aria-hidden="true"><path d="M9.049 2.927c.3-.921 1.603-.921 1.902 0l1.519 4.674a1 1 0 0 0 .95.69h4.915c.969 0 1.371 1.24.588 1.81l-3.976 2.888a1 1 0 0 0-.363 1.118l1.518 4.674c.3.922-.755 1.688-1.538 1.118l-3.976-2.888a1 1 0 0 0-1.176 0l-3.976 2.888c-.783.57-1.838-.196-1.538-1.118l1.518-4.674a1 1 0 0 0-.363-1.118L2.077 10.1c-.783-.57-.38-1.81.588-1.81h4.914a1 1 0 0 0 .951-.69z"/></svg>
                            </label>
                        @endfor
                    </div>
                    @error('rating')<p class="mt-1 text-xs text-red-600">{{ $message }}</p>@enderror
                </fieldset>

                <div class="sm:col-span-2">
                    <label for="review-title" class="block text-sm font-medium text-ink">Titlu <span class="font-normal text-ink-muted">(opțional)</span></label>
                    <input id="review-title" type="text" wire:model="title"
                           class="mt-1 w-full rounded-button border-line text-sm focus:border-accent focus:ring-accent">
                    @error('title')<p class="mt-1 text-xs text-red-600">{{ $message }}</p>@enderror
                </div>

                <div class="sm:col-span-2">
                    <label for="review-body" class="block text-sm font-medium text-ink">Recenzia ta</label>
                    <textarea id="review-body" rows="4" wire:model="body" required
                              class="mt-1 w-full rounded-button border-line text-sm focus:border-accent focus:ring-accent"></textarea>
                    @error('body')<p class="mt-1 text-xs text-red-600">{{ $message }}</p>@enderror
                </div>

                <div class="sm:col-span-2">
                    <button type="submit" wire:loading.attr="disabled" wire:target="submit"
                            class="inline-flex items-center justify-center rounded-button bg-accent px-6 py-2.5 text-sm font-medium text-white transition-colors hover:bg-accent-hover focus:outline-none focus-visible:ring-2 focus-visible:ring-offset-2 focus-visible:ring-accent disabled:opacity-60">
                        <span wire:loading.remove wire:target="submit">Trimite recenzia</span>
                        <span wire:loading wire:target="submit">Se trimite…</span>
                    </button>
                    <p class="mt-2 text-xs text-ink-muted">Recenzia apare public după verificare. Emailul nu se publică.</p>
                </div>
            </form>
        @endif
    </div>
</section>
