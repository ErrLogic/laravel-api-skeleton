<?php

namespace App\Repositories;

use App\Interfaces\AuthRepositoryInterface;
use App\Models\User;
use App\Services\OtpService;
use Illuminate\Support\Facades\Hash;
use Illuminate\Validation\ValidationException;
use Laravel\Sanctum\PersonalAccessToken;
use Random\RandomException;
use RuntimeException;

class AuthRepository implements AuthRepositoryInterface
{
    public function __construct(
        protected OtpService $otpService
    ) {}

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

    public function logout(User $user): void
    {
        $token = $user->currentAccessToken();

        if (! $token instanceof PersonalAccessToken) {
            throw new RuntimeException('Invalid token type');
        }

        $token->delete();
    }

    public function refreshToken(User $user): string
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

    /**
     * @throws RandomException
     */
    public function sendOtp(string $email): bool
    {
        $this->otpService->generateOtp($email)
            ->sendOtpEmail($email);

        return true;
    }

    public function validateOtp(string $email, string $otpCode): bool
    {
        return $this->otpService->validateOtp($email, $otpCode);
    }
}
