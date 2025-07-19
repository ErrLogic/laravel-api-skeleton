<?php

namespace App\Interfaces;

use App\Models\User;

interface AuthRepositoryInterface
{
    public function login(array $credentials);

    public function register(array $data);

    public function logout(User $user);

    public function refreshToken(User $user);

    public function changePassword(array $data);

    public function sendOtp(string $email);

    public function validateOtp(string $email, string $otpCode);
}
