<?php

namespace App\Livewire;

use App\Models\Order;
use App\Models\Product;
use App\Models\ProductReview;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\RateLimiter;
use Livewire\Component;

/**
 * Recenzii pe produs: listă (doar approved) + formular public pentru vizitatori.
 * Recenziile intră ÎNTOTDEAUNA pending — owner-ul moderează în Filament.
 */
class ProductReviews extends Component
{
    public int $productId;

    public string $author_name = '';

    public string $author_email = '';

    public ?int $rating = null;

    public string $title = '';

    public string $body = '';

    /** Honeypot anti-spam. */
    public string $website = '';

    public bool $submitted = false;

    protected function rules(): array
    {
        return [
            'author_name' => 'required|string|min:2|max:120',
            'author_email' => 'required|email|max:160',
            'rating' => 'required|integer|min:1|max:5',
            'title' => 'nullable|string|max:160',
            'body' => 'required|string|min:10|max:3000',
        ];
    }

    protected function messages(): array
    {
        return [
            'required' => 'Câmpul este obligatoriu.',
            'email' => 'Introdu o adresă de email validă.',
            'rating.required' => 'Alege un număr de stele.',
            'min' => 'Prea scurt.',
            'rating.min' => 'Alege între 1 și 5 stele.',
            'rating.max' => 'Alege între 1 și 5 stele.',
        ];
    }

    public function submit(): void
    {
        // Honeypot: bot → ignoră tăcut (nu confirmăm nimic spamerului).
        if (trim($this->website) !== '') {
            Log::channel('single')->info('ProductReviews: honeypot declanșat, ignor.');
            $this->reset(['author_name', 'author_email', 'rating', 'title', 'body', 'website']);
            $this->submitted = true;

            return;
        }

        $data = $this->validate();

        $key = 'product-review:'.request()->ip();
        if (RateLimiter::tooManyAttempts($key, 3)) {
            $this->addError('author_email', 'Prea multe recenzii trimise. Încearcă din nou peste o oră.');

            return;
        }
        RateLimiter::hit($key, 3600);

        ProductReview::create([
            'product_id' => $this->productId,
            'author_name' => $data['author_name'],
            'author_email' => $data['author_email'],
            'rating' => $data['rating'],
            'title' => $data['title'] ?: null,
            'body' => $data['body'],
            'status' => 'pending',
            'verified_purchase' => Order::where('email', $data['author_email'])->exists(),
        ]);

        $this->reset(['author_name', 'author_email', 'rating', 'title', 'body', 'website']);
        $this->submitted = true;
    }

    public function render()
    {
        $product = Product::findOrFail($this->productId);

        return view('livewire.product-reviews', [
            'reviews' => $product->approvedReviews()->take(20)->get(),
            'stats' => $product->reviews()->approved()
                ->selectRaw('COUNT(*) as cnt, AVG(rating) as avg_rating')
                ->toBase()
                ->first(),
        ]);
    }
}
