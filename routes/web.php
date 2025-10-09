<?php

use App\Http\Controllers\Admin\Auth\AdminAuthController;
use App\Http\Controllers\Admin\DashboardController;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Route;



// Public routes (no authentication required)
Route::middleware(['web'])->group(function () {
    // Admin login routes (only for guests)
    Route::middleware('guest:web')->group(function () {
        // Show login form
        Route::get('/admin/login', [AdminAuthController::class, 'showLoginForm'])
            ->name('admin.login');

        // Handle login form submission
        Route::post('/admin/login', [AdminAuthController::class, 'login'])
            ->name('admin.login.submit');
    });
});

// Protected admin routes (require authentication)
Route::middleware(['web', 'admin.auth'])->prefix('/admin')->group(function () {
    // Admin dashboard
    Route::get('/', [DashboardController::class, 'index'])
        ->name('admin.dashboard');

    // Logout
    Route::post('logout', [AdminAuthController::class, 'destroy'])
        ->name('admin.logout');

    // Include other admin routes
    require __DIR__ . '/admin/admin.php';
    require __DIR__ . '/admin/products.php';
    require __DIR__ . '/admin/page.php';
    require __DIR__ . '/admin/settings.php';
    require __DIR__ . '/admin/languages.php';
});