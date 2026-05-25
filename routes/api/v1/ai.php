<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\Api\AIController;

Route::prefix('ai')->group(function () {

    Route::get('/status', [AIController::class, 'status']);

    Route::middleware([
        'auth:sanctum',
        'permission:use-ai'
    ])->group(function () {

        Route::post('/generate-content', [AIController::class, 'generateContent']);

        Route::post('/generate-seo', [AIController::class, 'generateSeo']);

        Route::post('/translate', [AIController::class, 'translate']);
    });
});
