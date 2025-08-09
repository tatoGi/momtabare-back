<?php

use App\Http\Controllers\Website\Auth\LoginController;
use App\Http\Controllers\Website\Auth\RegisterController;
use App\Http\Controllers\Website\ProfileController;
use Illuminate\Support\Facades\Route;

Route::get('/login', [LoginController::class, 'showLoginForm'])->name('login');
Route::post('/login', [LoginController::class, 'login']);
Route::post('/logout', [LoginController::class, 'logout'])->name('web.logout');

Route::get('/register', [RegisterController::class, 'showRegistrationForm'])->name('register-show');
Route::post('/register', [RegisterController::class, 'register']);


Route::get('/user_profile', [ProfileController::class, 'edit'])->name('profile.edit');
Route::post('/profile', [ProfileController::class, 'update'])->name('profile.update');
