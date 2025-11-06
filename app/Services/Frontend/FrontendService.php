<?php

namespace App\Services\Frontend;

use App\Models\Banner;
use App\Models\Category;
use App\Models\Page;
use App\Models\Post;
use App\Models\Product;
use App\Models\Section;

class FrontendService
{
    /**
     * Get all active pages with their translations
     *
     * @param  int  $postsPerPage  Limit posts per page (default: 5)
     * @return \Illuminate\Database\Eloquent\Collection
     */
    public function getActivePages($postsPerPage = 5)
    {
        return Page::whereHas('translations', function ($query) {
            $query->where('active', 1);
        })
            ->with([
                'translations' => function ($query) {
                    $query->where('active', 1);
                },
                'products' => function ($query) {
                    $query->where('active', 1)
                        ->with(['images', 'translations']);
                },
                'banners.images',
                'posts' => function ($query) use ($postsPerPage) {
                    $query->where('active', 1)
                        ->orderBy('sort_order', 'asc')
                        ->orderBy('published_at', 'desc')
                        ->limit($postsPerPage)
                        ->with([
                            'translations',
                            'attributes',
                            'category.translations',
                        ]);
                },
            ])
            ->get()
            ->map(function ($page) {
                // Transform posts to add post type information for homepage
                if ($page->type_id == 1 && $page->posts && $page->posts->count() > 0) { // Homepage
                    $page->posts->transform(function ($post) {
                        // Check if attributes exist and are loaded
                        if ($post->attributes && $post->attributes->count() > 0) {
                            // Get post_type attribute value
                            $postTypeAttr = $post->attributes->where('attribute_key', 'post_type')->first();
                            $postType = $postTypeAttr ? $postTypeAttr->attribute_value : 'join_us';

                            // Add post_type to the post object for frontend identification
                            $post->post_type = $postType;
                        } else {
                            // Default to join_us if no attributes found
                            $post->post_type = 'join_us';
                        }

                        return $post;
                    });
                }

                return $page;
            });
    }

    /**
     * Get all active pages with paginated posts
     *
     * @param  int  $page  Current page number
     * @param  int  $postsPerPage  Posts per page
     * @return array
     */
    public function getActivePagesWithPaginatedPosts($page = 1, $postsPerPage = 10)
    {
        $pages = Page::whereHas('translations', function ($query) {
            $query->where('active', 1);
        })
            ->with([
                'translations' => function ($query) {
                    $query->where('active', 1);
                },
                'products' => function ($query) {
                    $query->where('active', 1)
                        ->with(['images', 'translations']);
                },
                'banners.images',
            ])
            ->get();

        // Use lazy loading for posts to avoid N+1 queries
        $pageIds = $pages->pluck('id')->toArray();

        // Get all posts for all pages in a single query with pagination
        $allPosts = Post::whereIn('page_id', $pageIds)
            ->where('active', 1)
            ->orderBy('sort_order', 'asc')
            ->orderBy('published_at', 'desc')
            ->with([
                'translations',
                'attributes',
                'category.translations',
            ])
            ->get()
            ->groupBy('page_id');

        // Add paginated posts to each page using chunking
        foreach ($pages as $page_item) {
            $pagePosts = $allPosts->get($page_item->id, collect());

            // Manual pagination using chunking
            $totalPosts = $pagePosts->count();
            $offset = ($page - 1) * $postsPerPage;
            $paginatedPosts = $pagePosts->slice($offset, $postsPerPage);
            $lastPage = ceil($totalPosts / $postsPerPage);

            $page_item->paginated_posts = [
                'data' => $paginatedPosts->values(),
                'pagination' => [
                    'current_page' => $page,
                    'last_page' => $lastPage,
                    'per_page' => $postsPerPage,
                    'total' => $totalPosts,
                    'from' => $totalPosts > 0 ? $offset + 1 : null,
                    'to' => min($offset + $postsPerPage, $totalPosts),
                    'has_more_pages' => $page < $lastPage,
                ],
            ];
        }

        return $pages;
    }

    public function getCategories()
    {
        return Category::with([
            'products' => function ($query) {
                $query->where('active', 1)
                    ->with(['images', 'translations']);
            },
            'translations',
            'children' => function ($query) {
                $query->with([
                    'products' => function ($query) {
                        $query->where('active', 1)
                            ->with(['images', 'translations']);
                    },
                    'translations',
                ]);
            },
        ])
            ->get();
    }

    /**
     * Get latest blog posts for homepage
     *
     * @param  int  $limit  Number of posts to return (default: 10)
     * @return array
     */
    public function getLatestBlogPosts($limit = 10)
    {
        $blogPage = Page::where('type_id', 2)
            ->select(['id', 'type_id'])
            ->first();

        if (! $blogPage) {
            return [
                'posts' => collect(),
                'total' => 0,
                'message' => 'Blog page not found',
            ];
        }

        $posts = Post::where('page_id', $blogPage->id)
            ->where('active', 1)
            ->orderBy('published_at', 'desc')
            ->orderBy('created_at', 'desc')
            ->with([
                'translations',
                'attributes',
                'category',
            ])
            ->limit($limit)
            ->get();

        return [
            'posts' => $posts,
            'total' => $posts->count(),
            'blog_page' => $blogPage->only(['id', 'type_id']),
            'message' => 'Latest blog posts retrieved successfully',
        ];
    }

    /**
     * Get a product by URL with related data
     *
     * @param  string  $url
     * @return array
     */
    public function getProductByUrl($url)
    {
        $product = Product::whereHas('translations', function ($query) use ($url) {
            $query->where('slug', $url);
        })->with('category', 'images')->first();

        if (! $product) {
            return ['error' => 'Product not found'];
        }

        $relatedProducts = $this->getRelatedProducts($product);

        return [
            'product' => $product,
            'seo' => $product->seo,
            'relatedProducts' => $relatedProducts,
        ];
    }

    /**
     * Get related products
     *
     * @param  Product  $product
     * @return \Illuminate\Database\Eloquent\Collection
     */
    protected function getRelatedProducts($product)
    {
        return Product::where('category_id', $product->category_id)
            ->where('id', '!=', $product->id)
            ->where('active', 1)
            ->with([
                'category',
                'images',
                'translations',
            ])
            ->take(4)
            ->get();
    }

    /**
     * Get section data by slug
     *
     * @param  string  $slug
     * @return array
     */
    public function getSectionData($slug)
    {
        $section = Page::with('translations')
            ->whereHas('translations', function ($query) use ($slug) {
                $query->where('slug', $slug);
            })
            ->firstOrFail();

        // Use chunking for products to improve memory usage
        $products = Product::where('active', '1')
            ->with([
                'category',
                'translations',
                'images',
            ])
            ->paginate(10);

        $categories = $products->pluck('category')->filter()->unique();
        $categoryIds = $products->pluck('category.id');

        // Optimize blog page query
        $blogpage = Page::where('type_id', 2)
            ->select(['id', 'type_id'])
            ->first();

        $blogPosts = collect();
        if ($blogpage) {
            $blogPosts = Post::where('page_id', $blogpage->id)
                ->where('active', '1')
                ->orderBy('sort_order', 'asc')
                ->orderBy('published_at', 'desc')
                ->with([
                    'translations',
                    'attributes',
                ])
                ->paginate(10);
        }

        return [
            'section' => $section,
            'categories' => $categories,
            'categoryIds' => $categoryIds,
            'products' => $products,
            'blogPosts' => $blogPosts,
            'slug' => $slug,
            'breadcrumbs' => $this->generateBreadcrumbs($section),
        ];
    }

    /**
     * Generate breadcrumbs for a section
     *
     * @param  Section  $section
     * @return array
     */
    /**
     * Get homepage data
     *
     * @param  int  $limit  Limit number of products (default: 50)
     * @return array
     */
    public function getHomePageData($limit = 50)
    {
        // Use chunking and limit for products to improve performance
        $products = Product::where('active', '1')
            ->with([
                'translations',
                'category',
                'images',
            ])
            ->limit($limit)
            ->get();

        // Optimize banner query with lazy loading
        $mainBanner = Banner::whereHas('translations')
            ->where('type_id', 1)
            ->orderBy('created_at', 'desc')
            ->with(['translations', 'images'])
            ->get();

        return [
            'mainBanner' => $mainBanner,
            'categories' => $products->pluck('category')->filter()->unique(),
            'products' => $products,
        ];
    }

    /**
     * Get complete home page data in one call
     * Combines: home page posts, banners, popular products, blog posts, pages list
     *
     * @param int $blogLimit Number of blog posts to return
     * @param int $productsLimit Number of products to return
     * @return array
     */
    public function getCompleteHomePageData($blogLimit = 10, $productsLimit = 50)
    {
        // Get home page (type_id = 1) with posts, banners, and products
        $homePage = Page::where('type_id', 1)
            ->whereHas('translations', function ($query) {
                $query->where('active', 1);
            })
            ->with([
                'translations' => function ($query) {
                    $query->where('active', 1);
                },
                'posts' => function ($query) {
                    $query->where('active', 1)
                        ->orderBy('sort_order', 'asc')
                        ->orderBy('published_at', 'desc')
                        ->with([
                            'translations',
                            'attributes',
                            'category.translations',
                        ]);
                },
                'banners.images',
                'products' => function ($query) {
                    $query->where('active', 1)
                        ->with(['images', 'translations']);
                },
            ])
            ->first();

        // Get popular products with better ordering
        $popularProducts = Product::where('active', '1')
            ->with([
                'translations',
                'category.translations',
                'images',
            ])
            ->orderByDesc('views')
            ->limit($productsLimit)
            ->get();

        // Get latest blog posts
        $blogPosts = $this->getLatestBlogPosts($blogLimit);

        // Get all active pages for navigation
        $pages = Page::whereHas('translations', function ($query) {
                $query->where('active', 1);
            })
            ->with([
                'translations' => function ($query) {
                    $query->where('active', 1);
                },
            ])
            ->orderBy('sort', 'asc')
            ->get();

        return [
            'posts' => $homePage->posts ?? [],
            'banners' => $homePage->banners ?? [],
            'products' => $homePage->products ?? [],
            'popular_products' => $popularProducts,
            'blog_posts' => $blogPosts,
            'pages' => $pages,
        ];
    }

    /**
     * Generate breadcrumbs for a section
     *
     * @param  mixed  $section
     * @return array
     */
    protected function generateBreadcrumbs($section)
    {
        return [
            ['url' => '', 'label' => 'Home'],
            ['url' => $section->slug ?? '', 'label' => $section->title ?? ''],
        ];
    }
}
