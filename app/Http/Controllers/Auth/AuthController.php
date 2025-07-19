<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use App\Http\Requests\ChangePasswordRequest;
use App\Http\Requests\LoginRequest;
use App\Http\Requests\RegisterRequest;
use App\Http\Requests\SendOtpRequest;
use App\Http\Requests\ValidateOtpRequest;
use App\Http\Resources\UserResource;
use App\Services\AuthService;
use App\Traits\ApiResponseTrait;
use Illuminate\Http\JsonResponse;

class AuthController extends Controller
{
    use ApiResponseTrait;

    public function __construct(
        protected AuthService $authService
    ) {}

    public function login(LoginRequest $request): ?JsonResponse
    {
        $data = $this->authService->login($request->validated());

        return $this->successResponse(
            data: [
                'user' => new UserResource($data['user']),
                'access_token' => $data['access_token'],
            ],
            message: 'Login successful'
        );
    }

    public function register(RegisterRequest $request): ?JsonResponse
    {
        $data = $this->authService->register($request->validated());

        return $this->successResponse(data: $data, message: 'Registration successful');
    }

    public function logout(): ?JsonResponse
    {
        $this->authService->logout(auth()->user());

        return $this->successResponse(message: 'Successfully logged out');
    }

    public function refreshToken(): ?JsonResponse
    {
        $data = $this->authService->refreshToken(auth()->user());

        return $this->successResponse(data: $data, message: 'Token refreshed successfully');
    }

    public function changePassword(ChangePasswordRequest $request): ?JsonResponse
    {
        $this->authService->changePassword($request->validated());

        return $this->successResponse(message: 'Password successfully changed. Please re-login again');
    }

    public function sendOtp(SendOtpRequest $request): ?JsonResponse
    {
        $this->authService->sendOtp($request->validated());

        return $this->successResponse(message: 'OTP sent to your email. Check your spam folder if you don’t see it.');
    }

    public function validateOtp(ValidateOtpRequest $request): ?JsonResponse
    {
        $this->authService->validateOtp($request->validated());

        return $this->successResponse(message: 'OTP validated successfully');
    }
}
