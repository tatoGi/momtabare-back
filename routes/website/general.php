<?php

use App\Http\Controllers\Admin\PromoCodeController;
use App\Http\Controllers\SitemapController;
use App\Http\Controllers\Website\FrontendController;
use App\Http\Controllers\Website\SearchController;
use Illuminate\Support\Facades\Route;

/*
|--------------------------------------------------------------------------
| General Website Routes
|--------------------------------------------------------------------------
|
| General API routes for the frontend application including pages,
| categories, blog posts, contact forms, and other utilities
|
*/

// Public API routes
Route::get('/home', [FrontendController::class, 'completeHomePage'])->name('api.home.complete');
Route::get('/pages', [FrontendController::class, 'pages']);
Route::get('/categories', [FrontendController::class, 'categories']);
Route::get('/categories/{id}', [FrontendController::class, 'showCategory'])->name('api.categories.show');
Route::get('/blog-posts', [FrontendController::class, 'latestBlogPosts'])->name('api.blog.latest');
Route::get('/languages', [FrontendController::class, 'languages'])->name('api.languages');

// Search functionality
Route::get('/search', [SearchController::class, 'search'])->name('search');

// Contact and subscription
Route::post('/contact-submit', [FrontendController::class, 'submitContactForm'])->name('contact.submit');
Route::post('/subscribe', [FrontendController::class, 'subscribe'])->name('subscribe');

// Locale and language management
Route::post('/locale/sync', [FrontendController::class, 'localeSync'])->name('locale.sync');

// Email functionality
Route::post('/auth/send-welcome-email', [FrontendController::class, 'sendWelcomeEmail'])
    ->middleware('throttle:5,1')
    ->name('auth.email.welcome');

// Promo code validation (public, for customers)
Route::post('/promo-codes/validate', [PromoCodeController::class, 'validateCode'])->name('promo-codes.validate');

// Sitemap
Route::get('/sitemap', [SitemapController::class, 'generate']);
