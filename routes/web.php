<?php

use App\Http\Controllers\Admin\CategoryController;
use App\Http\Controllers\Admin\ContactMessageController;
use App\Http\Controllers\Admin\ContentController;
use App\Http\Controllers\Admin\DashboardController;
use App\Http\Controllers\Admin\MediaController;
use App\Http\Controllers\Admin\MenuController;
use App\Http\Controllers\Admin\PermissionController;
use App\Http\Controllers\Admin\RoleController;
use App\Http\Controllers\Admin\SeoController;
use App\Http\Controllers\Admin\SettingController;
use App\Http\Controllers\Admin\ThemeController;
use App\Http\Controllers\Admin\UserController;
use Illuminate\Support\Facades\Route;

require __DIR__.'/site.php';

Route::prefix('admin')->group(function () {
    Route::middleware(['auth', 'verified'])->group(function () {
        Route::get('dashboard', [DashboardController::class, 'index'])->name('dashboard');

        Route::prefix('contents')->name('contents.')->group(function () {
            Route::get('/',            [ContentController::class, 'index'])->name('index');
            Route::get('/create',      [ContentController::class, 'create'])->name('create');
            Route::post('/',           [ContentController::class, 'store'])->name('store');
            Route::get('/{content}/edit', [ContentController::class, 'edit'])->name('edit');
            Route::put('/{content}',   [ContentController::class, 'update'])->name('update');
            Route::delete('/{content}', [ContentController::class, 'destroy'])->name('destroy');
            Route::post('/{content}/publish', [ContentController::class, 'publish'])->name('publish');
            Route::post('/{content}/archive', [ContentController::class, 'archive'])->name('archive');
        });

        Route::prefix('categories')->name('categories.')->group(function () {
            Route::get('/',                  [CategoryController::class, 'index'])->name('index');
            Route::get('/create',            [CategoryController::class, 'create'])->name('create');
            Route::post('/',                 [CategoryController::class, 'store'])->name('store');
            Route::get('/{category}/edit',   [CategoryController::class, 'edit'])->name('edit');
            Route::put('/{category}',        [CategoryController::class, 'update'])->name('update');
            Route::delete('/{category}',     [CategoryController::class, 'destroy'])->name('destroy');
        });

        Route::prefix('media')->name('media.')->group(function () {
            Route::get('/',            [MediaController::class, 'index'])->name('index');
            Route::post('/upload',     [MediaController::class, 'upload'])->name('upload');
            Route::delete('/{media}',  [MediaController::class, 'destroy'])->name('destroy');
        });

        Route::prefix('menus')->name('menus.')->group(function () {
            Route::get('/',                            [MenuController::class, 'index'])->name('index');
            Route::post('/',                           [MenuController::class, 'store'])->name('store');
            Route::delete('/{menu}',                   [MenuController::class, 'destroy'])->name('destroy');
            Route::post('/{menu}/items',               [MenuController::class, 'storeItem'])->name('items.store');
            Route::put('/{menu}/items/{menuItem}',     [MenuController::class, 'updateItem'])->name('items.update');
            Route::post('/{menu}/reorder',             [MenuController::class, 'reorder'])->name('reorder');
            Route::delete('/items/{menuItem}',         [MenuController::class, 'destroyItem'])->name('items.destroy');
        });

        Route::prefix('settings')->name('settings.')->group(function () {
            Route::get('/',  [SettingController::class, 'index'])->name('index');
            Route::put('/',  [SettingController::class, 'update'])->name('update');
        });

        Route::prefix('users')->name('admin.users.')
            ->middleware('permission:view-users')
            ->group(function () {
                Route::get('/',                   [UserController::class, 'index'])->name('index');
                Route::get('/create',             [UserController::class, 'create'])->middleware('permission:create-user')->name('create');
                Route::post('/',                  [UserController::class, 'store'])->middleware('permission:create-user')->name('store');
                Route::get('/{user}/edit',        [UserController::class, 'edit'])->middleware('permission:edit-user')->name('edit');
                Route::put('/{user}',             [UserController::class, 'update'])->middleware('permission:edit-user')->name('update');
                Route::delete('/{user}',          [UserController::class, 'destroy'])->middleware('permission:delete-user')->name('destroy');
                Route::post('/{user}/activate',   [UserController::class, 'activate'])->middleware('permission:edit-user')->name('activate');
                Route::post('/{user}/deactivate', [UserController::class, 'deactivate'])->middleware('permission:edit-user')->name('deactivate');
            });

        Route::prefix('roles')->name('admin.roles.')
            ->middleware('permission:manage-roles')
            ->group(function () {
                Route::get('/',            [RoleController::class, 'index'])->name('index');
                Route::get('/create',      [RoleController::class, 'create'])->name('create');
                Route::post('/',           [RoleController::class, 'store'])->name('store');
                Route::get('/{role}/edit', [RoleController::class, 'edit'])->name('edit');
                Route::put('/{role}',      [RoleController::class, 'update'])->name('update');
                Route::delete('/{role}',   [RoleController::class, 'destroy'])->name('destroy');
            });

        Route::prefix('permissions')->name('admin.permissions.')
            ->middleware('permission:view-permissions')
            ->group(function () {
                Route::get('/', [PermissionController::class, 'index'])->name('index');
            });

        Route::prefix('seo')->name('admin.seo.')
            ->middleware('permission:manage-seo')
            ->group(function () {
                Route::get('/',                [SeoController::class, 'index'])->name('index');
                Route::put('/',                [SeoController::class, 'update'])->name('update');
                Route::post('/og-image',       [SeoController::class, 'uploadOgImage'])->name('og-image');
                Route::post('/sitemap/flush',  [SeoController::class, 'flushSitemap'])->name('sitemap.flush');
            });

        Route::prefix('themes')->name('admin.themes.')
            ->middleware('permission:manage-settings')
            ->group(function () {
                Route::get('/',        [ThemeController::class, 'index'])->name('index');
                Route::post('/activate', [ThemeController::class, 'activate'])->name('activate');
            });

        Route::prefix('contact-messages')->name('admin.contact-messages.')
            ->middleware('permission:view-contact-messages')
            ->group(function () {
                Route::get('/',              [ContactMessageController::class, 'index'])->name('index');
                Route::get('/{contactMessage}', [ContactMessageController::class, 'show'])->name('show');
                Route::delete('/{contactMessage}', [ContactMessageController::class, 'destroy'])
                    ->middleware('permission:delete-contact-messages')
                    ->name('destroy');
            });
    });
});

require __DIR__.'/settings.php';
require __DIR__.'/auth.php';
