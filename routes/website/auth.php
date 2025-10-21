<?php

use App\Http\Controllers\Auth\SocialAuthController;
use App\Http\Controllers\Website\AuthController;
use App\Http\Controllers\Website\ChatController;
use App\Http\Controllers\Website\ProfileController;
use App\Models\WebUser;
use Illuminate\Support\Facades\Route;

// Public auth routes (no authentication required)
Route::post('/login', [AuthController::class, 'login'])->middleware(['web']);
Route::post('/send-registration-email', [AuthController::class, 'sendRegistrationEmail']);
Route::post('/verify-email', [AuthController::class, 'verifyEmailFromForm'])->name('verification.verify.post');
Route::post('/verify-email-code', [AuthController::class, 'verifyEmailCode']);
Route::post('/resend-email-verification', [AuthController::class, 'resendVerification'])->name('verification.resend');
Route::post('/complete-registration', [AuthController::class, 'completeRegistration']);

// Social authentication routes
Route::get('/auth/facebook', [SocialAuthController::class, 'redirectToFacebook']);
Route::get('/auth/facebook/callback', [SocialAuthController::class, 'handleFacebookCallback']);

// Email verification route
Route::get('/verify-email/{token}', [AuthController::class, 'verifyEmail']);

// Protected routes requiring authentication
Route::middleware(['auth:web,sanctum'])->group(function () {
    // Get current user
    Route::get('/me', [AuthController::class, 'me'])->name('profile.me');

    // Profile routes
    Route::post('/profile/retailer-request', [ProfileController::class, 'requestRetailer'])
        ->name('profile.retailer.request');

    Route::post('/profile/avatar', [ProfileController::class, 'uploadAvatar'])
        ->name('profile.avatar');

    Route::post('/logout', [AuthController::class, 'logout']);

    // Web-only routes (require session)
    Route::middleware(['web'])->group(function () {
        Route::get('/user_profile', [ProfileController::class, 'edit'])
            ->name('profile.edit');

        Route::match(['post', 'put'], '/profile', [ProfileController::class, 'update'])
            ->name('profile.update');

        // Chat routes
        Route::get('/chat/{user}', function (WebUser $user) {
            return view('chat', ['user' => $user]);
        })->middleware(['verified'])->name('chat');

        Route::resource(
            'messages/{user}',
            ChatController::class,
            ['only' => ['index', 'store']]
        )->middleware(['verified']);
    });

});
