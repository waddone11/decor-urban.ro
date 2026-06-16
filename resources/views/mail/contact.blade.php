<x-mail::message>
# Mesaj nou de contact

**Nume:** {{ $name }}
**Telefon:** {{ $phone }}
**Email:** {{ $email }}

---

{{ $userMessage }}

<x-mail::button :url="'mailto:'.$email">
Răspunde
</x-mail::button>

Mesaj trimis din formularul de contact {{ config('contact.brand') }}.
</x-mail::message>
