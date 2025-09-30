<?php

namespace App\Http\Controllers\Website;

use App\Http\Controllers\Controller;
use App\Models\Category;
use App\Models\Product;
use Illuminate\Support\Facades\Log;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;

class RetailerProductController extends Controller
{
    public function __construct()
    {
        $this->middleware('auth:sanctum');
        $this->middleware('retailer');
    }

    /**
     * Get count of retailer's products
     */
    public function countProducts(Request $request): JsonResponse
    {
        
        $user = $request->user_id;
        $count = Product::where('retailer_id', $user)->count();

        return response()->json([
            'success' => true,
            'count' => $count,
        ]);
    }

    /**
     * Get retailer's products
     */
    public function index(Request $request): JsonResponse
    {



        $products = Product::with(['category', 'images'])
            ->where('retailer_id', $request->user_id)
            ->orderBy('created_at', 'desc')
            ->paginate(10);

        return response()->json([
            'success' => true,
            'data' => $products,
        ]);
    }

    /**
     * Store a new retailer product
     */
    public function store(Request $request): JsonResponse
    {
        $user = Auth::user();

        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'category_id' => 'required|exists:categories,id',
            'description' => 'nullable|string',
            'price' => 'required|numeric|min:0',
            'currency' => 'required|in:GEL,USD',
            'location' => 'required|string|max:255',
            'contact_person' => 'required|string|max:255',
            'contact_phone' => 'required|string|max:20',
            'rental_period_start' => 'nullable|date',
            'rental_period_end' => 'nullable|date|after_or_equal:rental_period_start',
            'color' => 'nullable|string|max:100',
            'size' => 'nullable|string|max:100',
            'brand' => 'nullable|string|max:100',
            'image' => 'nullable|image|mimes:jpeg,png,jpg,gif,webp|max:5120', // 5MB max
        ]);

        // Format rental period as a string if both dates are provided
        $rentalPeriod = null;
        if (!empty($validated['rental_period_start']) && !empty($validated['rental_period_end'])) {
            $startDate = date('Y-m-d', strtotime($validated['rental_period_start']));
            $endDate = date('Y-m-d', strtotime($validated['rental_period_end']));
            $rentalPeriod = $startDate . ' to ' . $endDate;
        }

        // Generate unique product identifier
        $productIdentifyId = 'RTL-'.strtoupper(Str::random(8));

        // Create product
        $product = Product::create([
            'product_identify_id' => $productIdentifyId,
            'category_id' => $validated['category_id'],
            'retailer_id' => $user->id,
            'contact_person' => $validated['contact_person'],
            'contact_phone' => $validated['contact_phone'],
            'price' => $validated['price'],
            'currency' => $validated['currency'],
            'rental_period' => $rentalPeriod,
            'rental_start_date' => !empty($validated['rental_period_start']) ? $validated['rental_period_start'] : null,
            'rental_end_date' => !empty($validated['rental_period_end']) ? $validated['rental_period_end'] : null,
            'size' => $validated['size'] ?? null,
            'status' => 'pending', // Requires admin approval
            'active' => false, // Will be activated upon approval
            'sort_order' => 0,
        ]);

        // Add translatable fields
        $product->translateOrNew('ka')->title = $validated['name'];
        $product->translateOrNew('ka')->description = $validated['description'] ?? '';
        $product->translateOrNew('ka')->location = $validated['location'];
        $product->translateOrNew('ka')->color = $validated['color'] ?? '';
        $product->translateOrNew('ka')->brand = $validated['brand'] ?? '';
        $product->translateOrNew('ka')->slug = Str::slug($validated['name']);

        // Add English translations (same as Georgian for now)
        $product->translateOrNew('en')->title = $validated['name'];
        $product->translateOrNew('en')->description = $validated['description'] ?? '';
        $product->translateOrNew('en')->location = $validated['location'];
        $product->translateOrNew('en')->color = $validated['color'] ?? '';
        $product->translateOrNew('en')->brand = $validated['brand'] ?? '';
        $product->translateOrNew('en')->slug = Str::slug($validated['name']);

        $product->save();

        // Handle image upload
        if ($request->hasFile('image')) {
            $image = $request->file('image');
            $imageName = time().'_'.$image->getClientOriginalName();
            $imagePath = $image->storeAs('products', $imageName, 'public');

            $product->images()->create([
                'image_name' => $imageName,
                'image_path' => $imagePath,
                'is_main' => true,
            ]);
        }

        return response()->json([
            'success' => true,
            'message' => 'Product submitted successfully. It will be reviewed by admin.',
            'data' => $product->load(['category', 'images']),
        ], 201);
    }

    /**
     * Get specific retailer product
     */
    public function show($id): JsonResponse
    {
        $user = Auth::user();

        $product = Product::with(['category', 'images'])
            ->where('retailer_id', $user->id)
            ->findOrFail($id);

        return response()->json([
            'success' => true,
            'data' => $product,
        ]);
    }

    /**
     * Update retailer product
     */
    public function update(Request $request, $id): JsonResponse
    {
        $user = Auth::user();

        $product = Product::where('retailer_id', $user->id)
            ->findOrFail($id);

        // Only allow updates if product is pending or rejected
        if ($product->status === 'approved') {
            return response()->json([
                'success' => false,
                'message' => 'Cannot edit approved products. Please contact admin.',
            ], 403);
        }

        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'category_id' => 'required|exists:categories,id',
            'description' => 'nullable|string',
            'price' => 'required|numeric|min:0',
            'currency' => 'required|in:GEL,USD',
            'location' => 'required|string|max:255',
            'contact_person' => 'required|string|max:255',
            'contact_phone' => 'required|string|max:20',
            'rental_period_start' => 'nullable|date',
            'rental_period_end' => 'nullable|date|after_or_equal:rental_period_start',
            'color' => 'nullable|string|max:100',
            'size' => 'nullable|string|max:100',
            'brand' => 'nullable|string|max:100',
            'image' => 'nullable|image|mimes:jpeg,png,jpg,gif,webp|max:5120',
        ]);

        // Format rental period as a string if both dates are provided
        $rentalPeriod = null;
        if (!empty($validated['rental_period_start']) && !empty($validated['rental_period_end'])) {
            $startDate = date('Y-m-d', strtotime($validated['rental_period_start']));
            $endDate = date('Y-m-d', strtotime($validated['rental_period_end']));
            $rentalPeriod = $startDate . ' to ' . $endDate;
        }

        // Update product
        $product->update([
            'category_id' => $validated['category_id'],
            'contact_person' => $validated['contact_person'],
            'contact_phone' => $validated['contact_phone'],
            'price' => $validated['price'],
            'currency' => $validated['currency'],
            'rental_period' => $rentalPeriod,
            'rental_start_date' => !empty($validated['rental_period_start']) ? $validated['rental_period_start'] : null,
            'rental_end_date' => !empty($validated['rental_period_end']) ? $validated['rental_period_end'] : null,
            'size' => $validated['size'] ?? null,
            'status' => 'pending', // Reset to pending after edit
        ]);

        // Update translations
        $product->translateOrNew('ka')->title = $validated['name'];
        $product->translateOrNew('ka')->description = $validated['description'] ?? '';
        $product->translateOrNew('ka')->location = $validated['location'];
        $product->translateOrNew('ka')->color = $validated['color'] ?? '';
        $product->translateOrNew('ka')->brand = $validated['brand'] ?? '';
        $product->translateOrNew('ka')->slug = Str::slug($validated['name']);

        $product->translateOrNew('en')->title = $validated['name'];
        $product->translateOrNew('en')->description = $validated['description'] ?? '';
        $product->translateOrNew('en')->location = $validated['location'];
        $product->translateOrNew('en')->color = $validated['color'] ?? '';
        $product->translateOrNew('en')->brand = $validated['brand'] ?? '';
        $product->translateOrNew('en')->slug = Str::slug($validated['name']);

        $product->save();

        // Handle new image upload
        if ($request->hasFile('image')) {
            // Delete old images
            foreach ($product->images as $oldImage) {
                Storage::disk('public')->delete($oldImage->image_path);
                $oldImage->delete();
            }

            // Upload new image
            $image = $request->file('image');
            $imageName = time().'_'.$image->getClientOriginalName();
            $imagePath = $image->storeAs('products', $imageName, 'public');

            $product->images()->create([
                'image_name' => $imageName,
                'image_path' => $imagePath,
                'is_main' => true,
            ]);
        }

        return response()->json([
            'success' => true,
            'message' => 'Product updated successfully.',
            'data' => $product->load(['category', 'images']),
        ]);
    }

    /**
     * Delete retailer product
     */
    public function destroy($id): JsonResponse
    {
        try {
            $product = Product::with('images')->findOrFail($id);
    
            // Delete associated images
            foreach ($product->images as $image) {
                if (!empty($image->image_path)) {
                    Storage::disk('public')->delete($image->image_path);
                }
                $image->delete();
            }
    
            $product->delete();
    
            return response()->json([
                'success' => true,
                'message' => 'Product deleted successfully.',
            ]);
    
        } catch (\Exception $e) {
            Log::error('Error deleting product: ' . $e->getMessage());
            return response()->json([
                'success' => false,
                'message' => 'Failed to delete product. ' . $e->getMessage(),
            ], 500);
        }
    }

    /**
     * Get categories for product creation
     */
    public function categories(): JsonResponse
    {
        $categories = Category::with('translations')->get();

        return response()->json([
            'success' => true,
            'data' => $categories,
        ]);
    }
}
