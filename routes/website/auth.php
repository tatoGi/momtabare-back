<?php

use App\Http\Controllers\Website\AuthController;
use App\Http\Controllers\Website\ProfileController;
use Illuminate\Support\Facades\Route;

// Login routes
Route::post('/send-registration-email', [AuthController::class, 'sendRegistrationEmail']);
Route::post('/verify-email', [AuthController::class, 'verifyEmail']);
Route::post('/verify-email-code', [AuthController::class, 'verifyEmailCode']);
Route::post('/resend-email-verification', [AuthController::class, 'resendEmailVerification']);
Route::post('/complete-registration', [AuthController::class, 'completeRegistration']);
Route::post('/login', [AuthController::class, 'login']);
Route::post('/logout', [AuthController::class, 'logout']);


// Email verification routes
Route::get('/verify-email/{token}', [AuthController::class, 'verifyEmail'])->name('verification.verify');
Route::post('/verify-email', [AuthController::class, 'verifyEmailFromForm'])->name('verification.verify.post');
Route::post('/resend-verification', [AuthController::class, 'resendVerification'])->name('verification.resend');

// Protect profile routes with auth:webuser to ensure an authenticated WebUser instance
Route::middleware('auth:webuser')->group(function () {
    Route::get('/user_profile', [ProfileController::class, 'edit'])->name('profile.edit');
    Route::post('/profile', [ProfileController::class, 'update'])->name('profile.update');
});

// JSON profile routes for SPA usage
Route::middleware(['auth:webuser,sanctum'])->group(function () {
    Route::get('/me', [AuthController::class, 'me'])->name('profile.me');
    Route::put('/profile', [ProfileController::class, 'update'])->name('profile.update.json');
    Route::post('/profile/retailer-request', [ProfileController::class, 'requestRetailer'])->name('profile.retailer.request');
    Route::post('/profile/avatar', [ProfileController::class, 'uploadAvatar'])->name('profile.avatar');
});

