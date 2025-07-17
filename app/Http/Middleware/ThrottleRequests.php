<?php

namespace App\Http\Middleware;

use App\Services\RateLimiterService;
use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class ThrottleRequests
{
    public function __construct(
        protected RateLimiterService $rateLimiter
    ) {}

    /**
     * Handle an incoming request.
     *
     * @param  Closure(Request): (Response)  $next
     */
    public function handle(Request $request, Closure $next): Response
    {
        $key = $this->rateLimiter->resolveRequestSignature($request);
        $maxAttempts = config(key: 'throttle.defaults.max_attempts');
        $decaySeconds = config(key: 'throttle.defaults.decay_seconds');

        $this->rateLimiter->check(key: $key, maxAttempts: $maxAttempts, decaySeconds: $decaySeconds);

        return $next($request);
    }
}
