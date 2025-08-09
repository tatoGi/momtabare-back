<?php

use App\Http\Controllers\SitemapController;
use App\Http\Controllers\Website\FrontendController;
use App\Http\Controllers\Website\ProfileController;
use App\Http\Controllers\Website\SearchController;
use App\Http\Controllers\Website\wishlistController;
use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\Admin\DashboardController;

/*
|--------------------------------------------------------------------------
| Web Routes
|--------------------------------------------------------------------------
|
| Here is where you can register web routes for your application. These
| routes are loaded by the RouteServiceProvider within a group which
| contains the "web" middleware group. Now create something great!
|
*/

require __DIR__.'/auth.php';

// Email API routes (outside locale group for direct access)

    // Admin routes with auth middleware
    Route::middleware(['auth'])->group(function () {
        Route::prefix('admin')->group(function () {
            require __DIR__.'/admin/admin.php';
            require __DIR__.'/admin/products.php';
            require __DIR__.'/admin/page.php';
            require __DIR__.'/admin/settings.php';
            require __DIR__.'/admin/languages.php'; // Include language management routes
        });
    });

    // Public routes
    Route::get('/', [DashboardController::class, 'index'])->middleware('auth');
    Route::get('/search', [SearchController::class, 'search'])->name('search');
    Route::post('/contact-submit', [FrontendController::class, 'submitContactForm'])->name('contact.submit');
    Route::get('/sitemap', [SitemapController::class, 'generate']);
    Route::get('/pro/{url}', [FrontendController::class, 'show'])->name('single_product');
    Route::post('/subscribe', [FrontendController::class, 'subscribe'])->name('subscribe');
    Route::get('/home', [FrontendController::class, 'homePage']);
    Route::get('/pages', [FrontendController::class, 'pages']);
    Route::get('/blog-posts', [FrontendController::class, 'latestBlogPosts'])->name('api.blog.latest');
    Route::get('/products', [FrontendController::class, 'products'])->name('api.products.list');
    Route::get('/products/{id}', [FrontendController::class, 'productShow'])->name('api.products.show');


// Set the locale for the application (without locale prefix)
Route::get('/change-locale/{lang}', function ($lang) {
    if (in_array($lang, array_keys(config('app.locales')))) {
        session(['locale' => $lang]);
        app()->setLocale($lang);
        
        // Get the redirect path and clean it from any locale prefixes
        $redirect = request('redirect', '/');
        $redirect = ltrim(preg_replace('#^[a-z]{2}(?:-[A-Z]{2})?/#', '', $redirect), '/');
        $redirect = $lang === 'en' ? $redirect : $lang . '/' . $redirect;
        
        return redirect()->to($redirect);
    }
    return back();
})->name('set.locale')->withoutMiddleware(['locale']);

require __DIR__.'/website/auth.php';


Route::get('/', [DashboardController::class, 'index'])->middleware('auth');


Route::get('/search', [SearchController::class, 'search'])->name('search');
Route::post('/contact-submit', [FrontendController::class, 'submitContactForm'])->name('contact.submit');
Route::get('/sitemap', [SitemapController::class, 'generate']);
Route::get('/pro/{url}', [FrontendController::class, 'show'])->name('single_product');
Route::post('/subscribe', [FrontendController::class, 'subscribe'])->name('subscribe');
Route::get('/home', [FrontendController::class, 'homePage']);
Route::get('/pages', [FrontendController::class, 'pages']);
Route::get('/pages-with-posts', [FrontendController::class, 'pagesWithPaginatedPosts'])->name('pages.with.posts');
Route::get('/categories', [FrontendController::class, 'categories']);
// Keep catch-all route at the end


Route::get('/{slug}', [FrontendController::class, 'index'])->where('slug', '.*');


require __DIR__.'/website/basket.php';

Route::get('/clear-optimization', function () {

    Artisan::call('optimize:clear');

    // Display a message or redirect back
    return 'Optimization cache cleared!';
});
