<?php

namespace App\Http\Controllers\Website;

use App\Http\Controllers\Controller;
use App\Models\Category;
use App\Models\Product;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;

class RetailerProductController extends Controller
{

    /**
     * Get count of retailer's products
     */
    public function countProducts(Request $request): JsonResponse
    {

        $user = $request->user('sanctum');

        $count = Product::where('retailer_id', $user->id)->count();

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
        $user = $request->user('sanctum');

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
            'images' => 'nullable|array|max:10', // Allow up to 10 images
            'images.*' => 'image|mimes:jpeg,png,jpg,gif,webp|max:5120', // 5MB max per image
        ]);

        // Format rental period as a string if both dates are provided
        $rentalPeriod = null;
        if (! empty($validated['rental_period_start']) && ! empty($validated['rental_period_end'])) {
            $startDate = date('Y-m-d', strtotime($validated['rental_period_start']));
            $endDate = date('Y-m-d', strtotime($validated['rental_period_end']));
            $rentalPeriod = $startDate.' to '.$endDate;
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
            'rental_start_date' => ! empty($validated['rental_period_start']) ? $validated['rental_period_start'] : null,
            'rental_end_date' => ! empty($validated['rental_period_end']) ? $validated['rental_period_end'] : null,
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

        // Handle multiple image uploads
        if ($request->hasFile('images')) {
            $images = $request->file('images');
            $isFirstImage = true;

            foreach ($images as $image) {
                $imageName = time().'_'.uniqid().'_'.$image->getClientOriginalName();
                $imagePath = $image->storeAs('products', $imageName, 'public');

                $product->images()->create([
                    'image_name' => $imageName,
                    'image_path' => $imagePath,
                    'is_main' => $isFirstImage, // First image is main
                ]);

                $isFirstImage = false;
            }
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
    public function show(Request $request, $id): JsonResponse
    {
        $user = $request->user('sanctum');

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
        $user = $request->user('sanctum');

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
            'images' => 'nullable|array|max:10', // Allow up to 10 images
            'images.*' => 'image|mimes:jpeg,png,jpg,gif,webp|max:5120', // 5MB max per image
            'remove_image_ids' => 'nullable|array', // IDs of images to remove
            'remove_image_ids.*' => 'integer|exists:product_images,id',
        ]);

        // Format rental period as a string if both dates are provided
        $rentalPeriod = null;
        if (! empty($validated['rental_period_start']) && ! empty($validated['rental_period_end'])) {
            $startDate = date('Y-m-d', strtotime($validated['rental_period_start']));
            $endDate = date('Y-m-d', strtotime($validated['rental_period_end']));
            $rentalPeriod = $startDate.' to '.$endDate;
        }

        // Update product
        $product->update([
            'category_id' => $validated['category_id'],
            'contact_person' => $validated['contact_person'],
            'contact_phone' => $validated['contact_phone'],
            'price' => $validated['price'],
            'currency' => $validated['currency'],
            'rental_period' => $rentalPeriod,
            'rental_start_date' => ! empty($validated['rental_period_start']) ? $validated['rental_period_start'] : null,
            'rental_end_date' => ! empty($validated['rental_period_end']) ? $validated['rental_period_end'] : null,
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

        // Handle image deletion
        if ($request->has('remove_image_ids')) {
            $imagesToRemove = $product->images()->whereIn('id', $validated['remove_image_ids'])->get();

            foreach ($imagesToRemove as $oldImage) {
                Storage::disk('public')->delete($oldImage->image_path);
                $oldImage->delete();
            }
        }

        // Handle new image uploads
        if ($request->hasFile('images')) {
            $images = $request->file('images');
            $existingImagesCount = $product->images()->count();
            $isFirstImage = $existingImagesCount === 0; // Set first image as main if no images exist

            foreach ($images as $image) {
                $imageName = time().'_'.uniqid().'_'.$image->getClientOriginalName();
                $imagePath = $image->storeAs('products', $imageName, 'public');

                $product->images()->create([
                    'image_name' => $imageName,
                    'image_path' => $imagePath,
                    'is_main' => $isFirstImage,
                ]);

                $isFirstImage = false;
            }
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
                if (! empty($image->image_path)) {
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
            Log::error('Error deleting product: '.$e->getMessage());

            return response()->json([
                'success' => false,
                'message' => 'Failed to delete product. '.$e->getMessage(),
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

    /**
     * Add images to existing product
     */
    public function addImages(Request $request, $id): JsonResponse
    {
        $user = $request->user('sanctum');

        $product = Product::where('retailer_id', $user->id)->findOrFail($id);

        $validated = $request->validate([
            'images' => 'required|array|max:10',
            'images.*' => 'image|mimes:jpeg,png,jpg,gif,webp|max:5120',
        ]);

        $uploadedImages = [];
        $existingImagesCount = $product->images()->count();
        $isFirstImage = $existingImagesCount === 0;

        foreach ($request->file('images') as $image) {
            $imageName = time().'_'.uniqid().'_'.$image->getClientOriginalName();
            $imagePath = $image->storeAs('products', $imageName, 'public');

            $productImage = $product->images()->create([
                'image_name' => $imageName,
                'image_path' => $imagePath,
                'is_main' => $isFirstImage,
            ]);

            $uploadedImages[] = $productImage;
            $isFirstImage = false;
        }

        return response()->json([
            'success' => true,
            'message' => 'Images uploaded successfully.',
            'data' => $uploadedImages,
        ]);
    }

    /**
     * Remove image from product
     */
    public function removeImage(Request $request, $productId, $imageId): JsonResponse
    {
        $user = $request->user('sanctum');

        $product = Product::where('retailer_id', $user->id)->findOrFail($productId);
        $image = $product->images()->findOrFail($imageId);

        // Delete the file from storage
        Storage::disk('public')->delete($image->image_path);

        $wasMain = $image->is_main;
        $image->delete();

        // If deleted image was main, set the first remaining image as main
        if ($wasMain) {
            $firstImage = $product->images()->first();
            if ($firstImage) {
                $firstImage->update(['is_main' => true]);
            }
        }

        return response()->json([
            'success' => true,
            'message' => 'Image deleted successfully.',
        ]);
    }

    /**
     * Set image as main/featured image
     */
    public function setMainImage(Request $request, $productId, $imageId): JsonResponse
    {
        $user = $request->user('sanctum');

        $product = Product::where('retailer_id', $user->id)->findOrFail($productId);

        // Set all images to not main
        $product->images()->update(['is_main' => false]);

        // Set the selected image as main
        $image = $product->images()->findOrFail($imageId);
        $image->update(['is_main' => true]);

        return response()->json([
            'success' => true,
            'message' => 'Main image updated successfully.',
            'data' => $image,
        ]);
    }
}
