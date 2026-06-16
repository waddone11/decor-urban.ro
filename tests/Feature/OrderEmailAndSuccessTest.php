<?php

namespace Tests\Feature;

use App\Livewire\Checkout;
use App\Mail\OrderPlacedAdmin;
use App\Mail\OrderPlacedCustomer;
use App\Models\Order;
use App\Models\Product;
use App\Support\Cart;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Mail;
use Livewire\Livewire;
use Tests\TestCase;

class OrderEmailAndSuccessTest extends TestCase
{
    use RefreshDatabase;

    private function placeOrder(): Order
    {
        $p = Product::create(['name' => 'Coș C120', 'slug' => 'cos-c120', 'code' => '#C120', 'price_on_request' => true, 'is_active' => true, 'sort_order' => 1]);
        Cart::add($p->id, 2);

        Livewire::test(Checkout::class)
            ->set([
                'customer_name' => 'Ion Popescu', 'phone' => '0712345678', 'email' => 'ion@example.com',
                'county' => 'Olt', 'city' => 'Slatina', 'address' => 'Str. X nr. 1', 'payment_method' => 'ramburs',
            ])
            ->call('placeOrder');

        return Order::firstOrFail();
    }

    public function test_emails_sent_to_customer_and_admin(): void
    {
        Mail::fake();
        config(['contact.email' => 'admin@decor-urban.ro']);

        $order = $this->placeOrder();

        Mail::assertSent(OrderPlacedCustomer::class, fn ($m) => $m->hasTo('ion@example.com') && $m->order->is($order));
        Mail::assertSent(OrderPlacedAdmin::class, fn ($m) => $m->hasTo('admin@decor-urban.ro') && $m->order->is($order));
    }

    public function test_success_page_shows_summary_and_email_note(): void
    {
        Mail::fake();
        $order = $this->placeOrder();

        $this->get(route('order.success', $order->number))
            ->assertOk()
            ->assertSee($order->number)
            ->assertSee('Coș C120')
            ->assertSee('Am primit comanda ta')
            ->assertSee('ion@example.com');
    }

    public function test_unknown_order_number_404(): void
    {
        $this->get(route('order.success', 'DU-2026-9999'))->assertNotFound();
    }

    public function test_mail_templates_render_without_error(): void
    {
        $order = $this->placeOrder();

        $customer = (new OrderPlacedCustomer($order))->render();
        $admin = (new OrderPlacedAdmin($order))->render();

        $this->assertStringContainsString($order->number, $customer);
        $this->assertStringContainsString('Coș C120', $customer);
        $this->assertStringContainsString($order->customer_name, $admin);
        $this->assertStringContainsString($order->phone, $admin);
    }
}
