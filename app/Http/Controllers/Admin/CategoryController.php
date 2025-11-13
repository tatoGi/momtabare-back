<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Http\Requests\CreateCategoryRequest;
use App\Models\Category;
use App\Services\ImageService;
use Illuminate\Contracts\View\Factory;
use Illuminate\Contracts\View\View;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;

class CategoryController extends Controller
{
    protected ImageService $imageService;

    public function __construct(ImageService $imageService)
    {
        $this->imageService = $imageService;
    }

    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function index(): Factory|View
    {
        $categories = Category::withCount(['products', 'children'])
            ->with(['parent', 'translations'])
            ->orderBy('created_at', 'desc')
            ->paginate(15);

        // Calculate stats
        $totalCategories = Category::count();
        $activeCategories = Category::where('active', true)->count();
        $parentCategories = Category::whereNull('parent_id')->count();
        $totalProducts = \App\Models\Product::count();

        return view('admin.categories.index', compact('categories', 'totalCategories', 'activeCategories', 'parentCategories', 'totalProducts'));
    }

    /**
     * Show the form for creating a new resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function create(): Factory|View
    {
        $categories = Category::all();

        return view('admin.categories.create', compact('categories'));
    }

    /**
     * Store a newly created resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\Response
     */
    public function store(CreateCategoryRequest $request)
    {
        // Retrieve validated data from the request
        $data = $request->validated();

        // Handle icon upload before creating category - convert to WebP
        if ($request->hasFile('icon')) {
            $icon = $request->file('icon');
            $quality = $this->imageService->getOptimalQuality($icon);
            $iconPath = $this->imageService->uploadAsWebP($icon, 'categories', $quality);
            $data['icon'] = $iconPath;
        }

        // Handle main image upload before creating category - convert to WebP
        if ($request->hasFile('image')) {
            $image = $request->file('image');
            $quality = $this->imageService->getOptimalQuality($image);
            $imagePath = $this->imageService->uploadAsWebP($image, 'categories/images', $quality);
            $data['image'] = $imagePath;
        }

        // Create the category
        $category = Category::create($data);

        // Loop through each locale to update SEO data for translations
        foreach (config('app.locales') as $locale) {
            // Retrieve the SEO model for the translation
            $seo = $category->translate($locale)->seo;
            // Prepare SEO data
            $seoData = [
                'title' => $data[$locale]['title'],
                'description' => $data[$locale]['description'],
                'image' => isset($data[$locale]['image']) ? $data[$locale]['image'] : null,
                'author' => isset($data[$locale]['author']) ? $data[$locale]['author'] : null,
                'robots' => isset($data[$locale]['robots']) ? $data[$locale]['robots'] : null,
                // Add more fields here as needed
            ];
            // Update SEO data
            $seo->update($seoData);
        }

        return redirect()->route('categories.index', app()->getLocale());
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
    public function edit($id): Factory|View
    {

        $category = Category::findOrFail($id);
        $categories = Category::where('id', '!=', $category->id)->get();

        return view('admin.categories.edit', compact('category', 'categories'));

    }

    /**
     * Update the specified resource in storage.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function update(Request $request, $id)
    {
        $category = Category::findOrFail($id);
        $data = $request->except(['_token', '_method']);

        $data['active'] = $request->boolean('active');

        // Handle file upload - convert to WebP
        if ($request->hasFile('icon')) {
            $icon = $request->file('icon');
            $quality = $this->imageService->getOptimalQuality($icon);
            $iconPath = $this->imageService->updateImage($icon, $category->icon, 'categories', $quality);
            $data['icon'] = $iconPath;
        }

        // Handle image upload - convert to WebP
        if ($request->hasFile('image')) {
            $image = $request->file('image');
            $quality = $this->imageService->getOptimalQuality($image);
            $imagePath = $this->imageService->updateImage($image, $category->image, 'categories/images', $quality);
            $data['image'] = $imagePath;
        }

        $category->update($data);

        return redirect()->route('categories.index', [app()->getLocale()]);
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function destroy($id)
    {

        $category = Category::findOrFail($id);
        $category->delete();

        return redirect()->route('categories.index', app()->getLocale());
    }

    public function deleteIcon($id)
    {
        $category = Category::findOrFail($id);

        if ($category->icon) {
            // Delete the icon from storage
            Storage::disk('public')->delete($category->icon);

            // Remove the icon from the category
            $category->update(['icon' => null]);
        }

        return response()->json(['success' => 'Files Deleted']);
    }

    public function deleteImage($id)
    {
        $category = Category::findOrFail($id);

        if ($category->image) {
            Storage::disk('public')->delete($category->image);
            $category->update(['image' => null]);
        }

        return response()->json(['success' => 'Files Deleted']);
    }
}
