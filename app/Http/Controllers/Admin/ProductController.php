<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Http\Requests\ProductRequest;
use App\Models\Category;
use App\Models\Product;
use App\Models\ProductImage;
use App\Models\Page;
use App\Models\ProductOption;
use Illuminate\Contracts\View\Factory;
use Illuminate\Contracts\View\View;
use Illuminate\Http\Request;
use Illuminate\Support\Str;
use Illuminate\Support\Facades\Storage;
use Illuminate\Http\RedirectResponse;

class ProductController extends Controller
{
    public function __construct()
    {
        $this->middleware('auth');
    }

    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function index(): Factory|View
    {
        $products = Product::orderBy('created_at', 'desc')->with('category')->paginate(5);

        return view('admin.products.index', compact('products'));
    }

    /**
     * Show the form for creating a new resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function create($page_id = null): Factory|View
    {
        $categories = Category::all(); // Optionally, list all categories for selection
        return view('admin.products.create', compact('categories', 'page_id'));
    }

    /**
     * Store a newly created resource in storage.
     *
     * @return \Illuminate\Http\Response
     */
    public function store(ProductRequest $request)
    {
        // Validate the request data
        $data = $request->validated();

        // Handle active status
        $data['active'] = $request->has('active') ? 1 : 0;

        // Create the product
        $product = Product::create($data);

        // Handle product images
        if ($request->hasFile('images')) {
            $firstImage = null;
            foreach ($request->file('images') as $key => $image) {
                $imageName = $image->getClientOriginalName();
                $path = $image->storeAs('products', $imageName, 'public');
                $productImage = new ProductImage;
                $productImage->image_name = 'products/' . $imageName;
                $productImage->product_id = $product->id;
                $productImage->save();

                if ($key === 0) {
                    $firstImage = $imageName;
                }
            }

            foreach (config('app.locales') as $locale) {
                $seo = $product->translate($locale)->seo;
                $seoData = [
                    'title' => $data[$locale]['title'],
                    'description' => $data[$locale]['description'],
                    'image' => $firstImage,
                    'author' => $data[$locale]['author'] ?? null,
                    'robots' => $data[$locale]['robots'] ?? null,
                ];
                $seo->update($seoData);
            }
        }

        return redirect()->route('products.index', app()->getLocale());
    }

    /**
     * Display the specified resource.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function show($id)
    {
        //
    }

    /**
     * Show the form for editing the specified resource.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function edit(string $id): Factory|View
    {
        $product = Product::with('images')->findOrFail($id);
        $categories = Category::all();

        return view('admin.products.edit',
            ['categories' => $categories, 'product' => $product,
            ]);
    }

    /**
     * Update the specified resource in storage.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function update(ProductRequest $request, $id)
    {
        // Validate the request data
        $data = $request->validated();
        $product = Product::findOrFail($id);

        // Handle active status
        $data['active'] = $request->has('active') ? 1 : 0;

        // Format slugs to ensure they're URL-friendly
        foreach (config('app.locales') as $locale) {
            if (!empty($data[$locale]['slug'])) {
                $data[$locale]['slug'] = str_replace(' ', '-', $data[$locale]['slug']);
            }
        }

        // Update the base product attributes
        $product->update([
            'category_id' => $data['category_id'] ?? null,
            'price' => $data['price'],
            'active' => $data['active']
        ]);

        // Update translations for each locale
        foreach (config('app.locales') as $locale) {
            if (isset($data[$locale])) {
                $translation = $product->translateOrNew($locale);
                $translation->title = $data[$locale]['title'] ?? '';
                $translation->slug = $data[$locale]['slug'] ?? '';
                $translation->description = $data[$locale]['description'] ?? '';
                $translation->style = $data[$locale]['style'] ?? null;
                $translation->save();
            }
        }

        // Handle product images
        if ($request->hasFile('images')) {
            foreach ($request->file('images') as $key => $image) {
                $imageName = time() . '_' . $image->getClientOriginalName();
                $path = $image->storeAs('products', $imageName, 'public');
                $productImage = new ProductImage;
                $productImage->image_name = 'products/' . $imageName;
                $productImage->product_id = $product->id;
                $productImage->save();
            }
        }

        return redirect()->route('products.index', app()->getLocale());
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function destroy($id)
    {
        // Find the product
        $product = Product::findOrFail($id);

        // Delete associated SEO data
        if ($product->seo) {
            $product->seo->delete();
        }

        // Delete the product
        $product->delete();

        return redirect()->route('products.index', app()->getLocale());
    }

    public function deleteImage(Request $request, $image_id)
    {
        // Find the image by its ID
        $image = ProductImage::findOrFail($image_id);

        // Delete the image from storage
        Storage::delete('products/'.$image->image_name);

        // Delete the image record from the database
        $image->delete();

        // Return a JSON response with a success message
        return response()->json(['success' => 'Files Deleted']);
    }

    public function cleanupMissingImages()
    {
        $images = ProductImage::all();
        $deletedCount = 0;

        foreach ($images as $image) {
            if (!Storage::disk('public')->exists($image->image_name)) {
                $image->delete();
                $deletedCount++;
            }
        }

        return redirect()->back()->with('success', "Cleaned up {$deletedCount} missing image records.");
    }

    /**
     * Show the form for managing product pages.
     *
     * @param Product $product
     * @return View
     */
    public function managePages(Product $product): View
    {
        // Get all pages that are not already attached to the product
        $availablePages = Page::whereDoesntHave('products', function($query) use ($product) {
            $query->where('product_id', $product->id);
        })->get();

        return view('admin.product.options.index', [
            'product' => $product,
            'availablePages' => $availablePages
        ]);
    }

    /**
     * Attach a page to the product.
     *
     * @param Request $request
     * @param Product $product
     * @return RedirectResponse
     */
    public function attachPage(Request $request, Product $product): RedirectResponse
    {
        $request->validate([
            'page_id' => 'required|exists:pages,id',
            'sort_order' => 'nullable|integer|min:0'
        ]);

        // Check if the page is already attached
        if ($product->pages()->where('page_id', $request->page_id)->exists()) {
            return back()->with('error', 'This page is already attached to the product.');
        }

        // Attach the page with sort order
        $product->pages()->attach($request->page_id, [
            'sort' => $request->sort_order ?? 0
        ]);

        return redirect()->route('admin.products.pages.manage', $product->id)
            ->with('success', 'Page has been added to the product successfully.');
    }

    /**
     * Detach a page from the product.
     *
     * @param Product $product
     * @param int $pageId
     * @return RedirectResponse
     */
    public function detachPage(Product $product, int $pageId): RedirectResponse
    {
        $product->pages()->detach($pageId);

        return redirect()->route('admin.products.pages.manage', $product->id)
            ->with('success', 'Page has been removed from the product.');
    }
}
