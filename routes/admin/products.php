<?php

use App\Http\Controllers\Admin\ProductController;
use App\Http\Controllers\Admin\PageManagementController;
use Illuminate\Support\Facades\Route;

// Regular product routes
Route::resource('/products', ProductController::class)->parameters([
    'products' => 'product',
    'page_id' => 'page'
]);

// Add specific route for creating products with page_id
Route::get('/products/create/{page_id?}', [ProductController::class, 'create'])->name('products.create');

Route::get('/page-management/manage/{page}', [PageManagementController::class, 'manage'])->name('admin.pages.management.manage');

Route::get('/page-management/index/{page}', [PageManagementController::class, 'index'])->name('admin.pages.management.index');