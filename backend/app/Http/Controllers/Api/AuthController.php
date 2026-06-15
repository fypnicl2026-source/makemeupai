<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Controllers\Concerns\RespondsWithJson;
use App\Http\Resources\UserResource;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class AuthController extends Controller
{
    use RespondsWithJson;

    public function register(Request $request)
    {
        $validated = $request->validate([
            'name' => ['required', 'string', 'max:255'],
            'email' => ['required', 'string', 'email', 'max:255', 'unique:users'],
            'password' => ['required', 'string', 'min:8', 'confirmed'],
            'gender' => ['nullable', 'string', 'max:50'],
            'city' => ['nullable', 'string', 'max:100'],
            'profile_photo' => ['nullable', 'string', 'max:500'],
        ]);

        $user = User::create([
            'name' => $validated['name'],
            'email' => $validated['email'],
            'password' => $validated['password'],
            'gender' => $validated['gender'] ?? null,
            'city' => $validated['city'] ?? 'Lahore',
            'profile_photo' => $validated['profile_photo'] ?? null,
        ]);

        Auth::login($user);
        $request->session()->regenerate();

        $token = $user->createToken('spa')->plainTextToken;

        return $this->success(
            ['user' => new UserResource($user), 'token' => $token],
            'Registration successful.',
            201
        );
    }

    public function login(Request $request)
    {
        $credentials = $request->validate([
            'email' => ['required', 'string', 'email'],
            'password' => ['required', 'string'],
        ]);

        if (! Auth::attempt($credentials, $request->boolean('remember'))) {
            return $this->error('Invalid credentials.', [
                'email' => ['The provided credentials are incorrect.'],
            ], 422);
        }

        $request->session()->regenerate();

        $user = $request->user();
        $token = $user->createToken('spa')->plainTextToken;

        return $this->success(
            ['user' => new UserResource($user), 'token' => $token],
            'Login successful.'
        );
    }

    public function logout(Request $request)
    {
        $request->user()?->currentAccessToken()?->delete();

        Auth::guard('web')->logout();

        $request->session()->invalidate();
        $request->session()->regenerateToken();

        return $this->success(null, 'Logged out successfully.');
    }

    public function me(Request $request)
    {
        return $this->success(
            ['user' => new UserResource($request->user())],
            'Authenticated user retrieved.'
        );
    }
}
