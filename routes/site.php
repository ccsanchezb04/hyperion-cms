<?php

use App\Http\Controllers\Site\ContactController;
use App\Http\Controllers\Site\HomeController;
use App\Http\Controllers\Site\SeoController;
use App\Http\Controllers\Site\SolutionController;
use Illuminate\Support\Facades\Route;

// ─── Español (default, sin prefix) ────────────────────────────────────────
Route::get('/', HomeController::class)->name('home');

Route::get('/soluciones',          [SolutionController::class, 'index'])->name('site.solutions.index');
Route::get('/soluciones/{slug}',   [SolutionController::class, 'show'])->name('site.solutions.show');

Route::post('/contact', [ContactController::class, 'store'])
    ->middleware('throttle:10,1')
    ->name('site.contact.store');

Route::get('/sitemap.xml', [SeoController::class, 'sitemap'])->name('site.sitemap');
Route::get('/robots.txt',  [SeoController::class, 'robots'])->name('site.robots');

// ─── Inglés (prefix /en + middleware locale:en) ───────────────────────────
Route::prefix('en')->middleware('locale:en')->name('en.')->group(function () {
    Route::get('/', HomeController::class)->name('home');

    Route::get('/solutions',          [SolutionController::class, 'index'])->name('site.solutions.index');
    Route::get('/solutions/{slug}',   [SolutionController::class, 'show'])->name('site.solutions.show');

    Route::post('/contact', [ContactController::class, 'store'])
        ->middleware('throttle:10,1')
        ->name('site.contact.store');
});
