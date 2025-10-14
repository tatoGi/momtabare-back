@if(!isset($page))
    {{-- Standalone product index: use admin layout --}}
    <x-admin.admin-layout>
@endif

<div class="w-full py-4">
    <div class="flex flex-col md:flex-row justify-between items-center mb-6">
        <h2 class="text-2xl font-semibold text-gray-900 mb-3 md:mb-0">{{ __('admin.Products') }}</h2>
        <div class="flex gap-2">
            @if(isset($page))
                {{-- Page-specific context: Create product for this page --}}
                <a href="{{ route('admin.pages.products.create', ['locale' => app()->getLocale(), 'page' => $page->id]) }}"
                   class="bg-blue-500 hover:bg-blue-600 text-white px-4 py-2 rounded-lg flex items-center transition-colors">
                    <i class="fas fa-plus mr-2"></i> {{ __('admin.Add Product') }}
                </a>
            @else
                {{-- Standalone context: Create general product --}}
                <a href="{{ route('products.create', app()->getLocale()) }}"
                   class="bg-blue-500 hover:bg-blue-600 text-white px-4 py-2 rounded-lg flex items-center transition-colors">
                    <i class="fas fa-plus mr-2"></i> {{ __('admin.Add Product') }}
                </a>
            @endif
        </div>
    </div>

    @if(session('success'))
        <div class="bg-green-100 border border-green-400 text-green-700 px-4 py-3 rounded mb-4" role="alert">
            <span class="block sm:inline">{{ session('success') }}</span>
        </div>
    @endif

    <!-- Filter Bar -->
    <div class="bg-white shadow-sm rounded-lg p-4 mb-6">
        <form method="GET" action="{{ isset($page) ? route('admin.pages.products.index', ['locale' => app()->getLocale(), 'page' => $page->id]) : route('products.index', app()->getLocale()) }}" class="space-y-4">
            <div class="grid grid-cols-1 md:grid-cols-3 lg:grid-cols-4 gap-4">
                <!-- Product ID -->
                <div>
                    <label for="product_id" class="block text-sm font-medium text-gray-700 mb-1">Product ID</label>
                    <input type="text" name="product_id" id="product_id" value="{{ request('product_id') }}"
                           class="w-full rounded-md border-gray-300 shadow-sm focus:border-blue-500 focus:ring-blue-500 sm:text-sm">
                </div>

                <!-- Title -->
                <div>
                    <label for="title" class="block text-sm font-medium text-gray-700 mb-1">{{ __('admin.Title') }}</label>
                    <input type="text" name="title" id="title" value="{{ request('title') }}"
                           class="w-full rounded-md border-gray-300 shadow-sm focus:border-blue-500 focus:ring-blue-500 sm:text-sm">
                </div>

                <!-- Category -->
                <div>
                    <label for="category_id" class="block text-sm font-medium text-gray-700 mb-1">{{ __('admin.Category') }}</label>
                    <select name="category_id" id="category_id" class="w-full rounded-md border-gray-300 shadow-sm focus:border-blue-500 focus:ring-blue-500 sm:text-sm">
                        <option value="">All Categories</option>
                        @foreach(\App\Models\Category::all() as $category)
                            <option value="{{ $category->id }}" {{ request('category_id') == $category->id ? 'selected' : '' }}>
                                {{ $category->title }}
                            </option>
                        @endforeach
                    </select>
                </div>

                <!-- Brand -->
                <div>
                    <label for="brand" class="block text-sm font-medium text-gray-700 mb-1">Brand</label>
                    <select name="brand" id="brand" class="w-full rounded-md border-gray-300 shadow-sm focus:border-blue-500 focus:ring-blue-500 sm:text-sm">
                        <option value="">All Brands</option>
                        @php
                            $uniqueBrands = \App\Models\ProductTranslation::select('brand')
                                ->whereNotNull('brand')
                                ->groupBy('brand')
                                ->orderBy('brand')
                                ->pluck('brand');
                        @endphp
                        @foreach($uniqueBrands as $brand)
                            <option value="{{ $brand }}" {{ request('brand') == $brand ? 'selected' : '' }}>
                                {{ $brand }}
                            </option>
                        @endforeach
                    </select>
                </div>

                <!-- Color -->
                <div>
                    <label for="color" class="block text-sm font-medium text-gray-700 mb-1">Color</label>
                    <select name="color" id="color" class="w-full rounded-md border-gray-300 shadow-sm focus:border-blue-500 focus:ring-blue-500 sm:text-sm">
                        <option value="">All Colors</option>
                        @php
                            $colors = \App\Models\ProductTranslation::select('color')
                                ->whereNotNull('color')
                                ->where('color', '!=', '')
                                ->groupBy('color')
                                ->orderBy('color')
                                ->pluck('color');
                        @endphp
                        @foreach($colors as $color)
                            <option value="{{ $color }}" {{ request('color') == $color ? 'selected' : '' }}>
                                {{ $color }}
                            </option>
                        @endforeach
                    </select>
                </div>

                <!-- Size -->
                <div>
                    <label for="size" class="block text-sm font-medium text-gray-700 mb-1">Size</label>
                    <select name="size" id="size" class="w-full rounded-md border-gray-300 shadow-sm focus:border-blue-500 focus:ring-blue-500 sm:text-sm">
                        <option value="">All Sizes</option>
                        @php
                            $sizes = \App\Models\Product::select('size')
                                ->whereNotNull('size')
                                ->distinct()
                                ->orderBy('size')
                                ->pluck('size');
                        @endphp
                        @foreach($sizes as $size)
                            <option value="{{ $size }}" {{ request('size') == $size ? 'selected' : '' }}>
                                {{ $size }}
                            </option>
                        @endforeach
                    </select>
                </div>

                <!-- Location -->
                <div>
                    <label for="location" class="block text-sm font-medium text-gray-700 mb-1">Location</label>
                    <select name="location" id="location" class="w-full rounded-md border-gray-300 shadow-sm focus:border-blue-500 focus:ring-blue-500 sm:text-sm">
                        <option value="">All Locations</option>
                        @php
                            $locations = \App\Models\ProductTranslation::select('location')
                                ->whereNotNull('location')
                                ->where('location', '!=', '')
                                ->groupBy('location')
                                ->orderBy('location')
                                ->pluck('location');
                        @endphp
                        @foreach($locations as $location)
                            <option value="{{ $location }}" {{ request('location') == $location ? 'selected' : '' }}>
                                {{ $location }}
                            </option>
                        @endforeach
                    </select>
                </div>

                <!-- Status -->
                <div>
                    <label for="status" class="block text-sm font-medium text-gray-700 mb-1">{{ __('admin.Status') }}</label>
                    <select name="status" id="status" class="w-full rounded-md border-gray-300 shadow-sm focus:border-blue-500 focus:ring-blue-500 sm:text-sm">
                        <option value="">All Statuses</option>
                        <option value="1" {{ request('status') === '1' ? 'selected' : '' }}>{{ __('admin.Active') }}</option>
                        <option value="0" {{ request('status') === '0' ? 'selected' : '' }}>{{ __('admin.Inactive') }}</option>
                    </select>
                </div>

                <!-- Price Range -->
                <div class="col-span-2">
                    <label class="block text-sm font-medium text-gray-700 mb-1">{{ __('admin.Price Range') }}</label>
                    <div class="grid grid-cols-2 gap-3">
                        <div>
                            <label for="min_price" class="sr-only">{{ __('admin.Min Price') }}</label>
                            <div class="relative rounded-md shadow-sm">
                                <div class="absolute inset-y-0 left-0 pl-3 flex items-center pointer-events-none">
                                    <span class="text-gray-500 sm:text-sm">₾</span>
                                </div>
                                <input type="number" name="min_price" id="min_price"
                                       value="{{ request('min_price') }}"
                                       placeholder="{{ __('admin.Min') }}"
                                       min="0" step="0.01"
                                       class="pl-8 block w-full rounded-md border-gray-300 shadow-sm focus:border-blue-500 focus:ring-blue-500 sm:text-sm">
                            </div>
                        </div>
                        <div>
                            <label for="max_price" class="sr-only">{{ __('admin.Max Price') }}</label>
                            <div class="relative rounded-md shadow-sm">
                                <div class="absolute inset-y-0 left-0 pl-3 flex items-center pointer-events-none">
                                    <span class="text-gray-500 sm:text-sm">₾</span>
                                </div>
                                <input type="number" name="max_price" id="max_price"
                                       value="{{ request('max_price') }}"
                                       placeholder="{{ __('admin.Max') }}"
                                       min="0" step="0.01"
                                       class="pl-8 block w-full rounded-md border-gray-300 shadow-sm focus:border-blue-500 focus:ring-blue-500 sm:text-sm">
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <div class="flex justify-end space-x-3 pt-2">
                <a href="{{ isset($page) ? route('admin.pages.products.index', ['locale' => app()->getLocale(), 'page' => $page->id]) : route('products.index', app()->getLocale()) }}"
                   class="inline-flex items-center px-4 py-2 border border-gray-300 text-sm font-medium rounded-md text-gray-700 bg-white hover:bg-gray-50 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-blue-500">
                    {{ __('admin.Reset') }}
                </a>
                <button type="submit" class="inline-flex items-center px-4 py-2 border border-transparent text-sm font-medium rounded-md shadow-sm text-white bg-blue-600 hover:bg-blue-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-blue-500">
                    <i class="fas fa-filter mr-2"></i> {{ __('admin.Filter') }}
                </button>
            </div>
        </form>
    </div>

    <div class="bg-white shadow-md rounded-lg overflow-hidden">
        <div class="overflow-x-auto">
            <table class="min-w-full divide-y divide-gray-200">
                <thead class="bg-gradient-to-r from-gray-50 to-gray-100">
                    <tr>
                        <th class="px-4 py-3 text-left text-xs font-bold text-gray-600 uppercase tracking-wider">#</th>
                        <th class="px-4 py-3 text-left text-xs font-bold text-gray-600 uppercase tracking-wider">Product ID</th>
                        <th class="px-4 py-3 text-left text-xs font-bold text-gray-600 uppercase tracking-wider">{{ __('admin.Title') }}</th>
                        <th class="px-4 py-3 text-left text-xs font-bold text-gray-600 uppercase tracking-wider">{{ __('admin.Category') }}</th>
                        <th class="px-4 py-3 text-left text-xs font-bold text-gray-600 uppercase tracking-wider">Brand</th>
                        <th class="px-4 py-3 text-left text-xs font-bold text-gray-600 uppercase tracking-wider">Features</th>
                        <th class="px-4 py-3 text-left text-xs font-bold text-gray-600 uppercase tracking-wider">Location</th>
                        <th class="px-4 py-3 text-left text-xs font-bold text-gray-600 uppercase tracking-wider">{{ __('admin.Price') }}</th>
                        <th class="px-4 py-3 text-left text-xs font-bold text-gray-600 uppercase tracking-wider">Retailer</th>
                        <th class="px-4 py-3 text-center text-xs font-bold text-gray-600 uppercase tracking-wider">{{ __('admin.Status') }}</th>
                        <th class="px-4 py-3 text-center text-xs font-bold text-gray-600 uppercase tracking-wider">⭐ Favorite</th>
                        <th class="px-4 py-3 text-center text-xs font-bold text-gray-600 uppercase tracking-wider">{{ __('admin.Actions') }}</th>
                    </tr>
                </thead>
                <tbody class="bg-white divide-y divide-gray-200">
                    @forelse((isset($page) ? $page->products : $products) as $product)
                    <tr class="hover:bg-blue-50 transition-colors duration-150">
                        <td class="px-4 py-4 whitespace-nowrap text-sm font-semibold text-gray-700">{{ $loop->iteration }}</td>
                        <td class="px-4 py-4 whitespace-nowrap">
                            <span class="text-xs font-mono font-bold text-blue-700 bg-blue-100 px-3 py-1.5 rounded-md border border-blue-200">
                                {{ $product->product_identify_id }}
                            </span>
                        </td>
                        <td class="px-4 py-4 max-w-xs">
                            <div class="text-sm font-semibold text-gray-900 truncate" title="{{ $product->title }}">{{ $product->title }}</div>
                            @if($product->description)
                                <div class="text-xs text-gray-500 truncate mt-1" title="{{ strip_tags($product->description) }}">
                                    {{ Str::limit(strip_tags($product->description), 40) }}
                                </div>
                            @endif
                        </td>
                        <td class="px-4 py-4 whitespace-nowrap">
                            @if($product->category)
                                <span class="inline-flex items-center px-2.5 py-1 text-xs font-bold rounded-full bg-purple-100 text-purple-800 border border-purple-200">
                                    <svg class="w-3 h-3 mr-1" fill="currentColor" viewBox="0 0 20 20"><path d="M7 3a1 1 0 000 2h6a1 1 0 100-2H7zM4 7a1 1 0 011-1h10a1 1 0 110 2H5a1 1 0 01-1-1zM2 11a2 2 0 012-2h12a2 2 0 012 2v4a2 2 0 01-2 2H4a2 2 0 01-2-2v-4z"></path></svg>
                                    {{ $product->category->title }}
                                </span>
                            @else
                                <span class="text-sm text-gray-400 italic">No category</span>
                            @endif
                        </td>
                        <td class="px-4 py-4 whitespace-nowrap">
                            @if($product->brand)
                                <span class="text-sm font-medium text-gray-900">{{ $product->brand }}</span>
                            @else
                                <span class="text-sm text-gray-400">-</span>
                            @endif
                        </td>
                        <td class="px-4 py-4">
                            <div class="flex flex-wrap gap-1">
                                @if($product->color)
                                    <span class="inline-flex items-center px-2 py-0.5 text-xs font-semibold rounded-md bg-orange-100 text-orange-800 border border-orange-200">
                                        <svg class="w-3 h-3 mr-1" fill="currentColor" viewBox="0 0 20 20"><circle cx="10" cy="10" r="8"/></svg>
                                        {{ $product->color }}
                                    </span>
                                @endif
                                @if($product->size)
                                    <span class="inline-flex items-center px-2 py-0.5 text-xs font-semibold rounded-md bg-green-100 text-green-800 border border-green-200">
                                        <svg class="w-3 h-3 mr-1" fill="currentColor" viewBox="0 0 20 20"><path d="M3 4a1 1 0 011-1h12a1 1 0 011 1v2a1 1 0 01-1 1H4a1 1 0 01-1-1V4zM3 10a1 1 0 011-1h6a1 1 0 011 1v6a1 1 0 01-1 1H4a1 1 0 01-1-1v-6z"></path></svg>
                                        {{ $product->size }}
                                    </span>
                                @endif
                                @if(!$product->color && !$product->size)
                                    <span class="text-sm text-gray-400">-</span>
                                @endif
                            </div>
                        </td>
                        <td class="px-4 py-4 whitespace-nowrap">
                            @if($product->location)
                                <span class="inline-flex items-center text-sm text-gray-700">
                                    <svg class="w-4 h-4 mr-1 text-gray-500" fill="currentColor" viewBox="0 0 20 20"><path fill-rule="evenodd" d="M5.05 4.05a7 7 0 119.9 9.9L10 18.9l-4.95-4.95a7 7 0 010-9.9zM10 11a2 2 0 100-4 2 2 0 000 4z" clip-rule="evenodd"></path></svg>
                                    {{ $product->location }}
                                </span>
                            @else
                                <span class="text-sm text-gray-400">-</span>
                            @endif
                        </td>
                        <td class="px-4 py-4 whitespace-nowrap">
                            <span class="text-sm font-bold text-gray-900">{{ number_format((float)$product->price, 2) }}</span>
                            <span class="text-xs text-gray-500 ml-1">{{ $product->currency ?? config('app.currency', '₾') }}</span>
                        </td>
                        <td class="px-4 py-4 whitespace-nowrap">
                            @if($product->retailer_id)
                                <div class="text-sm">
                                    <div class="font-medium text-gray-900">{{ $product->retailer->first_name }} {{ $product->retailer->surname }}</div>
                                    <div class="text-xs text-gray-500">{{ $product->retailer->email }}</div>
                                    @if($product->status)
                                        <span class="inline-flex items-center px-2 py-0.5 text-xs font-bold rounded-md mt-1
                                            {{ $product->status === 'pending' ? 'bg-yellow-100 text-yellow-800 border border-yellow-200' :
                                               ($product->status === 'approved' ? 'bg-green-100 text-green-800 border border-green-200' : 'bg-red-100 text-red-800 border border-red-200') }}">
                                            {{ ucfirst($product->status) }}
                                        </span>
                                    @endif
                                </div>
                            @else
                                <span class="inline-flex items-center px-2 py-1 text-xs font-semibold rounded-md bg-indigo-100 text-indigo-800 border border-indigo-200">
                                    <svg class="w-3 h-3 mr-1" fill="currentColor" viewBox="0 0 20 20"><path d="M9 6a3 3 0 11-6 0 3 3 0 016 0zM17 6a3 3 0 11-6 0 3 3 0 016 0zM12.93 17c.046-.327.07-.66.07-1a6.97 6.97 0 00-1.5-4.33A5 5 0 0119 16v1h-6.07zM6 11a5 5 0 015 5v1H1v-1a5 5 0 015-5z"></path></svg>
                                    Admin Product
                                </span>
                            @endif
                        </td>
                        <td class="px-4 py-4 whitespace-nowrap text-center">
                            <span class="inline-flex items-center px-2.5 py-1 text-xs font-bold rounded-full {{ $product->active ? 'bg-green-100 text-green-800 border border-green-200' : 'bg-red-100 text-red-800 border border-red-200' }}">
                                @if($product->active)
                                    <svg class="w-3 h-3 mr-1" fill="currentColor" viewBox="0 0 20 20"><path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zm3.707-9.293a1 1 0 00-1.414-1.414L9 10.586 7.707 9.293a1 1 0 00-1.414 1.414l2 2a1 1 0 001.414 0l4-4z" clip-rule="evenodd"></path></svg>
                                @else
                                    <svg class="w-3 h-3 mr-1" fill="currentColor" viewBox="0 0 20 20"><path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zM8.707 7.293a1 1 0 00-1.414 1.414L8.586 10l-1.293 1.293a1 1 0 101.414 1.414L10 11.414l1.293 1.293a1 1 0 001.414-1.414L11.414 10l1.293-1.293a1 1 0 00-1.414-1.414L10 8.586 8.707 7.293z" clip-rule="evenodd"></path></svg>
                                @endif
                                {{ $product->active ? __('admin.Active') : __('admin.Inactive') }}
                            </span>
                        </td>
                        <td class="px-4 py-4 whitespace-nowrap text-center">
                            @if($product->is_favorite ?? false)
                                <span class="inline-flex items-center px-2.5 py-1 text-xs font-bold rounded-full bg-yellow-100 text-yellow-800 border border-yellow-200">
                                    <svg class="w-3 h-3 mr-1" fill="currentColor" viewBox="0 0 20 20"><path d="M9.049 2.927c.3-.921 1.603-.921 1.902 0l1.07 3.292a1 1 0 00.95.69h3.462c.969 0 1.371 1.24.588 1.81l-2.8 2.034a1 1 0 00-.364 1.118l1.07 3.292c.3.921-.755 1.688-1.54 1.118l-2.8-2.034a1 1 0 00-1.175 0l-2.8 2.034c-.784.57-1.838-.197-1.539-1.118l1.07-3.292a1 1 0 00-.364-1.118L2.98 8.72c-.783-.57-.38-1.81.588-1.81h3.461a1 1 0 00.951-.69l1.07-3.292z"></path></svg>
                                    Featured
                                </span>
                            @else
                                <span class="inline-flex items-center px-2.5 py-1 text-xs font-semibold rounded-md bg-gray-100 text-gray-600 border border-gray-200">
                                    Regular
                                </span>
                            @endif
                        </td>
                        <td class="px-4 py-4 whitespace-nowrap text-center">
                            <div class="flex justify-center space-x-3">
                                <a href="{{ route('products.edit', [app()->getLocale(), $product->id]) }}"
                                   class="inline-flex items-center px-3 py-1.5 bg-blue-100 text-blue-700 hover:bg-blue-200 rounded-md transition-colors duration-150 border border-blue-200"
                                   title="{{ __('admin.Edit') }}">
                                    <svg class="w-4 h-4 mr-1" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z"></path></svg>
                                    <span class="text-xs font-semibold">Edit</span>
                                </a>
                                <button type="button"
                                        class="inline-flex items-center px-3 py-1.5 bg-red-100 text-red-700 hover:bg-red-200 rounded-md transition-colors duration-150 border border-red-200 delete-product"
                                        data-id="{{ $product->id }}"
                                        @if(isset($page))data-page-id="{{ $page->id }}"@endif
                                        title="{{ __('admin.Delete') }}">
                                    <svg class="w-4 h-4 mr-1" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"></path></svg>
                                    <span class="text-xs font-semibold">Delete</span>
                                </button>
                            </div>
                        </td>
                    </tr>
                    @empty
                    <tr>
                        <td colspan="12" class="px-6 py-16 text-center">
                            <div class="flex flex-col items-center justify-center text-gray-400">
                                <svg class="w-20 h-20 mb-4 text-gray-300" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M20 7l-8-4-8 4m16 0l-8 4m8-4v10l-8 4m0-10L4 7m8 4v10M4 7v10l8 4"></path>
                                </svg>
                                <h3 class="text-xl font-semibold text-gray-700 mb-2">{{ __('admin.No products found') }}</h3>
                                <p class="text-sm text-gray-500 mb-6">{{ __('admin.Get started by adding your first product') }}</p>
                                @if(isset($page))
                                    <a href="{{ route('admin.pages.products.create', ['locale' => app()->getLocale(), 'page' => $page->id]) }}"
                                       class="inline-flex items-center px-4 py-2 bg-blue-600 hover:bg-blue-700 text-white rounded-lg transition-colors">
                                        <svg class="w-5 h-5 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"></path></svg>
                                        {{ __('admin.Add Product') }}
                                    </a>
                                @else
                                    <a href="{{ route('products.create', app()->getLocale()) }}"
                                       class="inline-flex items-center px-4 py-2 bg-blue-600 hover:bg-blue-700 text-white rounded-lg transition-colors">
                                        <svg class="w-5 h-5 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"></path></svg>
                                        {{ __('admin.Add Product') }}
                                    </a>
                                @endif
                            </div>
                        </td>
                    </tr>
                    @endforelse
                </tbody>
            </table>
        </div>

        <!-- Pagination -->
        @if(isset($page))
            {{-- Page-specific context: no pagination needed for page products --}}
        @else
            {{-- Standalone context: show pagination for all products --}}
            @if($products->hasPages())
                <div class="px-6 py-4 border-t border-gray-200">
                    {{ $products->links() }}
                </div>
            @endif
        @endif
    </div>
</div>

<!-- Delete Confirmation Modal -->
<div id="deleteModal" class="fixed inset-0 bg-gray-600 bg-opacity-50 overflow-y-auto h-full w-full hidden z-50">
    <div class="relative top-20 mx-auto p-5 border w-96 shadow-lg rounded-md bg-white">
        <div class="mt-3 text-center">
            <div class="mx-auto flex items-center justify-center h-12 w-12 rounded-full bg-red-100">
                <i class="fas fa-exclamation-triangle text-red-600"></i>
            </div>
            <h3 class="text-lg font-medium text-gray-900 mt-4">{{ __('admin.Confirm Deletion') }}</h3>
            <div class="mt-2 px-7 py-3">
                <p class="text-sm text-gray-500">
                    {{ __('admin.Are you sure you want to delete this product? This action cannot be undone.') }}
                </p>
            </div>
            <div class="flex justify-center space-x-4 mt-4">
                <button id="cancelDelete"
                        class="bg-gray-300 hover:bg-gray-400 text-gray-800 px-4 py-2 rounded-lg transition-colors">
                    {{ __('admin.Cancel') }}
                </button>
                <form id="deleteForm" method="POST" class="inline">
                    @csrf
                    @method('DELETE')
                    <input type="hidden" name="page_id" id="deletePageId" value="">
                    <button type="submit"
                            class="bg-red-500 hover:bg-red-600 text-white px-4 py-2 rounded-lg transition-colors">
                        {{ __('admin.Delete') }}
                    </button>
                </form>
            </div>
        </div>
    </div>
</div>

<script>
document.addEventListener('DOMContentLoaded', function() {
    // Delete product with confirmation
    const deleteButtons = document.querySelectorAll('.delete-product');
    const deleteModal = document.getElementById('deleteModal');
    const deleteForm = document.getElementById('deleteForm');
    const deletePageId = document.getElementById('deletePageId');
    const cancelDelete = document.getElementById('cancelDelete');

    deleteButtons.forEach(button => {
        button.addEventListener('click', function() {
            const productId = this.getAttribute('data-id');
            const pageId = this.getAttribute('data-page-id');

            deleteForm.action = `/{{ app()->getLocale() }}/admin/products/${productId}`;
            deletePageId.value = pageId;

            deleteModal.classList.remove('hidden');
        });
    });

    // Close modal on cancel
    cancelDelete.addEventListener('click', function() {
        deleteModal.classList.add('hidden');
    });

    // Close modal when clicking outside
    deleteModal.addEventListener('click', function(e) {
        if (e.target === deleteModal) {
            deleteModal.classList.add('hidden');
        }
    });

    // Auto-hide success message after 5 seconds
    const alert = document.querySelector('.bg-green-100');
    if (alert) {
        setTimeout(() => {
            alert.style.display = 'none';
        }, 5000);
    }
});
</script>

@if(!isset($page))
    {{-- Close admin layout for standalone product index --}}
    </x-admin.admin-layout>
@endif
