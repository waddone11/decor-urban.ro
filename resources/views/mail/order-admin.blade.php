<x-mail::message>
# Comandă nouă: {{ $order->number }}

## Client
**Nume:** {{ $order->customer_name }}
@if ($order->company)**Firmă/instituție:** {{ $order->company }}@endif
@if ($order->cui)**CUI:** {{ $order->cui }}@endif
**Telefon:** {{ $order->phone }}
**Email:** {{ $order->email }}
**Adresă:** {{ $order->address }}, {{ $order->city }}, jud. {{ $order->county }}
**Metodă:** {{ $order->paymentMethodLabel() }}
@if ($order->notes)
**Note:** {{ $order->notes }}
@endif

## Produse

<x-mail::table>
| Produs | Cod | Cantitate | Preț unitar |
|:-------|:----|:---------:|------------:|
@foreach ($order->items as $item)
| {{ $item->product_name }} | {{ $item->product_code ? ltrim($item->product_code, '#') : '—' }} | {{ $item->quantity }} | {{ $item->priceLabel() }} |
@endforeach
@if ($order->total !== null)
| **Total** | | | **{{ \App\Models\Product::formatLei((float) $order->total) }}** |
@endif
</x-mail::table>

<x-mail::button :url="url('/admin')">
Deschide în panel
</x-mail::button>

@if ($order->total !== null)
Comanda are prețuri afișate — verifică și confirmă către client.
@else
Prețuri la cerere — pregătește oferta și revino la client.
@endif
</x-mail::message>
