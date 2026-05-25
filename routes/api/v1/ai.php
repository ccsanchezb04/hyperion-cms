<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\Api\AIController;

Route::prefix('ai')
    ->middleware([
        'auth:sanctum',
        'role:editor,admin,super-admin'
    ])
    ->group(function () {

        Route::post('/generate-content', [AIController::class, 'generateContent']);

        Route::post('/generate-seo', [AIController::class, 'generateSeo']);

        Route::post('/translate', [AIController::class, 'translate']);

        Route::get('/status', [AIController::class, 'status']);
    });
