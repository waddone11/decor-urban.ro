<?php

namespace Tests\Feature;

use Illuminate\Foundation\Testing\RefreshDatabase;
use PHPUnit\Framework\Attributes\DataProvider;
use Tests\TestCase;

class StaticPagesTest extends TestCase
{
    use RefreshDatabase;

    public static function pageProvider(): array
    {
        return [
            'despre' => ['/despre', 'Despre noi'],
            'institutii' => ['/institutii', 'primării, școli și instituții'],
            'contact' => ['/contact', 'Contact'],
            'confidentialitate' => ['/confidentialitate', 'Politică de confidențialitate'],
            'termeni' => ['/termeni', 'Termeni și condiții'],
            'politica-cookies' => ['/politica-cookies', 'Politică de cookie-uri'],
        ];
    }

    #[DataProvider('pageProvider')]
    public function test_static_page_renders_with_single_h1(string $url, string $heading): void
    {
        $html = $this->get($url)->assertOk()->assertSee($heading)->getContent();

        $this->assertSame(1, substr_count($html, '<h1'), "Pagina {$url}: un singur h1");
        $this->assertStringContainsString('Breadcrumb', $html);
    }

    public function test_legal_pages_show_verify_notice(): void
    {
        foreach (['/confidentialitate', '/termeni', '/politica-cookies'] as $url) {
            $this->get($url)->assertSee('de verificat de un specialist');
        }
    }

    public function test_contact_page_has_form(): void
    {
        $this->get('/contact')
            ->assertOk()
            ->assertSeeLivewire(\App\Livewire\ContactForm::class)
            ->assertSee('Trimite mesajul');
    }
}
