<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Product;
use App\Models\ProductComment;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class ProductCommentController extends Controller
{
    public function __construct()
    {
        $this->middleware('auth:sanctum')->except(['index', 'show']);
    }

    /**
     * Display comments for a specific product
     */
    public function index(Request $request, $productId)
    {
        $product = Product::findOrFail($productId);

        $query = $product->approvedComments()
            ->with(['user:id,first_name,surname,email'])
            ->latest();

        // Pagination
        $perPage = $request->get('per_page', 10);
        $comments = $query->paginate($perPage);

        // Transform the data
        $transformedComments = $comments->getCollection()->map(function ($comment) {
            return [
                'id' => $comment->id,
                'comment' => $comment->comment,
                'rating' => $comment->rating,
                'created_at' => $comment->created_at->format('Y-m-d H:i:s'),
                'user' => [
                    'id' => $comment->user->id,
                    'name' => $comment->user->name,
                    'email' => $comment->user->email,
                ],
            ];
        });

        return response()->json([
            'data' => $transformedComments,
            'pagination' => [
                'current_page' => $comments->currentPage(),
                'last_page' => $comments->lastPage(),
                'per_page' => $comments->perPage(),
                'total' => $comments->total(),
                'from' => $comments->firstItem(),
                'to' => $comments->lastItem(),
            ],
            'product_stats' => [
                'average_rating' => round($product->average_rating, 1),
                'total_comments' => $product->total_comments,
            ],
        ]);
    }

    /**
     * Store a newly created comment
     */
    public function store(Request $request, $productId)
    {
        $product = Product::findOrFail($productId);

        $validated = $request->validate([
            'comment' => 'required|string|min:10|max:1000',
            'rating' => 'nullable|integer|min:1|max:5',
        ]);

        $user = Auth::guard('sanctum')->user();

        // Check if user already commented on this product
        $existingComment = ProductComment::where('product_id', $productId)
            ->where('user_id', $user->id)
            ->first();

        if ($existingComment) {
            return response()->json([
                'error' => 'You have already commented on this product. You can update your existing comment instead.',
            ], 422);
        }

        $comment = ProductComment::create([
            'product_id' => $productId,
            'user_id' => $user->id,
            'comment' => $validated['comment'],
            'rating' => $validated['rating'] ?? null,
            'is_approved' => false, // Comments need admin approval
        ]);

        return response()->json([
            'message' => 'Comment submitted successfully. It will be visible after admin approval.',
            'data' => [
                'id' => $comment->id,
                'comment' => $comment->comment,
                'rating' => $comment->rating,
                'is_approved' => $comment->is_approved,
                'created_at' => $comment->created_at->format('Y-m-d H:i:s'),
            ],
        ], 201);
    }

    /**
     * Display the specified comment
     */
    public function show($productId, $commentId)
    {
        $product = Product::findOrFail($productId);
        $comment = $product->comments()->with(['user:id,name,email'])->findOrFail($commentId);

        return response()->json([
            'data' => [
                'id' => $comment->id,
                'comment' => $comment->comment,
                'rating' => $comment->rating,
                'is_approved' => $comment->is_approved,
                'created_at' => $comment->created_at->format('Y-m-d H:i:s'),
                'user' => [
                    'id' => $comment->user->id,
                    'name' => $comment->user->name,
                    'email' => $comment->user->email,
                ],
            ],
        ]);
    }

    /**
     * Update the specified comment (only by comment owner)
     */
    public function update(Request $request, $productId, $commentId)
    {
        $product = Product::findOrFail($productId);
        $comment = $product->comments()->findOrFail($commentId);

        $user = Auth::guard('sanctum')->user();

        // Check if user owns this comment
        if ($comment->user_id !== $user->id) {
            return response()->json([
                'error' => 'You can only update your own comments.',
            ], 403);
        }

        $validated = $request->validate([
            'comment' => 'required|string|min:10|max:1000',
            'rating' => 'nullable|integer|min:1|max:5',
        ]);

        $comment->update([
            'comment' => $validated['comment'],
            'rating' => $validated['rating'] ?? $comment->rating,
            'is_approved' => false, // Reset approval status after update
        ]);

        return response()->json([
            'message' => 'Comment updated successfully. It will be visible after admin approval.',
            'data' => [
                'id' => $comment->id,
                'comment' => $comment->comment,
                'rating' => $comment->rating,
                'is_approved' => $comment->is_approved,
                'updated_at' => $comment->updated_at->format('Y-m-d H:i:s'),
            ],
        ]);
    }

    /**
     * Remove the specified comment (only by comment owner)
     */
    public function destroy($productId, $commentId)
    {
        $product = Product::findOrFail($productId);
        $comment = $product->comments()->findOrFail($commentId);

        $user = Auth::guard('sanctum')->user();

        // Check if user owns this comment
        if ($comment->user_id !== $user->id) {
            return response()->json([
                'error' => 'You can only delete your own comments.',
            ], 403);
        }

        $comment->delete();

        return response()->json([
            'message' => 'Comment deleted successfully.',
        ]);
    }

    /**
     * Get user's own comments for a product
     */
    public function userComments(Request $request, $productId)
    {
        $product = Product::findOrFail($productId);
        $user = Auth::guard('sanctum')->user();

        $comment = $product->comments()
            ->where('user_id', $user->id)
            ->first();

        if (! $comment) {
            return response()->json([
                'data' => null,
                'message' => 'You have not commented on this product yet.',
            ]);
        }

        return response()->json([
            'data' => [
                'id' => $comment->id,
                'comment' => $comment->comment,
                'rating' => $comment->rating,
                'is_approved' => $comment->is_approved,
                'created_at' => $comment->created_at->format('Y-m-d H:i:s'),
                'updated_at' => $comment->updated_at->format('Y-m-d H:i:s'),
            ],
        ]);
    }
}
