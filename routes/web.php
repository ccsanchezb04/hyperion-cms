<?php

use Illuminate\Support\Facades\Route;
use Inertia\Inertia;

Route::get('/', function () {
    return Inertia::render('Welcome');
})->name('home');

Route::get('dashboard', function () {
    return Inertia::render('Dashboard');
})->middleware(['auth', 'verified'])->name('dashboard');

// Content Management Routes
Route::middleware(['auth', 'verified'])->prefix('admin')->group(function () {
    Route::prefix('contents')->group(function () {
        Route::get('/', function () {
            return Inertia::render('Contents/Index');
        })->name('contents.index');
        
        Route::get('/create', function () {
            return Inertia::render('Contents/Create');
        })->name('contents.create');
        
        Route::get('/{id}/edit', function ($id) {
            return Inertia::render('Contents/Edit', ['id' => $id]);
        })->name('contents.edit');
    });

    // Media Library Routes
    Route::prefix('media')->group(function () {
        Route::get('/', function () {
            return Inertia::render('Media/Index');
        })->name('media.index');
        
        Route::get('/upload', function () {
            return Inertia::render('Media/Upload');
        })->name('media.upload');
    });

    // Categories Routes
    Route::prefix('categories')->group(function () {
        Route::get('/', function () {
            return Inertia::render('Categories/Index');
        })->name('categories.index');
        
        Route::get('/create', function () {
            return Inertia::render('Categories/Create');
        })->name('categories.create');
        
        Route::get('/{id}/edit', function ($id) {
            return Inertia::render('Categories/Edit', ['id' => $id]);
        })->name('categories.edit');
    });

    // Settings Routes
    Route::prefix('settings')->group(function () {
        Route::get('/', function () {
            return Inertia::render('Settings/Index');
        })->name('settings.index');
    });

    // Menus Routes
    Route::prefix('menus')->group(function () {
        Route::get('/', function () {
            return Inertia::render('Menus/Index');
        })->name('menus.index');
    });
});

require __DIR__.'/settings.php';
require __DIR__.'/auth.php';
