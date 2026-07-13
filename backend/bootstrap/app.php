<?php

use App\Http\Middleware\EnsureAnyPermission;
use App\Http\Middleware\EnsurePermission;
use App\Http\Middleware\SecurityHeaders;
use Illuminate\Auth\AuthenticationException;
use Illuminate\Foundation\Application;
use Illuminate\Foundation\Configuration\Exceptions;
use Illuminate\Foundation\Configuration\Middleware;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;

return Application::configure(basePath: dirname(__DIR__))
    ->withRouting(
        web: __DIR__.'/../routes/web.php',
        api: __DIR__.'/../routes/api.php',
        commands: __DIR__.'/../routes/console.php',
        health: '/up',
    )
    ->withMiddleware(function (Middleware $middleware) {
        $middleware->append(SecurityHeaders::class);

        $middleware->alias([
            'permission_any' => EnsureAnyPermission::class,
            'permission' => EnsurePermission::class,
        ]);
    })
    ->withExceptions(function (Exceptions $exceptions) {
        $exceptions->render(function (AuthenticationException $exception) {
            return response()->json([
                'success' => false,
                'message' => 'Unauthenticated.',
            ], 401);
        });

        $exceptions->render(function (NotFoundHttpException $exception) {
            return response()->json([
                'success' => false,
                'message' => 'Resource not found.',
                'errors' => [],
            ], 404);
        });
    })->create();
