<?php

namespace Tests\Feature;

use App\Livewire\Checkout;
use App\Mail\OrderPlacedAdmin;
use App\Mail\OrderPlacedCustomer;
use App\Models\Category;
use App\Models\Order;
use App\Models\Product;
use App\Support\Cart;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Livewire\Livewire;
use Tests\TestCase;

class PricePropagationTest extends TestCase
{
    use RefreshDatabase;

    /** B204: preț 1.159, promo 999 — cazul de test din DoD. */
    private function makeSaleProduct(array $attrs = []): Product
    {
        return Product::create(array_merge([
            'name' => 'Bancă stradală B204', 'slug' => 'banca-b204', 'code' => '#B204',
            'price' => 1159, 'sale_price' => 999, 'price_on_request' => false,
            'is_active' => true, 'sort_order' => 1,
        ], $attrs));
    }

    private function makeOnRequestProduct(): Product
    {
        return Product::create([
            'name' => 'Coș C120', 'slug' => 'cos-c120', 'code' => '#C120',
            'price_on_request' => true, 'is_active' => true, 'sort_order' => 2,
        ]);
    }

    private function placeOrder(): Order
    {
        Livewire::test(Checkout::class)
            ->set([
                'customer_name' => 'Ion Popescu', 'phone' => '0712345678', 'email' => 'ion@example.com',
                'county' => 'Olt', 'city' => 'Slatina', 'address' => 'Str. X nr. 1', 'payment_method' => 'ramburs',
            ])
            ->call('placeOrder');

        return Order::with('items')->firstOrFail();
    }

    // ── Snapshot comandă ─────────────────────────────────────────────────────

    public function test_order_item_snapshots_effective_price_not_raw_price(): void
    {
        Cart::add($this->makeSaleProduct()->id, 2);

        $item = $this->placeOrder()->items->first();

        $this->assertSame('999.00', $item->unit_price);
    }

    public function test_order_item_unit_price_null_for_on_request(): void
    {
        Cart::add($this->makeOnRequestProduct()->id, 1);

        $this->assertNull($this->placeOrder()->items->first()->unit_price);
    }

    public function test_order_total_set_when_all_lines_priced(): void
    {
        Cart::add($this->makeSaleProduct()->id, 2);

        $this->assertSame('1998.00', $this->placeOrder()->total);
    }

    public function test_order_total_null_when_any_line_on_request(): void
    {
        Cart::add($this->makeSaleProduct()->id, 1);
        Cart::add($this->makeOnRequestProduct()->id, 1);

        $this->assertNull($this->placeOrder()->total);
    }

    // ── Coș + checkout ───────────────────────────────────────────────────────

    public function test_cart_page_shows_effective_price_and_total(): void
    {
        Cart::add($this->makeSaleProduct()->id, 2);

        $res = $this->get('/cos')->assertOk();

        $res->assertSee('999,00 lei');
        $res->assertSee('1.998,00 lei');
        $res->assertDontSee('1.159,00 lei');
    }

    public function test_cart_page_keeps_la_cerere_for_unpriced(): void
    {
        Cart::add($this->makeOnRequestProduct()->id, 1);

        $this->get('/cos')->assertOk()->assertSee('La cerere');
    }

    public function test_checkout_summary_shows_effective_price(): void
    {
        Cart::add($this->makeSaleProduct()->id, 2);

        $this->get('/checkout')->assertOk()
            ->assertSee('999,00 lei')
            ->assertSee('1.998,00 lei');
    }

    // ── Email + WhatsApp ─────────────────────────────────────────────────────

    public function test_customer_email_shows_effective_price_and_total(): void
    {
        Cart::add($this->makeSaleProduct()->id, 2);
        $order = $this->placeOrder();

        $html = (new OrderPlacedCustomer($order))->render();

        $this->assertStringContainsString('999,00 lei', $html);
        $this->assertStringContainsString('1.998,00 lei', $html);
        $this->assertStringNotContainsString('1.159,00 lei', $html);
    }

    public function test_admin_email_shows_effective_price_and_total(): void
    {
        Cart::add($this->makeSaleProduct()->id, 2);
        $order = $this->placeOrder();

        $html = (new OrderPlacedAdmin($order))->render();

        $this->assertStringContainsString('999,00 lei', $html);
        $this->assertStringContainsString('1.998,00 lei', $html);
    }

    public function test_emails_keep_la_cerere_for_unpriced_orders(): void
    {
        Cart::add($this->makeOnRequestProduct()->id, 1);
        $order = $this->placeOrder();

        $html = (new OrderPlacedCustomer($order))->render();

        $this->assertStringContainsString('la cerere', mb_strtolower($html));
        $this->assertStringNotContainsString('lei', mb_strtolower(strip_tags($html)));
    }

    public function test_whatsapp_message_includes_effective_price_and_total(): void
    {
        Cart::add($this->makeSaleProduct()->id, 2);
        $order = $this->placeOrder();

        $msg = $order->whatsappMessage();

        $this->assertStringContainsString('999,00 lei', $msg);
        $this->assertStringContainsString('Total: 1.998,00 lei', $msg);
        $this->assertStringNotContainsString('1.159,00', $msg);
    }

    public function test_whatsapp_message_without_prices_stays_clean(): void
    {
        Cart::add($this->makeOnRequestProduct()->id, 1);
        $order = $this->placeOrder();

        $this->assertStringNotContainsString('lei', $order->whatsappMessage());
    }

    // ── Badge promoție ───────────────────────────────────────────────────────

    public function test_product_page_shows_discount_badge_in_signal_color(): void
    {
        $p = $this->makeSaleProduct();
        $cat = Category::create(['name' => 'Bănci', 'slug' => 'banci', 'sort_order' => 1, 'is_active' => true]);
        $p->categories()->attach($cat, ['is_primary' => true]);

        $res = $this->get('/produs/banca-b204')->assertOk();

        $res->assertSee('-14%');
        $res->assertSee('bg-signal', false);
    }

    public function test_card_shows_discount_badge_only_on_sale(): void
    {
        $sale = $this->makeSaleProduct();
        $plain = $this->makeOnRequestProduct();
        $cat = Category::create(['name' => 'Bănci', 'slug' => 'banci', 'sort_order' => 1, 'is_active' => true]);
        $sale->categories()->attach($cat, ['is_primary' => true]);
        $plain->categories()->attach($cat, ['is_primary' => true]);

        $res = $this->get('/categorie/banci')->assertOk();

        $res->assertSee('-14%');
        $this->assertSame(1, substr_count($res->getContent(), '-14%'));
    }
}
