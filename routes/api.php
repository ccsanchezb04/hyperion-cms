<?php

use Illuminate\Support\Facades\Route;

use App\Http\Controllers\Auth\LoginController;
use App\Http\Controllers\Auth\LogoutController;
use App\Http\Controllers\Auth\RefreshTokenController;

Route::post('/auth/login', LoginController::class);

Route::middleware('auth:sanctum')
    ->group(function () {

        Route::post(
            '/auth/logout',
            LogoutController::class
        );

        Route::post(
            '/auth/refresh',
            RefreshTokenController::class
        );

        Route::get('/admin', function () {

            return response()->json([
                'message' => 'Admin Area'
            ]);
        })->middleware('role:admin');
    });


Route::prefix('v1')
    ->middleware([
        'api'
    ])
    ->group(function () {

        require __DIR__ . '/api/v1/auth.php';

        require __DIR__ . '/api/v1/users.php';

        require __DIR__ . '/api/v1/contents.php';

        require __DIR__ . '/api/v1/categories.php';

        require __DIR__ . '/api/v1/media.php';

        require __DIR__ . '/api/v1/menus.php';

        require __DIR__ . '/api/v1/settings.php';

        require __DIR__ . '/api/v1/ai.php';

        require __DIR__ . '/api/v1/notifications.php';
    });
