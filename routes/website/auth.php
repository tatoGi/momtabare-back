<?php

use App\Http\Controllers\Website\AuthController;
use App\Http\Controllers\Website\ChatController;
use App\Http\Controllers\Website\ProfileController;
use App\Models\WebUser;
use Illuminate\Support\Facades\Route;

// Public auth routes (no authentication required)

Route::post('/login', [AuthController::class, 'login']);
Route::post('/send-registration-email', [AuthController::class, 'sendRegistrationEmail']);
Route::post('/verify-email', [AuthController::class, 'verifyEmail']);
Route::post('/verify-email-code', [AuthController::class, 'verifyEmailCode']);
Route::post('/resend-email-verification', [AuthController::class, 'resendEmailVerification']);
Route::post('/complete-registration', [AuthController::class, 'completeRegistration']);

// Email verification routes
Route::get('/verify-email/{token}', [AuthController::class, 'verifyEmail']);
Route::post('/verify-email', [AuthController::class, 'verifyEmailFromForm'])->name('verification.verify.post');
Route::post('/resend-verification', [AuthController::class, 'resendVerification'])->name('verification.resend');

// Protected routes requiring authentication
Route::middleware(['auth:sanctum'])->group(function () {
    Route::post('/logout', [AuthController::class, 'logout']);
    Route::get('/user_profile', [ProfileController::class, 'edit'])->name('profile.edit');
    Route::post('/profile', [ProfileController::class, 'update'])->name('profile.update');
});

// JSON profile routes for SPA usage
Route::middleware(['auth:sanctum'])->group(function () {
    Route::get('/chat/{user}', function (WebUser $user) {
        return view('chat', [
            'user' => $user,
        ]);
    })->middleware(['verified'])->name('chat');

    Route::resource(
        'messages/{user}',
        ChatController::class, ['only' => ['index', 'store']]
    )->middleware(['verified']);

    Route::get('/me', [AuthController::class, 'me'])->name('profile.me');
    Route::put('/profile', [ProfileController::class, 'update'])->name('profile.update.json');
    Route::post('/profile/retailer-request', [ProfileController::class, 'requestRetailer'])->name('profile.retailer.request');
    Route::post('/profile/avatar', [ProfileController::class, 'uploadAvatar'])->name('profile.avatar');

});
