<?php

namespace Tests\Feature;

use App\Models\Order;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class OrderWhatsappTest extends TestCase
{
    use RefreshDatabase;

    private function order(): Order
    {
        config(['contact.whatsapp' => '40712345678']);

        $order = Order::createWithNumber([
            'customer_name' => 'Ion Popescu', 'company' => 'Primăria Slatina',
            'phone' => '0712345678', 'email' => 'ion@example.com',
            'county' => 'Olt', 'city' => 'Slatina', 'address' => 'Str. X', 'payment_method' => 'ramburs',
        ]);
        $order->items()->create(['product_name' => 'Coș stradal C120', 'product_code' => '#C120', 'quantity' => 3]);
        $order->load('items');

        return $order;
    }

    public function test_whatsapp_message_contains_order_details(): void
    {
        $msg = $this->order()->whatsappMessage();

        $this->assertStringContainsString('Comandă DU-', $msg);
        $this->assertStringContainsString('Coș stradal C120 (C120) × 3', $msg);
        $this->assertStringContainsString('Ion Popescu', $msg);
        $this->assertStringContainsString('0712345678', $msg);
        $this->assertStringContainsString('Ramburs la livrare', $msg);
    }

    public function test_whatsapp_url_is_encoded(): void
    {
        $order = $this->order();
        $url = $order->whatsappUrl();

        $this->assertStringStartsWith('https://wa.me/40712345678?text=', $url);
        // Diacritice + newline URL-encoded corect.
        $this->assertSame($url, 'https://wa.me/40712345678?text='.rawurlencode($order->whatsappMessage()));
        $this->assertStringContainsString('%20', $url); // spații encodate
    }

    public function test_success_page_has_whatsapp_button(): void
    {
        $order = $this->order();

        $this->get(route('order.success', $order->number))
            ->assertOk()
            ->assertSee('Trimite comanda pe WhatsApp')
            ->assertSee('wa.me/40712345678', false);
    }
}
