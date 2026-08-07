<?php

namespace Tests\Feature\Auth;

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Hash;
use Tests\TestCase;

class RegistrationTest extends TestCase
{
    use RefreshDatabase;

    public function test_registration_screen_can_be_rendered(): void
    {
        $response = $this->get('/register');

        $response->assertStatus(200);
    }

    public function test_new_users_can_register_with_four_digit_password(): void
    {
        $response = $this->post('/register', [
            'name' => 'Test User',
            'email' => 'test@example.com',
            'country_code' => '+243',
            'phone' => '0997554905',
            'password' => '1234',
            'password_confirmation' => '1234',
            'entreprise_nom' => 'Entreprise Test',
            'entreprise_devise' => '$',
            'entreprise_taux' => '1.0000',
        ]);

        $response->assertRedirect();
        $this->assertAuthenticated();
        $this->assertDatabaseHas('users', ['email' => 'test@example.com']);
    }

    public function test_registration_rejects_duplicate_passwords(): void
    {
        User::factory()->create([
            'password' => Hash::make('1234'),
        ]);

        $response = $this->post('/register', [
            'name' => 'Test User 2',
            'email' => 'test2@example.com',
            'country_code' => '+243',
            'phone' => '0997554906',
            'password' => '1234',
            'password_confirmation' => '1234',
            'entreprise_nom' => 'Entreprise Test 2',
            'entreprise_devise' => '$',
            'entreprise_taux' => '1.0000',
        ]);

        $response->assertSessionHasErrors('password');
    }
}
