@php
    $categories = $navCategories ?? \App\Models\Category::query()->active()->ordered()->get();
    $phone = config('contact.phone');
    $email = config('contact.email');
    $whatsapp = config('contact.whatsapp');
    $isPlaceholder = config('contact.is_placeholder');
    $companyPlaceholder = config('company.is_placeholder');
    $legalName = config('company.legal_name');
    $cui = config('company.cui');
    $regCom = config('company.reg_com');
    $address = config('company.address');
    $supplierLabel = config('company.supplier_label');
@endphp

<footer id="contact" class="mt-24 border-t border-line bg-surface-card">
    <div class="mx-auto max-w-7xl px-4 sm:px-6 lg:px-8 py-14">
        <div class="grid grid-cols-2 gap-10 md:grid-cols-4">
            {{-- Brand + identitate legală --}}
            <div class="col-span-2 md:col-span-1">
                <span class="font-display text-xl font-bold tracking-[-0.02em] text-ink">Decor <span class="text-accent">Urban</span></span>
                <p class="mt-3 text-sm leading-relaxed text-ink-soft">
                    Mobilier stradal &amp; urban — {{ $supplierLabel }}. Bănci, coșuri, jardiniere,
                    locuri de joacă și soluții custom pentru spații publice.
                </p>
                @if ($legalName || $cui || $regCom || $address)
                    <dl class="mt-4 space-y-0.5 text-xs leading-relaxed text-ink-muted">
                        @if ($legalName)<div class="font-semibold text-ink-soft">{{ $legalName }}</div>@endif
                        @if ($cui)<div>CUI {{ $cui }}@if ($regCom) · Reg. Com. {{ $regCom }}@endif</div>@endif
                        @if ($address)<div>{{ $address }}</div>@endif
                    </dl>
                @endif
            </div>

            {{-- Categorii --}}
            <div>
                <h3 class="text-sm font-semibold uppercase tracking-wider text-ink">Categorii</h3>
                <ul class="mt-4 space-y-2">
                    @foreach ($categories as $category)
                        <li>
                            <a href="#" class="text-sm text-ink-soft hover:text-accent transition-colors">{{ $category->name }}</a>
                        </li>
                    @endforeach
                </ul>
            </div>

            {{-- Informații --}}
            <div>
                <h3 class="text-sm font-semibold uppercase tracking-wider text-ink">Informații</h3>
                <ul class="mt-4 space-y-2">
                    <li><a href="#" class="text-sm text-ink-soft hover:text-accent transition-colors">Despre noi</a></li>
                    <li><a href="#" class="text-sm text-ink-soft hover:text-accent transition-colors">Contact</a></li>
                    <li><a href="#" class="text-sm text-ink-soft hover:text-accent transition-colors">Politică de confidențialitate</a></li>
                    <li><a href="#" class="text-sm text-ink-soft hover:text-accent transition-colors">Termeni și condiții</a></li>
                </ul>
            </div>

            {{-- Contact --}}
            <div>
                <h3 class="text-sm font-semibold uppercase tracking-wider text-ink">Contact</h3>
                <ul class="mt-4 space-y-2 text-sm text-ink-soft">
                    <li>
                        <a href="tel:{{ preg_replace('/\s+/', '', $phone) }}" class="hover:text-accent transition-colors">{{ $phone }}</a>
                    </li>
                    <li>
                        <a href="mailto:{{ $email }}" class="hover:text-accent transition-colors">{{ $email }}</a>
                    </li>
                    <li>
                        <a href="https://wa.me/{{ $whatsapp }}" class="inline-flex items-center gap-1.5 font-medium text-accent hover:text-accent-hover transition-colors">
                            <svg class="h-4 w-4" fill="currentColor" viewBox="0 0 24 24"><path d="M.057 24l1.687-6.163a11.867 11.867 0 0 1-1.587-5.946C.16 5.335 5.495 0 12.05 0a11.82 11.82 0 0 1 8.413 3.488 11.82 11.82 0 0 1 3.48 8.414c-.003 6.557-5.338 11.892-11.893 11.892a11.9 11.9 0 0 1-5.688-1.448L.057 24zm6.597-3.807c1.676.995 3.276 1.591 5.392 1.592 5.448 0 9.886-4.434 9.889-9.885.002-5.462-4.415-9.89-9.881-9.892-5.452 0-9.887 4.434-9.889 9.884a9.86 9.86 0 0 0 1.51 5.26l-.999 3.648 3.488-1.607z"/></svg>
                            WhatsApp
                        </a>
                    </li>
                    @if ($isPlaceholder)
                        <li class="mt-1 text-xs text-ink-muted">⚠️ Date de contact provizorii — de confirmat.</li>
                    @endif
                </ul>
            </div>
        </div>

        @if ($companyPlaceholder)
            <p class="mt-8 text-xs text-ink-muted">⚠️ TODO de confirmat: cod CPV, prezență SEAP, proiecte livrate, referințe, standarde (ex. EN 1176) — de completat în <code>config/company.php</code>.</p>
        @endif

        <div class="mt-12 flex flex-col gap-3 border-t border-line pt-6 sm:flex-row sm:items-center sm:justify-between">
            <p class="text-sm text-ink-muted">© {{ date('Y') }} Decor Urban. Toate drepturile rezervate.</p>
            <p class="text-sm text-ink-muted">Producător direct · Livrare în toată țara</p>
        </div>
    </div>
</footer>
