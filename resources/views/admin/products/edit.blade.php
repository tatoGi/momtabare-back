<x-admin.admin-layout>
    <!-- Header Section -->
    <div class="mb-6">
        <div class="flex items-center justify-between">
            <div>
                <h1 class="text-3xl font-bold text-gray-800 flex items-center gap-3">
                    <svg class="w-8 h-8 text-blue-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z"></path>
                    </svg>
                    Edit Product
                </h1>
                <p class="text-gray-600 mt-2">Update product details with multilingual support</p>
            </div>
            <a href="{{ route('products.index', app()->getlocale()) }}"
               class="inline-flex items-center px-4 py-2 bg-gray-600 hover:bg-gray-700 text-white font-semibold rounded-lg shadow-md transition duration-300 ease-in-out transform hover:scale-105">
                <svg class="w-5 h-5 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 19l-7-7m0 0l7-7m-7 7h18"></path>
                </svg>
                Back to List
            </a>
        </div>
    </div>

    <!-- Error Display -->
    @if ($errors->any())
        <div class="mb-6 bg-red-50 border-l-4 border-red-500 rounded-lg p-4 shadow-md">
            <div class="flex items-start">
                <svg class="w-6 h-6 text-red-500 mr-3 flex-shrink-0" fill="currentColor" viewBox="0 0 20 20">
                    <path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zM8.707 7.293a1 1 0 00-1.414 1.414L8.586 10l-1.293 1.293a1 1 0 101.414 1.414L10 11.414l1.293 1.293a1 1 0 001.414-1.414L11.414 10l1.293-1.293a1 1 0 00-1.414-1.414L10 8.586 8.707 7.293z" clip-rule="evenodd"></path>
                </svg>
                <div>
                    <h3 class="text-red-800 font-semibold mb-2">Please fix the following errors:</h3>
                    <ul class="list-disc list-inside text-red-700 space-y-1">
                        @foreach ($errors->all() as $error)
                            <li>{{ $error }}</li>
                        @endforeach
                    </ul>
                </div>
            </div>
        </div>
    @endif

    <form id="productForm" action="{{ route('products.update', [app()->getlocale(), $product->id]) }}" method="POST" enctype="multipart/form-data">
        @csrf
        @method('PUT')
        @if(request()->has('page_id'))
            <input type="hidden" name="page_id" value="{{ request()->page_id }}">
        @endif

        <div class="grid grid-cols-1 lg:grid-cols-3 gap-6">
            <!-- Main Content Area (Left Side - 2/3) -->
            <div class="lg:col-span-2 space-y-6">

                <!-- Language Tabs Card -->
                <div class="bg-white rounded-xl shadow-lg overflow-hidden border border-gray-200">
                    <div class="bg-gradient-to-r from-blue-500 to-blue-600 px-6 py-4">
                        <h2 class="text-xl font-bold text-white flex items-center gap-2">
                            <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 5h12M9 3v2m1.048 9.5A18.022 18.022 0 016.412 9m6.088 9h7M11 21l5-10 5 10M12.751 5C11.783 10.77 8.07 15.61 3 18.129"></path>
                            </svg>
                            Product Details (Translations)
                        </h2>
                    </div>

                    <div class="p-6">
                        <!-- Language Selector -->
                        <div class="flex flex-wrap gap-4 mb-6 border-b pb-4 items-center">
                            <div class="flex gap-3">
                                @foreach (config('app.locales') as $locale)
                                    <button type="button"
                                            class="language-tab flex items-center gap-2 px-4 py-2 rounded-lg font-semibold transition-all duration-300
                                                   {{ $locale === app()->getLocale() ? 'bg-gradient-to-r from-blue-500 to-blue-600 text-white shadow-md' : 'bg-gray-100 text-gray-700 hover:bg-gray-200' }}"
                                            data-locale="{{ $locale }}">
                                        <img src="{{ $locale === 'en' ? asset('storage/flags/united-states.png') : asset('storage/flags/georgia.png') }}"
                                             alt="{{ $locale }}"
                                             class="w-5 h-5 rounded-full border-2 border-white shadow-sm">
                                        <span>{{ __('admin.locale_' . $locale) }}</span>
                                    </button>
                                @endforeach
                            </div>
                            <button type="button"
                                    id="auto-translate-btn"
                                    class="ml-auto flex items-center gap-2 px-4 py-2 rounded-lg font-semibold transition-all duration-300 bg-gradient-to-r from-purple-500 to-purple-600 text-white shadow-md hover:from-purple-600 hover:to-purple-700">
                                <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 5h12M9 3v2m1.048 9.5A18.022 18.022 0 016.412 9m6.088 9h7M11 21l5-10 5 10M12.751 5C11.783 10.77 8.07 15.61 3 18.129"></path>
                                </svg>
                                <span>Auto Translate</span>
                            </button>
                        </div>

                        <!-- Language Content -->
                        @foreach (config('app.locales') as $locale)
                            <div id="locale-{{ $locale }}" class="locale-content {{ $locale !== app()->getLocale() ? 'hidden' : '' }} space-y-4">

                                <!-- Title Field -->
                                <div class="form-group">
                                    <label for="title_{{ $locale }}" class="block text-sm font-semibold text-gray-700 mb-2">
                                        <span class="text-red-500">*</span> Title ({{ __('admin.locale_' . $locale) }})
                                    </label>
                                    <div class="relative">
                                        <input type="text"
                                               name="{{ $locale }}[title]"
                                               id="title_{{ $locale }}"
                                               value="{{ old($locale . '.title', $product->translate($locale)->title ?? '') }}"
                                               class="w-full px-4 py-3 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-transparent transition-all @error('title_' . $locale) border-red-500 @enderror"
                                               placeholder="Enter product title">
                                        <svg class="absolute right-3 top-3.5 w-5 h-5 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M7 7h.01M7 3h5c.512 0 1.024.195 1.414.586l7 7a2 2 0 010 2.828l-7 7a2 2 0 01-2.828 0l-7-7A1.994 1.994 0 013 12V7a4 4 0 014-4z"></path>
                                        </svg>
                                    </div>
                                    @error('title_' . $locale)
                                        <p class="text-red-500 text-sm mt-1 flex items-center gap-1">
                                            <svg class="w-4 h-4" fill="currentColor" viewBox="0 0 20 20">
                                                <path fill-rule="evenodd" d="M18 10a8 8 0 11-16 0 8 8 0 0116 0zm-7 4a1 1 0 11-2 0 1 1 0 012 0zm-1-9a1 1 0 00-1 1v4a1 1 0 102 0V6a1 1 0 00-1-1z" clip-rule="evenodd"></path>
                                            </svg>
                                            {{ $message }}
                                        </p>
                                    @enderror
                                </div>

                                <!-- Slug Field -->
                                <div class="form-group">
                                    <label for="slug_{{ $locale }}" class="block text-sm font-semibold text-gray-700 mb-2">
                                        <span class="text-red-500">*</span> URL Keyword ({{ __('admin.locale_' . $locale) }})
                                    </label>
                                    <div class="relative">
                                        <input type="text"
                                               name="{{ $locale }}[slug]"
                                               id="slug_{{ $locale }}"
                                               value="{{ old($locale . '.slug', $product->translate($locale)->slug ?? '') }}"
                                               class="w-full px-4 py-3 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-transparent transition-all @error('slug_' . $locale) border-red-500 @enderror"
                                               placeholder="product-url-keyword">
                                        <svg class="absolute right-3 top-3.5 w-5 h-5 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13.828 10.172a4 4 0 00-5.656 0l-4 4a4 4 0 105.656 5.656l1.102-1.101m-.758-4.899a4 4 0 005.656 0l4-4a4 4 0 00-5.656-5.656l-1.1 1.1"></path>
                                        </svg>
                                    </div>
                                    @error('slug_' . $locale)
                                        <p class="text-red-500 text-sm mt-1 flex items-center gap-1">
                                            <svg class="w-4 h-4" fill="currentColor" viewBox="0 0 20 20">
                                                <path fill-rule="evenodd" d="M18 10a8 8 0 11-16 0 8 8 0 0116 0zm-7 4a1 1 0 11-2 0 1 1 0 012 0zm-1-9a1 1 0 00-1 1v4a1 1 0 102 0V6a1 1 0 00-1-1z" clip-rule="evenodd"></path>
                                            </svg>
                                            {{ $message }}
                                        </p>
                                    @enderror
                                </div>

                                <!-- Description Field -->
                                <div class="form-group">
                                    <label for="description_{{ $locale }}" class="block text-sm font-semibold text-gray-700 mb-2">
                                        Description ({{ __('admin.locale_' . $locale) }})
                                    </label>
                                    <textarea id="description_{{ $locale }}"
                                              name="{{ $locale }}[description]"
                                              class="w-full px-4 py-3 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-transparent transition-all ckeditor @error('description_' . $locale) border-red-500 @enderror"
                                              rows="6"
                                              placeholder="Enter product description">{{ old($locale . '.description', $product->translate($locale)->description ?? '') }}</textarea>
                                    @error('description_' . $locale)
                                        <p class="text-red-500 text-sm mt-1 flex items-center gap-1">
                                            <svg class="w-4 h-4" fill="currentColor" viewBox="0 0 20 20">
                                                <path fill-rule="evenodd" d="M18 10a8 8 0 11-16 0 8 8 0 0116 0zm-7 4a1 1 0 11-2 0 1 1 0 012 0zm-1-9a1 1 0 00-1 1v4a1 1 0 102 0V6a1 1 0 00-1-1z" clip-rule="evenodd"></path>
                                            </svg>
                                            {{ $message }}
                                        </p>
                                    @enderror
                                </div>

                                <!-- Brand Field -->
                                <div class="form-group">
                                    <label for="brand_{{ $locale }}" class="block text-sm font-semibold text-gray-700 mb-2">
                                        Brand ({{ __('admin.locale_' . $locale) }})
                                    </label>
                                    @php
                                        $translation = $product->translate($locale);
                                        $brandValue = old($locale . '.brand', $translation->local_additional['ბრენდი'] ?? $translation->local_additional['brand'] ?? '');
                                    @endphp
                                    <input type="text"
                                           name="{{ $locale }}[brand]"
                                           id="brand_{{ $locale }}"
                                           value="{{ $brandValue }}"
                                           class="w-full px-4 py-3 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-transparent transition-all"
                                           placeholder="e.g., Nike, Adidas">
                                </div>

                                <!-- Color Field -->
                                <div class="form-group">
                                    <label for="color_{{ $locale }}" class="block text-sm font-semibold text-gray-700 mb-2">
                                        Color ({{ __('admin.locale_' . $locale) }})
                                    </label>
                                    @php
                                        $colorValue = old($locale . '.color', $translation->local_additional['ფერი'] ?? $translation->local_additional['color'] ?? '');
                                    @endphp
                                    <input type="text"
                                           name="{{ $locale }}[color]"
                                           id="color_{{ $locale }}"
                                           value="{{ $colorValue }}"
                                           class="w-full px-4 py-3 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-transparent transition-all"
                                           placeholder="e.g., Red, Blue, Black">
                                </div>

                                <!-- Location Field -->
                                <div class="form-group">
                                    <label for="location_{{ $locale }}" class="block text-sm font-semibold text-gray-700 mb-2">
                                        Location ({{ __('admin.locale_' . $locale) }})
                                    </label>
                                    <input type="text"
                                           name="{{ $locale }}[location]"
                                           id="location_{{ $locale }}"
                                           value="{{ old($locale . '.location', $product->translate($locale)->location ?? '') }}"
                                           class="w-full px-4 py-3 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-transparent transition-all"
                                           placeholder="e.g., Warehouse A, Shelf 12">
                                </div>

                                <!-- Style Field -->
                                <div class="form-group">
                                    <label for="style_{{ $locale }}" class="block text-sm font-semibold text-gray-700 mb-2">
                                        Style ({{ __('admin.locale_' . $locale) }})
                                    </label>
                                    <input type="text"
                                           name="{{ $locale }}[style]"
                                           id="style_{{ $locale }}"
                                           value="{{ old($locale . '.style', $product->translate($locale)->style ?? '') }}"
                                           class="w-full px-4 py-3 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-transparent transition-all"
                                           placeholder="e.g., Modern, Classic, Vintage">
                                </div>

                            </div>
                        @endforeach
                    </div>
                </div>

                <!-- Product Images Card -->
                <div class="bg-white rounded-xl shadow-lg overflow-hidden border border-gray-200">
                    <div class="bg-gradient-to-r from-purple-500 to-purple-600 px-6 py-4">
                        <h2 class="text-xl font-bold text-white flex items-center gap-2">
                            <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 16l4.586-4.586a2 2 0 012.828 0L16 16m-2-2l1.586-1.586a2 2 0 012.828 0L20 14m-6-6h.01M6 20h12a2 2 0 002-2V6a2 2 0 00-2-2H6a2 2 0 00-2 2v12a2 2 0 002 2z"></path>
                            </svg>
                            Product Images
                        </h2>
                    </div>
                    <div class="p-6">
                        <!-- Existing Images -->
                        @if($product->images && $product->images->count() > 0)
                            <div class="mb-6">
                                <h3 class="text-sm font-semibold text-gray-700 mb-3">Current Images</h3>
                                <div class="grid grid-cols-2 md:grid-cols-4 gap-4">
                                    @foreach($product->images as $image)
                                        <div class="relative group">
                                            <img src="{{ asset('storage/products/' . $image->image_name) }}"
                                                 alt="Product Image"
                                                 class="w-full h-32 object-cover rounded-lg shadow-md border-2 border-purple-200">
                                            <button type="button"
                                                    onclick="deleteImage({{ $image->id }})"
                                                    class="absolute top-2 right-2 bg-red-600 hover:bg-red-700 text-white p-2 rounded-full shadow-lg opacity-0 group-hover:opacity-100 transition-opacity">
                                                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"></path>
                                                </svg>
                                            </button>
                                        </div>
                                    @endforeach
                                </div>
                            </div>
                        @endif

                        <!-- Upload New Images -->
                        <div class="flex flex-col items-center">
                            <label class="w-full flex flex-col items-center px-6 py-8 bg-gradient-to-br from-purple-50 to-blue-50 border-2 border-dashed border-purple-300 rounded-xl cursor-pointer hover:border-purple-500 hover:bg-purple-50 transition-all duration-300">
                                <svg class="w-12 h-12 text-purple-500 mb-3" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M7 16a4 4 0 01-.88-7.903A5 5 0 1115.9 6L16 6a5 5 0 011 9.9M15 13l-3-3m0 0l-3 3m3-3v12"></path>
                                </svg>
                                <span class="text-sm font-semibold text-gray-700 mb-1">Upload New Images</span>
                                <span class="text-xs text-gray-500">PNG, JPG, GIF up to 10MB (Multiple files)</span>
                                <input type="file"
                                       name="images[]"
                                       id="images"
                                       multiple
                                       accept="image/*"
                                       class="hidden"
                                       onchange="previewImages(event)" />
                            </label>
                            <div id="image-preview" class="mt-4 w-full grid grid-cols-3 gap-4"></div>
                        </div>
                    </div>
                </div>

            </div>

            <!-- Sidebar (Right Side - 1/3) -->
            <div class="lg:col-span-1 space-y-6">

                <!-- Category Card -->
                <div class="bg-white rounded-xl shadow-lg overflow-hidden border border-gray-200">
                    <div class="bg-gradient-to-r from-green-500 to-green-600 px-6 py-4">
                        <h3 class="text-lg font-bold text-white flex items-center gap-2">
                            <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 7v10a2 2 0 002 2h14a2 2 0 002-2V9a2 2 0 00-2-2h-6l-2-2H5a2 2 0 00-2 2z"></path>
                            </svg>
                            Category
                        </h3>
                    </div>
                    <div class="p-6">
                        <label for="category_id" class="block text-sm font-semibold text-gray-700 mb-2">
                            <span class="text-red-500">*</span> Select Category
                        </label>
                        <div class="relative">
                            <select id="category_id"
                                    name="category_id"
                                    required
                                    class="w-full px-4 py-3 border border-gray-300 rounded-lg focus:ring-2 focus:ring-green-500 focus:border-transparent appearance-none transition-all @error('category_id') border-red-500 @enderror">
                                <option value="">Choose a Category</option>
                                @foreach ($categories as $category)
                                    <option value="{{ $category->id }}" {{ old('category_id', $product->category_id) == $category->id ? 'selected' : '' }}>
                                        {{ $category->title ?? '' }}
                                    </option>
                                @endforeach
                            </select>
                            <svg class="absolute right-3 top-3.5 w-5 h-5 text-gray-400 pointer-events-none" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7"></path>
                            </svg>
                        </div>
                        @error('category_id')
                            <p class="text-red-500 text-sm mt-1">{{ $message }}</p>
                        @enderror
                    </div>
                </div>

                <!-- Product Details Card -->
                <div class="bg-white rounded-xl shadow-lg overflow-hidden border border-gray-200">
                    <div class="bg-gradient-to-r from-orange-500 to-orange-600 px-6 py-4">
                        <h3 class="text-lg font-bold text-white flex items-center gap-2">
                            <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2"></path>
                            </svg>
                            Product Details
                        </h3>
                    </div>
                    <div class="p-6 space-y-4">
                        <div>
                            <label for="product_identify_id" class="block text-sm font-semibold text-gray-700 mb-2">
                                Product ID
                            </label>
                            <input type="text"
                                   name="product_identify_id"
                                   id="product_identify_id"
                                   value="{{ old('product_identify_id', $product->product_identify_id) }}"
                                   class="w-full px-4 py-3 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-transparent transition-all">
                        </div>

                        <div>
                            <label for="size" class="block text-sm font-semibold text-gray-700 mb-2">Size</label>
                            @php
                                // Get size from first available translation's local_additional
                                $sizeValue = old('size');
                                if (!$sizeValue) {
                                    foreach (config('app.locales') as $loc) {
                                        $trans = $product->translate($loc);
                                        if ($trans && isset($trans->local_additional['ზომა'])) {
                                            $sizeValue = $trans->local_additional['ზომა'];
                                            break;
                                        } elseif ($trans && isset($trans->local_additional['size'])) {
                                            $sizeValue = $trans->local_additional['size'];
                                            break;
                                        }
                                    }
                                }
                            @endphp
                            <input type="text"
                                   name="size"
                                   id="size"
                                   value="{{ $sizeValue }}"
                                   class="w-full px-4 py-3 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-transparent transition-all">
                        </div>

                        <div>
                            <label for="price" class="block text-sm font-semibold text-gray-700 mb-2">
                                <span class="text-red-500">*</span> Price
                            </label>
                            <input type="text"
                                   name="price"
                                   id="price"
                                   value="{{ old('price', $product->price) }}"
                                   class="w-full px-4 py-3 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-transparent transition-all @error('price') border-red-500 @enderror">
                            @error('price')
                                <p class="text-red-500 text-sm mt-1">{{ $message }}</p>
                            @enderror
                        </div>

                        <div>
                            <label for="currency" class="block text-sm font-semibold text-gray-700 mb-2">
                                <span class="text-red-500">*</span> Currency
                            </label>
                            <select name="currency"
                                    id="currency"
                                    class="w-full px-4 py-3 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-transparent transition-all">
                                <option value="">Select Currency</option>
                                <option value="GEL" {{ old('currency', $product->currency) == 'GEL' ? 'selected' : '' }}>GEL (₾)</option>
                                <option value="USD" {{ old('currency', $product->currency) == 'USD' ? 'selected' : '' }}>USD ($)</option>
                                <option value="EUR" {{ old('currency', $product->currency) == 'EUR' ? 'selected' : '' }}>EUR (€)</option>
                            </select>
                        </div>

                        <div>
                            <label for="sort_order" class="block text-sm font-semibold text-gray-700 mb-2">Sort Order</label>
                            <input type="number"
                                   name="sort_order"
                                   id="sort_order"
                                   value="{{ old('sort_order', $product->sort_order ?? 0) }}"
                                   min="0"
                                   class="w-full px-4 py-3 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-transparent transition-all">
                        </div>
                    </div>
                </div>

                <!-- Contact Information Card -->
                <div class="bg-white rounded-xl shadow-lg overflow-hidden border border-gray-200">
                    <div class="bg-gradient-to-r from-indigo-500 to-indigo-600 px-6 py-4">
                        <h3 class="text-lg font-bold text-white flex items-center gap-2">
                            <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z"></path>
                            </svg>
                            Contact Information
                        </h3>
                    </div>
                    <div class="p-6 space-y-4">
                        <div>
                            <label for="contact_person" class="block text-sm font-semibold text-gray-700 mb-2">
                                <span class="text-red-500">*</span> Contact Person
                            </label>
                            <input type="text"
                                   name="contact_person"
                                   id="contact_person"
                                   value="{{ old('contact_person', $product->contact_person) }}"
                                   class="w-full px-4 py-3 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-transparent transition-all">
                        </div>
                        <div>
                            <label for="contact_phone" class="block text-sm font-semibold text-gray-700 mb-2">
                                <span class="text-red-500">*</span> Contact Phone
                            </label>
                            <input type="text"
                                   name="contact_phone"
                                   id="contact_phone"
                                   value="{{ old('contact_phone', $product->contact_phone) }}"
                                   class="w-full px-4 py-3 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-transparent transition-all">
                        </div>
                    </div>
                </div>

                <!-- Rental Dates Card -->
                <div class="bg-white rounded-xl shadow-lg overflow-hidden border border-gray-200">
                    <div class="bg-gradient-to-r from-teal-500 to-teal-600 px-6 py-4">
                        <h3 class="text-lg font-bold text-white flex items-center gap-2">
                            <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z"></path>
                            </svg>
                            Rental Period
                        </h3>
                    </div>
                    <div class="p-6 space-y-4">
                        <div>
                            <label for="rental_start_date" class="block text-sm font-semibold text-gray-700 mb-2">Start Date</label>
                            <input type="date"
                                   name="rental_start_date"
                                   id="rental_start_date"
                                   value="{{ old('rental_start_date', $product->rental_start_date?->format('Y-m-d')) }}"
                                   class="w-full px-4 py-3 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-transparent transition-all">
                        </div>
                        <div>
                            <label for="rental_end_date" class="block text-sm font-semibold text-gray-700 mb-2">End Date</label>
                            <input type="date"
                                   name="rental_end_date"
                                   id="rental_end_date"
                                   value="{{ old('rental_end_date', $product->rental_end_date?->format('Y-m-d')) }}"
                                   class="w-full px-4 py-3 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-transparent transition-all">
                        </div>
                    </div>
                </div>

                <!-- Status Toggles Card -->
                <div class="bg-white rounded-xl shadow-lg overflow-hidden border border-gray-200">
                    <div class="bg-gradient-to-r from-pink-500 to-pink-600 px-6 py-4">
                        <h3 class="text-lg font-bold text-white flex items-center gap-2">
                            <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 6V4m0 2a2 2 0 100 4m0-4a2 2 0 110 4m-6 8a2 2 0 100-4m0 4a2 2 0 110-4m0 4v2m0-6V4m6 6v10m6-2a2 2 0 100-4m0 4a2 2 0 110-4m0 4v2m0-6V4"></path>
                            </svg>
                            Status Options
                        </h3>
                    </div>
                    <div class="p-6 space-y-4">
                        <div class="flex items-center justify-between p-3 bg-gray-50 rounded-lg">
                            <label class="text-sm font-semibold text-gray-700">Active</label>
                            <label class="relative inline-flex cursor-pointer items-center">
                                <input type="checkbox" class="sr-only peer" name="active" value="1" {{ old('active', $product->active) ? 'checked' : '' }}>
                                <div class="w-11 h-6 bg-gray-300 peer-focus:outline-none peer-focus:ring-4 peer-focus:ring-green-300 rounded-full peer peer-checked:after:translate-x-full peer-checked:after:border-white after:content-[''] after:absolute after:top-[2px] after:left-[2px] after:bg-white after:border-gray-300 after:border after:rounded-full after:h-5 after:w-5 after:transition-all peer-checked:bg-green-500"></div>
                            </label>
                        </div>

                        <div class="flex items-center justify-between p-3 bg-gray-50 rounded-lg">
                            <label class="text-sm font-semibold text-gray-700">Blocked</label>
                            <label class="relative inline-flex cursor-pointer items-center">
                                <input type="checkbox" class="sr-only peer" name="is_blocked" value="1" {{ old('is_blocked', $product->is_blocked) ? 'checked' : '' }}>
                                <div class="w-11 h-6 bg-gray-300 peer-focus:outline-none peer-focus:ring-4 peer-focus:ring-red-300 rounded-full peer peer-checked:after:translate-x-full peer-checked:after:border-white after:content-[''] after:absolute after:top-[2px] after:left-[2px] after:bg-white after:border-gray-300 after:border after:rounded-full after:h-5 after:w-5 after:transition-all peer-checked:bg-red-500"></div>
                            </label>
                        </div>

                        <div class="flex items-center justify-between p-3 bg-gray-50 rounded-lg">
                            <label class="text-sm font-semibold text-gray-700">Rented</label>
                            <label class="relative inline-flex cursor-pointer items-center">
                                <input type="checkbox" class="sr-only peer" name="is_rented" value="1" {{ old('is_rented', $product->is_rented) ? 'checked' : '' }}>
                                <div class="w-11 h-6 bg-gray-300 peer-focus:outline-none peer-focus:ring-4 peer-focus:ring-blue-300 rounded-full peer peer-checked:after:translate-x-full peer-checked:after:border-white after:content-[''] after:absolute after:top-[2px] after:left-[2px] after:bg-white after:border-gray-300 after:border after:rounded-full after:h-5 after:w-5 after:transition-all peer-checked:bg-blue-500"></div>
                            </label>
                        </div>

                        <div class="flex items-center justify-between p-3 bg-gray-50 rounded-lg">
                            <label class="text-sm font-semibold text-gray-700">Favorite</label>
                            <label class="relative inline-flex cursor-pointer items-center">
                                <input type="checkbox" class="sr-only peer" name="is_favorite" value="1" {{ old('is_favorite', $product->is_favorite) ? 'checked' : '' }}>
                                <div class="w-11 h-6 bg-gray-300 peer-focus:outline-none peer-focus:ring-4 peer-focus:ring-yellow-300 rounded-full peer peer-checked:after:translate-x-full peer-checked:after:border-white after:content-[''] after:absolute after:top-[2px] after:left-[2px] after:bg-white after:border-gray-300 after:border after:rounded-full after:h-5 after:w-5 after:transition-all peer-checked:bg-yellow-400"></div>
                            </label>
                        </div>

                        <div class="flex items-center justify-between p-3 bg-gray-50 rounded-lg">
                            <label class="text-sm font-semibold text-gray-700">Popular</label>
                            <label class="relative inline-flex cursor-pointer items-center">
                                <input type="checkbox" class="sr-only peer" name="is_popular" value="1" {{ old('is_popular', $product->is_popular) ? 'checked' : '' }}>
                                <div class="w-11 h-6 bg-gray-300 peer-focus:outline-none peer-focus:ring-4 peer-focus:ring-purple-300 rounded-full peer peer-checked:after:translate-x-full peer-checked:after:border-white after:content-[''] after:absolute after:top-[2px] after:left-[2px] after:bg-white after:border-gray-300 after:border after:rounded-full after:h-5 after:w-5 after:transition-all peer-checked:bg-purple-500"></div>
                            </label>
                        </div>
                    </div>
                </div>

            </div>
        </div>

        <!-- Action Buttons - Centered Below Form -->
        <div class="mt-8 flex items-center justify-center gap-4">
            <button type="button" onclick="handleFormSubmit()"
                    class="flex items-center justify-center gap-2 px-8 py-3 bg-gradient-to-r from-blue-600 to-blue-700 hover:from-blue-700 hover:to-blue-800 text-white font-bold rounded-lg shadow-lg transition-all duration-300 transform hover:scale-105">
                <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"></path>
                </svg>
                Update Product
            </button>

            <a href="{{ route('products.index', app()->getlocale()) }}"
               class="flex items-center justify-center gap-2 px-8 py-3 bg-gray-200 hover:bg-gray-300 text-gray-700 font-bold rounded-lg shadow-md transition-all duration-300">
                <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"></path>
                </svg>
                Cancel
            </a>
        </div>
    </form>

    <!-- Scripts -->
    <!-- CKEditor CDN -->
    <script src="https://cdn.ckeditor.com/ckeditor5/36.0.1/classic/ckeditor.js"></script>

    <script src="https://code.jquery.com/jquery-3.6.3.min.js"></script>
    <script>
        // Store CKEditor instances
        const editorInstances = {};

        $(document).ready(function() {
            console.log('Script loaded');

            // Initialize CKEditor for all locale textareas (only if ClassicEditor is available)
            if (typeof ClassicEditor !== 'undefined') {
                @foreach (config('app.locales') as $locale)
                    ClassicEditor
                        .create(document.querySelector('#description_{{ $locale }}'))
                        .then(editor => {
                            editorInstances['{{ $locale }}'] = editor;
                            console.log('CKEditor initialized for {{ $locale }}');
                        })
                        .catch(error => {
                            console.error('CKEditor error for {{ $locale }}:', error);
                        });
                @endforeach
            } else {
                console.warn('ClassicEditor is not loaded - description fields will be plain textareas');
            }

            // Language Tab Switching with Auto-Translation
            $('.language-tab').on('click', function(e) {
                e.preventDefault();
                const targetLocale = $(this).data('locale');

                console.log('Language tab clicked:', targetLocale);

                // Update tab styles - use attr to set full class
                $('.language-tab').each(function() {
                    $(this).attr('class', 'language-tab flex items-center gap-2 px-4 py-2 rounded-lg font-semibold transition-all duration-300 bg-gray-100 text-gray-700 hover:bg-gray-200');
                });

                // Add gradient to clicked tab
                $(this).attr('class', 'language-tab flex items-center gap-2 px-4 py-2 rounded-lg font-semibold transition-all duration-300 bg-gradient-to-r from-blue-500 to-blue-600 text-white shadow-md');

                // Show/hide content
                $('.locale-content').each(function() {
                    $(this).addClass('hidden');
                });
                $('#locale-' + targetLocale).removeClass('hidden');

                console.log('Content visibility changed');

                // Check if target locale is empty and auto-translate
                checkAndAutoTranslate(targetLocale);
            });
        });

        // Check if target locale fields are empty and trigger auto-translation
        function checkAndAutoTranslate(targetLocale) {
            const titleValue = $('#title_' + targetLocale).val();

            // If title is empty, trigger auto-translation
            if (!titleValue || titleValue.trim() === '') {
                console.log('Target locale is empty, attempting auto-translation');

                // Find source locale (first locale with data)
                const locales = {!! json_encode(config('app.locales')) !!};
                let sourceLocale = null;
                let sourceData = null;

                for (const locale of locales) {
                    const title = $('#title_' + locale).val();
                    if (title && title.trim() !== '' && locale !== targetLocale) {
                        sourceLocale = locale;

                        // Get description from CKEditor or textarea
                        let description = '';
                        if (editorInstances[locale]) {
                            description = editorInstances[locale].getData();
                        } else {
                            description = $('#description_' + locale).val();
                        }

                        sourceData = {
                            title: title,
                            description: description,
                            location: $('#location_' + locale).val(),
                            brand: $('#brand_' + locale).val(),
                            color: $('#color_' + locale).val(),
                        };
                        break;
                    }
                }

                if (sourceLocale && sourceData) {
                    console.log('Found source locale:', sourceLocale, sourceData);
                    performAutoTranslation(sourceLocale, targetLocale, sourceData);
                } else {
                    console.log('No source locale with data found');
                }
            }
        }

        // Handle form submit with auto-translation check
        function handleFormSubmit() {
            const locales = {!! json_encode(config('app.locales')) !!};
            const missingLocales = [];
            let sourceLocale = null;
            let sourceData = null;

            // Find source locale and check for missing translations
            for (const locale of locales) {
                const title = $('#title_' + locale).val();
                if (title && title.trim() !== '') {
                    if (!sourceLocale) {
                        sourceLocale = locale;

                        // Get description from CKEditor or textarea
                        let description = '';
                        if (editorInstances[locale]) {
                            description = editorInstances[locale].getData();
                        } else {
                            description = $('#description_' + locale).val();
                        }

                        sourceData = {
                            title: title,
                            description: description,
                            location: $('#location_' + locale).val(),
                            brand: $('#brand_' + locale).val(),
                            color: $('#color_' + locale).val(),
                        };
                    }
                } else {
                    missingLocales.push(locale);
                }
            }

            // If there are missing locales, translate them first
            if (missingLocales.length > 0 && sourceLocale && sourceData) {
                console.log('Missing locales detected:', missingLocales);
                console.log('Will translate from:', sourceLocale);

                // Translate all missing locales
                translateMissingLocales(sourceLocale, sourceData, missingLocales);
            } else {
                // All locales filled, submit form directly
                document.getElementById('productForm').submit();
            }
        }

        // Translate multiple missing locales before form submission
        function translateMissingLocales(sourceLocale, sourceData, missingLocales) {
            let completed = 0;
            const total = missingLocales.length;

            // Show global loading message
            const globalLoadingHtml = '<div id="global-translate-loading" class="fixed top-4 right-4 z-50 p-4 bg-blue-600 text-white rounded-lg shadow-2xl"><div class="flex items-center gap-3"><svg class="animate-spin h-6 w-6" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24"><circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle><path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path></svg><div><div class="font-bold">Auto-translating missing languages...</div><div class="text-sm opacity-90"><span id="translate-progress">0</span>/' + total + ' completed</div></div></div></div>';
            $('body').append(globalLoadingHtml);

            missingLocales.forEach(targetLocale => {
                $.ajax({
                    url: '/{{ app()->getLocale() }}/admin/products/auto-translate',
                    method: 'POST',
                    data: {
                        _token: '{{ csrf_token() }}',
                        source_locale: sourceLocale,
                        target_locale: targetLocale,
                        title: sourceData.title,
                        description: sourceData.description,
                        location: sourceData.location,
                        brand: sourceData.brand,
                        color: sourceData.color,
                    },
                    success: function(response) {
                        if (response.success) {
                            // Populate fields with translated data
                            $('#title_' + targetLocale).val(response.data.title);
                            $('#slug_' + targetLocale).val(response.data.slug);
                            $('#location_' + targetLocale).val(response.data.location);
                            $('#brand_' + targetLocale).val(response.data.brand);
                            $('#color_' + targetLocale).val(response.data.color);

                            // Set description in CKEditor or textarea
                            if (editorInstances[targetLocale]) {
                                editorInstances[targetLocale].setData(response.data.description);
                            } else {
                                $('#description_' + targetLocale).val(response.data.description);
                            }
                        }

                        completed++;
                        $('#translate-progress').text(completed);

                        // If all translations completed, submit form
                        if (completed === total) {
                            $('#global-translate-loading').remove();

                            // Show success message briefly before submit
                            const successMsg = '<div id="translate-success" class="fixed top-4 right-4 z-50 p-4 bg-green-600 text-white rounded-lg shadow-2xl"><div class="flex items-center gap-3"><svg class="h-6 w-6" fill="currentColor" viewBox="0 0 20 20"><path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zm3.707-9.293a1 1 0 00-1.414-1.414L9 10.586 7.707 9.293a1 1 0 00-1.414 1.414l2 2a1 1 0 001.414 0l4-4z" clip-rule="evenodd"></path></svg><div class="font-bold">Translation complete! Saving...</div></div></div>';
                            $('body').append(successMsg);

                            setTimeout(function() {
                                $('#translate-success').remove();
                                document.getElementById('productForm').submit();
                            }, 1000);
                        }
                    },
                    error: function(xhr, status, error) {
                        console.error('Translation failed for ' + targetLocale + ':', error);
                        completed++;
                        $('#translate-progress').text(completed);

                        // Continue even if one fails
                        if (completed === total) {
                            $('#global-translate-loading').remove();

                            const errorMsg = '<div id="translate-error" class="fixed top-4 right-4 z-50 p-4 bg-red-600 text-white rounded-lg shadow-2xl"><div class="flex items-center gap-3"><svg class="h-6 w-6" fill="currentColor" viewBox="0 0 20 20"><path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zM8.707 7.293a1 1 0 00-1.414 1.414L8.586 10l-1.293 1.293a1 1 0 101.414 1.414L10 11.414l1.293 1.293a1 1 0 001.414-1.414L11.414 10l1.293-1.293a1 1 0 00-1.414-1.414L10 8.586 8.707 7.293z" clip-rule="evenodd"></path></svg><div><div class="font-bold">Some translations failed</div><div class="text-sm">Attempting to save anyway...</div></div></div></div>';
                            $('body').append(errorMsg);

                            setTimeout(function() {
                                $('#translate-error').remove();
                                document.getElementById('productForm').submit();
                            }, 2000);
                        }
                    }
                });
            });
        }

        // Perform auto-translation via API
        function performAutoTranslation(sourceLocale, targetLocale, sourceData) {
            // Show loading indicator
            const loadingHtml = '<div class="auto-translate-loading p-4 bg-blue-50 border border-blue-200 rounded-lg mb-4"><div class="flex items-center gap-3"><svg class="animate-spin h-5 w-5 text-blue-600" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24"><circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle><path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path></svg><span class="text-blue-800 font-semibold">Auto-translating from ' + (sourceLocale === 'ka' ? 'Georgian' : 'English') + ' to ' + (targetLocale === 'ka' ? 'Georgian' : 'English') + '...</span></div></div>';
            $('#locale-' + targetLocale).prepend(loadingHtml);

            $.ajax({
                url: '/{{ app()->getLocale() }}/admin/products/auto-translate',
                method: 'POST',
                data: {
                    _token: '{{ csrf_token() }}',
                    source_locale: sourceLocale,
                    target_locale: targetLocale,
                    title: sourceData.title,
                    description: sourceData.description,
                    location: sourceData.location,
                    brand: sourceData.brand,
                    color: sourceData.color,
                },
                success: function(response) {
                    $('.auto-translate-loading').remove();

                    if (response.success) {
                        console.log('Translation successful:', response.data);

                        // Populate fields with translated data
                        $('#title_' + targetLocale).val(response.data.title);
                        $('#slug_' + targetLocale).val(response.data.slug);
                        $('#location_' + targetLocale).val(response.data.location);
                        $('#brand_' + targetLocale).val(response.data.brand);
                        $('#color_' + targetLocale).val(response.data.color);

                        // Set description in CKEditor or textarea
                        if (editorInstances[targetLocale]) {
                            editorInstances[targetLocale].setData(response.data.description);
                        } else {
                            $('#description_' + targetLocale).val(response.data.description);
                        }

                        // Show success message
                        const successHtml = '<div class="auto-translate-success p-4 bg-green-50 border border-green-200 rounded-lg mb-4"><div class="flex items-center gap-3"><svg class="h-5 w-5 text-green-600" fill="currentColor" viewBox="0 0 20 20"><path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zm3.707-9.293a1 1 0 00-1.414-1.414L9 10.586 7.707 9.293a1 1 0 00-1.414 1.414l2 2a1 1 0 001.414 0l4-4z" clip-rule="evenodd"></path></svg><span class="text-green-800 font-semibold">Auto-translation completed successfully!</span></div></div>';
                        $('#locale-' + targetLocale).prepend(successHtml);

                        // Remove success message after 3 seconds
                        setTimeout(function() {
                            $('.auto-translate-success').fadeOut(300, function() {
                                $(this).remove();
                            });
                        }, 3000);
                    }
                },
                error: function(xhr, status, error) {
                    $('.auto-translate-loading').remove();
                    console.error('Translation failed:', error);

                    // Show error message
                    const errorHtml = '<div class="auto-translate-error p-4 bg-red-50 border border-red-200 rounded-lg mb-4"><div class="flex items-center gap-3"><svg class="h-5 w-5 text-red-600" fill="currentColor" viewBox="0 0 20 20"><path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zM8.707 7.293a1 1 0 00-1.414 1.414L8.586 10l-1.293 1.293a1 1 0 101.414 1.414L10 11.414l1.293 1.293a1 1 0 001.414-1.414L11.414 10l1.293-1.293a1 1 0 00-1.414-1.414L10 8.586 8.707 7.293z" clip-rule="evenodd"></path></svg><span class="text-red-800 font-semibold">Auto-translation failed. Please fill manually.</span></div></div>';
                    $('#locale-' + targetLocale).prepend(errorHtml);

                    // Remove error message after 5 seconds
                    setTimeout(function() {
                        $('.auto-translate-error').fadeOut(300, function() {
                            $(this).remove();
                        });
                    }, 5000);
                }
            });
        }

        // Image Preview Function
        function previewImages(event) {
            const previewContainer = document.getElementById('image-preview');
            previewContainer.innerHTML = '';
            const files = event.target.files;

            Array.from(files).forEach((file, index) => {
                const reader = new FileReader();
                reader.onload = function(e) {
                    const div = document.createElement('div');
                    div.className = 'relative group';
                    div.innerHTML = `
                        <img src="${e.target.result}" class="w-full h-32 object-cover rounded-lg shadow-md border-2 border-purple-300">
                        <div class="absolute inset-0 bg-black bg-opacity-0 group-hover:bg-opacity-30 transition-all rounded-lg flex items-center justify-center">
                            <span class="text-white opacity-0 group-hover:opacity-100 text-sm font-semibold">Image ${index + 1}</span>
                        </div>
                    `;
                    previewContainer.appendChild(div);
                };
                reader.readAsDataURL(file);
            });
        }

        // New dynamic auto-translate button handler
        $('#auto-translate-btn').on('click', function(e) {
            e.preventDefault();
            console.log('Auto translate button clicked');

            const locales = {!! json_encode(config('app.locales')) !!};
            let sourceLocale = null;
            let targetLocale = null;
            let sourceData = {};

            // Find source and target locales
            for (const locale of locales) {
                const titleInput = document.querySelector(`#title_${locale}`);
                if (!titleInput) continue;

                const title = titleInput.value;
                if (title && title.trim() !== '') {
                    sourceLocale = locale;
                    console.log('Source locale:', sourceLocale);

                    // Collect all visible input fields for source locale
                    const localeContainer = document.querySelector(`#locale-${locale}`);
                    if (localeContainer) {
                        localeContainer.querySelectorAll('input[type="text"], textarea').forEach(field => {
                            const fieldName = field.name.replace(`${locale}[`, '').replace(']', '');
                            const fieldValue = field.value;
                            if (fieldValue) {
                                sourceData[fieldName] = fieldValue;
                                console.log(`Source field ${fieldName}:`, fieldValue);
                            }
                        });

                        // Handle CKEditor fields
                        if (editorInstances[locale]) {
                            const editorData = editorInstances[locale].getData();
                            if (editorData) {
                                sourceData['description'] = editorData;
                                console.log('Source description from CKEditor:', editorData.substring(0, 100));
                            }
                        }
                    }
                    break;
                }
            }

            // Find target locale (the one with empty title)
            for (const locale of locales) {
                if (locale !== sourceLocale) {
                    const titleInput = document.querySelector(`#title_${locale}`);
                    if (titleInput && (!titleInput.value || titleInput.value.trim() === '')) {
                        targetLocale = locale;
                        console.log('Target locale:', targetLocale);
                        break;
                    }
                }
            }

            if (!sourceLocale) {
                alert('Please fill in at least one language first!');
                return;
            }

            if (!targetLocale) {
                alert('All languages are already filled!');
                return;
            }

            console.log('Source locale:', sourceLocale);
            console.log('Target locale:', targetLocale);
            console.log('Source data:', sourceData);

            // Show loading indicator
            const loadingHtml = `
                <div id="translate-loading" class="fixed top-4 right-4 z-50 p-4 bg-blue-600 text-white rounded-lg shadow-2xl">
                    <div class="flex items-center gap-3">
                        <svg class="animate-spin h-6 w-6" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24">
                            <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
                            <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path>
                        </svg>
                        <div>
                            <div class="font-bold">Translating...</div>
                            <div class="text-sm opacity-90">From ${sourceLocale === 'ka' ? 'Georgian' : 'English'} to ${targetLocale === 'ka' ? 'Georgian' : 'English'}</div>
                        </div>
                    </div>
                </div>
            `;
            $('body').append(loadingHtml);

            // Perform translation
            fetch('/{{ app()->getLocale() }}/admin/products/auto-translate', {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                    'X-CSRF-TOKEN': '{{ csrf_token() }}'
                },
                body: JSON.stringify({
                    source_locale: sourceLocale,
                    target_locale: targetLocale,
                    data: sourceData
                })
            })
            .then(response => response.json())
            .then(data => {
                console.log('Translation response:', data);
                $('#translate-loading').remove();

                if (data.success && data.translated) {
                    console.log('Translation successful, applying fields...');

                    // Apply translated fields dynamically
                    Object.keys(data.translated).forEach(fieldName => {
                        const fieldValue = data.translated[fieldName];
                        console.log(`Setting ${fieldName} to:`, fieldValue);

                        // Try to find the input field
                        const inputField = document.querySelector(`#${fieldName}_${targetLocale}`);
                        if (inputField) {
                            // Check if it's a CKEditor field
                            if (fieldName === 'description' && editorInstances[targetLocale]) {
                                editorInstances[targetLocale].setData(fieldValue);
                                console.log('Set description in CKEditor');
                            } else {
                                inputField.value = fieldValue;
                                console.log(`Set ${fieldName} in input field`);
                            }
                        }
                    });

                    // Show success message
                    const successMsg = `
                        <div id="translate-success" class="fixed top-4 right-4 z-50 p-4 bg-green-600 text-white rounded-lg shadow-2xl">
                            <div class="flex items-center gap-3">
                                <svg class="h-6 w-6" fill="currentColor" viewBox="0 0 20 20">
                                    <path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zm3.707-9.293a1 1 0 00-1.414-1.414L9 10.586 7.707 9.293a1 1 0 00-1.414 1.414l2 2a1 1 0 001.414 0l4-4z" clip-rule="evenodd"></path>
                                </svg>
                                <div class="font-bold">Translation complete!</div>
                            </div>
                        </div>
                    `;
                    $('body').append(successMsg);

                    setTimeout(() => {
                        $('#translate-success').fadeOut(300, function() { $(this).remove(); });
                    }, 3000);
                } else {
                    throw new Error(data.message || 'Translation failed');
                }
            })
            .catch(error => {
                console.error('Translation error:', error);
                $('#translate-loading').remove();

                const errorMsg = `
                    <div id="translate-error" class="fixed top-4 right-4 z-50 p-4 bg-red-600 text-white rounded-lg shadow-2xl">
                        <div class="flex items-center gap-3">
                            <svg class="h-6 w-6" fill="currentColor" viewBox="0 0 20 20">
                                <path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zM8.707 7.293a1 1 0 00-1.414 1.414L8.586 10l-1.293 1.293a1 1 0 101.414 1.414L10 11.414l1.293 1.293a1 1 0 001.414-1.414L11.414 10l1.293-1.293a1 1 0 00-1.414-1.414L10 8.586 8.707 7.293z" clip-rule="evenodd"></path>
                            </svg>
                            <div>
                                <div class="font-bold">Translation failed</div>
                                <div class="text-sm">${error.message}</div>
                            </div>
                        </div>
                    </div>
                `;
                $('body').append(errorMsg);

                setTimeout(() => {
                    $('#translate-error').fadeOut(300, function() { $(this).remove(); });
                }, 5000);
            });
        });

        // Delete Image Function
        function deleteImage(imageId) {
            if (!confirm('Are you sure you want to delete this image?')) {
                return;
            }

            fetch(`/{{ app()->getLocale() }}/admin/products/delete/image/${imageId}`, {
                method: 'DELETE',
                headers: {
                    'X-CSRF-TOKEN': '{{ csrf_token() }}',
                    'Content-Type': 'application/json',
                    'Accept': 'application/json'
                }
            })
            .then(response => response.json())
            .then(data => {
                if (data.success) {
                    location.reload();
                } else {
                    alert('Error deleting image');
                }
            })
            .catch(error => {
                console.error('Error:', error);
                alert('Error deleting image');
            });
        }
    </script>
</x-admin.admin-layout>
