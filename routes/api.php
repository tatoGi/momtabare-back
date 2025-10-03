<?php

use App\Http\Controllers\Website\FrontendController;
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

require __DIR__.'/website/auth.php';

Route::get('/sanctum/csrf-cookie', function () {
    return response()->json(['status' => 'success']);
});
// Include website routes
require __DIR__.'/website/basket.php';
require __DIR__.'/website/retailer.php';
require __DIR__.'/website/general.php';
require __DIR__.'/website/products.php';
require __DIR__.'/website/comments.php';
require __DIR__.'/website/bog.php';
// Set the locale for the application (without locale prefix)
Route::get('/change-locale/{lang}', function ($lang) {
    if (in_array($lang, array_keys(config('app.locales')))) {
        session(['locale' => $lang]);
        app()->setLocale($lang);

        // Get the redirect path and clean it from any locale prefixes
        $redirect = request('redirect', '/');
        $redirect = ltrim(preg_replace('#^[a-z]{2}(?:-[A-Z]{2})?/#', '', $redirect), '/');
        $redirect = $lang === 'en' ? $redirect : $lang.'/'.$redirect;

        return redirect()->to($redirect);
    }

    return back();
})
    ->name('set.locale')
    ->withoutMiddleware(['locale']);

// Keep catch-all route at the end
Route::get('/website/{slug}', [FrontendController::class, 'index'])->where('slug', '.*');
