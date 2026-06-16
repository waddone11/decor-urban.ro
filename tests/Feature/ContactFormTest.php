<?php

namespace Tests\Feature;

use App\Livewire\ContactForm;
use App\Mail\ContactMail;
use Illuminate\Support\Facades\Mail;
use Livewire\Livewire;
use Tests\TestCase;

class ContactFormTest extends TestCase
{
    public function test_valid_submission_sends_mail_to_admin(): void
    {
        Mail::fake();
        config(['contact.email' => 'admin@decor-urban.ro']);

        Livewire::test(ContactForm::class)
            ->set('name', 'Ion Popescu')
            ->set('phone', '0712345678')
            ->set('email', 'ion@example.com')
            ->set('message', 'Doresc o ofertă pentru 10 bănci stradale.')
            ->call('submit')
            ->assertSet('sent', true)
            ->assertHasNoErrors();

        Mail::assertSent(ContactMail::class, function (ContactMail $mail) {
            return $mail->hasTo('admin@decor-urban.ro')
                && $mail->email === 'ion@example.com'
                && str_contains($mail->userMessage, 'bănci stradale');
        });
    }

    public function test_validation_blocks_empty_submission(): void
    {
        Mail::fake();

        Livewire::test(ContactForm::class)
            ->call('submit')
            ->assertHasErrors(['name', 'phone', 'email', 'message'])
            ->assertSet('sent', false);

        Mail::assertNothingSent();
    }

    public function test_honeypot_blocks_spam_without_sending(): void
    {
        Mail::fake();

        Livewire::test(ContactForm::class)
            ->set('name', 'Spam Bot')
            ->set('phone', '0700000000')
            ->set('email', 'spam@example.com')
            ->set('message', 'cumpără acum ieftin')
            ->set('website', 'http://spam.example')
            ->call('submit')
            ->assertSet('sent', true);

        Mail::assertNothingSent();
    }
}
