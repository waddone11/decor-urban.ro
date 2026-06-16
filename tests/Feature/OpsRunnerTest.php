<?php

namespace Tests\Feature;

use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class OpsRunnerTest extends TestCase
{
    use RefreshDatabase;

    private string $token = 'test-secret-token-very-long-0123456789';

    private function enableOps(): void
    {
        config(['ops.enabled' => true, 'ops.token' => $this->token]);
    }

    public function test_index_returns_404_without_token(): void
    {
        $this->enableOps();

        $this->get('/ops')->assertNotFound();
    }

    public function test_index_returns_404_with_wrong_token(): void
    {
        $this->enableOps();

        $this->get('/ops?token=gresit')->assertNotFound();
    }

    public function test_returns_404_when_ops_disabled_even_with_token(): void
    {
        config(['ops.enabled' => false, 'ops.token' => $this->token]);

        $this->get('/ops?token='.$this->token)->assertNotFound();
    }

    public function test_returns_404_when_token_config_empty(): void
    {
        config(['ops.enabled' => true, 'ops.token' => '']);

        $this->get('/ops?token=')->assertNotFound();
    }

    public function test_index_lists_commands_with_valid_token(): void
    {
        $this->enableOps();

        $this->get('/ops?token='.$this->token)
            ->assertOk()
            ->assertSee('migrate-status')
            ->assertSee('catalog-summary')
            ->assertSee('fresh');
    }

    public function test_runs_whitelisted_command(): void
    {
        $this->enableOps();

        $res = $this->get('/ops/migrate-status?token='.$this->token)->assertOk();
        $res->assertHeader('Content-Type', 'text/plain; charset=utf-8');
        $res->assertSee('php artisan migrate:status', false);
    }

    public function test_unknown_command_returns_404(): void
    {
        $this->enableOps();

        $this->get('/ops/rm-rf-totul?token='.$this->token)->assertNotFound();
    }

    public function test_destructive_command_requires_confirm(): void
    {
        $this->enableOps();

        // Fără confirm → refuzat (422), comanda NU rulează.
        $this->get('/ops/fresh?token='.$this->token)
            ->assertStatus(422)
            ->assertSee('confirm=YES');
    }
}
