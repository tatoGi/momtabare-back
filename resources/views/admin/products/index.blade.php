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
                            $colors = \App\Models\Product::select('color')
                                ->whereNotNull('color')
                                ->distinct()
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
                            $locations = \App\Models\Product::select('location')
                                ->whereNotNull('location')
                                ->distinct()
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

    <div class="bg-white shadow-sm rounded-lg overflow-hidden">
        <div class="overflow-x-auto">
            <table class="min-w-full divide-y divide-gray-200">
                <thead class="bg-gray-50">
                    <tr>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">#</th>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Product ID</th>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">{{ __('admin.Title') }}</th>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">{{ __('admin.Category') }}</th>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Brand</th>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Features</th>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Location</th>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">{{ __('admin.Price') }}</th>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Retailer</th>
                        <th class="px-6 py-3 text-center text-xs font-medium text-gray-500 uppercase tracking-wider">{{ __('admin.Status') }}</th>
                        <th class="px-6 py-3 text-center text-xs font-medium text-gray-500 uppercase tracking-wider">⭐ Favorite</th>
                        <th class="px-6 py-3 text-right text-xs font-medium text-gray-500 uppercase tracking-wider">{{ __('admin.Actions') }}</th>
                    </tr>
                </thead>
                <tbody class="bg-white divide-y divide-gray-200">
                    @forelse((isset($page) ? $page->products : $products) as $product)
                    <tr class="hover:bg-gray-50">
                        <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900">{{ $loop->iteration }}</td>
                        <td class="px-6 py-4 whitespace-nowrap">
                            <span class="text-sm font-mono text-blue-600 bg-blue-50 px-2 py-1 rounded">
                                {{ $product->product_identify_id }}
                            </span>
                        </td>
                        <td class="px-6 py-4">
                            <div class="text-sm font-medium text-gray-900">{{ $product->title }}</div>
                            @if($product->description)
                                <div class="text-sm text-gray-500 truncate max-w-xs" title="{{ strip_tags($product->description) }}">
                                    {{ Str::limit(strip_tags($product->description), 50) }}
                                </div>
                            @endif
                        </td>
                        <td class="px-6 py-4 whitespace-nowrap">
                            @if($product->category)
                                <span class="inline-flex px-2 py-1 text-xs font-semibold rounded-full bg-purple-100 text-purple-800">
                                    {{ $product->category->title }}
                                </span>
                            @else
                                <span class="text-sm text-gray-400">No category</span>
                            @endif
                        </td>
                        <td class="px-6 py-4 whitespace-nowrap">
                            @if($product->brand)
                                <span class="text-sm text-gray-900">{{ $product->brand }}</span>
                            @else
                                <span class="text-sm text-gray-400">-</span>
                            @endif
                        </td>
                        <td class="px-6 py-4">
                            <div class="flex flex-wrap gap-1">
                                @if($product->color)
                                    <span class="inline-flex px-2 py-1 text-xs font-semibold rounded-full bg-orange-100 text-orange-800">
                                        {{ $product->color }}
                                    </span>
                                @endif
                                @if($product->size)
                                    <span class="inline-flex px-2 py-1 text-xs font-semibold rounded-full bg-green-100 text-green-800">
                                        {{ $product->size }}
                                    </span>
                                @endif
                                @if(!$product->color && !$product->size)
                                    <span class="text-sm text-gray-400">-</span>
                                @endif
                            </div>
                        </td>
                        <td class="px-6 py-4 whitespace-nowrap">
                            @if($product->location)
                                <span class="text-sm text-gray-900">{{ $product->location }}</span>
                            @else
                                <span class="text-sm text-gray-400">-</span>
                            @endif
                        </td>
                        <td class="px-6 py-4 whitespace-nowrap text-sm font-medium text-gray-900">
                            {{ number_format((float)$product->price, 2) }} {{ $product->currency ?? config('app.currency', '$') }}
                        </td>
                        <td class="px-6 py-4 whitespace-nowrap">
                            @if($product->retailer_id)
                                <div class="text-sm">
                                    <div class="font-medium text-gray-900">{{ $product->retailer->first_name }} {{ $product->retailer->surname }}</div>
                                    <div class="text-gray-500">{{ $product->retailer->email }}</div>
                                    @if($product->status)
                                        <span class="inline-flex px-2 py-1 text-xs font-semibold rounded-full mt-1 
                                            {{ $product->status === 'pending' ? 'bg-yellow-100 text-yellow-800' : 
                                               ($product->status === 'approved' ? 'bg-green-100 text-green-800' : 'bg-red-100 text-red-800') }}">
                                            {{ ucfirst($product->status) }}
                                        </span>
                                    @endif
                                </div>
                            @else
                                <span class="text-sm text-gray-400">Admin Product</span>
                            @endif
                        </td>
                        <td class="px-6 py-4 whitespace-nowrap text-center">
                            <span class="inline-flex px-2 py-1 text-xs font-semibold rounded-full {{ $product->active ? 'bg-green-100 text-green-800' : 'bg-red-100 text-red-800' }}">
                                {{ $product->active ? __('admin.Active') : __('admin.Inactive') }}
                            </span>
                        </td>
                        <td class="px-6 py-4 whitespace-nowrap text-center">
                            @if($product->is_favorite ?? false)
                                <span class="inline-flex px-2 py-1 text-xs font-semibold rounded-full bg-yellow-100 text-yellow-800">
                                    ⭐ Featured
                                </span>
                            @else
                                <span class="inline-flex px-2 py-1 text-xs font-semibold rounded-full bg-gray-100 text-gray-600">
                                    Regular
                                </span>
                            @endif
                        </td>
                        <td class="px-6 py-4 whitespace-nowrap text-right text-sm font-medium">
                            <div class="flex justify-end space-x-2">
                                <a href="{{ route('products.edit', [app()->getLocale(), $product->id]) }}" 
                                   class="text-blue-600 hover:text-blue-900 transition-colors"
                                   title="{{ __('admin.Edit') }}">
                                    <i class="fas fa-edit"></i>
                                </a>
                                <button type="button" 
                                        class="text-red-600 hover:text-red-900 transition-colors delete-product"
                                        data-id="{{ $product->id }}"
                                        @if(isset($page))data-page-id="{{ $page->id }}"@endif
                                        title="{{ __('admin.Delete') }}">
                                    <i class="fas fa-trash"></i>
                                </button>
                            </div>
                        </td>
                    </tr>
                    @empty
                    <tr>
                        <td colspan="12" class="px-6 py-12 text-center">
                            <div class="text-gray-400">
                                <i class="fas fa-box-open text-4xl mb-4"></i>
                                <h3 class="text-lg font-medium text-gray-900 mb-2">{{ __('admin.No products found') }}</h3>
                                <p class="text-sm text-gray-500">{{ __('admin.Get started by adding your first product') }}</p>
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