<?php

use App\Http\Controllers\ApiController;
use App\Http\Middleware\ForceJsonResponse;
use App\Http\Middleware\ThrottleRequests;
use Illuminate\Auth\AuthenticationException;
use Illuminate\Database\Eloquent\ModelNotFoundException;
use Illuminate\Database\QueryException;
use Illuminate\Foundation\Application;
use Illuminate\Foundation\Configuration\Exceptions;
use Illuminate\Foundation\Configuration\Middleware;
use Illuminate\Http\Exceptions\ThrottleRequestsException;
use Illuminate\Validation\ValidationException;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpKernel\Exception\AccessDeniedHttpException;
use Symfony\Component\HttpKernel\Exception\MethodNotAllowedHttpException;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;
use Symfony\Component\Routing\Exception\RouteNotFoundException;

return Application::configure(basePath: dirname(__DIR__))
    ->withRouting(
        web: __DIR__.'/../routes/web.php',
        api: __DIR__.'/../routes/api.php',
        commands: __DIR__.'/../routes/console.php',
        health: '/up',
    )
    ->withMiddleware(function (Middleware $middleware): void {
        $middleware->append(middleware: ForceJsonResponse::class);
        $middleware->append(middleware: ThrottleRequests::class);
    })
    ->withExceptions(function (Exceptions $exceptions): void {
        $exceptions->render(function (Throwable $e, $request) {
            if ($request->is('api/*')) {
                $response = new ApiController;

                switch (true) {
                    case $e instanceof MethodNotAllowedHttpException:
                        return $response->errorResponse(
                            message: 'Method not allowed',
                            errors: ['method' => $request->method().' is not supported for this route'],
                            code: Response::HTTP_METHOD_NOT_ALLOWED
                        );

                    case $e instanceof RouteNotFoundException:
                    case $e instanceof ModelNotFoundException:
                    case $e instanceof NotFoundHttpException:
                        return $response->errorResponse(
                            message: 'Resource not found',
                            code: Response::HTTP_NOT_FOUND
                        );

                    case $e instanceof ThrottleRequestsException:
                        return $response->errorResponse(
                            message: $e->getMessage(),
                            code: Response::HTTP_TOO_MANY_REQUESTS
                        );

                    case $e instanceof AuthenticationException:
                        return $response->errorResponse(
                            message: 'Unauthenticated',
                            code: Response::HTTP_UNAUTHORIZED
                        );

                    case $e instanceof AccessDeniedHttpException:
                        return $response->errorResponse(
                            message: 'Forbidden',
                            code: Response::HTTP_FORBIDDEN
                        );

                    case $e instanceof ValidationException:
                        return $response->errorResponse(
                            message: 'Validation error',
                            errors: $e->errors(),
                            code: Response::HTTP_UNPROCESSABLE_ENTITY
                        );

                    case $e instanceof QueryException:
                        $message = config('app.debug')
                            ? $e->getMessage()
                            : 'Database error occurred';

                        return $response->errorResponse(
                            message: $message,
                            code: Response::HTTP_INTERNAL_SERVER_ERROR
                        );

                    default:
                        $statusCode = method_exists($e, 'getStatusCode')
                            ? $e->getStatusCode()
                            : Response::HTTP_INTERNAL_SERVER_ERROR;

                        return $response->errorResponse(
                            message: config('app.debug') ? $e->getMessage() : 'Server error',
                            errors: config('app.debug') ? [
                                'exception' => get_class($e),
                                'file' => $e->getFile(),
                                'line' => $e->getLine(),
                                'trace' => $e->getTrace(),
                            ] : 'Internal server error occurred',
                            code: $statusCode
                        );
                }
            }

            return null;
        });

        $exceptions->report(function (QueryException $e) {
            logger()?->error(message: 'Database error: '.$e->getMessage());
        });
    })->create();
