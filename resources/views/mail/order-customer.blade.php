<x-mail::message>
# Am primit comanda ta, {{ $order->customer_name }}!

Mulțumim pentru comanda **{{ $order->number }}**. Revenim curând cu confirmarea și oferta
(prețurile sunt la cerere). Pentru ceva urgent, ne poți scrie pe WhatsApp.

## Produse comandate

<x-mail::table>
| Produs | Cod | Cantitate |
|:-------|:----|:---------:|
@foreach ($order->items as $item)
| {{ $item->product_name }} | {{ $item->product_code ? ltrim($item->product_code, '#') : '—' }} | {{ $item->quantity }} |
@endforeach
</x-mail::table>

**Metodă:** {{ $order->paymentMethodLabel() }}
@if ($order->notes)

**Notele tale:** {{ $order->notes }}
@endif

## Date livrare
{{ $order->customer_name }}@if ($order->company), {{ $order->company }}@endif<br>
{{ $order->address }}, {{ $order->city }}, jud. {{ $order->county }}<br>
Tel: {{ $order->phone }}

<x-mail::button :url="url('/catalog')">
Vezi catalogul
</x-mail::button>

Cu drag,<br>
Echipa {{ config('contact.brand') }}
</x-mail::message>
