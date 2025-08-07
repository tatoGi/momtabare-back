<?php

namespace App\Services\Frontend;

use App\Models\Page;
use App\Models\Product;
use App\Models\Section;
use App\Models\Banner;

class FrontendService
{
    /**
     * Get all active pages with their translations
     *
     * @return \Illuminate\Database\Eloquent\Collection
     */
    public function getActivePages()
    {
        return Page::whereHas('translations', function($query) {
            $query->where('active', 1);
        })->with(['translations' => function($query) {
            $query->where('active', 1);
        }])->get();
    }

    /**
     * Get a product by URL with related data
     *
     * @param string $url
     * @return array
     */
    public function getProductByUrl($url)
    {
        $product = Product::whereHas('translations', function ($query) use ($url) {
            $query->where('slug', $url);
        })->with('category', 'images')->first();

        if (!$product) {
            return ['error' => 'Product not found'];
        }

        $relatedProducts = $this->getRelatedProducts($product);
        
        return [
            'product' => $product,
            'seo' => $product->seo,
            'relatedProducts' => $relatedProducts
        ];
    }

    /**
     * Get related products
     *
     * @param Product $product
     * @return \Illuminate\Database\Eloquent\Collection
     */
    protected function getRelatedProducts($product)
    {
        return Product::where('category_id', $product->category_id)
            ->where('id', '!=', $product->id)
            ->with('category')
            ->take(4)
            ->get();
    }

    /**
     * Get section data by slug
     *
     * @param string $slug
     * @return array
     */
    public function getSectionData($slug)
    {
        $section = Page::with('translations')
            ->whereHas('translations', function($query) use ($slug) {
                $query->where('slug', $slug);
            })
            ->firstOrFail();

        $products = Product::where('active', '1')
            ->with('category')
            ->paginate(10);

        $categories = $products->pluck('category')->filter()->unique();
        $categoryIds = $products->pluck('category.id');

        return [
            'section' => $section,
            'categories' => $categories,
            'categoryIds' => $categoryIds,
            'products' => $products,
            'slug' => $slug,
            'seo' => $section->seo,
            'breadcrumbs' => $this->generateBreadcrumbs($section)
        ];
    }

    /**
     * Generate breadcrumbs for a section
     *
     * @param Section $section
     * @return array
     */
    /**
     * Get homepage data
     *
     * @return array
     */
    public function getHomePageData()
    {
        $products = Product::where('active', '1')
            ->with('translation')
            ->with('category')
            ->get();
            
        $mainBanner = Banner::whereHas('translation')
            ->where('type_id', 1)
            ->orderBy('created_at', 'desc')
            ->get();
            
        $staticBanners = Banner::whereHas('translation')
            ->where('type_id', 2)
            ->orderBy('created_at', 'desc')
            ->get();

        return [
            'mainBanner' => $mainBanner,
            'categories' => $products->pluck('category')->unique(),
            'products' => $products,
            'staticBanners' => $staticBanners,
        ];
    }

    /**
     * Generate breadcrumbs for a section
     *
     * @param mixed $section
     * @return array
     */
    protected function generateBreadcrumbs($section)
    {
        return [
            ['url' => '', 'label' => 'Home'],
            ['url' => $section->slug ?? '', 'label' => $section->title ?? '']
        ];
    }
}
