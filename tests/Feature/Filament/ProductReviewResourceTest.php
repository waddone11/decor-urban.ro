<?php

namespace Tests\Feature\Filament;

use App\Filament\Resources\ProductReviews\Pages\ListProductReviews;
use App\Filament\Resources\ProductReviews\ProductReviewResource;
use App\Models\Product;
use App\Models\ProductReview;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Livewire\Livewire;
use Tests\TestCase;

class ProductReviewResourceTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();
        $this->actingAs(User::factory()->create());
    }

    private function makeReview(string $status = 'pending'): ProductReview
    {
        $product = Product::firstOrCreate(
            ['slug' => 'banca-b1'],
            ['name' => 'Bancă B1', 'price_on_request' => true, 'is_active' => true, 'sort_order' => 1]
        );

        return ProductReview::create([
            'product_id' => $product->id, 'author_name' => 'Ion Popescu', 'author_email' => 'ion@example.com',
            'rating' => 5, 'body' => 'Recenzie reală de la client.', 'status' => $status,
        ]);
    }

    public function test_list_renders_with_reviews(): void
    {
        $this->makeReview();

        Livewire::test(ListProductReviews::class)
            ->assertCountTableRecords(1)
            ->assertCanSeeTableRecords(ProductReview::all());
    }

    public function test_approve_action_publishes_review(): void
    {
        $review = $this->makeReview();

        Livewire::test(ListProductReviews::class)
            ->callTableAction('approve', $review);

        $this->assertSame('approved', $review->fresh()->status);
    }

    public function test_reject_action_hides_review(): void
    {
        $review = $this->makeReview();

        Livewire::test(ListProductReviews::class)
            ->callTableAction('reject', $review);

        $this->assertSame('rejected', $review->fresh()->status);
    }

    public function test_navigation_badge_counts_pending(): void
    {
        $this->makeReview('pending');
        $this->makeReview('approved');

        $this->assertSame('1', ProductReviewResource::getNavigationBadge());
    }

    public function test_resource_cannot_create_reviews(): void
    {
        $this->assertFalse(ProductReviewResource::canCreate());
    }
}
