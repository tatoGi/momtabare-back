<?php

namespace App\Http\Controllers\Website;

use App\Http\Controllers\Controller;
use App\Services\Frontend\FrontendService;
use App\Models\Contact;
use App\Models\Subscriber;
use App\Models\Basket;
use App\Models\BasketItem;
use App\Models\Product;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;
use App\Mail\ContactFormMail;
use Illuminate\Support\Facades\Mail;

class FrontendController extends Controller
{
    protected $frontendService;

    public function __construct(FrontendService $frontendService)
    {
        $this->frontendService = $frontendService;
    }

    public function homepage()
    {
        $data = $this->frontendService->getHomePageData();
        return response()->json($data);
    }

    public function index($slug = null)
    {
        if (empty($slug)) {
            return $this->homepage();
        }

        $data = $this->frontendService->getSectionData($slug);
        
        return response()->json($data);
    }

    public function pages()
    {
        $pages = $this->frontendService->getActivePages();
      
        return response()->json($pages);
    }

    /**
     * Get latest blog posts for homepage
     *
     * @param int $limit Number of posts to return (default: 10)
     * @return \Illuminate\Http\JsonResponse
     */
    public function latestBlogPosts(Request $request)
    {
        $limit = $request->get('limit', 10);
        $data = $this->frontendService->getLatestBlogPosts($limit);
        
        return response()->json($data);
    }

    /**
     * Get pages with paginated posts
     */
    public function pagesWithPaginatedPosts(Request $request)
    {
        $page = $request->get('page', 1);
        $postsPerPage = $request->get('posts_per_page', 10);
        
        $pages = $this->frontendService->getActivePagesWithPaginatedPosts($page, $postsPerPage);
        return response()->json($pages);
    }

    public function show($url)
    {
        $data = $this->frontendService->getProductByUrl($url);
       
        if (isset($data['error'])) {
            return response()->json($data, 404);
        }
        
        return response()->json($data);
    }

    public function categories()
    {
        $data = $this->frontendService->getCategories();
        return response()->json($data);
    }
    public function submitContactForm(Request $request)
    {
        // Validate the form data
        $validator = Validator::make($request->all(), [
            'customerName' => 'required|string|max:255',
            'customerEmail' => 'required|email|max:255',
            'contactSubject' => 'nullable|string|max:255',
            'contactMessage' => 'required|string',
        ]);

        if ($validator->fails()) {
            return response()->json(['errors' => $validator->errors()], 422);
        }

        // Save the contact form data to the database
        $contact = new Contact();
        $contact->name = $request->customerName;
        $contact->email = $request->customerEmail;
        $contact->subject = $request->contactSubject;
        $contact->message = $request->contactMessage;
        $contact->save();

        return response()->json(['message' => 'Thank you for your message! We will get back to you soon.'], 200);
    }
    public function subscribe(Request $request)
    {
        $request->validate([
            'email' => 'required|email',
        ]);

        $existingSubscriber = Subscriber::where('email', $request->email)->first();

        if ($existingSubscriber) {
            return response()->json(['error' => 'This email is already subscribed!'], 409);
        }

        Subscriber::create(['email' => $request->email]);

        return response()->json(['message' => 'You have subscribed successfully!'], 200);
    }

    /**
     * Get products list for Vue frontend
     *
     * @param Request $request
     * @return \Illuminate\Http\JsonResponse
     */
    public function products(Request $request)
    {
        $query = Product::with(['category', 'images'])
            ->where('active', 1)
            ->orderBy('sort_order');

        // Filter by category if provided
        if ($request->has('category_id') && $request->category_id) {
            $query->where('category_id', $request->category_id);
        }

        // Search by title if provided
        if ($request->has('search') && $request->search) {
            $query->whereTranslationLike('title', '%' . $request->search . '%');
        }

        // Pagination
        $perPage = $request->get('per_page', 12);
        $products = $query->paginate($perPage);

        // Transform the data for Vue frontend
        $transformedProducts = $products->getCollection()->map(function ($product) {
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
                'category' => $product->category ? [
                    'id' => $product->category->id,
                    'title' => $product->category->title,
                    'slug' => $product->category->slug,
                ] : null,
                'images' => $product->images->map(function ($image) {
                    return [
                        'id' => $image->id,
                        'url' => asset('storage/' . $image->image_name),
                        'alt' => $image->alt_text ?? '',
                    ];
                }),
                'featured_image' => $product->images->first() ? asset('storage/' . $product->images->first()->image_name) : null,
            ];
        });

        return response()->json([
            'data' => $transformedProducts,
            'pagination' => [
                'current_page' => $products->currentPage(),
                'last_page' => $products->lastPage(),
                'per_page' => $products->perPage(),
                'total' => $products->total(),
                'from' => $products->firstItem(),
                'to' => $products->lastItem(),
            ]
        ]);
    }

    /**
     * Get single product details for Vue frontend
     *
     * @param int $id
     * @return \Illuminate\Http\JsonResponse
     */
    public function productShow($id)
    {
        $product = Product::with(['category', 'images'])
            ->where('active', 1)
            ->find($id);

        if (!$product) {
            return response()->json([
                'error' => 'Product not found'
            ], 404);
        }

        // Transform the product data
        $transformedProduct = [
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
            'sort_order' => $product->sort_order,
            'category' => $product->category ? [
                'id' => $product->category->id,
                'title' => $product->category->title,
                'slug' => $product->category->slug,
                'description' => $product->category->description,
            ] : null,
            'images' => $product->images->map(function ($image) {
                return [
                    'id' => $image->id,
                    'url' => asset('storage/' . $image->image_name),
                    'alt' => $image->alt_text ?? '',
                ];
            }),
            'featured_image' => $product->images->first() ? asset('storage/' . $product->images->first()->image_name) : null,
            'created_at' => $product->created_at,
            'updated_at' => $product->updated_at,
        ];

        return response()->json([
            'data' => $transformedProduct
        ]);
    }

}
