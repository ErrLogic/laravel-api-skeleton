<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class ForceJsonResponse
{
    /**
     * Handle an incoming request.
     *
     * @param  Closure(Request): (Response)  $next
     */
    public function handle(Request $request, Closure $next): Response
    {
        if ($request->is('api/*')) {
            $request->headers->set(key: 'Accept', values: 'application/json');

            $response = $next($request);

            if ($response instanceof JsonResponse) {
                $response->header(key: 'Content-Type', values: 'application/json');
            }

            return $response;
        }

        return $next($request);
    }
}
