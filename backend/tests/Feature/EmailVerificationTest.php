<?php

namespace Tests\Feature;

use App\Models\User;
use App\Notifications\VerifyEmailNotification;
use Illuminate\Foundation\Http\Middleware\ValidateCsrfToken;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Notification;
use Illuminate\Support\Facades\URL;
use Tests\TestCase;

class EmailVerificationTest extends TestCase
{
    use RefreshDatabase;

    private function withStatefulFrontend(): static
    {
        return $this
            ->withHeader('Origin', 'http://localhost:5173')
            ->withoutMiddleware(ValidateCsrfToken::class);
    }

    public function test_verification_email_sent_on_registration(): void
    {
        Notification::fake();

        $this->withStatefulFrontend()->postJson('/api/auth/register', [
            'name' => 'Verify User',
            'email' => 'verify@example.com',
            'password' => 'password123',
            'password_confirmation' => 'password123',
        ])->assertCreated();

        $user = User::where('email', 'verify@example.com')->first();
        Notification::assertSentTo($user, VerifyEmailNotification::class);
    }

    public function test_unverified_user_cannot_access_protected_routes(): void
    {
        $user = User::factory()->unverified()->create();
        $this->actingAs($user);

        $response = $this->getJson('/api/wardrobe');

        $response->assertForbidden()
            ->assertJson([
                'success' => false,
                'message' => 'Please verify your email to continue.',
                'code' => 'unverified',
            ]);
    }

    public function test_valid_signed_link_verifies_email(): void
    {
        $user = User::factory()->unverified()->create(['email' => 'signed@example.com']);

        $url = URL::temporarySignedRoute(
            'verification.verify',
            now()->addMinutes(60),
            ['id' => $user->id, 'hash' => sha1($user->email)]
        );

        $response = $this->get($url);

        $response->assertRedirect(config('app.frontend_url').'/verify-email?status=success');
        $this->assertNotNull($user->fresh()->email_verified_at);
    }

    public function test_tampered_verification_link_is_rejected(): void
    {
        $user = User::factory()->unverified()->create(['email' => 'tamper@example.com']);

        $url = URL::temporarySignedRoute(
            'verification.verify',
            now()->addMinutes(60),
            ['id' => $user->id, 'hash' => 'invalid-hash']
        );

        $response = $this->get($url);

        $response->assertRedirect(config('app.frontend_url').'/verify-email?status=invalid');
        $this->assertNull($user->fresh()->email_verified_at);
    }

    public function test_expired_verification_link_is_rejected(): void
    {
        $user = User::factory()->unverified()->create(['email' => 'expired@example.com']);

        $url = URL::temporarySignedRoute(
            'verification.verify',
            now()->subMinute(),
            ['id' => $user->id, 'hash' => sha1($user->email)]
        );

        $response = $this->get($url);

        $response->assertRedirect(config('app.frontend_url').'/verify-email?status=invalid');
        $this->assertNull($user->fresh()->email_verified_at);
    }

    public function test_resend_is_rate_limited(): void
    {
        Notification::fake();

        $user = User::factory()->unverified()->create();
        $this->actingAs($user);

        $this->postJson('/api/auth/email/resend')->assertOk();

        $response = $this->postJson('/api/auth/email/resend');

        $response->assertStatus(429);
    }

    public function test_verified_user_can_access_protected_routes(): void
    {
        $user = User::factory()->create();
        $this->actingAs($user);

        $response = $this->getJson('/api/wardrobe');

        $response->assertOk()
            ->assertJson(['success' => true]);
    }
}
