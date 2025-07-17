<?php

namespace App\Services;

use Illuminate\Cache\RateLimiter;
use Illuminate\Http\Exceptions\ThrottleRequestsException;
use Illuminate\Http\Request;

class RateLimiterService
{
    public function __construct(
        protected RateLimiter $rateLimiter
    ) {}

    public function check(string $key, int $maxAttempts, int $decaySeconds): void
    {
        if ($this->rateLimiter->tooManyAttempts($key, $maxAttempts)) {
            $retryAfter = $this->rateLimiter->availableIn($key);

            throw new ThrottleRequestsException(
                message: 'Too many attempts. Please try again in '.$retryAfter.' seconds.'
            );
        }

        $this->rateLimiter->hit($key, $decaySeconds);
    }

    public function clear(Request $request): void
    {
        $this->rateLimiter->clear($this->resolveRequestSignature($request));
    }

    public function resolveRequestSignature(Request $request): string
    {
        return sha1(
            string: $request->method().
            '|'.$request->server('SERVER_NAME').
            '|'.$request->path().
            '|'.$request->ip()
        );
    }
}
