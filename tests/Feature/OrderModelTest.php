<?php

namespace Tests\Feature;

use App\Models\Order;
use App\Models\Product;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class OrderModelTest extends TestCase
{
    use RefreshDatabase;

    public function test_generates_sequential_number_per_year(): void
    {
        $this->assertSame('DU-2026-0001', Order::generateNumber(2026));

        Order::create(['number' => 'DU-2026-0001', 'customer_name' => 'A', 'phone' => '07', 'email' => 'a@b.ro', 'county' => 'Olt', 'city' => 'X', 'address' => 'Y', 'payment_method' => 'ramburs', 'status' => 'noua']);
        $this->assertSame('DU-2026-0002', Order::generateNumber(2026));

        // An nou resetează secvența.
        $this->assertSame('DU-2027-0001', Order::generateNumber(2027));
    }

    public function test_create_with_number_assigns_unique_number(): void
    {
        $o1 = Order::createWithNumber(['customer_name' => 'A', 'phone' => '07', 'email' => 'a@b.ro', 'county' => 'Olt', 'city' => 'X', 'address' => 'Y', 'payment_method' => 'ramburs']);
        $o2 = Order::createWithNumber(['customer_name' => 'B', 'phone' => '08', 'email' => 'b@b.ro', 'county' => 'Olt', 'city' => 'X', 'address' => 'Z', 'payment_method' => 'whatsapp']);

        $this->assertNotSame($o1->number, $o2->number);
        $this->assertStringStartsWith('DU-', $o1->number);
        $this->assertSame('noua', $o1->status);
        $this->assertNull($o1->total, 'Total null — la cerere, fără total fals');
    }

    public function test_items_relation_keeps_snapshot_when_product_deleted(): void
    {
        $product = Product::create(['name' => 'Bancă B201', 'slug' => 'banca-b201', 'code' => '#B201', 'price_on_request' => true, 'is_active' => true, 'sort_order' => 1]);

        $order = Order::createWithNumber(['customer_name' => 'A', 'phone' => '07', 'email' => 'a@b.ro', 'county' => 'Olt', 'city' => 'X', 'address' => 'Y', 'payment_method' => 'ramburs']);
        $order->items()->create([
            'product_id' => $product->id, 'product_name' => 'Bancă B201', 'product_code' => '#B201', 'quantity' => 3,
        ]);

        $product->delete();
        $item = $order->items()->first();

        $this->assertNull($item->fresh()->product_id, 'product_id devine null la ștergere');
        $this->assertSame('Bancă B201', $item->product_name, 'Snapshot-ul rămâne');
        $this->assertSame(3, $item->quantity);
    }
}
