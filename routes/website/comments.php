<?php

use App\Http\Controllers\Api\ProductCommentController;
use Illuminate\Support\Facades\Route;

/*
|--------------------------------------------------------------------------
| Product Comments Routes
|--------------------------------------------------------------------------
|
| Product comment-related API routes for the frontend application
|
*/

// Public comment routes (no authentication required)
Route::get('/products/{product}/comments', [ProductCommentController::class, 'index'])->name('api.products.comments.index');
Route::get('/products/{product}/comments/{comment}', [ProductCommentController::class, 'show'])->name('api.products.comments.show');

// Authenticated routes for comments
Route::middleware(['auth:sanctum'])->group(function () {
    Route::post('/products/{product}/comments', [ProductCommentController::class, 'store'])->name('api.products.comments.store');
    Route::put('/products/{product}/comments/{comment}', [ProductCommentController::class, 'update'])->name('api.products.comments.update');
    Route::delete('/products/{product}/comments/{comment}', [ProductCommentController::class, 'destroy'])->name('api.products.comments.destroy');
    Route::get('/products/{product}/my-comment', [ProductCommentController::class, 'userComments'])->name('api.products.comments.user');
});
