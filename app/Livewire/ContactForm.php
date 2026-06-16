<?php

namespace App\Livewire;

use App\Mail\ContactMail;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Mail;
use Livewire\Attributes\Validate;
use Livewire\Component;

class ContactForm extends Component
{
    #[Validate('required|string|min:2|max:120')]
    public string $name = '';

    #[Validate('required|string|min:4|max:40')]
    public string $phone = '';

    #[Validate('required|email|max:160')]
    public string $email = '';

    #[Validate('required|string|min:5|max:3000')]
    public string $message = '';

    /** Honeypot anti-spam: câmp ascuns; dacă e completat, e bot. */
    public string $website = '';

    public bool $sent = false;

    public function submit(): void
    {
        $data = $this->validate();

        // Honeypot completat → tratăm ca spam: confirmăm fără să trimitem nimic.
        if (trim($this->website) !== '') {
            Log::channel('single')->info('ContactForm: honeypot declanșat, ignor.');
            $this->reset(['name', 'phone', 'email', 'message', 'website']);
            $this->sent = true;

            return;
        }

        Mail::to(config('contact.email'))->send(new ContactMail(
            name: $data['name'],
            phone: $data['phone'],
            email: $data['email'],
            userMessage: $data['message'],
        ));

        $this->reset(['name', 'phone', 'email', 'message', 'website']);
        $this->sent = true;
    }

    public function render()
    {
        return view('livewire.contact-form');
    }
}
