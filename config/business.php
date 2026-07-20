<?php

return [
    'name' => env('BUSINESS_NAME', 'Decor Urban'),
    'legal_name' => env('BUSINESS_LEGAL_NAME', 'MOBILIER-STRADAL RO 2026 S.R.L.'),
    'vat_number' => env('BUSINESS_VAT_NUMBER', '54295156'),
    'registration_number' => env('BUSINESS_REGISTRATION_NUMBER', 'J2026018529009'),
    'address' => env('BUSINESS_ADDRESS', 'Str. Băltați nr. 149, Sat Băltați, Oraș Scornicești, Județul Olt, România'),
    'phone' => env('BUSINESS_PHONE', '+40 758 522 227'),
    'whatsapp' => env('BUSINESS_WHATSAPP', '+40 756 222 260'),
    'whatsapp_digits' => env('BUSINESS_WHATSAPP_DIGITS', '40756222260'),
    'whatsapp_url' => env('BUSINESS_WHATSAPP_URL', 'https://wa.me/40756222260'),
    'whatsapp_prefilled_url' => env('BUSINESS_WHATSAPP_PREFILLED_URL', 'https://wa.me/40756222260?text=Bun%C4%83%20ziua%2C%20doresc%20mai%20multe%20informa%C8%9Bii%20despre%20produsele%20Decor%20Urban.'),
    'email' => env('BUSINESS_EMAIL', 'contact@decor-urban.ro'),
    'website' => env('BUSINESS_WEBSITE', 'https://decor-urban.ro'),
    'google_maps_url' => env('BUSINESS_GOOGLE_MAPS_URL', 'https://share.google/sWYL0KoX1P7j3O06B'),
    'google_review_url' => env('BUSINESS_GOOGLE_REVIEW_URL', ''),
    'google_maps_embed_url' => env('BUSINESS_GOOGLE_MAPS_EMBED_URL', ''),
    'google_place_id' => env('BUSINESS_GOOGLE_PLACE_ID', ''),
    'google_places_api_key' => env('GOOGLE_PLACES_API_KEY', ''),
    'latitude' => env('BUSINESS_LATITUDE', ''),
    'longitude' => env('BUSINESS_LONGITUDE', ''),
    'social' => [
        'facebook' => env('BUSINESS_FACEBOOK', 'https://www.facebook.com/profile.php?id=61592205237734'),
        'instagram' => env('BUSINESS_INSTAGRAM', 'https://www.instagram.com/decor.urban.ro'),
        'tiktok' => env('BUSINESS_TIKTOK', 'https://www.tiktok.com/@decor.urban.ro'),
        'linkedin' => env('BUSINESS_LINKEDIN', ''),
        'youtube' => env('BUSINESS_YOUTUBE', ''),
    ],
    'tracking' => [
        'gtm_container_id' => env('GTM_CONTAINER_ID', ''),
        'ga4_measurement_id' => env('GA4_MEASUREMENT_ID', ''),
        'meta_pixel_id' => env('META_PIXEL_ID', ''),
        'tiktok_pixel_id' => env('TIKTOK_PIXEL_ID', ''),
    ],
    'verification' => [
        'google' => env('GOOGLE_SITE_VERIFICATION', ''),
        'bing' => env('BING_SITE_VERIFICATION', ''),
        'facebook' => env('FACEBOOK_DOMAIN_VERIFICATION', ''),
    ],
    'feeds' => [
        'meta_token' => env('META_FEED_TOKEN', ''),
    ],
];
