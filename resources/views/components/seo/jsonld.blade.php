@props([
    'data' => [],
])

{{-- Date structurate JSON-LD. Valid oriunde în document (head sau body) per schema.org. --}}
<script type="application/ld+json">{!! json_encode($data, JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE) !!}</script>
