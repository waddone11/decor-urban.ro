<?php

namespace Tests\Feature\Filament;

use App\Filament\Resources\Orders\OrderResource;
use App\Filament\Resources\Orders\Pages\EditOrder;
use App\Filament\Resources\Orders\Pages\ListOrders;
use App\Filament\Resources\Orders\RelationManagers\ItemsRelationManager;
use App\Models\Order;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Livewire\Livewire;
use Tests\TestCase;

class OrderResourceTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();
        $this->actingAs(User::factory()->create());
    }

    private function makeOrder(array $overrides = []): Order
    {
        $order = Order::createWithNumber(array_merge([
            'customer_name' => 'Ion Popescu', 'company' => 'Primăria Slatina',
            'phone' => '0712345678', 'email' => 'ion@example.com',
            'county' => 'Olt', 'city' => 'Slatina', 'address' => 'Str. X', 'payment_method' => 'ramburs',
        ], $overrides));
        $order->items()->create(['product_name' => 'Coș C120', 'product_code' => '#C120', 'quantity' => 2]);

        return $order;
    }

    public function test_list_shows_orders_and_columns(): void
    {
        $this->makeOrder();

        Livewire::test(ListOrders::class)
            ->assertCountTableRecords(1)
            ->assertCanRenderTableColumn('number')
            ->assertCanRenderTableColumn('customer_name')
            ->assertCanRenderTableColumn('items_count')
            ->assertCanRenderTableColumn('status')
            ->assertCanRenderTableColumn('payment_method');
    }

    public function test_search_by_number_and_phone(): void
    {
        $a = $this->makeOrder(['customer_name' => 'Alfa']);
        $b = $this->makeOrder(['customer_name' => 'Beta', 'phone' => '0799999999']);

        Livewire::test(ListOrders::class)
            ->searchTable($a->number)
            ->assertCanSeeTableRecords([$a])
            ->assertCanNotSeeTableRecords([$b]);

        Livewire::test(ListOrders::class)
            ->searchTable('0799999999')
            ->assertCanSeeTableRecords([$b])
            ->assertCanNotSeeTableRecords([$a]);
    }

    public function test_filter_by_status(): void
    {
        $noua = $this->makeOrder();
        $livrata = $this->makeOrder(['status' => 'livrata']);

        Livewire::test(ListOrders::class)
            ->filterTable('status', 'livrata')
            ->assertCanSeeTableRecords([$livrata])
            ->assertCanNotSeeTableRecords([$noua]);
    }

    public function test_status_is_editable(): void
    {
        $order = $this->makeOrder();

        Livewire::test(EditOrder::class, ['record' => $order->getRouteKey()])
            ->fillForm(['status' => 'confirmata'])
            ->call('save')
            ->assertHasNoFormErrors();

        $this->assertSame('confirmata', $order->refresh()->status);
    }

    public function test_items_relation_manager_is_readonly_list(): void
    {
        $order = $this->makeOrder();

        Livewire::test(ItemsRelationManager::class, [
            'ownerRecord' => $order,
            'pageClass' => EditOrder::class,
        ])
            ->assertCountTableRecords(1)
            ->assertCanRenderTableColumn('product_name')
            ->assertCanRenderTableColumn('quantity');
    }

    public function test_navigation_badge_counts_new_orders(): void
    {
        $this->makeOrder();
        $this->makeOrder(['status' => 'livrata']);

        $this->assertSame('1', OrderResource::getNavigationBadge());
    }
}
