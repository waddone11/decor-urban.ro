<?php

namespace Tests\Feature;

use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class CommandRoutesTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();
        config(['commands.secret' => 'test-secret-cheie-lunga']);
    }

    public function test_missing_secret_returns_404(): void
    {
        $this->get('/commands/about')->assertNotFound();
    }

    public function test_wrong_secret_returns_404(): void
    {
        $this->get('/commands/about?secret=gresit')->assertNotFound();
    }

    public function test_404_when_secret_not_configured(): void
    {
        config(['commands.secret' => null]);
        $this->get('/commands/about?secret=orice')->assertNotFound();
    }

    public function test_correct_secret_runs_command(): void
    {
        $res = $this->get('/commands/migrate-status?secret=test-secret-cheie-lunga')->assertOk();
        $res->assertHeader('Content-Type', 'text/plain; charset=utf-8');
        $res->assertSee('php artisan migrate:status', false);
    }

    public function test_secret_via_header_works(): void
    {
        $this->withHeader('X-Command-Secret', 'test-secret-cheie-lunga')
            ->get('/commands/about')
            ->assertOk();
    }

    public function test_migrate_fresh_seed_requires_confirm(): void
    {
        $this->get('/commands/migrate-fresh-seed?secret=test-secret-cheie-lunga')
            ->assertStatus(422)
            ->assertSee('confirm=YES');
    }

    public function test_index_lists_commands(): void
    {
        $this->get('/commands?secret=test-secret-cheie-lunga')
            ->assertOk()
            ->assertSee('migrate-fresh-seed')
            ->assertSee('create-sitemap');
    }
}
