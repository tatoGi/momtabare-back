<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\RetailerShop;
use App\Models\WebUser;
use App\Services\ImageService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;

class RetailerShopController extends Controller
{
    protected ImageService $imageService;

    public function __construct(ImageService $imageService)
    {
        $this->imageService = $imageService;
    }

    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        $shops = RetailerShop::with('user')->latest()->paginate(10);

        return view('admin.retailer-shops.index', compact('shops'));
    }

    /**
     * Show the form for creating a new resource.
     */
    public function create()
    {
        $retailers = WebUser::where('is_retailer', true)->get();

        return view('admin.retailer-shops.create', compact('retailers'));
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {
        $validated = $this->validateShop($request);

        // Handle file uploads - convert to WebP
        if ($request->hasFile('avatar')) {
            $quality = $this->imageService->getOptimalQuality($request->file('avatar'));
            $validated['avatar'] = $this->imageService->uploadAsWebP($request->file('avatar'), 'retailer/avatars', $quality);
        }

        if ($request->hasFile('cover_image')) {
            $quality = $this->imageService->getOptimalQuality($request->file('cover_image'));
            $validated['cover_image'] = $this->imageService->uploadAsWebP($request->file('cover_image'), 'retailer/covers', $quality);
        }

        // Create the shop
        $shop = RetailerShop::create($validated);

        // Save translations
        $locales = config('translatable.locales');
        foreach ($locales as $locale) {
            if ($request->has("name_$locale")) {
                $shop->translateOrNew($locale)->name = $request->input("name_$locale");
                $shop->translateOrNew($locale)->description = $request->input("description_$locale");
            }
        }

        $shop->save();

        return redirect()
            ->route('admin.retailer-shops.index')
            ->with('success', 'Retailer shop created successfully.');
    }

    /**
     * Display the specified resource.
     */
    public function show(RetailerShop $retailerShop)
    {
        $retailerShop->load('user', 'products');

        return view('admin.retailer-shops.show', compact('retailerShop'));
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit(RetailerShop $retailerShop)
    {
        $retailers = WebUser::where('is_retailer', true)->get();
        $shop = $retailerShop;

        return view('admin.retailer-shops.edit', compact('shop', 'retailers'));
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, RetailerShop $retailerShop)
    {
        $validated = $this->validateShop($request, $retailerShop->id);

        // Handle avatar upload - convert to WebP
        if ($request->hasFile('avatar')) {
            $quality = $this->imageService->getOptimalQuality($request->file('avatar'));
            $validated['avatar'] = $this->imageService->updateImage(
                $request->file('avatar'),
                $retailerShop->avatar,
                'retailer/avatars',
                $quality
            );
        }

        // Handle cover image upload - convert to WebP
        if ($request->hasFile('cover_image')) {
            $quality = $this->imageService->getOptimalQuality($request->file('cover_image'));
            $validated['cover_image'] = $this->imageService->updateImage(
                $request->file('cover_image'),
                $retailerShop->cover_image,
                'retailer/covers',
                $quality
            );
        }

        // Update the shop
        $retailerShop->update($validated);

        // Update translations
        $locales = config('translatable.locales');
        foreach ($locales as $locale) {
            if ($request->has("name_$locale")) {
                $retailerShop->translateOrNew($locale)->name = $request->input("name_$locale");
                $retailerShop->translateOrNew($locale)->description = $request->input("description_$locale");
            }
        }

        $retailerShop->save();

        return redirect()
            ->route('admin.retailer-shops.index')
            ->with('success', 'Retailer shop updated successfully.');
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(RetailerShop $retailerShop)
    {
        // Delete associated files
        if ($retailerShop->avatar) {
            Storage::disk('public')->delete($retailerShop->avatar);
        }

        if ($retailerShop->cover_image) {
            Storage::disk('public')->delete($retailerShop->cover_image);
        }

        $retailerShop->delete();

        return redirect()
            ->route('admin.retailer-shops.index')
            ->with('success', 'Retailer shop deleted successfully.');
    }

    /**
     * Validate the shop data.
     */
    protected function validateShop(Request $request, $id = null)
    {
        $rules = [
            'user_id' => ['required', 'exists:web_users,id', 'unique:retailer_shops,user_id,'.$id],
            'avatar' => ['nullable', 'image', 'mimes:jpeg,png,jpg,gif', 'max:5120'], // 5MB max
            'cover_image' => ['nullable', 'image', 'mimes:jpeg,png,jpg,gif', 'max:5120'], // 5MB max
            'location' => ['required', 'string', 'max:255'],
            'contact_person' => ['required', 'string', 'max:255'],
            'contact_phone' => ['required', 'string', 'max:20'],
            'is_active' => ['boolean'],
        ];

        // Add translation rules for each locale
        $locales = config('translatable.locales');
        foreach ($locales as $locale) {
            $rules["name_$locale"] = ['required', 'string', 'max:255'];
            $rules["description_$locale"] = ['nullable', 'string'];
        }

        return $request->validate($rules);
    }
}
