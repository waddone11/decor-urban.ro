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
        Cookie-urile sunt fișiere mici stocate în browserul tău. Le folosim pentru funcționarea site-ului,
        pentru reținerea preferințelor și, doar cu acordul tău, pentru măsurare și marketing.
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
        <li>
            <strong>Analytics:</strong> Google Analytics 4 și Google Tag Manager, dacă sunt configurate,
            pentru statistici agregate despre utilizarea site-ului. Se activează numai după consimțământ.
        </li>
        <li>
            <strong>Marketing:</strong> Meta Pixel și TikTok Pixel, dacă sunt configurate, pentru măsurarea
            campaniilor și audiențe de remarketing. Se activează numai după consimțământ.
        </li>
    </ul>

    <p>
        Site-ul folosește Google Consent Mode v2. Implicit, în România/UE, analytics și marketing sunt
        dezactivate până la acord. Poți refuza cookie-urile opționale sau poți salva separat preferințele
        pentru analytics și marketing.
    </p>

    <h2>Furnizori și durată</h2>
    <ul>
        <li><strong>Decor Urban:</strong> sesiune, securitate, coș/cerere de ofertă și preferință de consimțământ; durata variază de la sesiune la maximum 12 luni.</li>
        <li><strong>Google:</strong> Analytics/Tag Manager, numai cu acord analytics; durata este stabilită de Google conform configurației contului.</li>
        <li><strong>Meta:</strong> Meta Pixel, numai cu acord marketing; durata este stabilită de Meta conform configurației contului.</li>
        <li><strong>TikTok:</strong> TikTok Pixel, numai cu acord marketing; durata este stabilită de TikTok conform configurației contului.</li>
    </ul>

    <h2>Cum controlezi cookie-urile</h2>
    <p>
        Poți șterge sau bloca cookie-urile din setările browserului. Blocarea cookie-urilor strict necesare
        poate afecta funcționarea site-ului.
        Pentru retragerea consimțământului, șterge cookie-ul <code>cookie_consent</code> din browser și
        reîncarcă pagina; bannerul va apărea din nou.
    </p>

    <p>Întrebări? Scrie-ne la <a href="mailto:{{ $email }}">{{ $email }}</a>.</p>
</x-static.legal-page>
