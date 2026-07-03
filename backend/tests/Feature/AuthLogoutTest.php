<?php

namespace Tests\Feature;

use App\Models\User;
use Illuminate\Foundation\Http\Middleware\ValidateCsrfToken;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class AuthLogoutTest extends TestCase
{
    use RefreshDatabase;

    private function withStatefulFrontend(): static
    {
        return $this
            ->withHeader('Origin', 'http://localhost:5173')
            ->withoutMiddleware(ValidateCsrfToken::class);
    }

    public function test_logout_with_session_auth_returns_200(): void
    {
        $user = User::factory()->create();
        $this->actingAs($user);

        $response = $this->withStatefulFrontend()->postJson('/api/auth/logout');

        $response->assertOk()
            ->assertJson([
                'success' => true,
                'message' => 'Logged out successfully.',
            ]);
    }

    public function test_logout_with_bearer_token_returns_200(): void
    {
        $user = User::factory()->create();
        $token = $user->createToken('spa')->plainTextToken;

        $response = $this->postJson('/api/auth/logout', [], [
            'Authorization' => 'Bearer '.$token,
        ]);

        $response->assertOk()
            ->assertJson([
                'success' => true,
                'message' => 'Logged out successfully.',
            ]);
    }
}
