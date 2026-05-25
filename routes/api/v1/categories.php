<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\Api\CategoryController;

Route::prefix('categories')->group(function () {

    Route::get('/', [CategoryController::class, 'index']);

    Route::get('/tree', [CategoryController::class, 'tree']);

    Route::middleware([
        'auth:sanctum',
        'permission:create-category,edit-category,delete-category'
    ])->group(function () {

        Route::post('/', [CategoryController::class, 'store']);

        Route::get('/{category}', [CategoryController::class, 'show']);

        Route::put('/{category}', [CategoryController::class, 'update']);

        Route::delete('/{category}', [CategoryController::class, 'destroy']);

        Route::post('/{category}/move', [CategoryController::class, 'move']);
    });
});
