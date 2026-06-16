<?php

namespace Tests\Feature;

use App\Livewire\AddToCart;
use App\Livewire\CartCounter;
use App\Livewire\CartPage;
use App\Models\Product;
use App\Support\Cart;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Livewire\Livewire;
use Tests\TestCase;

class CartTest extends TestCase
{
    use RefreshDatabase;

    private function product(string $slug = 'banca-b201'): Product
    {
        return Product::create([
            'name' => 'Bancă '.$slug, 'slug' => $slug, 'code' => '#B201',
            'price_on_request' => true, 'is_active' => true, 'sort_order' => 1,
        ]);
    }

    public function test_add_to_cart_adds_with_quantity(): void
    {
        $p = $this->product();

        Livewire::test(AddToCart::class, ['productId' => $p->id])
            ->set('qty', 3)
            ->call('add')
            ->assertSet('added', true)
            ->assertDispatched('cart-updated');

        $this->assertSame(3, Cart::count());
    }

    public function test_header_counter_reflects_cart(): void
    {
        $p = $this->product();
        Cart::add($p->id, 2);

        Livewire::test(CartCounter::class)
            ->assertSet('count', 2)
            ->call('refresh')
            ->assertSet('count', 2);
    }

    public function test_cart_page_lists_updates_and_removes(): void
    {
        $p = $this->product();
        Cart::add($p->id, 1);

        Livewire::test(CartPage::class)
            ->assertSee('Bancă banca-b201')
            ->call('increment', $p->id);
        $this->assertSame(2, Cart::count());

        Livewire::test(CartPage::class)->call('updateQty', $p->id, 5);
        $this->assertSame(5, Cart::count());

        Livewire::test(CartPage::class)->call('remove', $p->id);
        $this->assertTrue(Cart::isEmpty());
    }

    public function test_cart_persists_on_session_and_drops_inactive(): void
    {
        $active = $this->product('activ');
        $inactive = Product::create(['name' => 'Inactiv', 'slug' => 'inactiv', 'price_on_request' => true, 'is_active' => false, 'sort_order' => 2]);

        Cart::add($active->id, 1);
        Cart::add($inactive->id, 1);

        // Liniile întorc doar produsul activ; cel inactiv e curățat din sesiune.
        $lines = Cart::lines();
        $this->assertCount(1, $lines);
        $this->assertSame($active->id, $lines->first()['product']->id);
    }

    public function test_cart_route_renders(): void
    {
        $this->get('/cos')->assertOk()->assertSeeLivewire(CartPage::class);
    }

    public function test_empty_cart_shows_friendly_state(): void
    {
        Livewire::test(CartPage::class)->assertSee('Coșul tău e gol');
    }
}
