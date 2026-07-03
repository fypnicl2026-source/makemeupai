<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\User;
use Illuminate\Auth\Events\Verified;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\RateLimiter;

class EmailVerificationController extends Controller
{
    public function verify(Request $request, string $id, string $hash)
    {
        $frontendUrl = rtrim(config('app.frontend_url'), '/');

        if (! $request->hasValidSignature()) {
            return redirect("{$frontendUrl}/verify-email?status=invalid");
        }

        $user = User::findOrFail($id);

        if (! hash_equals($hash, sha1($user->getEmailForVerification()))) {
            return redirect("{$frontendUrl}/verify-email?status=invalid");
        }

        if ($user->hasVerifiedEmail()) {
            return redirect("{$frontendUrl}/verify-email?status=success");
        }

        if ($user->markEmailAsVerified()) {
            event(new Verified($user));
        }

        return redirect("{$frontendUrl}/verify-email?status=success");
    }

    public function resend(Request $request)
    {
        $user = $request->user();

        if ($user->hasVerifiedEmail()) {
            return response()->json([
                'success' => true,
                'message' => 'Email already verified.',
                'data' => (object) [],
            ]);
        }

        $key = 'resend-verification:'.$user->id;

        if (RateLimiter::tooManyAttempts($key, 1)) {
            $seconds = RateLimiter::availableIn($key);

            return response()->json([
                'success' => false,
                'message' => "Please wait {$seconds} seconds before resending.",
                'data' => (object) [],
            ], 429);
        }

        RateLimiter::hit($key, 60);

        $user->sendEmailVerificationNotification();

        return response()->json([
            'success' => true,
            'message' => 'Verification link sent.',
            'data' => (object) [],
        ]);
    }
}
