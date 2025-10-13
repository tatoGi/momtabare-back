<?php

// In routes/website/basket.php
use App\Http\Controllers\Website\wishlistController;
use Illuminate\Support\Facades\Route;

// Use auth:sanctum for API token authentication
Route::middleware(['auth:web,sanctum'])->group(function () {
    Route::get('/show-cart', [wishlistController::class, 'showCart'])->name('fetch-cart');
    Route::post('/add-to-cart', [wishlistController::class, 'addToCart'])->name('add.to.cart');
    Route::post('/remove-from-cart', [wishlistController::class, 'removeFromCart'])->name('remove-from-cart');
    Route::post('/update-cart-item', [wishlistController::class, 'updateCartItem'])->name('update-cart-item');
});

Route::post('/add-to-wishlist', [wishlistController::class, 'addToWishlist'])->name('add.to.wishlist');
Route::post('/wishlist', [wishlistController::class, 'wishlist'])->name('wishlist');
Route::post('/remove-from-wishlist', [wishlistController::class, 'removeFromWishlist'])->name('removeFromWishlist');
