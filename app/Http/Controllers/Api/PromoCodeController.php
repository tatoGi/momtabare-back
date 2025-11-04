<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\PromoCode;
use App\Models\Product;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Log;

class PromoCodeController extends Controller
{
    /**
     * Validate and apply a promo code
     *
     * POST /api/promo-code/apply
     * Body: { "code": "SUMMER2024", "order_amount": 100.00, "product_ids": [1,2,3] }
     */
    public function apply(Request $request)
    {
        try {
            $validated = $request->validate([
                'code' => 'required|string',
                'order_amount' => 'required|numeric|min:0',
                'product_ids' => 'nullable|array',
                'product_ids.*' => 'integer|exists:products,id',
            ]);

            $code = strtoupper($validated['code']);
            $orderAmount = (float) $validated['order_amount'];
            $productIds = $validated['product_ids'] ?? [];

            // Find promo code
            $promoCode = PromoCode::where('code', $code)
                ->with(['products', 'categories', 'users'])
                ->first();

            if (!$promoCode) {
                return response()->json([
                    'success' => false,
                    'message' => 'Invalid promo code',
                    'error_code' => 'INVALID_CODE',
                ], 404);
            }

            // Check if promo code is active and within validity period
            if (!$promoCode->isValid()) {
                $reason = 'Promo code is not active or has expired';

                if ($promoCode->usage_limit && $promoCode->usage_count >= $promoCode->usage_limit) {
                    $reason = 'Promo code has reached its usage limit';
                }

                return response()->json([
                    'success' => false,
                    'message' => $reason,
                    'error_code' => 'INVALID_PROMO',
                ], 400);
            }

            // Check minimum order amount
            if (!$promoCode->meetsMinimumOrderAmount($orderAmount)) {
                return response()->json([
                    'success' => false,
                    'message' => 'Minimum order amount of $' . number_format((float)$promoCode->minimum_order_amount, 2) . ' is required',
                    'error_code' => 'MINIMUM_NOT_MET',
                    'minimum_amount' => (float) $promoCode->minimum_order_amount,
                    'current_amount' => $orderAmount,
                ], 400);
            }

            // Check if user is authenticated and promo code is user-specific
            $user = Auth::guard('sanctum')->user();
            $assignedUsers = $promoCode->users;

            if ($assignedUsers->isNotEmpty()) {
                if (!$user) {
                    return response()->json([
                        'success' => false,
                        'message' => 'This promo code requires authentication',
                        'error_code' => 'AUTH_REQUIRED',
                    ], 401);
                }

                $isAssigned = $assignedUsers->contains('id', $user->id);
                if (!$isAssigned) {
                    return response()->json([
                        'success' => false,
                        'message' => 'This promo code is not available for your account',
                        'error_code' => 'NOT_ASSIGNED',
                    ], 403);
                }
            }

            // Check if promo code applies to the products
            $applicableProducts = [];
            $nonApplicableProducts = [];

            if (!empty($productIds)) {
                $promoProducts = $promoCode->products;
                $promoCategories = $promoCode->categories;

                // If specific products/categories are set, validate
                if ($promoProducts->isNotEmpty() || $promoCategories->isNotEmpty()) {
                    $products = Product::whereIn('id', $productIds)->get();

                    foreach ($products as $product) {
                        $applies = false;

                        // Check if product is in the promo products list
                        if ($promoProducts->contains('id', $product->id)) {
                            $applies = true;
                        }

                        // Check if product's category is in the promo categories list
                        if (!$applies && $promoCategories->contains('id', $product->category_id)) {
                            $applies = true;
                        }

                        if ($applies) {
                            $applicableProducts[] = [
                                'id' => $product->id,
                                'title' => $product->title ?? 'Product #' . $product->id,
                            ];
                        } else {
                            $nonApplicableProducts[] = [
                                'id' => $product->id,
                                'title' => $product->title ?? 'Product #' . $product->id,
                            ];
                        }
                    }

                    // If no products are applicable, return error
                    if (empty($applicableProducts)) {
                        return response()->json([
                            'success' => false,
                            'message' => 'This promo code does not apply to any products in your cart',
                            'error_code' => 'NOT_APPLICABLE',
                        ], 400);
                    }
                } else {
                    // Applies to all products
                    $products = Product::whereIn('id', $productIds)->get();
                    foreach ($products as $product) {
                        $applicableProducts[] = [
                            'id' => $product->id,
                            'title' => $product->title ?? 'Product #' . $product->id,
                        ];
                    }
                }
            }

            // Calculate discount
            $discountAmount = $promoCode->calculateDiscount($orderAmount);
            $finalAmount = $orderAmount - $discountAmount;

            Log::info('Promo code validated', [
                'code' => $code,
                'user_id' => $user->id ?? null,
                'order_amount' => $orderAmount,
                'discount_amount' => $discountAmount,
            ]);

            return response()->json([
                'success' => true,
                'message' => 'Promo code applied successfully',
                'data' => [
                    'promo_code' => [
                        'id' => $promoCode->id,
                        'code' => $promoCode->code,
                        'discount_percentage' => (float) $promoCode->discount_percentage,
                        'description' => $promoCode->description,
                        'valid_until' => $promoCode->valid_until ? $promoCode->valid_until->format('Y-m-d H:i:s') : null,
                    ],
                    'order_summary' => [
                        'original_amount' => $orderAmount,
                        'discount_amount' => round($discountAmount, 2),
                        'final_amount' => round($finalAmount, 2),
                        'savings' => round($discountAmount, 2),
                    ],
                    'applicable_products' => $applicableProducts,
                    'non_applicable_products' => $nonApplicableProducts,
                ],
            ]);

        } catch (\Illuminate\Validation\ValidationException $e) {
            return response()->json([
                'success' => false,
                'message' => 'Validation failed',
                'errors' => $e->errors(),
            ], 422);
        } catch (\Exception $e) {
            Log::error('Failed to validate promo code', [
                'error' => $e->getMessage(),
                'request_data' => $request->all(),
            ]);

            return response()->json([
                'success' => false,
                'message' => 'Failed to validate promo code',
                'error' => $e->getMessage(),
            ], 500);
        }
    }

    /**
     * Get user's assigned promo codes
     *
     * GET /api/promo-code/my-codes
     */
    public function myCodes(Request $request)
    {
        try {
            /** @var \App\Models\WebUser $user */
            $user = Auth::guard('sanctum')->user();

            if (!$user) {
                return response()->json([
                    'success' => false,
                    'message' => 'Unauthenticated',
                ], 401);
            }

            $promoCodes = $user->promoCodes()
                ->where('is_active', true)
                ->where(function ($query) {
                    $query->whereNull('valid_until')
                          ->orWhere('valid_until', '>=', now());
                })
                ->with(['products.translations', 'categories.translations'])
                ->get()
                ->map(function ($promoCode) {
                    $products = $promoCode->products;
                    $categories = $promoCode->categories;

                    return [
                        'id' => $promoCode->id,
                        'code' => $promoCode->code,
                        'discount_percentage' => (float) $promoCode->discount_percentage,
                        'description' => $promoCode->description,
                        'valid_from' => $promoCode->valid_from ? $promoCode->valid_from->format('Y-m-d H:i:s') : null,
                        'valid_until' => $promoCode->valid_until ? $promoCode->valid_until->format('Y-m-d H:i:s') : null,
                        'minimum_order_amount' => $promoCode->minimum_order_amount ? (float) $promoCode->minimum_order_amount : null,
                        'usage_count' => $promoCode->usage_count,
                        'usage_limit' => $promoCode->usage_limit,
                        'is_valid' => $promoCode->isValid(),
                        'applicable_products' => $products->map(fn($p) => [
                            'id' => $p->id,
                            'title' => $p->title ?? 'Product #' . $p->id,
                        ])->toArray(),
                        'applicable_categories' => $categories->map(fn($c) => [
                            'id' => $c->id,
                            'title' => $c->title ?? 'Category #' . $c->id,
                        ])->toArray(),
                        'applies_to_all_products' => $products->isEmpty() && $categories->isEmpty(),
                    ];
                });

            return response()->json([
                'success' => true,
                'data' => $promoCodes,
            ]);

        } catch (\Exception $e) {
            Log::error('Failed to retrieve user promo codes', [
                'error' => $e->getMessage(),
                'user_id' => $user->id ?? null,
            ]);

            return response()->json([
                'success' => false,
                'message' => 'Failed to retrieve promo codes',
                'error' => $e->getMessage(),
            ], 500);
        }
    }
}
