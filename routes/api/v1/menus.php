<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\Api\MenuController;

Route::prefix('menus')->group(function () {

    Route::get('/', [MenuController::class, 'index']);

    Route::get('/{slug}', [MenuController::class, 'show']);

    Route::middleware([
        'auth:sanctum',
        'role:admin,super-admin'
    ])->group(function () {

        Route::post('/', [MenuController::class, 'store']);

        Route::put('/{menu}', [MenuController::class, 'update']);

        Route::delete('/{menu}', [MenuController::class, 'destroy']);

        Route::post('/{menu}/items', [MenuController::class, 'addItem']);

        Route::put('/items/{menuItem}', [MenuController::class, 'updateItem']);

        Route::delete('/items/{menuItem}', [MenuController::class, 'deleteItem']);

        Route::post('/{menu}/items/reorder', [MenuController::class, 'reorderItems']);
    });
});
