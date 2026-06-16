<?php

namespace App\Livewire;

use App\Models\Order;
use App\Support\Cart;
use Illuminate\Support\Facades\DB;
use Livewire\Component;

class Checkout extends Component
{
    public string $customer_name = '';
    public string $company = '';
    public string $cui = '';
    public string $phone = '';
    public string $email = '';
    public string $county = '';
    public string $city = '';
    public string $address = '';
    public string $payment_method = 'ramburs';
    public string $notes = '';

    /** Honeypot anti-spam. */
    public string $website = '';

    /** Județele României (+ București) pentru select. */
    public const COUNTIES = [
        'Alba', 'Arad', 'Argeș', 'Bacău', 'Bihor', 'Bistrița-Năsăud', 'Botoșani', 'Brăila',
        'Brașov', 'București', 'Buzău', 'Călărași', 'Caraș-Severin', 'Cluj', 'Constanța',
        'Covasna', 'Dâmbovița', 'Dolj', 'Galați', 'Giurgiu', 'Gorj', 'Harghita', 'Hunedoara',
        'Ialomița', 'Iași', 'Ilfov', 'Maramureș', 'Mehedinți', 'Mureș', 'Neamț', 'Olt',
        'Prahova', 'Sălaj', 'Satu Mare', 'Sibiu', 'Suceava', 'Teleorman', 'Timiș', 'Tulcea',
        'Vâlcea', 'Vaslui', 'Vrancea',
    ];

    public function mount()
    {
        if (Cart::isEmpty()) {
            return redirect()->route('cart');
        }
    }

    protected function rules(): array
    {
        return [
            'customer_name' => 'required|string|min:2|max:120',
            'company' => 'nullable|string|max:160',
            'cui' => 'nullable|string|max:20',
            'phone' => 'required|string|min:4|max:40',
            'email' => 'required|email|max:160',
            'county' => 'required|string|max:60',
            'city' => 'required|string|max:120',
            'address' => 'required|string|min:3|max:255',
            'payment_method' => 'required|in:ramburs,whatsapp',
            'notes' => 'nullable|string|max:3000',
        ];
    }

    protected function messages(): array
    {
        return [
            'required' => 'Câmpul este obligatoriu.',
            'email' => 'Introdu o adresă de email validă.',
            'min' => 'Prea scurt.',
            'in' => 'Alege o metodă validă.',
        ];
    }

    public function placeOrder()
    {
        $data = $this->validate();

        // Honeypot: bot → ignoră tăcut.
        if (trim($this->website) !== '') {
            Cart::clear();

            return redirect()->route('cart');
        }

        $lines = Cart::lines();
        if ($lines->isEmpty()) {
            return redirect()->route('cart');
        }

        $order = DB::transaction(function () use ($data, $lines) {
            $order = Order::createWithNumber($data);

            foreach ($lines as $line) {
                /** @var \App\Models\Product $product */
                $product = $line['product'];
                $order->items()->create([
                    'product_id' => $product->id,
                    'product_name' => $product->name,
                    'product_code' => $product->code,
                    'quantity' => $line['qty'],
                    'unit_price' => $product->price_on_request ? null : $product->price,
                ]);
            }

            return $order;
        });

        Cart::clear();
        $this->dispatch('cart-updated');

        return redirect()->route('order.success', $order->number);
    }

    public function render()
    {
        return view('livewire.checkout', [
            'lines' => Cart::lines(),
            'count' => Cart::count(),
            'counties' => self::COUNTIES,
            'methods' => Order::PAYMENT_METHODS,
        ])->layout('components.layouts.storefront', ['title' => 'Finalizează comanda']);
    }
}
