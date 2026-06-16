<?php

namespace Tests\Feature;

use App\Livewire\Checkout;
use App\Models\Order;
use App\Models\Product;
use App\Support\Cart;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Livewire\Livewire;
use Tests\TestCase;

class CheckoutTest extends TestCase
{
    use RefreshDatabase;

    private function productInCart(int $qty = 2): Product
    {
        $p = Product::create(['name' => 'Coș C120', 'slug' => 'cos-c120', 'code' => '#C120', 'price_on_request' => true, 'is_active' => true, 'sort_order' => 1]);
        Cart::add($p->id, $qty);

        return $p;
    }

    private function validData(): array
    {
        return [
            'customer_name' => 'Primăria Slatina',
            'company' => 'Primăria Municipiului Slatina',
            'cui' => 'RO123456',
            'phone' => '0249123456',
            'email' => 'achizitii@primaria.ro',
            'county' => 'Olt',
            'city' => 'Slatina',
            'address' => 'Str. Primăriei nr. 1',
            'payment_method' => 'ramburs',
            'notes' => 'Livrare în 30 zile.',
        ];
    }

    public function test_empty_cart_redirects_to_cart(): void
    {
        Livewire::test(Checkout::class)->assertRedirect(route('cart'));
    }

    public function test_validation_blocks_incomplete_submit(): void
    {
        $this->productInCart();

        Livewire::test(Checkout::class)
            ->call('placeOrder')
            ->assertHasErrors(['customer_name', 'phone', 'email', 'county', 'city', 'address']);

        $this->assertSame(0, Order::count());
    }

    public function test_valid_submit_creates_order_with_snapshot_and_clears_cart(): void
    {
        $p = $this->productInCart(2);

        Livewire::test(Checkout::class)
            ->set($this->validData())
            ->call('placeOrder')
            ->assertHasNoErrors()
            ->assertRedirect();

        $this->assertSame(1, Order::count());
        $order = Order::first();
        $this->assertSame('Primăria Slatina', $order->customer_name);
        $this->assertSame('ramburs', $order->payment_method);
        $this->assertSame('noua', $order->status);
        $this->assertNull($order->total);

        $item = $order->items()->first();
        $this->assertSame('Coș C120', $item->product_name);
        $this->assertSame('#C120', $item->product_code);
        $this->assertSame(2, $item->quantity);
        $this->assertSame($p->id, $item->product_id);

        $this->assertTrue(Cart::isEmpty(), 'Coșul se golește după comandă');
    }

    public function test_honeypot_blocks_order_creation(): void
    {
        $this->productInCart();

        Livewire::test(Checkout::class)
            ->set($this->validData())
            ->set('website', 'http://spam')
            ->call('placeOrder')
            ->assertRedirect(route('cart'));

        $this->assertSame(0, Order::count());
    }
}
