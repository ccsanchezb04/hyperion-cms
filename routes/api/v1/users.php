<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\Api\UserController;

Route::prefix('users')
    ->middleware(['auth:sanctum'])
    ->group(function () {

        Route::get('/', [UserController::class, 'index'])
            ->middleware('permission:view-users');

        Route::post('/', [UserController::class, 'store'])
            ->middleware('permission:create-user');

        Route::get('/statistics', [UserController::class, 'statistics'])
            ->middleware('permission:view-users');

        Route::get('/{user}', [UserController::class, 'show'])
            ->middleware('permission:view-users');

        Route::put('/{user}', [UserController::class, 'update'])
            ->middleware('permission:edit-user');

        Route::delete('/{user}', [UserController::class, 'destroy'])
            ->middleware('permission:delete-user');

        Route::post('/{user}/roles', [UserController::class, 'assignRoles'])
            ->middleware('permission:assign-roles');

        Route::post('/{user}/activate', [UserController::class, 'activate'])
            ->middleware('permission:edit-user');

        Route::post('/{user}/deactivate', [UserController::class, 'deactivate'])
            ->middleware('permission:edit-user');
    });
