<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\Admin\DashboardController;
use App\Http\Controllers\Admin\Auth\AdminAuthController;

// Redirect root to locale
Route::get('/', function () {
    return redirect(app()->getLocale());
})->middleware('auth');

// Admin authentication routes (only accessible to guests)
Route::middleware('guest')->prefix('admin')->group(function () {
    Route::get('login/dashboard', [AdminAuthController::class, 'index'])
        ->name('admin.login');
        
    Route::post('login', [AdminAuthController::class, 'store'])
        ->name('admin.login.submit');
});

// Protected admin routes (require authentication)
Route::middleware('auth')->prefix('admin')->group(function () {
    // Admin dashboard
    Route::get('/', [DashboardController::class, 'index'])
        ->name('admin.dashboard');
        
    // Logout Route
    Route::post('logout', [AdminAuthController::class, 'destroy'])
        ->name('admin.logout');
    
    // Other admin routes
    require __DIR__.'/admin/admin.php';
    require __DIR__.'/admin/products.php';
    require __DIR__.'/admin/page.php';
    require __DIR__.'/admin/settings.php';
    require __DIR__.'/admin/languages.php';
});