<?php

namespace Tests\Feature;

use App\Models\User;
use Illuminate\Auth\Notifications\ResetPassword;
use Illuminate\Foundation\Http\Middleware\ValidateCsrfToken;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Notification;
use Illuminate\Support\Facades\Password;
use Tests\TestCase;

class PasswordResetTest extends TestCase
{
    use RefreshDatabase;

    private function withStatefulFrontend(): static
    {
        return $this
            ->withHeader('Origin', 'http://localhost:5173')
            ->withoutMiddleware(ValidateCsrfToken::class);
    }

    public function test_forgot_password_with_unknown_email_returns_generic_success(): void
    {
        Notification::fake();

        $response = $this->withStatefulFrontend()->postJson('/api/auth/forgot-password', [
            'email' => 'unknown@example.com',
        ]);

        $response->assertOk()
            ->assertJson([
                'success' => true,
                'message' => 'If that email exists, a reset link was sent.',
            ]);
    }

    public function test_password_reset_flow_end_to_end(): void
    {
        Notification::fake();

        $user = User::factory()->create(['email' => 'reset@example.com']);
        $token = Password::createToken($user);
        $oldToken = $user->createToken('spa')->plainTextToken;

        $response = $this->withStatefulFrontend()->postJson('/api/auth/reset-password', [
            'email' => 'reset@example.com',
            'token' => $token,
            'password' => 'newpassword123',
            'password_confirmation' => 'newpassword123',
        ]);

        $response->assertOk()
            ->assertJson(['success' => true, 'message' => 'Password has been reset.']);

        $user->refresh();
        $this->assertTrue(Hash::check('newpassword123', $user->password));
        $this->assertCount(0, $user->tokens);

        $this->getJson('/api/wardrobe', [
            'Authorization' => 'Bearer '.$oldToken,
        ])->assertUnauthorized();

        $this->withStatefulFrontend()->postJson('/api/auth/login', [
            'email' => 'reset@example.com',
            'password' => 'newpassword123',
        ])->assertOk();
    }

    public function test_expired_reset_token_is_rejected(): void
    {
        $user = User::factory()->create(['email' => 'expired@example.com']);
        $token = Password::createToken($user);

        $this->travel(61)->minutes();

        $response = $this->withStatefulFrontend()->postJson('/api/auth/reset-password', [
            'email' => 'expired@example.com',
            'token' => $token,
            'password' => 'newpassword123',
            'password_confirmation' => 'newpassword123',
        ]);

        $response->assertStatus(422)
            ->assertJson(['success' => false]);
    }

    public function test_reused_reset_token_is_rejected(): void
    {
        $user = User::factory()->create(['email' => 'reuse@example.com']);
        $token = Password::createToken($user);

        $this->withStatefulFrontend()->postJson('/api/auth/reset-password', [
            'email' => 'reuse@example.com',
            'token' => $token,
            'password' => 'newpassword123',
            'password_confirmation' => 'newpassword123',
        ])->assertOk();

        $response = $this->withStatefulFrontend()->postJson('/api/auth/reset-password', [
            'email' => 'reuse@example.com',
            'token' => $token,
            'password' => 'anotherpass123',
            'password_confirmation' => 'anotherpass123',
        ]);

        $response->assertStatus(422)
            ->assertJson(['success' => false]);
    }

    public function test_forgot_password_sends_notification_for_existing_user(): void
    {
        Notification::fake();

        $user = User::factory()->create(['email' => 'exists@example.com']);

        $this->withStatefulFrontend()->postJson('/api/auth/forgot-password', [
            'email' => 'exists@example.com',
        ])->assertOk();

        Notification::assertSentTo($user, ResetPassword::class);
    }
}
