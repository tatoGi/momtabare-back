<?php

use App\Http\Controllers\Admin\PageController;
use App\Http\Controllers\Admin\PageOptionController;
use Illuminate\Support\Facades\Route;

Route::resource('/pages', PageController::class);
Route::resource('/options', PageOptionController::class);
Route::post('/pages/arrange', [PageController::class, 'arrange']);

