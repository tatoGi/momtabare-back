<?php

namespace App\Http\Controllers\Website;

use App\Http\Controllers\Controller;
use App\Models\Cart;
use App\Models\Product;
use App\Models\WebUser;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;

class wishlistController extends Controller
{
    public function addToWishlist(Request $request)
    {
        try {
            // Validate the request
            $request->validate([
                'product_id' => 'required|integer|exists:products,id',
            ]);

            $productId = $request->input('product_id');

            // Check if user is authenticated
            $user = $request->user('sanctum');

            if (!$user) {
                return response()->json([
                    'success' => false,
                    'message' => 'Please log in to add items to wishlist',
                    'requires_auth' => true,
                ], 401);
            }

            // Check if product exists and is active
            $product = Product::where('id', $productId)
                ->where('active', 1)
                ->first();

            if (!$product) {
                return response()->json([
                    'success' => false,
                    'message' => 'Product not found or inactive',
                ], 404);
            }

            // Toggle is_favorite for this product
            $product->is_favorite = 1;
            $product->save();

            // Get wishlist count (all favorite products for this user)
            $wishlistCount = Product::where('is_favorite', 1)
                ->where('active', 1)
                ->count();

            return response()->json([
                'success' => true,
                'message' => 'Product added to wishlist',
                'exists' => false,
                'wishlistCount' => $wishlistCount,
            ]);

        } catch (\Illuminate\Validation\ValidationException $e) {
            return response()->json([
                'success' => false,
                'message' => 'Validation failed',
                'errors' => $e->errors(),
            ], 422);
        } catch (\Exception $e) {
            Log::error('Add to wishlist error: ' . $e->getMessage(), [
                'file' => $e->getFile(),
                'line' => $e->getLine(),
                'trace' => $e->getTraceAsString(),
            ]);

            return response()->json([
                'success' => false,
                'message' => 'Server Error',
                'error' => config('app.debug') ? $e->getMessage() : null,
            ], 500);
        }
    }

    public function wishlist(Request $request)
    {
        try {
            // Check if user is authenticated
            $user = $request->user('sanctum');

            if (!$user) {
                return response()->json([
                    'success' => false,
                    'message' => 'Please log in to view wishlist',
                    'requires_auth' => true,
                    'products' => [],
                ], 401);
            }

            // Retrieve products where is_favorite = 1 with full details
            $products = Product::where('is_favorite', 1)
                ->where('active', 1)
                ->with(['translations', 'images', 'category'])
                ->get()
                ->map(function ($product) {
                    return [
                        'id' => $product->id,
                        'product_identify_id' => $product->product_identify_id,
                        'title' => $product->title,
                        'slug' => $product->slug,
                        'description' => $product->description,
                        'brand' => $product->brand,
                        'location' => $product->location,
                        'color' => $product->color,
                        'size' => $product->size,
                        'price' => $product->price,
                        'currency' => $product->currency,
                        'is_favorite' => true,
                        'rental_period' => $product->rental_period,
                        'rental_start_date' => $product->rental_start_date ? $product->rental_start_date->format('Y-m-d H:i:s') : null,
                        'rental_end_date' => $product->rental_end_date ? $product->rental_end_date->format('Y-m-d H:i:s') : null,
                        'category' => $product->category ? [
                            'id' => $product->category->id,
                            'title' => $product->category->title,
                            'slug' => $product->category->slug,
                        ] : null,
                        'images' => $product->images->map(function ($image) {
                            return [
                                'id' => $image->id,
                                'url' => asset('storage/products/' . $image->image_name),
                                'alt' => $image->alt_text ?? '',
                            ];
                        }),
                        'featured_image' => $product->images->first() ? asset('storage/products/' . $product->images->first()->image_name) : null,
                    ];
                });

            return response()->json([
                'success' => true,
                'products' => $products,
                'count' => $products->count(),
            ]);

        } catch (\Exception $e) {
            Log::error('Wishlist error: ' . $e->getMessage(), [
                'file' => $e->getFile(),
                'line' => $e->getLine(),
            ]);

            return response()->json([
                'success' => false,
                'message' => 'Server Error',
                'products' => [],
                'error' => config('app.debug') ? $e->getMessage() : null,
            ], 500);
        }
    }

    public function removeFromWishlist(Request $request)
    {
        try {
            // Validate the request
            $request->validate([
                'productId' => 'required|integer|exists:products,id',
            ]);

            $productId = $request->input('productId');

            // Check if user is authenticated
            $user = $request->user('sanctum');

            if (!$user) {
                return response()->json([
                    'success' => false,
                    'message' => 'Please log in to remove items from wishlist',
                    'requires_auth' => true,
                ], 401);
            }

            // Find the product
            $product = Product::find($productId);

            if (!$product) {
                return response()->json([
                    'success' => false,
                    'message' => 'Product not found',
                ], 404);
            }

            // Update product is_favorite to 0
            $product->is_favorite = 0;
            $product->save();

            // Get remaining wishlist count
            $wishlistCount = Product::where('is_favorite', 1)
                ->where('active', 1)
                ->count();

            return response()->json([
                'success' => true,
                'message' => 'Product removed from wishlist',
                'wishlistCount' => $wishlistCount,
            ]);

        } catch (\Illuminate\Validation\ValidationException $e) {
            return response()->json([
                'success' => false,
                'message' => 'Validation failed',
                'errors' => $e->errors(),
            ], 422);
        } catch (\Exception $e) {
            Log::error('Remove from wishlist error: ' . $e->getMessage(), [
                'file' => $e->getFile(),
                'line' => $e->getLine(),
            ]);

            return response()->json([
                'success' => false,
                'message' => 'Server Error',
                'error' => config('app.debug') ? $e->getMessage() : null,
            ], 500);
        }
    }

    public function addToCart(Request $request)
    {
        try {
            // Check if user is authenticated using webuser guard
            $user = $request->user('sanctum');

            if (! $user) {
                return response()->json([
                    'success' => false,
                    'message' => 'Please log in to add items to cart',
                    'requires_auth' => true,
                ], 401);
            }

            $productId = $request->input('productId');
            $userId = $user->id; // Get ID from webuser guard

            // Validate product ID
            if (! $productId) {
                return response()->json([
                    'success' => false,
                    'message' => 'Product ID is required',
                ], 400); // 400 Bad Request
            }

            // Check if product exists
            $product = Product::find($productId);
            if (! $product) {
                return response()->json([
                    'success' => false,
                    'message' => 'Product not found',
                ], 404); // 404 Not Found
            }

            // Check if product already exists in cart
            $existingCartItem = Cart::where('user_id', $userId)
                ->where('product_id', $productId)
                ->first();

            if ($existingCartItem) {
                // Product already in cart - return message
                return response()->json([
                    'success' => false,
                    'message' => 'Product already in cart',
                    'cart' => $this->fetchCartData($userId)->original,
                ], 409); // 409 Conflict status code
            }

            // Add new product to cart
            Cart::create([
                'user_id' => $userId,
                'product_id' => $productId,
                'quantity' => 1,
            ]);

            // Get updated cart data
            $cartData = $this->fetchCartData($userId)->original;

            // Update session cart data
            session()->put('cart', $cartData);

            // Return success response with cart data
            return response()->json([
                'success' => true,
                'message' => 'Product added to cart successfully',
                'cart' => $cartData,
                'cart_count' => count($cartData['cart'] ?? []),
            ]);

        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'An error occurred while adding to cart',
                'error' => config('app.debug') ? $e->getMessage() : null,
            ], 500);
        }
    }

    public function removeFromCart(Request $request)
    {
        //
         $user = $request->user('sanctum');
        if ($user) {
            // Get the authenticated user's ID
            $userId = $user->id;

            // Find and delete the cart item for the specified user and product
            Cart::where('user_id', $userId)->where('product_id', $request->productId)->delete();

            // Update session cart data
            session()->put('cart', $this->fetchCartData($userId)->original);

            // Return updated cart data
            return $this->fetchCartData($userId);
        } else {
            // User is not authenticated
            return response()->json(
                [
                    'success' => false,
                    'message' => 'Authentication required',
                    'redirect' => route('login'),
                ],
                401,
            );
        }
    }

    public function updateCartItem(Request $request)
    {
        $request->validate([
            'item_id' => 'required|integer|exists:products,id',
            'quantity' => 'required|integer|min:1',
            'rental_days' => 'nullable|integer|min:1',
        ]);

        $user = $request->user('sanctum');
        if (! $user) {
            return response()->json([
                'success' => false,
                'message' => 'Authentication required',
            ], 401);
        }

        $productId = (int) $request->input('item_id');
        $quantity = (int) $request->input('quantity');
        $rentalDays = (int) ($request->input('rental_days') ?? 1);

        // Find cart item for this user and product
        $cartItem = Cart::where('user_id', $user->id)
            ->where('product_id', $productId)
            ->with('product')
            ->first();

        if (! $cartItem) {
            return response()->json([
                'success' => false,
                'message' => 'Cart item not found',
            ], 404);
        }

        $cartItem->quantity = $quantity;
        $cartItem->save();

        $unitPrice = (float) $cartItem->product->price;
        // If rental days affect price, multiply; default to at least 1 day
        $lineTotal = $unitPrice * $quantity * max(1, $rentalDays);

        // Recalculate subtotal for all items (assuming rental days apply uniformly to all items)
        $cartItems = Cart::where('user_id', $user->id)->with('product')->get();
        $subtotal = $cartItems->sum(function ($item) use ($rentalDays) {
            $days = max(1, (int) $rentalDays);

            return ((float) $item->product->price) * $item->quantity * $days;
        });

        return response()->json([
            'success' => true,
            'line_total' => $lineTotal,
            'subtotal' => $subtotal,
        ]);
    }

    public function fetchCartData($userId)
    {
        $user = WebUser::find($userId);
        $cartItems = Cart::where('user_id', $userId)->with('product')->get();

        // Prepare array to store formatted product data
        $products = [];

        foreach ($cartItems as $cartItem) {
            $product = $cartItem->product;

            // Construct image URL using asset() function
            $imageUrl = asset('storage/products/'.$product->images->first()->image_name);

            // Customize product data as needed for frontend
            $products[] = [
                'id' => $product->id,
                'name' => $product->title,
                'price' => $product->price,
                'image' => $imageUrl, // Pass the constructed image URL
            ];
        }

        // Calculate subtotal and format cart data
        $subtotal = $cartItems->sum(function ($item) {
            return $item->product->price * $item->quantity;
        });

        return response()->json([
            'user' => $user,
            'items' => $products,
            'subtotal' => $subtotal,
            'cartCount' => $cartItems->count(),
        ]);
    }

    /**
     * Get cart data for the current authenticated user (for route calls)
     */
    public function showCart(Request $request)
    {
        try {
            $user = $request->user('sanctum');

            if (! $user) {
                return response()->json([
                    'success' => false,
                    'message' => 'User not authenticated',
                    'requires_auth' => true,
                ], 401);
            }

            // Get cart items directly instead of calling fetchCartData
            $cartItems = Cart::where('user_id', $user->id)->with('product')->get();

            // Prepare array to store formatted product data
            $products = [];

            foreach ($cartItems as $cartItem) {
                // Skip if product is not found
                if (! $cartItem->product) {
                    continue;
                }

                $product = $cartItem->product;

                // Check if product has images before accessing
                $imageUrl = null;
                if ($product->images && $product->images->first()) {
                    $imageUrl = asset('storage/products/'.$product->images->first()->image_name);
                }

                // Customize product data as needed for frontend
                $products[] = [
                    'id' => $product->id,
                    'name' => $product->title,
                    'price' => $product->price,
                    'quantity' => $cartItem->quantity,
                    'image' => $imageUrl,
                ];
            }

            // Calculate subtotal
            $subtotal = $cartItems->sum(function ($item) {
                return $item->product ? ($item->product->price * $item->quantity) : 0;
            });

            return response()->json([
                'success' => true,
                'owner' => [
                    'id' => $user->id,
                    'name' => $user->name,
                    'email' => $user->email,
                    // Add other necessary user fields
                ],
                'items' => $products,
                'subtotal' => $subtotal,
                'cartCount' => $cartItems->count(),
            ]);

        } catch (\Exception $e) {
            Log::error('Error in showCart: '.$e->getMessage(), [
                'trace' => $e->getTraceAsString(),
            ]);

            return response()->json([
                'success' => false,
                'message' => 'An error occurred while fetching cart data',
                'error' => config('app.debug') ? $e->getMessage() : null,
            ], 500);
        }
    }
}
