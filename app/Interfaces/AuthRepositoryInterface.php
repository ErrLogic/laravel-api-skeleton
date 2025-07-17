<?php

namespace App\Interfaces;

interface AuthRepositoryInterface
{
    public function login(array $credentials);

    public function register(array $data);

    public function logout($user);

    public function refreshToken($user);

    public function changePassword(array $data);
}
