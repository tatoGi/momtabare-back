<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Product;
use App\Models\ProductComment;
use App\Models\WebUser; // Assuming your model name is WebUser
use Illuminate\Contracts\View\Factory;
use Illuminate\Contracts\View\View;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Storage;

class WebUserController extends Controller
{
    public function index(): Factory|View
    {
        $webUsers = WebUser::with(['retailerShop.translations'])->latest()->get();

        return view('admin.webuser.index', compact('webUsers'));
    }

    /**
     * Approve retailer request
     */
    public function approveRetailer(Request $request, $id): JsonResponse
    {
        $webUser = WebUser::findOrFail($id);

        $webUser->update([
            'retailer_status' => 'approved',
            'is_retailer' => true,
        ]);

        return response()->json([
            'success' => true,
            'message' => 'Retailer request approved successfully.',
        ]);
    }

    /**
     * Reject retailer request
     */
    public function rejectRetailer(Request $request, $id): JsonResponse
    {
        $webUser = WebUser::findOrFail($id);

        $webUser->update([
            'retailer_status' => 'rejected',
            'is_retailer' => false,
        ]);

        return response()->json([
            'success' => true,
            'message' => 'Retailer request rejected.',
        ]);
    }

    /**
     * Get retailer products for admin review
     */
    public function retailerProducts(Request $request)
    {
        $products = \App\Models\Product::with(['retailer', 'category', 'images'])
            ->whereNotNull('retailer_id')
            ->when($request->status, function ($query, $status) {
                return $query->where('status', $status);
            })
            ->when($request->retailer_id, function ($query, $retailerId) {
                return $query->where('retailer_id', $retailerId);
            })
            ->orderBy('created_at', 'desc')
            ->paginate(20);

        return view('admin.retailer-products.index', compact('products'));
    }

    /**
     * Approve retailer product
     */
    public function approveProduct(Request $request, $id): JsonResponse
    {
        $product = Product::findOrFail($id);

        $product->update([
            'status' => 'approved',
            'active' => true,
            'approved_at' => now(),
        ]);

        return response()->json([
            'success' => true,
            'message' => 'Product approved successfully.',
        ]);
    }

    /**
     * Reject retailer product
     */
    public function rejectProduct(Request $request, $id): JsonResponse
    {
        $product = Product::findOrFail($id);

        $product->update([
            'status' => 'rejected',
            'active' => false,
        ]);

        return response()->json([
            'success' => true,
            'message' => 'Product rejected.',
        ]);
    }

    /**
     * Delete retailer product
     */
    public function deleteProduct(Request $request, $id): JsonResponse
    {
        try {
            $product = Product::findOrFail($id);

            // Delete associated images from storage
            if ($product->images) {
                foreach ($product->images as $image) {
                    // Delete the image file from storage
                    if (Storage::disk('public')->exists($image->image_path)) {
                        Storage::disk('public')->delete($image->image_path);
                    }
                    // Delete the image record from database
                    $image->delete();
                }
            }

            // Delete the product
            $product->delete();

            return response()->json([
                'success' => true,
                'message' => 'Product deleted successfully.',
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Failed to delete product: '.$e->getMessage(),
            ], 500);
        }
    }

    /**
     * Show all pending comments for approval
     */
    public function comments(): Factory|View
    {
        $comments = ProductComment::with(['user', 'product.translations'])
            ->where('is_approved', false)
            ->latest()
            ->paginate(20);

        return view('admin.webuser.comments', compact('comments'));
    }

    /**
     * Show all comments (approved and pending)
     */
    public function allComments(): Factory|View
    {
        $comments = ProductComment::with(['user', 'product.translations', 'approvedBy'])
            ->latest()
            ->paginate(20);

        return view('admin.webuser.all-comments', compact('comments'));
    }

    /**
     * Approve a comment
     */
    public function approveComment(Request $request, $id): JsonResponse
    {
        $comment = ProductComment::findOrFail($id);

        $comment->update([
            'is_approved' => true,
            'approved_at' => now(),
            'approved_by' => Auth::id(),
        ]);

        return response()->json([
            'success' => true,
            'message' => 'Comment approved successfully.',
        ]);
    }

    /**
     * Reject/Delete a comment
     */
    public function rejectComment(Request $request, $id): JsonResponse
    {
        $comment = ProductComment::findOrFail($id);
        $comment->delete();

        return response()->json([
            'success' => true,
            'message' => 'Comment rejected and deleted.',
        ]);
    }

    /**
     * Show web user details with their comments and products
     */
    public function show(Request $request, $id): Factory|View
    {
        $webUser = WebUser::with([
            'retailerShop.translations',
            'products.translations',
        ])->findOrFail($id);

        // Get user's comments with filtering
        $commentsQuery = $webUser->comments()->with(['product.translations', 'approvedBy']);

        // Apply status filter if provided
        if ($request->has('status')) {
            if ($request->status === 'pending') {
                $commentsQuery->where('is_approved', false);
            } elseif ($request->status === 'approved') {
                $commentsQuery->where('is_approved', true);
            }
        }

        $comments = $commentsQuery->latest()->paginate(10);

        // Get comprehensive statistics
        $stats = [
            // Comment statistics
            'comments' => [
                'total' => $webUser->comments()->count(),
                'pending' => $webUser->comments()->where('is_approved', false)->count(),
                'approved' => $webUser->comments()->where('is_approved', true)->count(),
            ],
            // Product statistics (for retailers)
            'products' => [
                'total' => $webUser->products()->count(),
                'pending' => $webUser->products()->where('status', 'pending')->count(),
                'approved' => $webUser->products()->where('status', 'approved')->count(),
                'rejected' => $webUser->products()->where('status', 'rejected')->count(),
                'active' => $webUser->products()->where('active', true)->count(),
            ],
            // User status
            'user' => [
                'is_retailer' => $webUser->is_retailer,
                'retailer_status' => $webUser->retailer_status,
                'retailer_requested_at' => $webUser->retailer_requested_at,
                'email_verified' => $webUser->hasVerifiedEmail(),
                'created_at' => $webUser->created_at,
            ],
        ];

        // For backward compatibility
        $pendingCount = $stats['comments']['pending'];
        $approvedCount = $stats['comments']['approved'];

        return view('admin.webuser.show', compact('webUser', 'comments', 'pendingCount', 'approvedCount', 'stats'));
    }

    /**
     * Delete a web user
     */
    public function destroy($id): JsonResponse
    {
        $webUser = WebUser::with([
            'products.images',
            'products.pages',
            'products.bogPayments',
            'products.comments',
            'retailerShop',
            'addresses',
            'comments',
        ])->findOrFail($id);

        try {
            DB::transaction(function () use ($webUser) {
                // Delete user avatar if stored locally
                if (! empty($webUser->avatar) && Storage::disk('public')->exists($webUser->avatar)) {
                    Storage::disk('public')->delete($webUser->avatar);
                }

                // Remove associated retailer shop (and its assets)
                if ($webUser->retailerShop) {
                    if (! empty($webUser->retailerShop->avatar) && Storage::disk('public')->exists($webUser->retailerShop->avatar)) {
                        Storage::disk('public')->delete($webUser->retailerShop->avatar);
                    }

                    if (! empty($webUser->retailerShop->cover_image) && Storage::disk('public')->exists($webUser->retailerShop->cover_image)) {
                        Storage::disk('public')->delete($webUser->retailerShop->cover_image);
                    }

                    $webUser->retailerShop->delete();
                }

                // Detach promo codes if any
                $webUser->promoCodes()->detach();

                // Delete addresses
                $webUser->addresses()->delete();

                // Delete comments authored by the user
                $webUser->comments()->delete();

                // Delete products owned by the user along with related data
                foreach ($webUser->products as $product) {
                    foreach ($product->images as $image) {
                        $paths = array_filter([
                            $image->image_path ?? null,
                            $image->image_name ? 'products/'.$image->image_name : null,
                        ]);

                        foreach ($paths as $path) {
                            if (Storage::disk('public')->exists($path)) {
                                Storage::disk('public')->delete($path);
                            }
                        }

                        $image->delete();
                    }

                    $product->pages()->detach();
                    $product->bogPayments()->detach();
                    $product->comments()->delete();

                    $product->delete();
                }

                $webUser->delete();
            });

            return response()->json([
                'success' => true,
                'message' => 'Web user and related records deleted successfully.',
            ]);
        } catch (\Throwable $exception) {
            Log::error('Failed to delete web user', [
                'web_user_id' => $id,
                'error' => $exception->getMessage(),
            ]);

            return response()->json([
                'success' => false,
                'message' => 'Failed to delete user. Please try again later.',
            ], 500);
        }
    }

    /**
     * Toggle user active status
     */
    public function toggleStatus($id): JsonResponse
    {
        $webUser = WebUser::findOrFail($id);

        $webUser->update([
            'is_active' => ! $webUser->is_active,
        ]);

        return response()->json([
            'success' => true,
            'message' => 'User status updated successfully.',
            'is_active' => $webUser->is_active,
        ]);
    }
}
