<?php

namespace Tests\Feature;

use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class CookieConsentTest extends TestCase
{
    use RefreshDatabase;

    public function test_cookie_banner_renders_on_pages(): void
    {
        $html = $this->get('/despre')->assertOk()->getContent();

        $this->assertStringContainsString('Consimțământ cookie-uri', $html);
        $this->assertStringContainsString('cookie_consent=', $html);
        $this->assertStringContainsString(route('politica-cookies'), $html);
        $this->assertStringContainsString('analytics_storage', $html);
        $this->assertStringContainsString('Meta Pixel', $html);
        $this->assertStringContainsString('TikTok Pixel', $html);
        $this->assertStringContainsString('Acceptă tot', $html);
        $this->assertStringContainsString('Respinge', $html);
    }
}
