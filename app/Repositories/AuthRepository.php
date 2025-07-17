<?php

namespace App\Repositories;

use App\Interfaces\AuthRepositoryInterface;
use App\Models\User;
use Illuminate\Support\Facades\Hash;
use Illuminate\Validation\ValidationException;

class AuthRepository implements AuthRepositoryInterface
{
    public function login(array $credentials): User
    {
        $user = User::where('email', $credentials['email'])->first();

        if (! $user || ! Hash::check($credentials['password'], $user->password)) {
            throw ValidationException::withMessages([
                'invalid_credentials' => ['The provided credentials are incorrect.'],
            ]);
        }

        if (! $user->hasVerifiedEmail()) {
            throw ValidationException::withMessages([
                'unverified' => ['Unverified email.'],
            ]);
        }

        return $user;
    }

    public function register(array $data): User
    {
        return User::create($data);
    }

    public function logout($user): void
    {
        $user->currentAccessToken()->delete();
    }

    public function refreshToken($user): string
    {
        $user->tokens()->delete();

        return $user->createToken('auth_token')->plainTextToken;
    }

    public function changePassword(array $data): void
    {
        $user = auth()->user();
        $user->password = Hash::make($data['new_password']);
        $user->save();
        $user->tokens()->delete();
    }
}
