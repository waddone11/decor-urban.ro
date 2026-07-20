<?php

namespace App\Support;

use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;

class GoogleReviews
{
    public const CACHE_KEY = 'google.reviews.place_details';

    public static function cached(): array
    {
        return Cache::get(self::CACHE_KEY, [
            'rating' => null,
            'user_ratings_total' => null,
            'reviews' => [],
            'fetched_at' => null,
        ]);
    }

    public static function fetchAndCache(): array
    {
        $placeId = (string) config('business.google_place_id');
        $apiKey = (string) config('business.google_places_api_key');

        if ($placeId === '' || $apiKey === '') {
            $payload = [
                'rating' => null,
                'user_ratings_total' => null,
                'reviews' => [],
                'fetched_at' => now()->toAtomString(),
                'status' => 'missing_config',
            ];
            Cache::put(self::CACHE_KEY, $payload, now()->addDay());

            return $payload;
        }

        $response = Http::timeout(10)->get('https://maps.googleapis.com/maps/api/place/details/json', [
            'place_id' => $placeId,
            'fields' => 'rating,user_ratings_total,reviews',
            'language' => 'ro',
            'key' => $apiKey,
        ]);

        if (! $response->ok()) {
            Log::warning('Google reviews fetch failed', ['status' => $response->status()]);

            return self::cached();
        }

        $json = $response->json();
        if (($json['status'] ?? null) !== 'OK') {
            Log::warning('Google reviews fetch returned non-OK status', ['status' => $json['status'] ?? null]);

            return self::cached();
        }

        $result = $json['result'] ?? [];
        $payload = [
            'rating' => $result['rating'] ?? null,
            'user_ratings_total' => $result['user_ratings_total'] ?? null,
            'reviews' => collect($result['reviews'] ?? [])
                ->map(fn (array $review): array => [
                    'author_name' => $review['author_name'] ?? '',
                    'author_url' => $review['author_url'] ?? '',
                    'profile_photo_url' => $review['profile_photo_url'] ?? '',
                    'rating' => $review['rating'] ?? null,
                    'relative_time_description' => $review['relative_time_description'] ?? '',
                    'text' => $review['text'] ?? '',
                ])
                ->filter(fn (array $review): bool => $review['author_name'] !== '' && $review['rating'])
                ->values()
                ->all(),
            'fetched_at' => now()->toAtomString(),
            'status' => 'ok',
        ];

        Cache::put(self::CACHE_KEY, $payload, now()->addDay());

        return $payload;
    }

    public static function forgetCache(): void
    {
        Cache::forget(self::CACHE_KEY);
    }

    public static function summaryText(): string
    {
        $data = self::cached();

        return implode("\n", [
            'Google Reviews',
            'Status: '.($data['status'] ?? 'cached'),
            'Rating: '.($data['rating'] ?? 'n/a'),
            'Total recenzii: '.($data['user_ratings_total'] ?? 'n/a'),
            'Recenzii în cache: '.count($data['reviews'] ?? []),
            'Fetched at: '.($data['fetched_at'] ?? 'n/a'),
            '',
        ]);
    }
}
