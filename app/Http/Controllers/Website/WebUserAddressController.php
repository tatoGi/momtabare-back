<?php

namespace App\Http\Controllers\Website;

use App\Http\Controllers\Controller;
use App\Models\WebUserAddress;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;

class WebUserAddressController extends Controller
{
    /**
     * Get all addresses for a user
     */
    public function index(Request $request, $userId): JsonResponse
    {
        try {
            $user = $request->user('sanctum');

            // Ensure the user can only access their own addresses
            if (!$user || $user->id != $userId) {
                return response()->json([
                    'success' => false,
                    'message' => 'Unauthorized access',
                ], 403);
            }

            $addresses = WebUserAddress::where('web_user_id', $userId)
                ->orderBy('is_default', 'desc')
                ->orderBy('created_at', 'desc')
                ->get();

            return response()->json([
                'success' => true,
                'data' => $addresses,
            ]);

        } catch (\Exception $e) {
            Log::error('Error fetching user addresses: ' . $e->getMessage(), [
                'user_id' => $userId,
                'trace' => $e->getTraceAsString(),
            ]);

            return response()->json([
                'success' => false,
                'message' => 'Failed to fetch addresses',
            ], 500);
        }
    }

    /**
     * Store a new address
     */
    public function store(Request $request, $userId): JsonResponse
    {
        try {
            $user = $request->user('sanctum');

            // Log authentication status for debugging
            Log::info('Address creation attempt', [
                'user_id_param' => $userId,
                'authenticated_user' => $user ? $user->id : null,
                'has_bearer_token' => $request->bearerToken() ? 'yes' : 'no',
                'headers' => $request->headers->all(),
            ]);

            // Check if user is authenticated
            if (!$user) {
                return response()->json([
                    'success' => false,
                    'message' => 'Not authenticated. Please login first.',
                ], 401);
            }

            // Ensure the user can only create addresses for themselves
            if ($user->id != $userId) {
                return response()->json([
                    'success' => false,
                    'message' => 'Unauthorized access',
                ], 403);
            }

            $validated = $request->validate([
                'name' => ['required', 'string', 'max:255'],
                'city' => ['required', 'string', 'max:255'],
                'address' => ['required', 'string', 'max:500'],
                'lat' => ['required', 'numeric', 'between:-90,90'],
                'lng' => ['required', 'numeric', 'between:-180,180'],
            ]);

            // If this is the first address, make it default
            $isFirstAddress = !WebUserAddress::where('web_user_id', $userId)->exists();

            $address = WebUserAddress::create([
                'web_user_id' => $userId,
                'name' => $validated['name'],
                'city' => $validated['city'],
                'address' => $validated['address'],
                'lat' => $validated['lat'],
                'lng' => $validated['lng'],
                'is_default' => $isFirstAddress,
            ]);

            return response()->json([
                'success' => true,
                'message' => 'Address created successfully',
                'data' => $address,
            ], 201);

        } catch (\Illuminate\Validation\ValidationException $e) {
            return response()->json([
                'success' => false,
                'message' => 'Validation failed',
                'errors' => $e->errors(),
            ], 422);

        } catch (\Exception $e) {
            Log::error('Error creating address: ' . $e->getMessage(), [
                'user_id' => $userId,
                'data' => $request->all(),
                'trace' => $e->getTraceAsString(),
            ]);

            return response()->json([
                'success' => false,
                'message' => 'Failed to create address',
            ], 500);
        }
    }

    /**
     * Update an existing address
     */
    public function update(Request $request, $userId, $addressId): JsonResponse
    {
        try {
            $user = $request->user('sanctum');

            // Ensure the user can only update their own addresses
            if (!$user || $user->id != $userId) {
                return response()->json([
                    'success' => false,
                    'message' => 'Unauthorized access',
                ], 403);
            }

            $address = WebUserAddress::where('web_user_id', $userId)
                ->where('id', $addressId)
                ->firstOrFail();

            $validated = $request->validate([
                'name' => ['sometimes', 'string', 'max:255'],
                'city' => ['sometimes', 'string', 'max:255'],
                'address' => ['sometimes', 'string', 'max:500'],
                'lat' => ['sometimes', 'numeric', 'between:-90,90'],
                'lng' => ['sometimes', 'numeric', 'between:-180,180'],
                'is_default' => ['sometimes', 'boolean'],
            ]);

            // If setting as default, unset other defaults
            if (isset($validated['is_default']) && $validated['is_default']) {
                WebUserAddress::where('web_user_id', $userId)
                    ->where('id', '!=', $addressId)
                    ->update(['is_default' => false]);
            }

            $address->update($validated);

            return response()->json([
                'success' => true,
                'message' => 'Address updated successfully',
                'data' => $address,
            ]);

        } catch (\Illuminate\Database\Eloquent\ModelNotFoundException $e) {
            return response()->json([
                'success' => false,
                'message' => 'Address not found',
            ], 404);

        } catch (\Illuminate\Validation\ValidationException $e) {
            return response()->json([
                'success' => false,
                'message' => 'Validation failed',
                'errors' => $e->errors(),
            ], 422);

        } catch (\Exception $e) {
            Log::error('Error updating address: ' . $e->getMessage(), [
                'user_id' => $userId,
                'address_id' => $addressId,
                'trace' => $e->getTraceAsString(),
            ]);

            return response()->json([
                'success' => false,
                'message' => 'Failed to update address',
            ], 500);
        }
    }

    /**
     * Delete an address
     */
    public function destroy(Request $request, $userId, $addressId): JsonResponse
    {
        try {
            $user = $request->user('sanctum');

            // Ensure the user can only delete their own addresses
            if (!$user || $user->id != $userId) {
                return response()->json([
                    'success' => false,
                    'message' => 'Unauthorized access',
                ], 403);
            }

            $address = WebUserAddress::where('web_user_id', $userId)
                ->where('id', $addressId)
                ->firstOrFail();

            $wasDefault = $address->is_default;
            $address->delete();

            // If the deleted address was default, set another as default
            if ($wasDefault) {
                $nextAddress = WebUserAddress::where('web_user_id', $userId)
                    ->orderBy('created_at', 'desc')
                    ->first();

                if ($nextAddress) {
                    $nextAddress->update(['is_default' => true]);
                }
            }

            return response()->json([
                'success' => true,
                'message' => 'Address deleted successfully',
            ]);

        } catch (\Illuminate\Database\Eloquent\ModelNotFoundException $e) {
            return response()->json([
                'success' => false,
                'message' => 'Address not found',
            ], 404);

        } catch (\Exception $e) {
            Log::error('Error deleting address: ' . $e->getMessage(), [
                'user_id' => $userId,
                'address_id' => $addressId,
                'trace' => $e->getTraceAsString(),
            ]);

            return response()->json([
                'success' => false,
                'message' => 'Failed to delete address',
            ], 500);
        }
    }

    /**
     * Set an address as default
     */
    public function setDefault(Request $request, $userId, $addressId): JsonResponse
    {
        try {
            $user = $request->user('sanctum');

            // Ensure the user can only modify their own addresses
            if (!$user || $user->id != $userId) {
                return response()->json([
                    'success' => false,
                    'message' => 'Unauthorized access',
                ], 403);
            }

            $address = WebUserAddress::where('web_user_id', $userId)
                ->where('id', $addressId)
                ->firstOrFail();

            // Unset all other defaults
            WebUserAddress::where('web_user_id', $userId)
                ->update(['is_default' => false]);

            // Set this one as default
            $address->update(['is_default' => true]);

            return response()->json([
                'success' => true,
                'message' => 'Default address updated',
                'data' => $address,
            ]);

        } catch (\Illuminate\Database\Eloquent\ModelNotFoundException $e) {
            return response()->json([
                'success' => false,
                'message' => 'Address not found',
            ], 404);

        } catch (\Exception $e) {
            Log::error('Error setting default address: ' . $e->getMessage(), [
                'user_id' => $userId,
                'address_id' => $addressId,
                'trace' => $e->getTraceAsString(),
            ]);

            return response()->json([
                'success' => false,
                'message' => 'Failed to set default address',
            ], 500);
        }
    }
}
