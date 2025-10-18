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

    <form action="{{ route('products.update', [app()->getlocale(), $product->id]) }}" method="POST" enctype="multipart/form-data">
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
                        <div class="flex gap-3 mb-6 border-b pb-4">
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
                                    <input type="text"
                                           name="{{ $locale }}[brand]"
                                           id="brand_{{ $locale }}"
                                           value="{{ old($locale . '.brand', $product->translate($locale)->brand ?? '') }}"
                                           class="w-full px-4 py-3 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-transparent transition-all"
                                           placeholder="e.g., Nike, Adidas">
                                </div>

                                <!-- Color Field -->
                                <div class="form-group">
                                    <label for="color_{{ $locale }}" class="block text-sm font-semibold text-gray-700 mb-2">
                                        Color ({{ __('admin.locale_' . $locale) }})
                                    </label>
                                    <input type="text"
                                           name="{{ $locale }}[color]"
                                           id="color_{{ $locale }}"
                                           value="{{ old($locale . '.color', $product->translate($locale)->color ?? '') }}"
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
                            <input type="text"
                                   name="size"
                                   id="size"
                                   value="{{ old('size', $product->size) }}"
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
                                   value="{{ old('rental_start_date', $product->rental_start_date) }}"
                                   class="w-full px-4 py-3 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-transparent transition-all">
                        </div>
                        <div>
                            <label for="rental_end_date" class="block text-sm font-semibold text-gray-700 mb-2">End Date</label>
                            <input type="date"
                                   name="rental_end_date"
                                   id="rental_end_date"
                                   value="{{ old('rental_end_date', $product->rental_end_date) }}"
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
            <button type="submit"
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
        $(document).ready(function() {
            console.log('Script loaded');

            // Initialize CKEditor for all locale textareas (only if ClassicEditor is available)
            if (typeof ClassicEditor !== 'undefined') {
                @foreach (config('app.locales') as $locale)
                    ClassicEditor
                        .create(document.querySelector('#description_{{ $locale }}'))
                        .then(editor => {
                            console.log('CKEditor initialized for {{ $locale }}');
                        })
                        .catch(error => {
                            console.error('CKEditor error for {{ $locale }}:', error);
                        });
                @endforeach
            } else {
                console.warn('ClassicEditor is not loaded - description fields will be plain textareas');
            }

            // Language Tab Switching
            $('.language-tab').on('click', function(e) {
                e.preventDefault();
                const locale = $(this).data('locale');

                console.log('Language tab clicked:', locale);

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
                $('#locale-' + locale).removeClass('hidden');

                console.log('Content visibility changed');
            });
        });

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
