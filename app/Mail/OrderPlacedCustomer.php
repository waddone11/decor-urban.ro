<?php

namespace App\Mail;

use App\Models\Order;
use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Mail\Mailables\Content;
use Illuminate\Mail\Mailables\Envelope;
use Illuminate\Queue\SerializesModels;

class OrderPlacedCustomer extends Mailable
{
    use Queueable, SerializesModels;

    public function __construct(public Order $order) {}

    public function envelope(): Envelope
    {
        return new Envelope(
            subject: 'Am primit comanda ta '.$this->order->number.' — '.config('contact.brand'),
        );
    }

    public function content(): Content
    {
        return new Content(markdown: 'mail.order-customer', with: ['order' => $this->order]);
    }
}
