<?php

namespace Tests\Feature;

use App\Models\Order;
use App\Support\GoogleReviews;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Http;
use Tests\TestCase;

class GoogleReviewsTest extends TestCase
{
    use RefreshDatabase;

    public function test_review_cta_is_hidden_until_review_url_is_configured(): void
    {
        config(['business.google_review_url' => '']);

        $this->get('/contact')
            ->assertOk()
            ->assertDontSee('Lasă-ne o recenzie pe Google');

        config(['business.google_review_url' => 'https://g.page/r/test/review']);

        $this->get('/contact')
            ->assertOk()
            ->assertSee('Lasă-ne o recenzie pe Google')
            ->assertSee('outbound_review_click')
            ->assertSee('https://g.page/r/test/review', false);
    }

    public function test_order_success_shows_review_cta_when_configured(): void
    {
        config(['business.google_review_url' => 'https://g.page/r/test/review']);

        $order = Order::create([
            'number' => 'DU-2026-0001',
            'customer_name' => 'Client Test',
            'phone' => '+40700000000',
            'email' => 'client@example.test',
            'county' => 'Olt',
            'city' => 'Scornicești',
            'address' => 'Str. Test',
            'payment_method' => 'whatsapp',
        ]);

        $this->get('/comanda/'.$order->number)
            ->assertOk()
            ->assertSee('O recenzie pe Google ne ajută enorm')
            ->assertSee('Lasă-ne o recenzie pe Google');
    }

    public function test_google_reviews_fetches_real_api_payload_and_caches_it(): void
    {
        config([
            'business.google_place_id' => 'place-real',
            'business.google_places_api_key' => 'api-key',
        ]);

        Http::fake([
            'maps.googleapis.com/*' => Http::response([
                'status' => 'OK',
                'result' => [
                    'rating' => 4.9,
                    'user_ratings_total' => 27,
                    'reviews' => [[
                        'author_name' => 'Client Real',
                        'author_url' => 'https://maps.google.com/client-real',
                        'profile_photo_url' => 'https://lh3.googleusercontent.com/photo',
                        'rating' => 5,
                        'relative_time_description' => 'acum 2 luni',
                        'text' => 'Produs foarte bun.',
                    ]],
                ],
            ]),
        ]);

        $data = GoogleReviews::fetchAndCache();

        $this->assertSame(4.9, $data['rating']);
        $this->assertSame(27, $data['user_ratings_total']);
        $this->assertSame('Client Real', $data['reviews'][0]['author_name']);
        $this->assertSame($data, Cache::get(GoogleReviews::CACHE_KEY));

        Http::assertSent(fn ($request): bool => str_contains((string) $request->url(), 'place/details/json')
            && $request['place_id'] === 'place-real'
            && $request['fields'] === 'rating,user_ratings_total,reviews');
    }

    public function test_reviews_section_renders_cached_reviews_with_google_attribution(): void
    {
        config(['business.google_review_url' => 'https://g.page/r/test/review']);
        Cache::put(GoogleReviews::CACHE_KEY, [
            'rating' => 5,
            'user_ratings_total' => 1,
            'reviews' => [[
                'author_name' => 'Client Real',
                'author_url' => 'https://maps.google.com/client-real',
                'profile_photo_url' => '',
                'rating' => 5,
                'relative_time_description' => 'acum o lună',
                'text' => 'Recomand Decor Urban.',
            ]],
            'fetched_at' => now()->toAtomString(),
            'status' => 'ok',
        ], now()->addDay());

        $html = $this->get('/contact')->assertOk()->getContent();

        $this->assertStringContainsString('Client Real', $html);
        $this->assertStringContainsString('Recomand Decor Urban.', $html);
        $this->assertStringContainsString('pe Google', $html);
        $this->assertStringNotContainsString('aggregateRating', $html);
        $this->assertStringNotContainsString('"@type":"Review"', $html);
    }

    public function test_reviews_fetch_command_is_available_through_commands_runner(): void
    {
        config([
            'commands.secret' => 'test-secret-cheie-lunga',
            'business.google_place_id' => '',
            'business.google_places_api_key' => '',
        ]);

        $this->get('/commands/reviews-fetch?secret=test-secret-cheie-lunga')
            ->assertOk()
            ->assertHeader('Content-Type', 'text/plain; charset=utf-8')
            ->assertSee('php artisan google:reviews-fetch')
            ->assertSee('Status: missing_config');
    }
}
