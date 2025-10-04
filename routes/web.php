<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\Admin\DashboardController;
use App\Http\Controllers\Admin\Auth\AdminAuthController;
use Illuminate\Support\Facades\Log;
// Test session route (for debugging)
Route::post('/login-debug', function (\Illuminate\Http\Request $request) {
    $credentials = $request->only('email','password');
    Log::info('Login attempt', [
        'credentials' => $credentials,
        'session_id' => session()->getId(),
        'cookies' => $request->cookies->all()
    ]);
    return 'Check laravel.log';
});

// Public routes (no authentication required)
Route::middleware(['web'])->group(function () {
    // Admin login routes (only for guests)
    Route::middleware('guest:web')->group(function () {
        Route::get('admin/login/dashboard', [AdminAuthController::class, 'index'])
            ->name('admin.login.dashboard');
            
        Route::post('admin/login', [AdminAuthController::class, 'store'])
            ->name('admin.login.submit');
    });
});

// Protected admin routes (require authentication)
Route::middleware(['web', 'auth'])->prefix('admin')->group(function () {
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
