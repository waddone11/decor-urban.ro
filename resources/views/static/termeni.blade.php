@php
    $brand = config('contact.brand');
    $legalName = config('company.legal_name') ?: $brand;
    $email = config('contact.email');
    $phone = config('contact.phone');
@endphp

<x-static.legal-page
    title="Termeni și condiții"
    :description="'Termenii de utilizare a site-ului '.$brand.' și condițiile generale de ofertare.'">

    <p>
        Acești termeni reglementează utilizarea site-ului {{ $brand }} și condițiile generale în baza
        cărora {{ $legalName }} oferă produse și servicii. Prin utilizarea site-ului, ești de acord cu ei.
    </p>

    <h2>Despre noi</h2>
    <p>
        Site-ul este operat de {{ $legalName }}. Ne poți contacta la
        <a href="mailto:{{ $email }}">{{ $email }}</a> sau la {{ $phone }}.
    </p>

    <h2>Produse și oferte</h2>
    <p>
        Produsele afișate sunt prezentate cu titlu informativ. Prețurile sunt „la cerere": confirmăm
        prețul, finisajele, dimensiunile și termenul de livrare printr-o ofertă scrisă, valabilă pe
        perioada indicată în ea. Imaginile au caracter ilustrativ; producția la comandă poate diferi în
        detalii agreate.
    </p>

    <h2>Comenzi și plată</h2>
    <p>
        O comandă se consideră fermă după acceptarea ofertei de ambele părți. Modalitățile de plată
        (ramburs sau transfer bancar; cu factură pentru instituții) se stabilesc în ofertă.
    </p>

    <h2>Livrare</h2>
    <p>
        Livrăm în toată țara. Termenul de livrare se confirmă în ofertă și depinde de produs și cantitate.
    </p>

    <h2>Garanție și retur</h2>
    <p>
        Acordăm garanție conform legislației aplicabile și specificului fiecărui produs. Pentru produsele
        fabricate la comandă (dimensiuni/finisaje personalizate) se aplică regimul legal specific. Detaliile
        se confirmă în ofertă/contract.
    </p>

    <h2>Proprietate intelectuală</h2>
    <p>
        Conținutul site-ului (texte, imagini, identitate vizuală) aparține {{ $legalName }} și nu poate fi
        folosit fără acord.
    </p>

    <h2>Limitarea răspunderii</h2>
    <p>
        Depunem eforturi rezonabile pentru acuratețea informațiilor, dar nu garantăm că site-ul este lipsit
        de erori. Răspunderea contractuală se stabilește prin oferta/contractul agreat.
    </p>

    <h2>Legea aplicabilă</h2>
    <p>
        Acestor termeni li se aplică legea română; eventualele litigii se soluționează de instanțele
        competente de la sediul {{ $legalName }}.
    </p>
</x-static.legal-page>
