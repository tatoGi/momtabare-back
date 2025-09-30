<?php

use App\Http\Controllers\Admin\RetailerShopController;
use Illuminate\Support\Facades\Route;

Route::group(['prefix' => 'retailer-shops', 'as' => 'retailer-shops.'], function () {
    Route::get('/', [RetailerShopController::class, 'index'])->name('index');
    Route::get('/create', [RetailerShopController::class, 'create'])->name('create');
    Route::post('/', [RetailerShopController::class, 'store'])->name('store');
    Route::get('/{retailerShop}', [RetailerShopController::class, 'show'])->name('show');
    Route::get('/{retailerShop}/edit', [RetailerShopController::class, 'edit'])->name('edit');
    Route::put('/{retailerShop}', [RetailerShopController::class, 'update'])->name('update');
    Route::delete('/{retailerShop}', [RetailerShopController::class, 'destroy'])->name('destroy');

    // Additional routes can be added here as needed
});
