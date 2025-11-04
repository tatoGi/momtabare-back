<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\PromoCode;
use App\Models\Product;
use App\Models\Category;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;

class PromoCodeController extends Controller
{
    /**
     * Display a listing of promo codes
     */
    public function index(Request $request)
    {
        try {
            $query = PromoCode::query();

            // Filter by search term
            if ($request->has('search') && !empty($request->search)) {
                $search = $request->search;
                $query->where('code', 'like', "%{$search}%")
                    ->orWhere('description', 'like', "%{$search}%");
            }

            // Filter by status
            if ($request->has('is_active') && $request->is_active !== '') {
                $query->where('is_active', (bool) $request->is_active);
            }

            // Order by creation date (newest first)
            $query->orderBy('created_at', 'desc');

            $perPage = $request->get('per_page', 15);
            $promoCodes = $query->paginate($perPage)->appends($request->query());

            // Calculate stats
            $activeCodes = PromoCode::where('is_active', true)->count();
            $inactiveCodes = PromoCode::where('is_active', false)->count();
            $totalUsage = PromoCode::sum('usage_count');

            // Check if request wants JSON (for API)
            if ($request->wantsJson() || $request->is('api/*')) {
                return response()->json([
                    'success' => true,
                    'data' => $promoCodes->items(),
                    'pagination' => [
                        'current_page' => $promoCodes->currentPage(),
                        'last_page' => $promoCodes->lastPage(),
                        'per_page' => $promoCodes->perPage(),
                        'total' => $promoCodes->total(),
                    ],
                ]);
            }

            // Return view for web interface
            return view('admin.promo-codes.index', compact('promoCodes', 'activeCodes', 'inactiveCodes', 'totalUsage'));
        } catch (\Exception $e) {
            Log::error('Failed to retrieve promo codes', [
                'error' => $e->getMessage(),
            ]);

            if ($request->wantsJson() || $request->is('api/*')) {
                return response()->json([
                    'success' => false,
                    'message' => 'Failed to retrieve promo codes',
                ], 500);
            }

            return back()->with('error', 'Failed to retrieve promo codes');
        }
    }

    /**
     * Show the form for creating a new promo code
     */
    public function create()
    {
        $products = Product::with('translations')->get();
        $categories = Category::with('translations')->get();
        $users = \App\Models\WebUser::select('id', 'first_name', 'email')->orderBy('first_name')->get();
        return view('admin.promo-codes.create', compact('products', 'categories', 'users'));
    }

    /**
     * Store a newly created promo code
     */
    public function store(Request $request)
    {
        try {
            $validated = $request->validate([
                'code' => 'required|string|unique:promo_codes,code|min:3|max:50',
                'discount_percentage' => 'required|numeric|min:0.01|max:100',
                'description' => 'nullable|string|max:1000',
                'max_uses' => 'nullable|integer|min:1',
                'max_uses_per_user' => 'nullable|integer|min:1',
                'is_active' => 'nullable|boolean',
                'valid_from' => 'nullable|date',
                'valid_until' => 'nullable|date|after_or_equal:valid_from',
                'product_ids' => 'nullable|array',
                'product_ids.*' => 'integer|exists:products,id',
                'category_ids' => 'nullable|array',
                'category_ids.*' => 'integer|exists:categories,id',
                'user_ids' => 'nullable|array',
                'user_ids.*' => 'integer|exists:web_users,id',
                'minimum_order_amount' => 'nullable|numeric|min:0',
            ]);

            // Convert datetime-local format to database format
            if (!empty($validated['valid_from'])) {
                $validated['valid_from'] = date('Y-m-d H:i:s', strtotime($validated['valid_from']));
            }
            if (!empty($validated['valid_until'])) {
                $validated['valid_until'] = date('Y-m-d H:i:s', strtotime($validated['valid_until']));
            }

            $promoCode = PromoCode::create([
                'code' => strtoupper($validated['code']),
                'discount_percentage' => $validated['discount_percentage'],
                'description' => $validated['description'] ?? null,
                'usage_limit' => $validated['max_uses'] ?? null,
                'per_user_limit' => $validated['max_uses_per_user'] ?? null,
                'is_active' => $validated['is_active'] ?? true,
                'valid_from' => $validated['valid_from'] ?? null,
                'valid_until' => $validated['valid_until'] ?? null,
                'minimum_order_amount' => $validated['minimum_order_amount'] ?? null,
            ]);

            // Sync relationships
            if (!empty($validated['product_ids'])) {
                $promoCode->products()->sync($validated['product_ids']);
            }
            if (!empty($validated['category_ids'])) {
                $promoCode->categories()->sync($validated['category_ids']);
            }
            if (!empty($validated['user_ids'])) {
                $promoCode->users()->sync($validated['user_ids']);

                // Load products and categories for notifications
                $promoCode->load(['products.translations', 'categories.translations']);

                // Send notifications to assigned users
                $users = \App\Models\WebUser::whereIn('id', $validated['user_ids'])->get();
                foreach ($users as $user) {
                    $user->notify(new \App\Notifications\PromoCodeAssignedNotification($promoCode));
                }
            }

            Log::info('Promo code created', [
                'promo_code_id' => $promoCode->id,
                'code' => $promoCode->code,
                'discount_percentage' => $promoCode->discount_percentage,
                'assigned_users_count' => !empty($validated['user_ids']) ? count($validated['user_ids']) : 0,
            ]);

            // Check if request wants JSON
            if ($request->wantsJson() || $request->is('api/*')) {
                return response()->json([
                    'success' => true,
                    'message' => 'Promo code created successfully',
                    'data' => $promoCode,
                ], 201);
            }

            // Redirect to index for web
            return redirect()->route('promo-codes.index', app()->getLocale())
                ->with('success', 'Promo code created successfully!');

        } catch (\Illuminate\Validation\ValidationException $e) {
            if ($request->wantsJson() || $request->is('api/*')) {
                return response()->json([
                    'success' => false,
                    'message' => 'Validation failed',
                    'errors' => $e->errors(),
                ], 422);
            }
            return back()->withErrors($e->errors())->withInput();
        } catch (\Exception $e) {
            Log::error('Failed to create promo code', [
                'error' => $e->getMessage(),
                'request_data' => $request->all(),
            ]);

            if ($request->wantsJson() || $request->is('api/*')) {
                return response()->json([
                    'success' => false,
                    'message' => 'Failed to create promo code',
                    'error' => $e->getMessage(),
                ], 500);
            }
            return back()->with('error', 'Failed to create promo code: ' . $e->getMessage())->withInput();
        }
    }

    /**
     * Display a specific promo code
     */
    public function show(PromoCode $promoCode)
    {
        try {
            return response()->json([
                'success' => true,
                'data' => $promoCode,
            ]);
        } catch (\Exception $e) {
            Log::error('Failed to retrieve promo code', [
                'error' => $e->getMessage(),
                'promo_code_id' => $promoCode->id ?? null,
            ]);

            return response()->json([
                'success' => false,
                'message' => 'Failed to retrieve promo code',
            ], 500);
        }
    }

    /**
     * Show the form for editing a promo code
     */
    public function edit(PromoCode $promoCode)
    {
        $products = Product::with('translations')->get();
        $categories = Category::with('translations')->get();
        $users = \App\Models\WebUser::select('id', 'first_name', 'email')->orderBy('first_name')->get();
        return view('admin.promo-codes.edit', compact('promoCode', 'products', 'categories', 'users'));
    }

    /**
     * Update a promo code
     */
    public function update(Request $request, PromoCode $promoCode)
    {
        try {
            $validated = $request->validate([
                'code' => 'sometimes|string|unique:promo_codes,code,' . $promoCode->id . '|min:3|max:50',
                'discount_percentage' => 'sometimes|numeric|min:0.01|max:100',
                'description' => 'nullable|string|max:1000',
                'max_uses' => 'nullable|integer|min:1',
                'max_uses_per_user' => 'nullable|integer|min:1',
                'is_active' => 'nullable|boolean',
                'valid_from' => 'nullable|date',
                'valid_until' => 'nullable|date|after_or_equal:valid_from',
                'product_ids' => 'nullable|array',
                'product_ids.*' => 'integer|exists:products,id',
                'category_ids' => 'nullable|array',
                'category_ids.*' => 'integer|exists:categories,id',
                'user_ids' => 'nullable|array',
                'user_ids.*' => 'integer|exists:web_users,id',
                'minimum_order_amount' => 'nullable|numeric|min:0',
            ]);

            // Convert datetime-local format to database format
            if (!empty($validated['valid_from'])) {
                $validated['valid_from'] = date('Y-m-d H:i:s', strtotime($validated['valid_from']));
            }
            if (!empty($validated['valid_until'])) {
                $validated['valid_until'] = date('Y-m-d H:i:s', strtotime($validated['valid_until']));
            }

            // Prepare update data
            $updateData = [];
            if (isset($validated['code'])) {
                $updateData['code'] = strtoupper($validated['code']);
            }
            if (isset($validated['discount_percentage'])) {
                $updateData['discount_percentage'] = $validated['discount_percentage'];
            }
            if (isset($validated['description'])) {
                $updateData['description'] = $validated['description'];
            }
            if (isset($validated['max_uses'])) {
                $updateData['usage_limit'] = $validated['max_uses'];
            }
            if (isset($validated['max_uses_per_user'])) {
                $updateData['per_user_limit'] = $validated['max_uses_per_user'];
            }
            if (isset($validated['is_active'])) {
                $updateData['is_active'] = $validated['is_active'];
            }
            if (isset($validated['valid_from'])) {
                $updateData['valid_from'] = $validated['valid_from'];
            }
            if (isset($validated['valid_until'])) {
                $updateData['valid_until'] = $validated['valid_until'];
            }
            if (isset($validated['minimum_order_amount'])) {
                $updateData['minimum_order_amount'] = $validated['minimum_order_amount'];
            }

            $promoCode->update($updateData);

            // Sync relationships
            if (isset($validated['product_ids'])) {
                $promoCode->products()->sync($validated['product_ids'] ?? []);
            }
            if (isset($validated['category_ids'])) {
                $promoCode->categories()->sync($validated['category_ids'] ?? []);
            }
            if (isset($validated['user_ids'])) {
                // Get currently assigned users
                $currentUserIds = $promoCode->users()->pluck('web_users.id')->toArray();
                $newUserIds = $validated['user_ids'] ?? [];

                // Find newly added users (not previously assigned)
                $addedUserIds = array_diff($newUserIds, $currentUserIds);

                // Sync the users
                $promoCode->users()->sync($newUserIds);

                // Load products and categories for notifications
                $promoCode->load(['products.translations', 'categories.translations']);

                // Send "assigned" notification to newly added users
                if (!empty($addedUserIds)) {
                    $newUsers = \App\Models\WebUser::whereIn('id', $addedUserIds)->get();
                    foreach ($newUsers as $user) {
                        $user->notify(new \App\Notifications\PromoCodeAssignedNotification($promoCode));
                    }
                }

                // Send "updated" notification to all currently assigned users
                if (!empty($newUserIds)) {
                    $allUsers = \App\Models\WebUser::whereIn('id', $newUserIds)->get();
                    foreach ($allUsers as $user) {
                        $user->notify(new \App\Notifications\PromoCodeUpdatedNotification($promoCode));
                    }
                }
            } else {
                // If user_ids is not in the request, send update notification to existing assigned users
                $existingUsers = $promoCode->users;
                if ($existingUsers->isNotEmpty()) {
                    // Load products and categories for notifications
                    $promoCode->load(['products.translations', 'categories.translations']);

                    foreach ($existingUsers as $user) {
                        $user->notify(new \App\Notifications\PromoCodeUpdatedNotification($promoCode));
                    }
                }
            }

            Log::info('Promo code updated', [
                'promo_code_id' => $promoCode->id,
                'code' => $promoCode->code,
                'updated_fields' => array_keys($updateData),
            ]);

            // Check if request wants JSON
            if ($request->wantsJson() || $request->is('api/*')) {
                return response()->json([
                    'success' => true,
                    'message' => 'Promo code updated successfully',
                    'data' => $promoCode,
                ]);
            }

            // Redirect to index for web
            return redirect()->route('promo-codes.index', app()->getLocale())
                ->with('success', 'Promo code updated successfully!');

        } catch (\Illuminate\Validation\ValidationException $e) {
            if ($request->wantsJson() || $request->is('api/*')) {
                return response()->json([
                    'success' => false,
                    'message' => 'Validation failed',
                    'errors' => $e->errors(),
                ], 422);
            }
            return back()->withErrors($e->errors())->withInput();
        } catch (\Exception $e) {
            Log::error('Failed to update promo code', [
                'error' => $e->getMessage(),
                'promo_code_id' => $promoCode->id,
            ]);

            if ($request->wantsJson() || $request->is('api/*')) {
                return response()->json([
                    'success' => false,
                    'message' => 'Failed to update promo code',
                    'error' => $e->getMessage(),
                ], 500);
            }
            return back()->with('error', 'Failed to update promo code')->withInput();
        }
    }

    /**
     * Delete a promo code
     */
    public function destroy(Request $request, PromoCode $promoCode)
    {
        try {
            $code = $promoCode->code;
            $discountPercentage = (float) $promoCode->discount_percentage;

            // Get all assigned users before deleting
            $assignedUsers = $promoCode->users;

            // Send notification to all assigned users
            if ($assignedUsers->isNotEmpty()) {
                foreach ($assignedUsers as $user) {
                    $user->notify(new \App\Notifications\PromoCodeDeletedNotification($code, $discountPercentage));
                }
            }

            $promoCode->delete();

            Log::info('Promo code deleted', [
                'promo_code_id' => $promoCode->id,
                'code' => $code,
                'notified_users_count' => $assignedUsers->count(),
            ]);

            // Check if request wants JSON
            if ($request->wantsJson() || $request->is('api/*')) {
                return response()->json([
                    'success' => true,
                    'message' => 'Promo code deleted successfully',
                ]);
            }

            // Redirect to index for web
            return redirect()->route('promo-codes.index', app()->getLocale())
                ->with('success', 'Promo code deleted successfully!');

        } catch (\Exception $e) {
            Log::error('Failed to delete promo code', [
                'error' => $e->getMessage(),
                'promo_code_id' => $promoCode->id,
            ]);

            if ($request->wantsJson() || $request->is('api/*')) {
                return response()->json([
                    'success' => false,
                    'message' => 'Failed to delete promo code',
                    'error' => $e->getMessage(),
                ], 500);
            }

            return back()->with('error', 'Failed to delete promo code');
        }
    }

    /**
     * Get available products for promo code selection
     */
    public function getAvailableProducts(Request $request)
    {
        try {
            $search = $request->get('search', '');
            $query = Product::query();

            if (!empty($search)) {
                $query->whereHas('translations', function ($q) use ($search) {
                    $q->where('name', 'like', "%{$search}%");
                });
            }

            $products = $query->select('id', 'category_id')
                ->with('translations:id,product_id,name')
                ->limit(100)
                ->get()
                ->map(function ($product) {
                    return [
                        'id' => $product->id,
                        'name' => $product->translations->first()->name ?? "Product {$product->id}",
                        'category_id' => $product->category_id,
                    ];
                });

            return response()->json([
                'success' => true,
                'data' => $products,
            ]);
        } catch (\Exception $e) {
            Log::error('Failed to retrieve available products', [
                'error' => $e->getMessage(),
            ]);

            return response()->json([
                'success' => false,
                'message' => 'Failed to retrieve available products',
            ], 500);
        }
    }

    /**
     * Get available categories for promo code selection
     */
    public function getAvailableCategories(Request $request)
    {
        try {
            $search = $request->get('search', '');
            $query = Category::query();

            if (!empty($search)) {
                $query->whereHas('translations', function ($q) use ($search) {
                    $q->where('name', 'like', "%{$search}%");
                });
            }

            $categories = $query->select('id')
                ->with('translations:id,category_id,name')
                ->limit(100)
                ->get()
                ->map(function ($category) {
                    return [
                        'id' => $category->id,
                        'name' => $category->translations->first()->name ?? "Category {$category->id}",
                    ];
                });

            return response()->json([
                'success' => true,
                'data' => $categories,
            ]);
        } catch (\Exception $e) {
            Log::error('Failed to retrieve available categories', [
                'error' => $e->getMessage(),
            ]);

            return response()->json([
                'success' => false,
                'message' => 'Failed to retrieve available categories',
            ], 500);
        }
    }

    /**
     * Validate a promo code (for customers)
     */
    public function validateCode(Request $request)
    {
        try {
            $validated = $request->validate([
                'code' => 'required|string',
                'total_amount' => 'required|numeric|min:0',
            ]);

            $promoCode = PromoCode::where('code', strtoupper($validated['code']))->first();

            if (!$promoCode) {
                return response()->json([
                    'success' => false,
                    'message' => 'Promo code not found',
                ], 404);
            }

            if (!$promoCode->isValid()) {
                return response()->json([
                    'success' => false,
                    'message' => 'Promo code is no longer valid',
                ], 400);
            }

            if (!$promoCode->meetsMinimumOrderAmount($validated['total_amount'])) {
                return response()->json([
                    'success' => false,
                    'message' => 'Order amount does not meet minimum requirement',
                    'minimum_required' => $promoCode->minimum_order_amount,
                ], 400);
            }

            $discount = $promoCode->calculateDiscount($validated['total_amount']);

            return response()->json([
                'success' => true,
                'data' => [
                    'code' => $promoCode->code,
                    'discount_percentage' => $promoCode->discount_percentage,
                    'discount_amount' => round($discount, 2),
                    'original_amount' => $validated['total_amount'],
                    'final_amount' => round($validated['total_amount'] - $discount, 2),
                ],
                'message' => 'Promo code is valid',
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
            ]);

            return response()->json([
                'success' => false,
                'message' => 'Failed to validate promo code',
            ], 500);
        }
    }
}
