<?php

use App\Http\Controllers\Admin\Auth\AdminAuthController;
use App\Http\Controllers\Admin\DashboardController;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Route;

// Test session route (for debugging)
Route::get('/test-session', function () {
    return response()->json([
        'session_id' => session()->getId(),
        'session_test' => session('test', 'not_set'),
        'auth_check' => Auth::check(),
        'user' => Auth::user(),
        'session_data' => session()->all(),
        'session_driver' => config('session.driver'),
        'session_lifetime' => config('session.lifetime'),
        'session_path' => config('session.files'),
        'cookies' => request()->cookies->all(),
    ]);
})->name('test.session');

// Test route to check if routes are working
Route::get('/admin/test-routes', function () {
    $routes = collect(Route::getRoutes())->map(function ($route) {
        return [
            'uri' => $route->uri(),
            'name' => $route->getName(),
            'methods' => $route->methods(),
        ];
    });

    return response()->json([
        'total_routes' => $routes->count(),
        'admin_routes' => $routes->filter(function ($route) {
            return str_contains($route['uri'], 'admin') || str_contains($route['name'] ?? '', 'admin');
        })->values(),
        'all_routes' => $routes->take(20)->values()
    ]);
})->name('test.routes');

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
Route::middleware(['web', 'auth'])->prefix('/admin')->name('admin.')->group(function () {
    // Admin dashboard
    Route::get('/', [DashboardController::class, 'index'])
        ->name('dashboard');

    // Logout
    Route::post('logout', [AdminAuthController::class, 'destroy'])
        ->name('logout');

    // Include other admin routes
    require __DIR__ . '/admin/admin.php';
    require __DIR__ . '/admin/products.php';
    require __DIR__ . '/admin/page.php';
    require __DIR__ . '/admin/settings.php';
    require __DIR__ . '/admin/languages.php';
});