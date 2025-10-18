<?php

use App\Http\Controllers\Website\FrontendController;
use Illuminate\Support\Facades\Route;

/*
|--------------------------------------------------------------------------
| Product Routes
|--------------------------------------------------------------------------
|
| Product-related API routes for the frontend application
|
*/

// Public product routes
Route::get('/products', [FrontendController::class, 'products'])->name('api.products.list');
Route::get('/products/{id}', [FrontendController::class, 'productShow'])->name('api.products.show');
Route::get('/user/products', [FrontendController::class, 'userProducts'])->name('api.user.products');

// Bulk update product rental status (requires authentication)
Route::post('/products/bulk-rental-status', [\App\Http\Controllers\Website\BogPaymentController::class, 'bulkUpdateRentalStatus'])
    ->middleware('auth:sanctum')
    ->name('api.products.bulk-rental-status');

// Product management routes (admin only)
Route::middleware(['auth'])->group(function () {
    Route::post('/products/{product}/toggle-block', [FrontendController::class, 'toggleBlockProduct'])->name('api.products.toggle-block');
    Route::post('/products/{product}/toggle-rent', [FrontendController::class, 'toggleRentProduct'])->name('api.products.toggle-rent');
});
