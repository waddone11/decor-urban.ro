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
    $whatsappUrl = \App\Support\Business::whatsappUrl();
@endphp

<footer id="contact" class="mt-24 border-t border-shell-line bg-shell text-ink">
    <div class="mx-auto max-w-7xl px-4 sm:px-6 lg:px-8 py-14">
        <div class="grid grid-cols-2 gap-10 md:grid-cols-4">
            {{-- Brand: logo (marcă + wordmark) + identitate legală --}}
            <div class="col-span-2 md:col-span-1">
                <a href="{{ url('/') }}" class="flex items-center gap-2.5">
                    <img src="{{ asset('images/logo.svg') }}" alt="Decor Urban" class="h-9 w-9 text-ink" width="36" height="36">
                    <x-wordmark class="text-xl" />
                </a>
                <p class="mt-4 text-sm leading-relaxed text-ink-soft">
                    Mobilier stradal &amp; urban — {{ $supplierLabel }}. Bănci, coșuri, jardiniere,
                    locuri de joacă și soluții custom pentru spații publice.
                </p>
                @if ($legalName || $cui || $regCom || $address)
                    <dl class="mt-4 space-y-0.5 text-xs leading-relaxed text-ink-soft">
                        @if ($legalName)<div class="font-semibold text-ink">{{ $legalName }}</div>@endif
                        @if ($cui)<div>CUI {{ $cui }}@if ($regCom) · Reg. Com. {{ $regCom }}@endif</div>@endif
                        @if ($address)<div>{{ $address }}</div>@endif
                    </dl>
                @endif
                <x-storefront.social-links class="mt-5" />
            </div>

            {{-- Categorii --}}
            <div>
                <h3 class="text-sm font-semibold uppercase tracking-wider text-ink">Categorii</h3>
                <ul class="mt-4 space-y-2">
                    @foreach ($categories as $category)
                        <li>
                            <a href="{{ route('category', $category->slug) }}" class="text-sm text-ink-soft hover:text-accent transition-colors">{{ $category->name }}</a>
                        </li>
                    @endforeach
                </ul>
            </div>

            {{-- Informații --}}
            <div>
                <h3 class="text-sm font-semibold uppercase tracking-wider text-ink">Informații</h3>
                <ul class="mt-4 space-y-2">
                    <li><a href="{{ route('despre') }}" class="text-sm text-ink-soft hover:text-accent transition-colors">Despre noi</a></li>
                    <li><a href="{{ route('contact') }}" class="text-sm text-ink-soft hover:text-accent transition-colors">Contact</a></li>
                    <li><a href="{{ route('confidentialitate') }}" class="text-sm text-ink-soft hover:text-accent transition-colors">Politică de confidențialitate</a></li>
                    <li><a href="{{ route('termeni') }}" class="text-sm text-ink-soft hover:text-accent transition-colors">Termeni și condiții</a></li>
                    <li><a href="{{ route('politica-cookies') }}" class="text-sm text-ink-soft hover:text-accent transition-colors">Politică de cookie-uri</a></li>
                </ul>
            </div>

            {{-- Contact --}}
            <div>
                <h3 class="text-sm font-semibold uppercase tracking-wider text-ink">Contact</h3>
                <ul class="mt-4 space-y-2 text-sm text-ink-soft">
                    <li>
                        <span class="block text-xs text-ink-muted">Telefon general</span>
                        <a href="{{ \App\Support\Business::phoneHref() }}" class="hover:text-accent transition-colors" {!! \App\Support\Tracking::attrs('click_phone') !!}>{{ $phone }}</a>
                    </li>
                    <li>
                        <a href="mailto:{{ $email }}" class="hover:text-accent transition-colors" {!! \App\Support\Tracking::attrs('click_email') !!}>{{ $email }}</a>
                    </li>
                    <li>
                        <span class="block text-xs text-ink-muted">WhatsApp</span>
                        <a href="{{ $whatsappUrl }}" target="_blank" rel="noopener noreferrer" aria-label="Contactează Decor Urban pe WhatsApp" class="inline-flex items-center gap-1.5 font-medium text-accent hover:text-accent-hover transition-colors" {!! \App\Support\Tracking::attrs('click_whatsapp') !!}>
                            <svg class="h-4 w-4" fill="currentColor" viewBox="0 0 24 24"><path d="M.057 24l1.687-6.163a11.867 11.867 0 0 1-1.587-5.946C.16 5.335 5.495 0 12.05 0a11.82 11.82 0 0 1 8.413 3.488 11.82 11.82 0 0 1 3.48 8.414c-.003 6.557-5.338 11.892-11.893 11.892a11.9 11.9 0 0 1-5.688-1.448L.057 24zm6.597-3.807c1.676.995 3.276 1.591 5.392 1.592 5.448 0 9.886-4.434 9.889-9.885.002-5.462-4.415-9.89-9.881-9.892-5.452 0-9.887 4.434-9.889 9.884a9.86 9.86 0 0 0 1.51 5.26l-.999 3.648 3.488-1.607z"/></svg>
                            {{ config('business.whatsapp') }}
                        </a>
                    </li>
                    <li>
                        <a href="{{ config('business.google_maps_url') }}" target="_blank" rel="noopener noreferrer" aria-label="Vezi Decor Urban pe Google Maps" class="hover:text-accent transition-colors" {!! \App\Support\Tracking::attrs('click_google_maps') !!}>Google Maps</a>
                    </li>
                    @if ($isPlaceholder && app()->environment('local'))
                        <li class="mt-1 text-xs text-ink-soft">⚠️ [dev] Date de contact provizorii — de confirmat.</li>
                    @endif
                </ul>
            </div>
        </div>

        <div class="mt-12 flex flex-col gap-3 border-t border-shell-line pt-6 sm:flex-row sm:items-center sm:justify-between">
            <p class="text-sm text-ink-soft">© {{ date('Y') }} Decor Urban. Toate drepturile rezervate.</p>
            <p class="text-sm text-ink-soft">Producător direct · Livrare în toată țara</p>
        </div>
    </div>
</footer>
