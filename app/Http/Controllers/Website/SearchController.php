<?php

namespace App\Http\Controllers\Website;

use App\Http\Controllers\Controller;
use App\Models\Category;
use App\Models\CategoryTranslation;
use App\Models\Product;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Str;

class SearchController extends Controller
{
    public function search(Request $request)
    {
        $searchText = $request->input('search', '');
        $startDate = $request->input('start_date');
        $endDate = $request->input('end_date');
        $location = $request->input('location');
        $perPage = $request->input('per_page', 10);
        $page = $request->input('page', 1);
        $locale = $request->header('X-Localization', app()->getLocale());

        // Base query for available products with translations
        $products = Product::query()
            ->available() // Only get available products (not blocked, not rented, active)
            ->with(['translations' => function ($query) use ($locale) {
                $query->where('locale', $locale);
            }, 'images', 'category.translations' => function ($query) use ($locale) {
                $query->where('locale', $locale);
            }]);

        // Filter by search text if provided
        if (! empty($searchText)) {
            $products->whereHas('translations', function ($query) use ($searchText, $locale) {
                $query->where('locale', $locale)
                    ->where(function ($q) use ($searchText) {
                        $q->where('title', 'LIKE', "%{$searchText}%")
                            ->orWhere('description', 'LIKE', "%{$searchText}%")
                            // Brand is stored in local_additional JSON field
                            ->orWhere('local_additional', 'LIKE', "%{$searchText}%");
                    });
            });
        }

        // Filter by location if provided
        if (! empty($location)) {
            $products->whereHas('translations', function ($query) use ($location, $locale) {
                $query->where('locale', $locale)
                    ->where('location', 'LIKE', "%{$location}%");
            });
        }

        // Filter by date range availability if both dates are provided
        if ($startDate && $endDate) {
            $startDateCarbon = Carbon::parse($startDate);
            $endDateCarbon = Carbon::parse($endDate);

            $products->where(function ($query) use ($startDateCarbon, $endDateCarbon) {
                // Check if product's rental period is available for the requested dates
                $query->where(function ($q) use ($startDateCarbon, $endDateCarbon) {
                    // Product is available if:
                    // 1. No rental dates set (always available)
                    $q->where(function ($q1) {
                        $q1->whereNull('rental_start_date')
                            ->whereNull('rental_end_date');
                    })
                        // 2. Or if rental period doesn't overlap with requested dates
                        ->orWhere(function ($q2) use ($startDateCarbon, $endDateCarbon) {
                            $q2->where('rental_start_date', '>', $endDateCarbon)
                                ->orWhere('rental_end_date', '<', $startDateCarbon);
                        });
                });
            });
        }

        // Order by created_at desc for better user experience
        $products->orderBy('created_at', 'desc');

        // Get paginated results
        $products = $products->paginate($perPage, ['*'], 'page', $page);

        // Prepare product data for the API response
        $productData = $products->map(function ($product) use ($locale) {
            $translation = $product->translate($locale);
            $categoryTranslation = $product->category ? $product->category->translate($locale) : null;
            $firstImage = $product->images()->first();

            return [
                'id' => $product->id,
                'slug' => $translation->slug ?? '#',
                'title' => $translation->title,
                'description' => Str::limit(strip_tags($translation->description ?? ''), 150),
                'brand' => $translation->local_additional['ბრენდი'] ?? $translation->local_additional['brand'] ?? null,
                'price' => $product->price,
                'currency' => $product->currency,
                'image' => $firstImage ? asset("storage/{$firstImage->image_name}") : null,
                'location' => $translation->location,
                'size' => $translation->local_additional['ზომა'] ?? $translation->local_additional['size'] ?? null,
                'local_additional' => $translation->local_additional ?? [],
                'category' => $categoryTranslation ? [
                    'id' => $product->category->id,
                    'title' => $categoryTranslation->title,
                    'slug' => $categoryTranslation->slug,
                ] : null,
                'rental_period' => $product->rental_period,
                'rental_start_date' => $product->rental_start_date,
                'rental_end_date' => $product->rental_end_date,
                'is_favorite' => $product->is_favorite,
                'is_popular' => $product->is_popular,
                'average_rating' => $product->average_rating,
                'total_comments' => $product->total_comments,
                'created_at' => $product->created_at,
                'updated_at' => $product->updated_at,
            ];
        });

        // Search in categories if search text is provided
        $categoryData = collect([]);
        if (! empty($searchText)) {
            $categoryTranslationIds = CategoryTranslation::whereLocale($locale)
                ->where('title', 'LIKE', "%{$searchText}%")
                ->pluck('category_id')
                ->toArray();

            $categories = Category::whereIn('id', $categoryTranslationIds)
                ->with(['translations' => function ($query) use ($locale) {
                    $query->where('locale', $locale);
                }])
                ->get();

            $categoryData = $categories->map(function ($category) use ($locale) {
                $translation = $category->translate($locale);

                return [
                    'id' => $category->id,
                    'slug' => $translation->slug ?? '#',
                    'title' => $translation->title,
                    'description' => Str::limit(strip_tags($translation->description ?? ''), 100),
                    'image' => $category->getFirstMediaUrl('featured_image'),
                ];
            });
        }

        // Prepare the API response
        return response()->json([
            'success' => true,
            'data' => [
                'products' => $productData,
                'categories' => $categoryData,
                'pagination' => [
                    'total' => $products->total(),
                    'per_page' => $products->perPage(),
                    'current_page' => $products->currentPage(),
                    'last_page' => $products->lastPage(),
                    'from' => $products->firstItem(),
                    'to' => $products->lastItem(),
                ],
                'filters' => [
                    'search' => $searchText,
                    'start_date' => $startDate,
                    'end_date' => $endDate,
                    'location' => $location,
                ],
            ],
        ]);
    }
}
