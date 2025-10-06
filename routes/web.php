<?php

use App\Http\Controllers\Admin\Auth\AdminAuthController;
use App\Http\Controllers\Admin\DashboardController;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Route;

// Test session route (for debugging)
Route::get('/test-session', function () {
    // Test database connection
    try {
        DB::connection()->getPdo();
        $dbConnected = true;
        $dbTables = DB::select('SHOW TABLES');
        $sessionsTableExists = collect($dbTables)->contains(function ($table) {
            return strtolower(head((array)$table)) === 'sessions';
        });
    } catch (\Exception $e) {
        $dbConnected = false;
        $sessionsTableExists = false;
    }

    // Test session
    $sessionId = session()->getId();
    session(['test' => 'test_value']);
    
    return response()->json([
        'session' => [
            'id' => $sessionId,
            'driver' => config('session.driver'),
            'lifetime' => config('session.lifetime'),
            'expire_on_close' => config('session.expire_on_close'),
            'encrypt' => config('session.encrypt'),
            'connection' => config('session.connection'),
            'table' => config('session.table'),
            'data' => session()->all(),
        ],
        'database' => [
            'connected' => $dbConnected,
            'connection' => config('database.default'),
            'sessions_table_exists' => $sessionsTableExists,
        ],
        'auth' => [
            'check' => Auth::check(),
            'user' => Auth::user(),
        ],
        'cookies' => [
            'all' => request()->cookies->all(),
            'session' => request()->cookie(config('session.cookie')),
        ],
        'server' => [
            'session_use_cookies' => ini_get('session.use_cookies'),
            'session_cookie_httponly' => ini_get('session.cookie_httponly'),
            'session_cookie_secure' => ini_get('session.cookie_secure'),
        ],
    ]);
})->name('test.session');



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
Route::middleware(['web', 'auth'])->prefix('/admin')->group(function () {
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