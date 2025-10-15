<x-admin.admin-layout>
    <div class="max-w-4xl mx-auto py-6 px-4">
        <div class="bg-white rounded-lg shadow-sm">
            <div class="px-6 py-4 border-b border-gray-200">
                <h1 class="text-2xl font-semibold text-gray-900">Edit Product</h1>
            </div>

            <form action="{{ route('products.update', [app()->getlocale(), $product->id]) }}" method="POST" enctype="multipart/form-data">
                @csrf
                @method('PUT')
                @if(request()->has('page_id'))
                    <input type="hidden" name="page_id" value="{{ request()->page_id }}">
                @endif

                <div class="p-6 space-y-6">
                    <!-- Basic Information -->
                    <div class="bg-white rounded-lg shadow-sm p-6">
                        <h3 class="text-lg font-medium text-gray-900 mb-4">Basic Information</h3>

                        <!-- Category Selection -->
                        <div class="mb-4">
                            <label for="category_id" class="block text-sm font-medium text-gray-700 mb-2">
                                <i class="fas fa-folder mr-2"></i>{{ __('admin.Category') }}
                            </label>
                            <select name="category_id" id="category_id" class="w-full border border-gray-300 rounded-lg px-3 py-2 focus:ring-2 focus:ring-green-500 focus:border-green-500">
                                <option value="">{{ __('admin.Select Category') }} ({{ __('admin.Optional') }})</option>
                                @foreach($categories as $category)
                                    <option value="{{ $category->id }}" {{ old('category_id', $product->category_id) == $category->id ? 'selected' : '' }}>
                                        {{ $category->title }}
                                    </option>
                                @endforeach
                            </select>
                            @error('category_id')
                                <span class="text-red-500 text-sm">{{ $message }}</span>
                            @enderror
                        </div>

                        <!-- Product Identify ID -->
                        <div class="mb-4">
                            <label for="product_identify_id" class="block text-sm font-medium text-gray-700 mb-2">
                                Product Identify ID <span class="text-red-500">*</span>
                            </label>
                            <input type="text" name="product_identify_id" id="product_identify_id"
                                value="{{ old('product_identify_id', $product->product_identify_id) }}"
                                class="w-full border border-gray-300 rounded-lg px-3 py-2 focus:ring-2 focus:ring-green-500 focus:border-green-500 @error('product_identify_id') border-red-500 @enderror"
                                placeholder="e.g., PROD-ABC123">
                            @error('product_identify_id')
                                <span class="text-red-500 text-sm">{{ $message }}</span>
                            @enderror
                        </div>

                        <!-- Features: Size (Location and Color are now translatable) -->
                        <div class="mb-4">
                            <div>
                                <label for="size" class="block text-sm font-medium text-gray-700 mb-2">Size</label>
                                <input type="text" name="size" id="size"
                                    value="{{ old('size', $product->size) }}"
                                    class="w-full border border-gray-300 rounded-lg px-3 py-2 focus:ring-2 focus:ring-green-500 focus:border-green-500 @error('size') border-red-500 @enderror"
                                    placeholder="e.g., S, M, L, XL">
                                @error('size')
                                    <span class="text-red-500 text-sm">{{ $message }}</span>
                                @enderror
                            </div>
                        </div>

                        <!-- Currency -->
                        <div class="mb-4">
                            <label for="currency" class="block text-sm font-medium text-gray-700 mb-2">
                                <span class="text-red-500">*</span>Currency
                            </label>
                            <select name="currency" id="currency"
                                class="w-full border border-gray-300 rounded-lg px-3 py-2 focus:ring-2 focus:ring-green-500 focus:border-green-500 @error('currency') border-red-500 @enderror">
                                <option value="">Select Currency</option>
                                <option value="GEL" {{ old('currency', $product->currency) == 'GEL' ? 'selected' : '' }}>GEL (₾)</option>
                                <option value="USD" {{ old('currency', $product->currency) == 'USD' ? 'selected' : '' }}>USD ($)</option>
                                <option value="EUR" {{ old('currency', $product->currency) == 'EUR' ? 'selected' : '' }}>EUR (€)</option>
                            </select>
                            @error('currency')
                                <span class="text-red-500 text-sm">{{ $message }}</span>
                            @enderror
                        </div>

                        <!-- Contact Information -->
                        <div class="grid grid-cols-1 md:grid-cols-2 gap-4 mb-4">
                            <div>
                                <label for="contact_person" class="block text-sm font-medium text-gray-700 mb-2">
                                    <span class="text-red-500">*</span>Contact Person
                                </label>
                                <input type="text" name="contact_person" id="contact_person"
                                    value="{{ old('contact_person', $product->contact_person) }}"
                                    class="w-full border border-gray-300 rounded-lg px-3 py-2 focus:ring-2 focus:ring-green-500 focus:border-green-500 @error('contact_person') border-red-500 @enderror"
                                    placeholder="Full Name">
                                @error('contact_person')
                                    <span class="text-red-500 text-sm">{{ $message }}</span>
                                @enderror
                            </div>

                            <div>
                                <label for="contact_phone" class="block text-sm font-medium text-gray-700 mb-2">
                                    <span class="text-red-500">*</span>Contact Phone
                                </label>
                                <input type="text" name="contact_phone" id="contact_phone"
                                    value="{{ old('contact_phone', $product->contact_phone) }}"
                                    class="w-full border border-gray-300 rounded-lg px-3 py-2 focus:ring-2 focus:ring-green-500 focus:border-green-500 @error('contact_phone') border-red-500 @enderror"
                                    placeholder="+995 XXX XXX XXX">
                                @error('contact_phone')
                                    <span class="text-red-500 text-sm">{{ $message }}</span>
                                @enderror
                            </div>
                        </div>

                        <!-- Rental Dates -->
                        <div class="grid grid-cols-1 md:grid-cols-2 gap-4 mb-4">
                            <div>
                                <label for="rental_start_date" class="block text-sm font-medium text-gray-700 mb-2">
                                    Rental Start Date
                                </label>
                                <input type="date" name="rental_start_date" id="rental_start_date"
                                    value="{{ old('rental_start_date', $product->rental_start_date ? $product->rental_start_date->format('Y-m-d') : '') }}"
                                    class="w-full border border-gray-300 rounded-lg px-3 py-2 focus:ring-2 focus:ring-green-500 focus:border-green-500 @error('rental_start_date') border-red-500 @enderror">
                                @error('rental_start_date')
                                    <span class="text-red-500 text-sm">{{ $message }}</span>
                                @enderror
                            </div>

                            <div>
                                <label for="rental_end_date" class="block text-sm font-medium text-gray-700 mb-2">
                                    Rental End Date
                                </label>
                                <input type="date" name="rental_end_date" id="rental_end_date"
                                    value="{{ old('rental_end_date', $product->rental_end_date ? $product->rental_end_date->format('Y-m-d') : '') }}"
                                    class="w-full border border-gray-300 rounded-lg px-3 py-2 focus:ring-2 focus:ring-green-500 focus:border-green-500 @error('rental_end_date') border-red-500 @enderror">
                                @error('rental_end_date')
                                    <span class="text-red-500 text-sm">{{ $message }}</span>
                                @enderror
                            </div>
                        </div>

                        <!-- Price, Sort Order, Status, and Favorite -->
                        <div class="grid grid-cols-1 md:grid-cols-2 gap-4 mb-4">
                            <div>
                                <label for="price" class="block text-sm font-medium text-gray-700 mb-2">
                                    <span class="text-red-500">*</span>Price
                                </label>
                                <input type="text" name="price" id="price"
                                    value="{{ old('price', $product->price) }}"
                                    class="w-full border border-gray-300 rounded-lg px-3 py-2 focus:ring-2 focus:ring-green-500 focus:border-green-500 @error('price') border-red-500 @enderror"
                                    placeholder="e.g., 99.99">
                                @error('price')
                                    <span class="text-red-500 text-sm">{{ $message }}</span>
                                @enderror
                            </div>

                            <div>
                                <label for="sort_order" class="block text-sm font-medium text-gray-700 mb-2">Sort Order</label>
                                <input type="number" name="sort_order" id="sort_order"
                                    value="{{ old('sort_order', $product->sort_order) }}"
                                    class="w-full border border-gray-300 rounded-lg px-3 py-2 focus:ring-2 focus:ring-green-500 focus:border-green-500 @error('sort_order') border-red-500 @enderror"
                                    min="0">
                                @error('sort_order')
                                    <span class="text-red-500 text-sm">{{ $message }}</span>
                                @enderror
                            </div>
                        </div>

                        <!-- Status Toggles -->
                        <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                            <div>
                                <label class="block text-sm font-medium text-gray-700 mb-2">Status</label>
                                <label class="relative inline-flex cursor-pointer items-center">
                                    <input type="checkbox" class="peer sr-only" name="active"
                                        value="1" {{ old('active', $product->active) ? 'checked' : '' }} />
                                    <div class="peer h-6 w-12 rounded-full border bg-gray-300 after:absolute after:left-[2px] after:top-0.5 after:h-5 after:w-5 after:rounded-full after:border after:border-gray-300 after:bg-white after:transition-all after:content-[''] peer-checked:bg-green-500 peer-checked:after:translate-x-full peer-checked:after:border-white peer-focus:ring-4 peer-focus:ring-green-300"></div>
                                    <span class="ml-2 text-sm font-medium text-gray-700">
                                        {{ $product->active ? 'Active' : 'Inactive' }}
                                    </span>
                                </label>
                            </div>
                            <div>
                                <label class="block text-sm font-medium text-gray-700 mb-2">Blocked</label>
                                <label class="relative inline-flex cursor-pointer items-center">
                                    <input type="checkbox" class="peer sr-only" name="is_blocked"
                                        value="1" {{ old('is_blocked', $product->is_blocked) ? 'checked' : '' }} />
                                    <div class="peer h-6 w-12 rounded-full border bg-gray-300 after:absolute after:left-[2px] after:top-0.5 after:h-5 after:w-5 after:rounded-full after:border after:border-gray-300 after:bg-white after:transition-all after:content-[''] peer-checked:bg-red-500 peer-checked:after:translate-x-full peer-checked:after:border-white peer-focus:ring-4 peer-focus:ring-red-200"></div>
                                    <span class="ml-2 text-sm font-medium text-gray-700">
                                        {{ $product->is_blocked ? 'Blocked' : 'Not Blocked' }}
                                    </span>
                                </label>
                            </div>
                            <div>
                                <label class="block text-sm font-medium text-gray-700 mb-2">Rented</label>
                                <label class="relative inline-flex cursor-pointer items-center">
                                    <input type="checkbox" class="peer sr-only" name="is_rented"
                                        value="1" {{ old('is_rented', $product->is_rented) ? 'checked' : '' }} />
                                    <div class="peer h-6 w-12 rounded-full border bg-gray-300 after:absolute after:left-[2px] after:top-0.5 after:h-5 after:w-5 after:rounded-full after:border after:border-gray-300 after:bg-white after:transition-all after:content-[''] peer-checked:bg-blue-500 peer-checked:after:translate-x-full peer-checked:after:border-white peer-focus:ring-4 peer-focus:ring-blue-200"></div>
                                    <span class="ml-2 text-sm font-medium text-gray-700">
                                        {{ $product->is_rented ? 'Rented' : 'Available' }}
                                    </span>
                                </label>
                                @if($product->is_rented && $product->rented_at)
                                    <p class="mt-1 text-xs text-gray-500">
                                        Rented on: {{ $product->rented_at->format('M d, Y H:i') }}
                                        @if($product->renter)
                                            <br>By: {{ $product->renter->name }}
                                        @endif
                                    </p>
                                @endif
                            </div>
                            <div>
                                <label class="block text-sm font-medium text-gray-700 mb-2">Favorite</label>
                                <label class="relative inline-flex cursor-pointer items-center">
                                    <input type="checkbox" class="peer sr-only" name="is_favorite"
                                        value="1" {{ old('is_favorite', $product->is_favorite) ? 'checked' : '' }} />
                                    <div class="peer h-6 w-12 rounded-full border bg-gray-300 after:absolute after:left-[2px] after:top-0.5 after:h-5 after:w-5 after:rounded-full after:border after:border-gray-300 after:bg-white after:transition-all after:content-[''] peer-checked:bg-yellow-400 peer-checked:after:translate-x-full peer-checked:after:border-white peer-focus:ring-4 peer-focus:ring-yellow-200"></div>
                                    <span class="ml-2 text-sm font-medium text-gray-700">
                                        {{ $product->is_favorite ? 'Yes' : 'No' }}
                                    </span>
                                </label>
                            </div>
                            <div>
                                <label class="block text-sm font-medium text-gray-700 mb-2">Popular</label>
                                <label class="relative inline-flex cursor-pointer items-center">
                                    <input type="checkbox" class="peer sr-only" name="is_popular"
                                        value="1" {{ old('is_popular', $product->is_popular) ? 'checked' : '' }} />
                                    <div class="peer h-6 w-12 rounded-full border bg-gray-300 after:absolute after:left-[2px] after:top-0.5 after:h-5 after:w-5 after:rounded-full after:border after:border-gray-300 after:bg-white after:transition-all after:content-[''] peer-checked:bg-purple-500 peer-checked:after:translate-x-full peer-checked:after:border-white peer-focus:ring-4 peer-focus:ring-purple-200"></div>
                                    <span class="ml-2 text-sm font-medium text-gray-700">
                                        {{ $product->is_popular ? 'Yes' : 'No' }}
                                    </span>
                                </label>
                            </div>
                        </div>
                    </div>

                    <!-- Translatable Content -->
                    <div class="bg-white rounded-lg shadow-sm p-6">
                        <h3 class="text-lg font-medium text-gray-900 mb-4">{{ __('admin.Content') }}</h3>

                        <!-- Language Tabs -->
                        <div class="border-b border-gray-200 mb-6">
                            <nav class="-mb-px flex space-x-8">
                                @foreach(config('app.locales') as $locale)
                                    <a href="#"
                                       class="language-tab py-2 px-1 border-b-2 font-medium text-sm {{ $loop->first ? 'border-green-500 text-green-600 bg-green-50' : 'border-transparent text-gray-500 hover:text-gray-700 hover:border-gray-300' }}"
                                       data-locale="{{ $locale }}"
                                       onclick="event.preventDefault();">
                                        {{ __('admin.locale_' . $locale) }}
                                        <img src="{{ $locale === 'en' ? asset('storage/flags/united-states.png') : asset('storage/flags/georgia.png') }}"
                                             alt="{{ $locale }}" class="inline w-4 h-4 ml-1">
                                    </a>
                                @endforeach
                            </nav>
                        </div>

                        @foreach(config('app.locales') as $locale)
                            <div class="language-content {{ !$loop->first ? 'hidden' : '' }}" data-locale="{{ $locale }}">
                                <div class="grid grid-cols-1 gap-4">
                                    <!-- Title -->
                                    <div>
                                        <label for="{{ $locale }}_title" class="block text-sm font-medium text-gray-700 mb-2">
                                            {{ __('admin.Title') }} ({{ __('admin.locale_' . $locale) }})
                                            <span class="text-red-500">*</span>
                                        </label>
                                        <input type="text"
                                               name="{{ $locale }}[title]"
                                               id="{{ $locale }}_title"
                                               value="{{ old($locale . '.title', $product->translate($locale)->title ?? '') }}"
                                               class="w-full border border-gray-300 rounded-lg px-3 py-2 focus:ring-2 focus:ring-green-500 focus:border-green-500 @error($locale . '.title') border-red-500 @enderror"
                                               placeholder="{{ __('admin.Title') }}">
                                        @error($locale . '.title')
                                            <span class="text-red-500 text-sm">{{ $message }}</span>
                                        @enderror
                                    </div>

                                    <!-- Slug -->
                                    <div>
                                        <label for="{{ $locale }}_slug" class="block text-sm font-medium text-gray-700 mb-2">
                                            {{ __('admin.Slug') }} ({{ __('admin.locale_' . $locale) }})
                                            <span class="text-red-500">*</span>
                                        </label>
                                        <input type="text"
                                               name="{{ $locale }}[slug]"
                                               id="{{ $locale }}_slug"
                                               value="{{ old($locale . '.slug', $product->translate($locale)->slug ?? '') }}"
                                               class="w-full border border-gray-300 rounded-lg px-3 py-2 focus:ring-2 focus:ring-green-500 focus:border-green-500 @error($locale . '.slug') border-red-500 @enderror"
                                               placeholder="URL Keyword">
                                        @error($locale . '.slug')
                                            <span class="text-red-500 text-sm">{{ $message }}</span>
                                        @enderror
                                    </div>


                                    <!-- Location -->
                                    <div>
                                        <label for="{{ $locale }}_location" class="block text-sm font-medium text-gray-700 mb-2">
                                            Location ({{ __('admin.locale_' . $locale) }})
                                        </label>
                                        <input type="text"
                                               name="{{ $locale }}[location]"
                                               id="{{ $locale }}_location"
                                               value="{{ old($locale . '.location', $product->translate($locale)->location ?? '') }}"
                                               class="w-full border border-gray-300 rounded-lg px-3 py-2 focus:ring-2 focus:ring-green-500 focus:border-green-500 @error($locale . '.location') border-red-500 @enderror"
                                               placeholder="e.g., Warehouse A, Shelf 12">
                                        @error($locale . '.location')
                                            <span class="text-red-500 text-sm">{{ $message }}</span>
                                        @enderror
                                    </div>

                                    <!-- Color -->
                                    <div>
                                        <label for="{{ $locale }}_color" class="block text-sm font-medium text-gray-700 mb-2">
                                            Color ({{ __('admin.locale_' . $locale) }})
                                        </label>
                                        <input type="text"
                                               name="{{ $locale }}[color]"
                                               id="{{ $locale }}_color"
                                               value="{{ old($locale . '.color', $product->translate($locale)->color ?? '') }}"
                                               class="w-full border border-gray-300 rounded-lg px-3 py-2 focus:ring-2 focus:ring-green-500 focus:border-green-500 @error($locale . '.color') border-red-500 @enderror"
                                               placeholder="e.g., Red, Blue, Black">
                                        @error($locale . '.color')
                                            <span class="text-red-500 text-sm">{{ $message }}</span>
                                        @enderror
                                    </div>

                                    <!-- Style -->
                                    <div>
                                        <label for="{{ $locale }}_style" class="block text-sm font-medium text-gray-700 mb-2">
                                            Style ({{ __('admin.locale_' . $locale) }})
                                        </label>
                                        <input type="text"
                                               name="{{ $locale }}[style]"
                                               id="{{ $locale }}_style"
                                               value="{{ old($locale . '.style', $product->translate($locale)->style ?? '') }}"
                                               class="w-full border border-gray-300 rounded-lg px-3 py-2 focus:ring-2 focus:ring-green-500 focus:border-green-500 @error($locale . '.style') border-red-500 @enderror"
                                               placeholder="e.g., Modern, Classic, Vintage">
                                        @error($locale . '.style')
                                            <span class="text-red-500 text-sm">{{ $message }}</span>
                                        @enderror
                                    </div>

                                    <!-- Brand -->
                                    <div>
                                        <label for="{{ $locale }}_brand" class="block text-sm font-medium text-gray-700 mb-2">
                                            Brand ({{ __('admin.locale_' . $locale) }})
                                        </label>
                                        <input type="text"
                                               name="{{ $locale }}[brand]"
                                               id="{{ $locale }}_brand"
                                               value="{{ old($locale . '.brand', $product->translate($locale)->brand ?? '') }}"
                                               class="w-full border border-gray-300 rounded-lg px-3 py-2 focus:ring-2 focus:ring-green-500 focus:border-green-500 @error($locale . '.brand') border-red-500 @enderror"
                                               placeholder="e.g., Nike, Adidas, Samsung">
                                        @error($locale . '.brand')
                                            <span class="text-red-500 text-sm">{{ $message }}</span>
                                        @enderror
                                    </div>

                                    <!-- Description -->
                                    <div>
                                        <label for="{{ $locale }}_description" class="block text-sm font-medium text-gray-700 mb-2">
                                            {{ __('admin.Description') }} ({{ __('admin.locale_' . $locale) }})
                                        </label>
                                        <textarea name="{{ $locale }}[description]"
                                                  id="{{ $locale }}_description"
                                                  rows="6"
                                                  class="w-full border border-gray-300 rounded-lg px-3 py-2 focus:ring-2 focus:ring-green-500 focus:border-green-500 @error($locale . '.description') border-red-500 @enderror"
                                                  placeholder="{{ __('admin.Description') }}">{{ old($locale . '.description', $product->translate($locale)->description ?? '') }}</textarea>
                                        @error($locale . '.description')
                                            <span class="text-red-500 text-sm">{{ $message }}</span>
                                        @enderror
                                    </div>
                                </div>
                            </div>
                        @endforeach
                    </div>

                    <!-- Product Images -->
                    <div class="bg-white rounded-lg shadow-sm p-6">
                        <h3 class="text-lg font-medium text-gray-900 mb-4">Product Images</h3>

                        <!-- Current Images -->
                        @if($product->images->count() > 0)
                            <div class="mb-4">
                                <h4 class="text-sm font-medium text-gray-700 mb-2">Current Images</h4>
                                <div class="grid grid-cols-2 md:grid-cols-4 gap-4">
                                    @foreach($product->images as $image)

                                        <div class="relative group">
                                            <img src="{{ asset('storage/products/' . $image->image_name) }}"
                                                 alt="Product Image"
                                                 class="w-full h-32 object-cover rounded-lg border"
                                                 >
                                            <button type="button"
                                                    data-route="{{ route('products.images.delete', [app()->getlocale(), $image->id]) }}"
                                                    data-id="{{ $image->id }}"
                                                    data-token="{{ csrf_token() }}"
                                                    class="absolute top-2 right-2 bg-red-500 text-white rounded-full w-6 h-6 flex items-center justify-center opacity-0 group-hover:opacity-100 transition-opacity delete-image">
                                                <i class="fas fa-times text-xs"></i>
                                            </button>
                                        </div>
                                    @endforeach
                                </div>
                            </div>
                        @endif

                        <!-- Upload New Images -->
                        <div>
                            <label for="images" class="block text-sm font-medium text-gray-700 mb-2">Add New Images</label>
                            <input type="file"
                                   name="images[]"
                                   id="images"
                                   multiple
                                   accept="image/*"
                                   class="w-full border border-gray-300 rounded-lg px-3 py-2 focus:ring-2 focus:ring-green-500 focus:border-green-500">
                            @error('images')
                                <span class="text-red-500 text-sm">{{ $message }}</span>
                            @enderror
                        </div>
                    </div>

                    <!-- Form Actions -->
                    <div class="flex justify-between items-center pt-6 border-t border-gray-200">
                        <a href="{{ route('products.index', app()->getlocale()) }}"
                           class="bg-gray-500 hover:bg-gray-600 text-white px-6 py-2 rounded-lg transition-colors">
                            {{ __('admin.Back') }}
                        </a>
                        <button type="submit"
                                class="bg-green-500 hover:bg-green-600 text-white px-6 py-2 rounded-lg transition-colors">
                            {{ __('admin.Update Product') }}
                        </button>
                    </div>
                </div>
            </form>
        </div>
    </div>

    <!-- JavaScript for Language Tabs and Image Deletion -->
    <script>
        // Language tab switching
        function switchLanguageTab(locale) {
            // Remove active classes from all tabs
            document.querySelectorAll('.language-tab').forEach(tab => {
                tab.classList.remove('border-green-500', 'text-green-600', 'bg-green-50');
                tab.classList.add('border-transparent', 'text-gray-500');
            });

            // Add active classes to selected tab
            document.querySelectorAll(`.language-tab[data-locale="${locale}"]`).forEach(tab => {
                tab.classList.add('border-green-500', 'text-green-600', 'bg-green-50');
                tab.classList.remove('border-transparent', 'text-gray-500');
            });

            // Hide all content sections
            document.querySelectorAll('.language-content').forEach(content => {
                content.classList.add('hidden');
            });

            // Show selected content section
            document.querySelectorAll(`.language-content[data-locale="${locale}"]`).forEach(content => {
                content.classList.remove('hidden');
            });
        }

        document.addEventListener('DOMContentLoaded', function() {
            // Language tab switching
            document.querySelectorAll('.language-tab').forEach(tab => {
                tab.addEventListener('click', function(e) {
                    e.preventDefault();
                    switchLanguageTab(this.dataset.locale);
                });
            });

            // Initialize first language tab as active
            const firstTab = document.querySelector('.language-tab');
            if (firstTab) {
                switchLanguageTab(firstTab.dataset.locale);
            }

            // Image deletion
            document.querySelectorAll('.delete-image').forEach(button => {
                button.addEventListener('click', function() {
                    if (confirm('Are you sure you want to delete this image?')) {
                        const route = this.dataset.route;
                        const token = this.dataset.token;

                        fetch(route, {
                            method: 'DELETE',
                            headers: {
                                'X-CSRF-TOKEN': token,
                                'Content-Type': 'application/json',
                            },
                        })
                        .then(response => response.json())
                        .then(data => {
                            if (data.success) {
                                this.closest('.relative').remove();
                            }
                        })
                        .catch(error => {
                            console.error('Error:', error);
                            alert('Error deleting image');
                        });
                    }
                });
            });
        });
    </script>

            </div>

        </div>

    </div>



    <script src="https://code.jquery.com/jquery-3.6.3.min.js"></script>

    <script>
        $(document).ready(function () {

            @foreach(config('app.locales') as $locale)

            ClassicEditor

                .create(document.querySelector('#description_{{ $locale }}'))

                .then(editor => {

                    console.log(editor);

                })

                .catch(error => {

                    console.error(error);

                });
            @endforeach

        });

    </script>

</x-admin.admin-layout>
