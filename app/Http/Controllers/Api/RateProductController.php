<?php

namespace App\Http\Controllers\Api;

use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use Illuminate\Http\JsonResponse;
use App\Models\ProductRating;
class RateProductController extends Controller
{
    public function set(Request $request): JsonResponse
    {
        // Validate input
        $validated = $request->validate([
            'product_id' => 'required|integer',
            'rating' => 'required|integer|min:1|max:5',
        ]);

          $user = $request->user('sanctum');
        // Save rating to database
        ProductRating::create([
            'product_id' => $validated['product_id'],
            'user_id' => $user ? $user->id : null,
            'rating' => $validated['rating'],
            'comment' => $request->input('comment'),
        ]);

        // Return response to frontend
        return response()->json([
            'success' => true,
            'message' => 'Rating submitted successfully',
        ]);
    }
        public function get($product_id): JsonResponse
    {
        $ratings = ProductRating::where('product_id', $product_id)
            ->orderByDesc('created_at')
            ->get();

        // Optionally, you can return average rating and count
        $average = $ratings->avg('rating');
        $count = $ratings->count();

        return response()->json([
            'success' => true,
            'ratings' => $ratings,
            'average' => $average,
            'count' => $count,
        ]);
    }
}
