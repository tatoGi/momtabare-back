<?php

use App\Http\Controllers\Website\WebUserAddressController;
use Illuminate\Support\Facades\Route;

/*
|--------------------------------------------------------------------------
| Web User Address API Routes
|--------------------------------------------------------------------------
|
| API routes for managing user addresses with map coordinates
|
*/

Route::prefix('users/{userId}/addresses')->group(function () {
    // Get all addresses for a user
    Route::get('/', [WebUserAddressController::class, 'index']);

    // Create a new address
    Route::post('/', [WebUserAddressController::class, 'store']);

    // Update an address
    Route::put('/{addressId}', [WebUserAddressController::class, 'update']);

    // Delete an address
    Route::delete('/{addressId}', [WebUserAddressController::class, 'destroy']);

    // Set an address as default
    Route::post('/{addressId}/set-default', [WebUserAddressController::class, 'setDefault']);
});

// Test endpoint to verify authentication
Route::get('/test-address-auth', function (Illuminate\Http\Request $request) {
    $user = $request->user('sanctum');
    return response()->json([
        'authenticated' => $user ? true : false,
        'user' => $user ? [
            'id' => $user->id,
            'email' => $user->email,
        ] : null,
        'bearer_token_present' => $request->bearerToken() ? true : false,
    ]);
});
