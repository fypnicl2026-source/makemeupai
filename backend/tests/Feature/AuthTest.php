<?php

namespace Tests\Feature;

use App\Models\User;
use Illuminate\Foundation\Http\Middleware\ValidateCsrfToken;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Notification;
use Tests\TestCase;

class AuthTest extends TestCase
{
    use RefreshDatabase;

    private function withStatefulFrontend(): static
    {
        return $this
            ->withHeader('Origin', 'http://localhost:5173')
            ->withoutMiddleware(ValidateCsrfToken::class);
    }

    private function validRegisterPayload(array $overrides = []): array
    {
        return array_merge([
            'name' => 'Test User',
            'email' => 'auth-test@example.com',
            'password' => 'password123',
            'password_confirmation' => 'password123',
        ], $overrides);
    }

    public function test_register_with_valid_data_returns_201(): void
    {
        Notification::fake();

        $response = $this->withStatefulFrontend()->postJson('/api/auth/register', $this->validRegisterPayload([
            'email' => 'newauth@example.com',
        ]));

        $response->assertCreated()
            ->assertJson(['success' => true])
            ->assertJsonPath('data.user.email', 'newauth@example.com')
            ->assertJsonPath('data.user.email_verified', false)
            ->assertJsonStructure(['data' => ['user', 'token']]);
    }

    public function test_register_with_duplicate_email_returns_422(): void
    {
        User::factory()->create(['email' => 'duplicate@example.com']);

        $response = $this->withStatefulFrontend()->postJson('/api/auth/register', $this->validRegisterPayload([
            'email' => 'duplicate@example.com',
        ]));

        $response->assertStatus(422)
            ->assertJsonValidationErrors(['email']);
    }

    public function test_register_with_weak_password_returns_422(): void
    {
        $response = $this->withStatefulFrontend()->postJson('/api/auth/register', $this->validRegisterPayload([
            'email' => 'weak@example.com',
            'password' => 'short',
            'password_confirmation' => 'short',
        ]));

        $response->assertStatus(422)
            ->assertJsonValidationErrors(['password']);
    }

    public function test_login_with_correct_credentials_returns_200(): void
    {
        $user = User::factory()->create([
            'email' => 'loginauth@example.com',
            'password' => 'password123',
        ]);

        $response = $this->withStatefulFrontend()->postJson('/api/auth/login', [
            'email' => 'loginauth@example.com',
            'password' => 'password123',
        ]);

        $response->assertOk()
            ->assertJson(['success' => true])
            ->assertJsonPath('data.user.id', $user->id);
    }

    public function test_login_with_wrong_password_returns_401(): void
    {
        User::factory()->create([
            'email' => 'wrongauth@example.com',
            'password' => 'password123',
        ]);

        $response = $this->withStatefulFrontend()->postJson('/api/auth/login', [
            'email' => 'wrongauth@example.com',
            'password' => 'wrong-password',
        ]);

        $response->assertStatus(401)
            ->assertJson(['success' => false, 'message' => 'Invalid credentials.']);
    }

    public function test_login_is_rate_limited_after_five_attempts(): void
    {
        User::factory()->create([
            'email' => 'ratelimit@example.com',
            'password' => 'password123',
        ]);

        for ($i = 0; $i < 5; $i++) {
            $this->withStatefulFrontend()->postJson('/api/auth/login', [
                'email' => 'ratelimit@example.com',
                'password' => 'wrong-password',
            ])->assertStatus(401);
        }

        $response = $this->withStatefulFrontend()->postJson('/api/auth/login', [
            'email' => 'ratelimit@example.com',
            'password' => 'wrong-password',
        ]);

        $response->assertStatus(429);
    }
}
