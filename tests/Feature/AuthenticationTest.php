<?php

namespace Tests\Feature;

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class AuthenticationTest extends TestCase
{
    use RefreshDatabase;

    public function test_a_visitor_can_register_and_keep_an_authenticated_session(): void
    {
        $response = $this->post('/register', [
            'alias' => 'مستخدم تجريبي',
            'password' => 'secret-password',
            'password_confirmation' => 'secret-password',
            'policy_accepted' => '1',
        ]);

        $user = User::query()->sole();

        $response->assertRedirect(route('dashboard'));
        $this->assertAuthenticatedAs($user);
        $this->get('/dashboard')->assertOk();
    }

    public function test_a_registered_user_can_log_out_and_log_in_again(): void
    {
        $user = User::factory()->create([
            'username' => 'AMAN-12345',
            'password' => 'secret-password',
        ]);

        $this->post('/login', [
            'username' => 'aman-12345',
            'password' => 'secret-password',
        ])->assertRedirect(route('dashboard'));

        $this->assertAuthenticatedAs($user);

        $this->post('/logout')->assertRedirect('/');
        $this->assertGuest();
    }
}
