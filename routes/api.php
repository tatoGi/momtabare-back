<?php

use App\Http\Controllers\Api\RateProductController;

use App\Http\Controllers\website\FrontendController;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;

/*
|--------------------------------------------------------------------------
| API Routes
|--------------------------------------------------------------------------
|
| Here is where you can register API routes for your application. These
| routes are loaded by the RouteServiceProvider within a group which
| is assigned the "api" middleware group. Enjoy building your API!
|
*/

// CORS preflight for all routes for front
Route::options('/{any}', function () {
    return response('', 200)
        ->header('Access-Control-Allow-Methods', 'GET, POST, PUT, DELETE, OPTIONS')
        ->header('Access-Control-Allow-Headers', 'X-Requested-With, Content-Type, X-Token-Auth, Authorization, X-XSRF-TOKEN, X-Socket-Id')
        ->header('Access-Control-Allow-Credentials', 'true');
})->where('any', '.*');

// CSRF Cookie Route
Route::get('/sanctum/csrf-cookie', function (Request $request) {
    return response()->json(['status' => 'success'])
        ->withCookie(cookie(
            'XSRF-TOKEN',
            csrf_token(),
            config('session.lifetime'),
            '/',
            parse_url(config('app.url'), PHP_URL_HOST),
            true,  // Secure
            true,  // HttpOnly
            false, // Raw
            'Lax'  // SameSite
        ));
})->middleware('web');

// Include other API routes
require __DIR__.'/website/auth.php';
require __DIR__.'/website/basket.php';
require __DIR__.'/website/retailer.php';
require __DIR__.'/website/general.php';
require __DIR__.'/website/products.php';
require __DIR__.'/website/comments.php';
require __DIR__.'/website/addresses.php';
require __DIR__.'/website/bog.php';
// Rate product API
Route::post('/rate-product', [RateProductController::class, 'set']);
// Get product ratings API
Route::get('/product-ratings/{product_id}', [RateProductController::class, 'get']);

// Set the locale for the application
Route::get('/change-locale/{lang}', function ($lang) {
    if (in_array($lang, array_keys(config('app.locales')))) {
        session(['locale' => $lang]);
        app()->setLocale($lang);

        $redirect = request('redirect', '/');
        $redirect = ltrim(preg_replace('#^[a-z]{2}(?:-[A-Z]{2})?/#', '', $redirect), '/');
        $redirect = $lang === 'en' ? $redirect : $lang.'/'.$redirect;

        return redirect($redirect);
    }

    return back();
})->name('change.locale');

// About, Service, Confidential, and Privacy API routes
Route::get('/about', [FrontendController::class, 'about']);
Route::get('/service', [FrontendController::class, 'service']);
Route::get('/confidential', [FrontendController::class, 'confidential']);
Route::get('/privacy', [FrontendController::class, 'privacy']);
