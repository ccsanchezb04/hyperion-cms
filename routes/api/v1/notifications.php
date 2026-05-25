<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\Api\NotificationController;

Route::prefix('notifications')
    ->middleware(['auth:sanctum'])
    ->group(function () {

        Route::get('/', [NotificationController::class, 'index']);

        Route::get('/unread-count', [NotificationController::class, 'unreadCount']);

        Route::get('/{id}', [NotificationController::class, 'show']);

        Route::post('/{id}/mark-read', [NotificationController::class, 'markAsRead']);

        Route::post('/mark-all-read', [NotificationController::class, 'markAllAsRead']);

        Route::delete('/{id}', [NotificationController::class, 'destroy']);
    });
