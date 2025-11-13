<?php

use App\Http\Controllers\Admin\AboutController;
use App\Http\Controllers\Admin\BannerController;
use App\Http\Controllers\Admin\CategoryController;
use App\Http\Controllers\Admin\ConfidentialController;
use App\Http\Controllers\Admin\ContactController;
use App\Http\Controllers\Admin\HelpController;
use App\Http\Controllers\Admin\LanguageController;
use App\Http\Controllers\Admin\PaymentController;
use App\Http\Controllers\Admin\PostController;
use App\Http\Controllers\Admin\PrivacyController;
use App\Http\Controllers\Admin\PromoCodeController;
use App\Http\Controllers\Admin\ServiceController;
use App\Http\Controllers\Admin\WebUserController;
use Illuminate\Support\Facades\Route;

Route::resource('/categories', CategoryController::class);
Route::get('/contact', [ContactController::class, 'index'])->name('admin.contact');
Route::get('/subscribers', [ContactController::class, 'subscribers']);
Route::get('/languages', [LanguageController::class, 'index'])->name('languages.index');
Route::post('/languages/update', [LanguageController::class, 'update'])->name('languages.update');
Route::resource('/banners', BannerController::class)->parameters([
    'banners' => 'banner',
    'page_id' => 'page',
]);
Route::get('/banners/create/{page_id?}', [BannerController::class, 'create'])->name('banners.create.with.page');
Route::delete('/category/icon/{id}', [CategoryController::class, 'deleteIcon'])->name('category.icon.delete');
Route::delete('/category/image/{id}', [CategoryController::class, 'deleteImage'])->name('category.image.delete');
Route::get('/webusers', [WebUserController::class, 'index'])->name('admin.webusers.index');
Route::get('/webusers/{id}', [WebUserController::class, 'show'])->name('admin.webusers.show');
Route::post('/webusers/{id}/approve-retailer', [WebUserController::class, 'approveRetailer'])->name('admin.webusers.approve-retailer');
Route::post('/webusers/{id}/reject-retailer', [WebUserController::class, 'rejectRetailer'])->name('admin.webusers.reject-retailer');
Route::post('/webusers/{id}/toggle-status', [WebUserController::class, 'toggleStatus'])->name('admin.webusers.toggle-status');
Route::delete('/webusers/{id}', [WebUserController::class, 'destroy'])->name('admin.webusers.destroy');

// Payment management routes
Route::get('/payments', [PaymentController::class, 'index'])->name('admin.payments.index');
Route::get('/payments/export/csv', [PaymentController::class, 'export'])->name('admin.payments.export');
Route::get('/payments/{id}', [PaymentController::class, 'show'])->name('admin.payments.show')->where('id', '[0-9]+');

// Comment management routes
Route::get('/comments', [WebUserController::class, 'comments'])->name('admin.comments.index');
Route::post('/comments/{id}/approve', [WebUserController::class, 'approveComment'])->name('admin.comments.approve');
Route::delete('/comments/{id}/reject', [WebUserController::class, 'rejectComment'])->name('admin.comments.reject');

// Retailer product management routes
Route::get('/retailer-products', [WebUserController::class, 'retailerProducts'])->name('admin.retailer-products.index');
Route::post('/retailer-products/{id}/approve', [WebUserController::class, 'approveProduct'])->name('admin.retailer-products.approve');
Route::post('/retailer-products/{id}/reject', [WebUserController::class, 'rejectProduct'])->name('admin.retailer-products.reject');
Route::delete('/retailer-products/{id}/delete', [WebUserController::class, 'deleteProduct'])->name('admin.retailer-products.delete');

// Retailer shops management
Route::resource('retailer-shops', 'App\Http\Controllers\Admin\RetailerShopController')->names([
    'index' => 'admin.retailer-shops.index',
    'create' => 'admin.retailer-shops.create',
    'store' => 'admin.retailer-shops.store',
    'show' => 'admin.retailer-shops.show',
    'edit' => 'admin.retailer-shops.edit',
    'update' => 'admin.retailer-shops.update',
    'destroy' => 'admin.retailer-shops.destroy',
]);
Route::delete('/banners/delete/image/{image_id}', [BannerController::class, 'deleteImage'])->name('banners.images.delete');

// Image upload route for TinyMCE editor
Route::post('/upload-image', [PostController::class, 'uploadImage'])->name('admin.upload.image');

// Post management routes (nested under pages)
Route::prefix('pages/{page}')->name('admin.pages.')->group(function () {
    Route::get('/posts', [PostController::class, 'index'])->name('posts.index');
    Route::get('/posts/create', [PostController::class, 'create'])->name('posts.create');
    Route::post('/posts', [PostController::class, 'store'])->name('posts.store');
    Route::get('/posts/{post}/edit', [PostController::class, 'edit'])->name('posts.edit');
    Route::put('/posts/{post}', [PostController::class, 'update'])->name('posts.update');
    Route::delete('/posts/{post}', [PostController::class, 'destroy'])->name('posts.destroy');
});
Route::get('/about-us', [AboutController::class, 'edit'])->name('admin.about_us');
Route::post('/about/update', [AboutController::class, 'update'])->name('about.update');
Route::get('/our-services', [ServiceController::class, 'edit'])->name('admin.our_services');
Route::post('/our-services/update', [ServiceController::class, 'update'])->name('our_services.update');
Route::get('/confidential', [ConfidentialController::class, 'edit'])->name('admin.confidential');
Route::post('/confidential/update', [ConfidentialController::class, 'update'])->name('confidential.update');
Route::get('/privacy', [PrivacyController::class, 'edit'])->name('admin.privacy');
Route::post('/privacy/update', [PrivacyController::class, 'update'])->name('privacy.update');
Route::get('/help', [HelpController::class, 'edit'])->name('admin.help');
Route::post('/help/update', [HelpController::class, 'update'])->name('help.update');

// Promo Code management routes
Route::resource('/promo-codes', PromoCodeController::class)->parameters([
    'promo-codes' => 'promoCode',
]);
Route::get('/promo-codes/available/products', [PromoCodeController::class, 'getAvailableProducts'])->name('promo-codes.available-products');
Route::get('/promo-codes/available/categories', [PromoCodeController::class, 'getAvailableCategories'])->name('promo-codes.available-categories');
