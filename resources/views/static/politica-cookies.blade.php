@php
    $brand = config('contact.brand');
    $email = config('contact.email');
@endphp

<x-static.legal-page
    title="Politică de cookie-uri"
    :description="'Ce cookie-uri folosește site-ul '.$brand.' și cum le poți controla.'">

    <p>
        Această pagină explică ce cookie-uri folosește site-ul {{ $brand }} și cum le poți controla.
    </p>

    <h2>Ce sunt cookie-urile</h2>
    <p>
        Cookie-urile sunt fișiere mici stocate în browserul tău. Le folosim doar cât este necesar pentru
        funcționarea site-ului și pentru a reține preferințele tale.
    </p>

    <h2>Ce cookie-uri folosim</h2>
    <ul>
        <li>
            <strong>Strict necesare:</strong> pentru funcționarea site-ului și securitate (ex. sesiune,
            token-ul anti-CSRF). Fără ele, site-ul nu funcționează corect.
        </li>
        <li>
            <strong>Preferință de consimțământ:</strong> reținem opțiunea ta privind banner-ul de
            cookie-uri, ca să nu te întrebăm la fiecare vizită.
        </li>
    </ul>

    <p>
        În prezent <strong>nu folosim cookie-uri de marketing sau de analiză (tracking)</strong>. Dacă vom
        adăuga vreodată astfel de instrumente, vom cere consimțământul tău în prealabil și vom actualiza
        această pagină.
    </p>

    <h2>Cum controlezi cookie-urile</h2>
    <p>
        Poți șterge sau bloca cookie-urile din setările browserului. Blocarea cookie-urilor strict necesare
        poate afecta funcționarea site-ului.
    </p>

    <p>Întrebări? Scrie-ne la <a href="mailto:{{ $email }}">{{ $email }}</a>.</p>
</x-static.legal-page>
