<?php

use App\Http\Controllers\Website\FrontendController;
use App\Http\Controllers\Website\RetailerProductController;
use Illuminate\Support\Facades\Route;

/*
|--------------------------------------------------------------------------
| Retailer Routes
|--------------------------------------------------------------------------
|
| Retailer-specific routes for authenticated retailers
|
*/

// Public retailer routes
Route::get('/retailers', [FrontendController::class, 'getRetailerOrUser'])->name('retailer.show');

// Protected retailer routes (require auth and retailer middleware)
Route::prefix('retailer')->group(function () {
    Route::get('/user/products', [RetailerProductController::class, 'index'])->name('retailer.products.index');
    Route::post('/products', [RetailerProductController::class, 'store'])->name('retailer.products.store');
    Route::get('/products/{id}', [RetailerProductController::class, 'show'])->name('retailer.products.show');
    Route::put('/products/{id}', [RetailerProductController::class, 'update'])->name('retailer.products.update');
    Route::delete('/products/{id}', [RetailerProductController::class, 'destroy'])->name('retailer.products.destroy');
    Route::get('/users/products/count', [RetailerProductController::class, 'countProducts']);

    // Retailer shop management
    Route::post('/retailer-shop/store', [FrontendController::class, 'storeRetailerShop'])->name('retailer-shop.store');
    Route::get('/retailer-shop/count', [FrontendController::class, 'retailerShopCount'])->name('retailer-shop.count');
    Route::get('/retailer-shops', [FrontendController::class, 'retailerShops'])->name('retailer-shops');
    Route::get('/retailer-shop', [FrontendController::class, 'retailerShopEdit'])->name('retailer-shop.edit');
});
