<?php

use Illuminate\Foundation\Application;
use Illuminate\Foundation\Configuration\Exceptions;
use Illuminate\Foundation\Configuration\Middleware;

return Application::configure(basePath: dirname(__DIR__))
    ->withRouting(
        web: __DIR__.'/../routes/web.php',
        api: __DIR__.'/../routes/api.php',
        commands: __DIR__.'/../routes/console.php',
        health: '/up',
    )
    ->withMiddleware(function (Middleware $middleware) {
        // Register custom middleware aliases
        $middleware->alias([
            'ensure.company.setup' => \App\Http\Middleware\EnsureCompanySetup::class,
        ]);
        
        // Configure API middleware groups
        $middleware->api(prepend: [
            // Add any global API middleware here if needed
        ]);
        
        // Configure Sanctum for API authentication
        $middleware->statefulApi();
    })
    ->withExceptions(function (Exceptions $exceptions) {
        // Handle API authentication exceptions
        $exceptions->render(function (\Illuminate\Auth\AuthenticationException $e, $request) {
            if ($request->expectsJson() || $request->is('api/*')) {
                return response()->json([
                    'message' => 'Authentication required',
                    'error_code' => 'UNAUTHENTICATED'
                ], 401);
            }
        });
        
        // Handle authorization exceptions
        $exceptions->render(function (\Illuminate\Auth\Access\AuthorizationException $e, $request) {
            if ($request->expectsJson() || $request->is('api/*')) {
                return response()->json([
                    'message' => 'Access denied',
                    'error_code' => 'UNAUTHORIZED'
                ], 403);
            }
        });
        
        // Handle validation exceptions
        $exceptions->render(function (\Illuminate\Validation\ValidationException $e, $request) {
            if ($request->expectsJson() || $request->is('api/*')) {
                return response()->json([
                    'message' => 'Validation failed',
                    'errors' => $e->errors(),
                    'error_code' => 'VALIDATION_ERROR'
                ], 422);
            }
        });
        
        // Handle rate limiting exceptions
        $exceptions->render(function (\Illuminate\Http\Exceptions\ThrottleRequestsException $e, $request) {
            if ($request->expectsJson() || $request->is('api/*')) {
                return response()->json([
                    'message' => 'Too many requests',
                    'retry_after' => $e->getHeaders()['Retry-After'] ?? null,
                    'error_code' => 'RATE_LIMIT_EXCEEDED'
                ], 429);
            }
        });
    })->create();