<?php

namespace Tests\Feature;

use App\Livewire\ProductReviews;
use App\Models\Category;
use App\Models\Order;
use App\Models\Product;
use App\Models\ProductReview;
use App\Support\JsonLd;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\RateLimiter;
use Livewire\Livewire;
use Tests\TestCase;

class ProductReviewTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();
        RateLimiter::clear('product-review:127.0.0.1');
    }

    private function makeProduct(): Product
    {
        $cat = Category::create(['name' => 'Bănci', 'slug' => 'banci', 'sort_order' => 1, 'is_active' => true]);
        $product = Product::create([
            'name' => 'Bancă stradală B1', 'slug' => 'banca-b1', 'code' => '#B1',
            'price_on_request' => true, 'is_active' => true, 'sort_order' => 1,
        ]);
        $product->categories()->attach($cat, ['is_primary' => true]);

        return $product;
    }

    private function submitReview(Product $product, array $overrides = []): \Livewire\Features\SupportTesting\Testable
    {
        return Livewire::test(ProductReviews::class, ['productId' => $product->id])
            ->set(array_merge([
                'author_name' => 'Ion Popescu',
                'author_email' => 'ion@example.com',
                'rating' => 5,
                'title' => 'Foarte bună',
                'body' => 'Bancă solidă, montaj ușor, arată excelent în parc.',
            ], $overrides))
            ->call('submit');
    }

    // ── Submit → pending, moderat ────────────────────────────────────────────

    public function test_submitted_review_is_pending_and_not_visible(): void
    {
        $product = $this->makeProduct();

        $this->submitReview($product)->assertSee('Mulțumim');

        $review = ProductReview::firstOrFail();
        $this->assertSame('pending', $review->status);

        $this->get('/produs/banca-b1')->assertOk()->assertDontSee('Bancă solidă, montaj ușor');
    }

    public function test_honeypot_drops_review_silently(): void
    {
        $product = $this->makeProduct();

        $this->submitReview($product, ['website' => 'http://spam.example']);

        $this->assertSame(0, ProductReview::count());
    }

    public function test_rating_must_be_between_1_and_5(): void
    {
        $product = $this->makeProduct();

        $this->submitReview($product, ['rating' => 6])->assertHasErrors(['rating']);
        $this->assertSame(0, ProductReview::count());
    }

    public function test_rate_limit_blocks_flood(): void
    {
        $product = $this->makeProduct();

        foreach (range(1, 3) as $i) {
            $this->submitReview($product, ['author_email' => "ion{$i}@example.com"]);
        }
        $this->assertSame(3, ProductReview::count());

        $this->submitReview($product, ['author_email' => 'ion4@example.com'])
            ->assertHasErrors(['author_email']);
        $this->assertSame(3, ProductReview::count());
    }

    public function test_verified_purchase_set_when_email_has_order(): void
    {
        $product = $this->makeProduct();
        Order::createWithNumber([
            'customer_name' => 'Ion Popescu', 'phone' => '0712345678', 'email' => 'ion@example.com',
            'county' => 'Olt', 'city' => 'Slatina', 'address' => 'Str. X 1', 'payment_method' => 'ramburs',
        ]);

        $this->submitReview($product);

        $this->assertTrue(ProductReview::firstOrFail()->verified_purchase);
    }

    public function test_unknown_email_is_not_verified_purchase(): void
    {
        $product = $this->makeProduct();

        $this->submitReview($product);

        $this->assertFalse(ProductReview::firstOrFail()->verified_purchase);
    }

    // ── Afișare doar approved ────────────────────────────────────────────────

    public function test_approved_review_visible_on_product_page(): void
    {
        $product = $this->makeProduct();
        ProductReview::create([
            'product_id' => $product->id, 'author_name' => 'Maria Ionescu', 'author_email' => 'maria@example.com',
            'rating' => 4, 'title' => 'Recomand', 'body' => 'Produs de calitate, livrare rapidă.',
            'status' => 'approved', 'verified_purchase' => true,
        ]);

        $res = $this->get('/produs/banca-b1')->assertOk();

        $res->assertSee('Maria Ionescu');
        $res->assertSee('Produs de calitate, livrare rapidă.');
        $res->assertSee('Achiziție verificată');
        $res->assertDontSee('maria@example.com');
    }

    public function test_rejected_and_pending_reviews_are_hidden(): void
    {
        $product = $this->makeProduct();
        foreach (['pending' => 'Text în așteptare aici.', 'rejected' => 'Text respins aici.'] as $status => $body) {
            ProductReview::create([
                'product_id' => $product->id, 'author_name' => 'X Y', 'author_email' => 'x@example.com',
                'rating' => 5, 'body' => $body, 'status' => $status,
            ]);
        }

        $this->get('/produs/banca-b1')->assertOk()
            ->assertDontSee('Text în așteptare aici.')
            ->assertDontSee('Text respins aici.');
    }

    public function test_empty_state_invites_first_review(): void
    {
        $this->makeProduct();

        $this->get('/produs/banca-b1')->assertOk()->assertSee('Fii primul care lasă o recenzie');
    }

    // ── JSON-LD: doar recenzii reale ─────────────────────────────────────────

    public function test_no_aggregate_rating_without_approved_reviews(): void
    {
        $product = $this->makeProduct();
        ProductReview::create([
            'product_id' => $product->id, 'author_name' => 'X Y', 'author_email' => 'x@example.com',
            'rating' => 5, 'body' => 'În așteptare, nu contează.', 'status' => 'pending',
        ]);

        $ld = JsonLd::product($product);

        $this->assertArrayNotHasKey('aggregateRating', $ld);
        $this->assertArrayNotHasKey('review', $ld);
    }

    public function test_aggregate_rating_computed_from_real_approved_reviews(): void
    {
        $product = $this->makeProduct();
        foreach ([5 => 'Ana', 4 => 'Dan'] as $rating => $name) {
            ProductReview::create([
                'product_id' => $product->id, 'author_name' => $name, 'author_email' => strtolower($name).'@example.com',
                'rating' => $rating, 'body' => 'Recenzie reală de la client.', 'status' => 'approved',
            ]);
        }

        $ld = JsonLd::product($product);

        $this->assertSame('AggregateRating', $ld['aggregateRating']['@type']);
        $this->assertSame(4.5, $ld['aggregateRating']['ratingValue']);
        $this->assertSame(2, $ld['aggregateRating']['reviewCount']);
        $this->assertCount(2, $ld['review']);
        $this->assertSame('Ana', $ld['review'][0]['author']['name']);
        $this->assertSame(5, $ld['review'][0]['reviewRating']['ratingValue']);
    }

    public function test_business_schema_has_no_aggregate_rating(): void
    {
        $this->assertArrayNotHasKey('aggregateRating', JsonLd::business());
    }
}
