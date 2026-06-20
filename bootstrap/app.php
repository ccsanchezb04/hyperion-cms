<?php

use App\Http\Middleware\HandleInertiaRequests;
use Illuminate\Foundation\Application;
use Illuminate\Foundation\Configuration\Exceptions;
use Illuminate\Foundation\Configuration\Middleware;
use Illuminate\Http\Middleware\AddLinkHeadersForPreloadedAssets;
use Illuminate\Http\Request;
use Inertia\Inertia;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;

return Application::configure(basePath: dirname(__DIR__))
    ->withRouting(
        web: __DIR__ . '/../routes/web.php',
        commands: __DIR__ . '/../routes/console.php',
        api: __DIR__ . '/../routes/api.php',
        health: '/up',
    )
    ->withMiddleware(function (Middleware $middleware) {
        $middleware->web(append: [
            HandleInertiaRequests::class,
            AddLinkHeadersForPreloadedAssets::class,
        ]);
        $middleware->alias([
            'role' =>
            \App\Http\Middleware\RoleMiddleware::class,
            'permission' =>
            \App\Http\Middleware\PermissionMiddleware::class,
            'verified' =>
            \Illuminate\Auth\Middleware\EnsureEmailIsVerified::class,
        ]);
        $middleware->validateCsrfTokens(except: [
            'admin/login',
            'admin/register',
        ]);
    })
    ->withExceptions(function (Exceptions $exceptions) {
        // 404 dentro del look & feel del tema activo (o del admin si la ruta empieza con /admin).
        // HandleInertiaRequests resuelve el root view por path automáticamente.
        $exceptions->render(function (NotFoundHttpException $e, Request $request) {
            if ($request->expectsJson() || $request->is('api/*')) {
                return null; // dejar que Laravel devuelva JSON 404
            }

            return Inertia::render('Errors/404', [
                'status' => 404,
                'message' => 'Página no encontrada',
            ])
                ->toResponse($request)
                ->setStatusCode(404);
        });
    })->create();
