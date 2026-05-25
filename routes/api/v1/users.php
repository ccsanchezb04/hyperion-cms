<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\Api\UserController;

Route::prefix('users')
    ->middleware(['auth:sanctum'])
    ->group(function () {

        Route::get('/', [UserController::class, 'index'])
            ->middleware('role:admin,super-admin');

        Route::post('/', [UserController::class, 'store'])
            ->middleware('role:super-admin');

        Route::get('/statistics', [UserController::class, 'statistics'])
            ->middleware('role:admin,super-admin');

        Route::get('/{user}', [UserController::class, 'show'])
            ->middleware('role:admin,super-admin');

        Route::put('/{user}', [UserController::class, 'update'])
            ->middleware('role:admin,super-admin');

        Route::delete('/{user}', [UserController::class, 'destroy'])
            ->middleware('role:super-admin');

        Route::post('/{user}/roles', [UserController::class, 'assignRoles'])
            ->middleware('role:super-admin');

        Route::post('/{user}/activate', [UserController::class, 'activate'])
            ->middleware('role:admin,super-admin');

        Route::post('/{user}/deactivate', [UserController::class, 'deactivate'])
            ->middleware('role:admin,super-admin');
    });
