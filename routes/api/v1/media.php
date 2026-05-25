<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\Api\MediaController;

Route::prefix('media')
    ->middleware(['auth:sanctum', 'permission:view-media,upload-media,edit-media,delete-media'])
    ->group(function () {

        Route::get('/', [MediaController::class, 'index']);

        Route::post('/upload', [MediaController::class, 'upload']);

        Route::post('/batch-upload', [MediaController::class, 'batchUpload']);

        Route::get('/{media}', [MediaController::class, 'show']);

        Route::put('/{media}', [MediaController::class, 'update']);

        Route::delete('/{media}', [MediaController::class, 'destroy']);
    });
