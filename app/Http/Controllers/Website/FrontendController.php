<?php

namespace App\Http\Controllers\Website;

use App\Http\Controllers\Controller;
use App\Mail\WelcomeMail;
use App\Models\Contact;
use App\Models\Language;
use App\Models\Product;
use App\Models\RetailerShop;
use App\Models\Subscriber;
use App\Models\WebUser;
use App\Services\Frontend\FrontendService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\Validator;

class FrontendController extends Controller
{
    protected $frontendService;

    public function __construct(FrontendService $frontendService)
    {
        $this->frontendService = $frontendService;
    }

    /**
     * Store a newly created retailer shop in storage.
     *
     * @return \Illuminate\Http\JsonResponse
     */
    public function storeRetailerShop(Request $request)
    {
        // Validate the request
        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'location' => 'required|string|max:255',
            'contact_person' => 'required|string|max:255',
            'contact_phone' => 'required|string|max:20',
            'avatar' => 'nullable|image|max:5120', // 5MB max
            'cover_image' => 'nullable|image|max:5120', // 5MB max
        ]);

        try {
            // Get the authenticated user using sanctum guard
            $user = Auth::guard('sanctum')->user();

            if (! $user) {
                return response()->json([
                    'success' => false,
                    'message' => 'User not authenticated.',
                ], 401);
            }

            // Handle file uploads
            $avatarPath = null;
            $coverPath = null;

            if ($request->hasFile('avatar')) {
                $avatarPath = $request->file('avatar')->store('retailer-shops/avatars', 'public');
            }

            if ($request->hasFile('cover_image')) {
                $coverPath = $request->file('cover_image')->store('retailer-shops/covers', 'public');
            }

            // Create the retailer shop
            $retailerShop = RetailerShop::create([
                'user_id' => $user->id,
                'avatar' => $avatarPath,
                'cover_image' => $coverPath,
                'location' => $validated['location'],
                'contact_person' => $validated['contact_person'],
                'contact_phone' => $validated['contact_phone'],
                'is_active' => false, // Set to false initially, admin needs to approve
            ]);

            // Create translations
            $locales = config('app.available_locales', ['en', 'ka']);

            foreach ($locales as $locale) {
                $retailerShop->translateOrNew($locale)->name = $validated['name'];
                $retailerShop->translateOrNew($locale)->description = ''; // Add empty description
            }

            $retailerShop->save();

            // Update user's retailer status
            $user->retailer_status = 'pending';
            $user->retailer_requested_at = now();
            $user->save();

            return response()->json([
                'success' => true,
                'message' => 'Retailer shop submitted successfully. Waiting for admin approval.',
                'data' => $retailerShop,
            ]);

        } catch (\Exception $e) {
            // Delete uploaded files if there was an error
            if (isset($avatarPath) && Storage::disk('public')->exists($avatarPath)) {
                Storage::disk('public')->delete($avatarPath);
            }
            if (isset($coverPath) && Storage::disk('public')->exists($coverPath)) {
                Storage::disk('public')->delete($coverPath);
            }

            return response()->json([
                'success' => false,
                'message' => 'Failed to create retailer shop: '.$e->getMessage(),
            ], 500);
        }
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
     * @param  int  $limit  Number of posts to return (default: 10)
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

    /**
     * Sync locale with frontend via headers.
     * Reads X-Language or Accept-Language, resolves to supported locales,
     * sets session and app locale, and returns selection with supported list.
     */
    public function localeSync(Request $request)
    {
        $supported = array_keys(config('app.locales', [config('app.locale')]));
        $fallback = config('app.fallback_locale', 'en');

        $header = $request->header('X-Language') ?: $request->header('Accept-Language');
        $raw = is_string($header) ? trim($header) : '';

        $picked = null;
        $matchedVia = 'default';

        if ($raw !== '') {
            // Take the first language token from Accept-Language if present
            $first = explode(',', $raw)[0] ?? $raw;
            $first = trim(explode(';', $first)[0] ?? $first);
            $first = str_replace('_', '-', $first);

            // Normalize to lower/upper pattern like en or en-US
            $norm = strtolower($first);

            // Try exact match
            if (in_array($norm, $supported, true)) {
                $picked = $norm;
                $matchedVia = 'exact';
            } else {
                // Try by primary subtag (e.g., en-US -> en)
                $primary = substr($norm, 0, 2);
                if ($primary && in_array($primary, $supported, true)) {
                    $picked = $primary;
                    $matchedVia = 'primary';
                }
            }
        }

        if (! $picked) {
            // Use session if already set and supported
            $sessionLocale = session('locale');
            if ($sessionLocale && in_array($sessionLocale, $supported, true)) {
                $picked = $sessionLocale;
                $matchedVia = 'session';
            } else {
                $picked = in_array($fallback, $supported, true) ? $fallback : ($supported[0] ?? 'en');
                $matchedVia = 'default';
            }
        }

        // Persist selection
        session(['locale' => $picked]);
        app()->setLocale($picked);

        // Build supported locales payload with labels if available
        $localesConfig = config('app.locales', []);
        $supportedPayload = [];
        foreach ($supported as $lc) {
            $supportedPayload[] = [
                'code' => $lc,
                'label' => is_array($localesConfig) && array_key_exists($lc, $localesConfig) ? $localesConfig[$lc] : strtoupper($lc),
            ];
        }

        return response()->json([
            'current_locale' => $picked,
            'matched_via' => $matchedVia,
            'supported_locales' => $supportedPayload,
            'received' => [
                'x_language' => $request->header('X-Language'),
                'accept_language' => $request->header('Accept-Language'),
            ],
        ]);
    }

    /**
     * Return languages from Languages CRUD for frontend to display.
     * Only active languages, ordered by sort_order.
     */
    public function languages()
    {
        $items = Language::where('is_active', true)
            ->orderBy('sort_order')
            ->get(['code', 'name', 'native_name', 'is_default', 'is_active', 'sort_order']);

        return response()->json([
            'data' => $items,
        ]);
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
        $contact = new Contact;
        $contact->name = $request->customerName;
        $contact->email = $request->customerEmail;
        $contact->subject = $request->contactSubject;
        $contact->message = $request->contactMessage;
        $contact->save();

        return response()->json(['message' => 'Thank you for your message! We will get back to you soon.'], 200);
    }

    /**
     * Test endpoint to send a Welcome email using configured SMTP (e.g., SendGrid).
     * Body: { email: string, name?: string }
     */
    public function sendWelcomeEmail(Request $request)
    {
        $data = $request->validate([
            'email' => 'required|email',
            'name' => 'nullable|string|max:255',
        ]);

        $user = (object) [
            'email' => $data['email'],
            'name' => $data['name'] ?? null,
        ];

        Mail::to($user->email)->send(new WelcomeMail($user));

        return response()->json([
            'message' => 'Welcome email queued for sending (synchronously sent).',
        ]);
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
     * @return \Illuminate\Http\JsonResponse
     */
    public function products(Request $request)
    {
        $query = Product::with(['category', 'images'])
            ->notBlocked()
            ->where('active', 1)
            ->orderBy('sort_order');

        // Filter by category if provided
        if ($request->has('category_id') && $request->category_id) {
            $query->where('category_id', $request->category_id);
        }

        // Search by title if provided
        if ($request->has('search') && $request->search) {
            $query->whereTranslationLike('title', '%'.$request->search.'%');
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
                'is_favorite' => $product->is_favorite,
                'is_popular' => $product->is_popular,
                'category' => $product->category ? [
                    'id' => $product->category->id,
                    'title' => $product->category->title,
                    'slug' => $product->category->slug,
                ] : null,
                'images' => $product->images->map(function ($image) {
                    return [
                        'id' => $image->id,
                        'url' => asset('storage/products/'.$image->image_name),
                        'alt' => $image->alt_text ?? '',
                    ];
                }),
                'featured_image' => $product->images->first() ? asset('storage/products/'.$product->images->first()->image_name) : null,
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
            ],
        ]);
    }

    /**
     * Get single product details for Vue frontend
     *
     * @param  int  $id
     * @return \Illuminate\Http\JsonResponse
     */
    public function productShow($id)
    {
        $product = Product::with(['category', 'images', 'retailer.products']) // load retailer & their products
            ->where('active', 1)
            ->find($id);

        if (! $product) {
            return response()->json([
                'error' => 'Product not found',
            ], 404);
        }

        $product_owner = $product->retailer;

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
                    'url' => asset('storage/products/'.$image->image_name),
                    'alt' => $image->alt_text ?? '',
                ];
            }),
            'featured_image' => $product->images->first() ? asset('storage/products/'.$product->images->first()->image_name) : null,
            'created_at' => $product->created_at,
            'updated_at' => $product->updated_at,
            'product_owner' => $product_owner ? [
                'id' => $product_owner->id,
                'first_name' => $product_owner->first_name,
                'surname' => $product_owner->surname,
                'email' => $product_owner->email,
                'products_count' => $product_owner->products->count(),
            ] : null,
        ];

        return response()->json([
            'data' => $transformedProduct,
        ]);
    }

    public function userProducts(Request $request)
    {
        $user = WebUser::find($request->user_id);
        $products = $user->products()
            ->with(['category', 'images'])
            ->notBlocked()
            ->get();
        $product_owner = $user->retailer;

        return response()->json([
            'data' => $products,
            'product_owner' => $product_owner,
        ]);
    }

    /**
     * Block/Unblock a product (Admin only)
     */
    public function toggleBlockProduct(Request $request, $productId)
    {
        $product = Product::findOrFail($productId);
        $product->is_blocked = ! $product->is_blocked;
        $product->save();

        return response()->json([
            'success' => true,
            'message' => $product->is_blocked ? 'Product has been blocked' : 'Product has been unblocked',
            'is_blocked' => $product->is_blocked,
        ]);
    }

    /**
     * Mark product as rented/available
     */
    public function toggleRentProduct(Request $request, $productId)
    {
        $request->validate([
            'is_rented' => 'required|boolean',
        ]);

        $product = Product::findOrFail($productId);
        $product->is_rented = $request->is_rented;
        $product->rented_at = $request->is_rented ? now() : null;
        $product->rented_by = $request->is_rented ? Auth::id() : null;
        $product->save();

        return response()->json([
            'success' => true,
            'message' => $request->is_rented ? 'Product marked as rented' : 'Product marked as available',
            'is_rented' => $product->is_rented,
        ]);
    }

    public function retailerShops()
    {
        $retailerShops = RetailerShop::all();

        return response()->json($retailerShops);
    }

    public function retailerShopCount()
    {
        $retailerShops = RetailerShop::count();

        return response()->json($retailerShops);
    }

    public function retailerShopEdit()
    {
        $retailerShop = Auth::user()->retailerShop;
        $countProduct = Auth::user()->products()->count();

        return response()->json([
            'retailerShop' => $retailerShop,
            'countProduct' => $countProduct,
        ]);
    }
}
