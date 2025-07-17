<?php

namespace App\Services;

use App\Interfaces\AuthRepositoryInterface;
use App\Models\User;
use Carbon\Carbon;

class AuthService
{
    public function __construct(
        protected AuthRepositoryInterface $authRepository
    ) {}

    public function login(array $credentials): array
    {
        $user = $this->authRepository->login($credentials);
        $tokenExpirationMinutes = $this->getTokenExpirationTime();

        $token = $user->createToken('Bearer', ['*'], $tokenExpirationMinutes);

        return [
            'user' => $user,
            'access_token' => [
                'type' => $token->accessToken->name,
                'token' => $token->plainTextToken,
                'expires_at' => $token->accessToken->expires_at?->format('Y-m-d H:i:s'),
            ],
        ];
    }

    public function register($data): User
    {
        return $this->authRepository->register($data);
    }

    public function logout($user): void
    {
        $this->authRepository->logout($user);
    }

    public function refreshToken($user): array
    {
        $token = $this->authRepository->refreshToken($user);

        return [
            'token' => $token,
        ];
    }

    public function changePassword($data): void
    {
        $this->authRepository->changePassword($data);
    }

    public function getTokenExpirationTime(): ?Carbon
    {
        return transform(
            config('sanctum.expiration'),
            static fn ($minutes) => $minutes ? now()->addMinutes((int) $minutes) : null
        );
    }
}
