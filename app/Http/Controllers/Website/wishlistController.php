<?php

namespace App\Http\Controllers\Website;

use App\Http\Controllers\Controller;
use App\Models\Cart;
use App\Models\Product;
use App\Models\WebUser;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Log;

class wishlistController extends Controller
{
    public function addToWishlist(Request $request)
    {
        $productId = $request->input('product_id');

        $wishlist = $request->session()->get('wishlist', []);

        // Check if the product already exists in the wishlist
        if (in_array($productId, $wishlist)) {
            // Return JSON response indicating that the product is already in the wishlist
            return response()->json(['exists' => true]);
        }

        // Add the product ID to the wishlist array
        $wishlist[] = $productId;
        $request->session()->put('wishlist', $wishlist);

        // Update product is_favorite to 1
        Product::where('id', $productId)->update(['is_favorite' => 1]);

        // Return JSON response with updated wishlist count
        return response()->json(['exists' => false, 'wishlistCount' => count($wishlist)]);
    }

    public function wishlist(Request $request)
    {
        // Retrieve products where is_favorite = 1
        $products = Product::where('is_favorite', 1)->where('active', 1)->with('translations')->with('images')->get();

        return response()->json(['products' => $products]);
    }

    public function removeFromWishlist(Request $request)
    {
        $productId = $request->input('productId');

        $wishlist = $request->session()->get('wishlist', []);

        // Remove the product ID from the wishlist array
        $wishlist = array_diff($wishlist, [$productId]);

        $request->session()->put('wishlist', $wishlist);

        // Update product is_favorite to 0
        Product::where('id', $productId)->update(['is_favorite' => 0]);

        // Return JSON response indicating success
        return response()->json(['success' => true]);
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
        // Check if user is authenticated
        if (Auth::guard('webuser')->check()) {
            // Get the authenticated user's ID
            $userId = Auth::guard('webuser')->user()->id;

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

        $user = $request->user();
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
