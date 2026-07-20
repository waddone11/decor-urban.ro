<?php

namespace App\Support;

class Business
{
    public static function name(): string
    {
        return config('business.name', 'Decor Urban');
    }

    public static function website(): string
    {
        return rtrim((string) config('business.website', config('app.url')), '/');
    }

    public static function phoneHref(): string
    {
        return 'tel:'.preg_replace('/\s+/', '', (string) config('business.phone'));
    }

    public static function whatsappUrl(?string $message = null): string
    {
        $digits = config('business.whatsapp_digits');

        if ($message) {
            return 'https://wa.me/'.$digits.'?text='.rawurlencode($message);
        }

        return config('business.whatsapp_url');
    }

    public static function directionsUrl(): string
    {
        $query = trim(self::name().' '.config('business.address'));

        return 'https://www.google.com/maps/dir/?api=1&destination='.rawurlencode($query);
    }

    public static function mapsEmbedUrl(): string
    {
        if (config('business.google_maps_embed_url')) {
            return config('business.google_maps_embed_url');
        }

        return 'https://maps.google.com/maps?q='.rawurlencode(self::name().' '.config('business.address')).'&output=embed';
    }

    public static function sameAs(): array
    {
        return array_values(array_filter([
            config('business.social.facebook'),
            config('business.social.instagram'),
            config('business.social.tiktok'),
            config('business.social.linkedin'),
            config('business.social.youtube'),
        ]));
    }

    public static function socialLinks(): array
    {
        return array_filter([
            'facebook' => config('business.social.facebook'),
            'instagram' => config('business.social.instagram'),
            'tiktok' => config('business.social.tiktok'),
            'whatsapp' => self::whatsappUrl(),
            'google_maps' => config('business.google_maps_url'),
        ]);
    }
}
