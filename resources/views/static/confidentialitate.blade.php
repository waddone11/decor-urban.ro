@php
    $brand = config('contact.brand');
    $legalName = config('company.legal_name') ?: $brand;
    $email = config('contact.email');
@endphp

<x-static.legal-page
    title="Politică de confidențialitate"
    :description="'Cum prelucrează '.$brand.' datele cu caracter personal (GDPR).'">

    <p>
        Această politică descrie cum {{ $legalName }} colectează și prelucrează datele cu caracter
        personal, conform Regulamentului (UE) 2016/679 (GDPR). Pentru întrebări, scrie-ne la
        <a href="mailto:{{ $email }}">{{ $email }}</a>.
    </p>

    <h2>Ce date colectăm</h2>
    <p>Colectăm doar datele pe care ni le transmiți direct, de regulă prin formularul de contact:</p>
    <ul>
        <li>nume;</li>
        <li>telefon;</li>
        <li>adresă de email;</li>
        <li>conținutul mesajului tău (ex. detalii despre produsul dorit).</li>
    </ul>

    <h2>Scopul prelucrării</h2>
    <p>
        Folosim aceste date exclusiv pentru a-ți răspunde la solicitare și a pregăti o ofertă.
        Temeiul legal este interesul legitim de a răspunde cererilor comerciale și/sau demersurile
        precontractuale, la cererea ta.
    </p>

    <h2>Cât timp păstrăm datele</h2>
    <p>
        Păstrăm datele cât este necesar pentru a-ți răspunde și pentru obligațiile legale aplicabile
        (ex. fiscale, dacă se încheie o tranzacție). Apoi le ștergem sau le anonimizăm.
    </p>

    <h2>Cui le divulgăm</h2>
    <p>
        Nu vindem datele tale. Le putem divulga doar furnizorilor care ne ajută să operăm (ex. găzduire,
        email), pe bază de contract, și autorităților când legea o cere.
    </p>

    <h2>Drepturile tale</h2>
    <p>
        Ai dreptul de acces, rectificare, ștergere, restricționare, opoziție și portabilitate, precum și
        dreptul de a depune o plângere la Autoritatea Națională de Supraveghere a Prelucrării Datelor cu
        Caracter Personal (ANSPDCP). Pentru exercitarea drepturilor, scrie-ne la
        <a href="mailto:{{ $email }}">{{ $email }}</a>.
    </p>

    <h2>Cookie-uri</h2>
    <p>
        Detalii despre cookie-uri în <a href="{{ route('politica-cookies') }}">Politica de cookie-uri</a>.
    </p>
</x-static.legal-page>
