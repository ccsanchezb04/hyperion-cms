<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\Api\SettingController;

Route::prefix('settings')
    ->middleware([
        'auth:sanctum',
        'role:super-admin'
    ])
    ->group(function () {

        Route::get('/', [SettingController::class, 'index']);

        Route::put('/', [SettingController::class, 'update']);

        Route::get('/group/{group}', [SettingController::class, 'showGroup']);

        Route::get('/{key}', [SettingController::class, 'show']);

        Route::put('/{key}', [SettingController::class, 'updateSetting']);

        Route::delete('/{key}', [SettingController::class, 'destroy']);

        Route::post('/group/{group}/reset', [SettingController::class, 'resetGroup']);
    });
