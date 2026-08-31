<?php

namespace Tests\Feature;

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class ServeuseLogoutFlowTest extends TestCase
{
    use RefreshDatabase;

    public function test_serveuse_logout_keeps_the_authenticated_session_alive(): void
    {
        $user = User::factory()->create([
            'role' => 'Serveuse',
            'email' => 'serveuse@example.com',
        ]);

        $this->actingAs($user);

        $response = $this->get(route('serveuse.logout', ['serveuse_logout' => 1]));

        $response->assertRedirect(route('serveuse.login'));
        $this->assertAuthenticatedAs($user);
    }
}
