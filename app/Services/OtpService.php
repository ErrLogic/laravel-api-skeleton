<?php

namespace App\Services;

use App\Models\Otp;
use App\Models\User;
use App\Notifications\OtpNotification;
use Illuminate\Support\Facades\Notification;
use Illuminate\Validation\ValidationException;
use Random\RandomException;
use RuntimeException;

class OtpService
{
    protected ?string $lastGeneratedOtp = null;

    protected int $otpLength = 6;

    protected int $otpExpiryMinutes = 15;

    protected int $otpCleanupThreshold = 5;

    public function validateOtp(string $email, string $otpCode): bool
    {
        $otp = $this->findValidOtp($email, $otpCode);

        if (! $otp) {
            throw ValidationException::withMessages([
                'otp' => ['Invalid or expired OTP'],
            ]);
        }

        $otp->update(['is_verified' => true]);
        $this->verifyEmail($email);

        if (rand(1, $this->otpCleanupThreshold) === 1) {
            $this->cleanupVerifiedOtps();
        }

        return true;
    }

    /**
     * @throws RandomException
     */
    public function generateOtp(string $email): self
    {
        $this->validateEmail($email);

        Otp::where('email', $email)->update(['is_verified' => true]);

        $this->lastGeneratedOtp = $this->generateRandomOtp();

        Otp::create([
            'email' => $email,
            'otp_code' => $this->lastGeneratedOtp,
            'expires_at' => now()->addMinutes($this->otpExpiryMinutes),
        ]);

        return $this;
    }

    public function sendOtpEmail(string $email, ?string $otpCode = null): self
    {
        $this->validateEmail($email);

        $codeToSend = $otpCode ?? $this->getLastGeneratedOtp();

        Notification::route('mail', $email)
            ->notify(new OtpNotification($codeToSend));

        return $this;
    }

    protected function findValidOtp(string $email, string $otpCode): ?Otp
    {
        return Otp::where('email', $email)
            ->where('otp_code', $otpCode)
            ->where('expires_at', '>', now())
            ->where('is_verified', false)
            ->first();
    }

    protected function verifyEmail(string $email): void
    {
        User::where('email', $email)->update(['email_verified_at' => now()]);
    }

    /**
     * @throws RandomException
     */
    protected function generateRandomOtp(): string
    {
        return str_pad(random_int(0, 10 ** $this->otpLength - 1), $this->otpLength, '0', STR_PAD_LEFT);
    }

    protected function validateEmail(string $email): void
    {
        if (! User::where('email', $email)->exists()) {
            throw ValidationException::withMessages([
                'email' => ['Unregistered user'],
            ]);
        }
    }

    protected function getLastGeneratedOtp(): string
    {
        if (! $this->lastGeneratedOtp) {
            throw new RuntimeException('No OTP code available to send');
        }

        return $this->lastGeneratedOtp;
    }

    protected function cleanupVerifiedOtps(): void
    {
        Otp::where('is_verified', true)
            ->orWhere('expires_at', '<', now())
            ->delete();
    }
}
