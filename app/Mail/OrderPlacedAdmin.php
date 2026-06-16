<?php

namespace App\Mail;

use App\Models\Order;
use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Mail\Mailables\Address;
use Illuminate\Mail\Mailables\Content;
use Illuminate\Mail\Mailables\Envelope;
use Illuminate\Queue\SerializesModels;

class OrderPlacedAdmin extends Mailable
{
    use Queueable, SerializesModels;

    public function __construct(public Order $order) {}

    public function envelope(): Envelope
    {
        return new Envelope(
            subject: 'Comandă nouă '.$this->order->number.' — '.$this->order->customer_name,
            // Răspuns direct către client.
            replyTo: [new Address($this->order->email, $this->order->customer_name)],
        );
    }

    public function content(): Content
    {
        return new Content(markdown: 'mail.order-admin', with: ['order' => $this->order]);
    }
}
