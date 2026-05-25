<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\Api\ContentController;

Route::prefix('contents')->group(function () {

    Route::get('/', [ContentController::class, 'index']);

    Route::get('/{slug}', [ContentController::class, 'show']);

    Route::middleware([
        'auth:sanctum',
        'role:author,editor,admin,super-admin'
    ])->group(function () {

        Route::post('/', [ContentController::class, 'store']);

        Route::put('/{content}', [ContentController::class, 'update']);

        Route::delete('/{content}', [ContentController::class, 'destroy']);

        Route::post('/{content}/publish', [ContentController::class, 'publish']);

        Route::post('/{content}/archive', [ContentController::class, 'archive']);

        Route::get('/{content}/versions', [ContentController::class, 'versions']);

        Route::post('/{content}/versions/{versionNumber}/restore', [ContentController::class, 'restoreVersion']);
    });
});
