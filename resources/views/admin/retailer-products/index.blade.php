<x-admin.admin-layout>
    <section class="p-6">
        <div class="mb-6">
            <h1 class="text-2xl font-bold text-gray-900 dark:text-white">Retailer Products Management</h1>
            <p class="text-gray-600 dark:text-gray-400">Review and manage products submitted by retailers</p>
        </div>

        <!-- Filter Tabs -->
        <div class="mb-6">
            <div class="border-b border-gray-200 dark:border-gray-700">
                <nav class="-mb-px flex space-x-8">
                    <a href="{{ url()->current() }}" 
                       class="border-b-2 {{ !request('status') ? 'border-blue-500 text-blue-600' : 'border-transparent text-gray-500 hover:text-gray-700 hover:border-gray-300' }} whitespace-nowrap py-2 px-1 text-sm font-medium">
                        All Products ({{ $products->total() }})
                    </a>
                    <a href="{{ url()->current() }}?status=pending" 
                       class="border-b-2 {{ request('status') === 'pending' ? 'border-yellow-500 text-yellow-600' : 'border-transparent text-gray-500 hover:text-gray-700 hover:border-gray-300' }} whitespace-nowrap py-2 px-1 text-sm font-medium">
                        Pending Review
                    </a>
                    <a href="{{ url()->current() }}?status=approved" 
                       class="border-b-2 {{ request('status') === 'approved' ? 'border-green-500 text-green-600' : 'border-transparent text-gray-500 hover:text-gray-700 hover:border-gray-300' }} whitespace-nowrap py-2 px-1 text-sm font-medium">
                        Approved
                    </a>
                    <a href="{{ url()->current() }}?status=rejected" 
                       class="border-b-2 {{ request('status') === 'rejected' ? 'border-red-500 text-red-600' : 'border-transparent text-gray-500 hover:text-gray-700 hover:border-gray-300' }} whitespace-nowrap py-2 px-1 text-sm font-medium">
                        Rejected
                    </a>
                </nav>
            </div>
        </div>

        <!-- Products Table -->
        <div class="bg-white dark:bg-gray-800 shadow-sm rounded-lg overflow-hidden">
            <div class="overflow-x-auto">
                <table class="min-w-full divide-y divide-gray-200 dark:divide-gray-700">
                    <thead class="bg-gray-50 dark:bg-gray-700">
                        <tr>
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-300 uppercase tracking-wider">Product</th>
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-300 uppercase tracking-wider">Retailer</th>
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-300 uppercase tracking-wider">Category</th>
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-300 uppercase tracking-wider">Price</th>
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-300 uppercase tracking-wider">Status</th>
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-300 uppercase tracking-wider">Submitted</th>
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-300 uppercase tracking-wider">Actions</th>
                        </tr>
                    </thead>
                    <tbody class="bg-white dark:bg-gray-800 divide-y divide-gray-200 dark:divide-gray-700">
                        @forelse($products as $product)
                        <tr class="hover:bg-gray-50 dark:hover:bg-gray-700">
                            <td class="px-6 py-4 whitespace-nowrap">
                                <div class="flex items-center">
                                    @if($product->images->first())
                                        <img class="h-12 w-12 rounded-lg object-cover mr-4" 
                                             src="{{ asset('storage/' . $product->images->first()->image_path) }}" 
                                             alt="{{ $product->translate('ka')->title ?? 'Product Image' }}">
                                    @else
                                        <div class="h-12 w-12 rounded-lg bg-gray-200 dark:bg-gray-600 mr-4 flex items-center justify-center">
                                            <svg class="h-6 w-6 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 16l4.586-4.586a2 2 0 012.828 0L16 16m-2-2l1.586-1.586a2 2 0 012.828 0L20 14m-6-6h.01M6 20h12a2 2 0 002-2V6a2 2 0 00-2-2H6a2 2 0 00-2 2v12a2 2 0 002 2z"></path>
                                            </svg>
                                        </div>
                                    @endif
                                    <div>
                                        <div class="text-sm font-medium text-gray-900 dark:text-white">
                                            {{ $product->translate('ka')->title ?? 'N/A' }}
                                        </div>
                                        <div class="text-sm text-gray-500 dark:text-gray-400">
                                            ID: {{ $product->product_identify_id }}
                                        </div>
                                    </div>
                                </div>
                            </td>
                            <td class="px-6 py-4 whitespace-nowrap">
                                <div class="text-sm text-gray-900 dark:text-white">{{ $product->retailer->first_name }} {{ $product->retailer->surname }}</div>
                                <div class="text-sm text-gray-500 dark:text-gray-400">{{ $product->retailer->email }}</div>
                            </td>
                            <td class="px-6 py-4 whitespace-nowrap">
                                <div class="text-sm text-gray-900 dark:text-white">
                                    {{ $product->category->translate('ka')->name ?? 'N/A' }}
                                </div>
                            </td>
                            <td class="px-6 py-4 whitespace-nowrap">
                                <div class="text-sm font-medium text-gray-900 dark:text-white">
                                    {{ number_format($product->price, 2) }} {{ $product->currency }}
                                </div>
                                @if($product->rental_period)
                                    <div class="text-xs text-gray-500 dark:text-gray-400">{{ $product->rental_period }}</div>
                                @endif
                            </td>
                            <td class="px-6 py-4 whitespace-nowrap">
                                @if($product->status === 'pending')
                                    <span class="inline-flex px-2 py-1 text-xs font-semibold rounded-full bg-yellow-100 text-yellow-800 dark:bg-yellow-900 dark:text-yellow-200">
                                        Pending
                                    </span>
                                @elseif($product->status === 'approved')
                                    <span class="inline-flex px-2 py-1 text-xs font-semibold rounded-full bg-green-100 text-green-800 dark:bg-green-900 dark:text-green-200">
                                        Approved
                                    </span>
                                @elseif($product->status === 'rejected')
                                    <span class="inline-flex px-2 py-1 text-xs font-semibold rounded-full bg-red-100 text-red-800 dark:bg-red-900 dark:text-red-200">
                                        Rejected
                                    </span>
                                @endif
                            </td>
                            <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500 dark:text-gray-400">
                                {{ $product->created_at->format('M d, Y') }}
                                <div class="text-xs">{{ $product->created_at->format('H:i') }}</div>
                            </td>
                            <td class="px-6 py-4 whitespace-nowrap text-sm font-medium">
                                @if($product->status === 'pending')
                                    <button onclick="approveProduct({{ $product->id }})" 
                                            class="inline-flex items-center px-3 py-1 border border-transparent text-xs font-medium rounded-md text-white bg-green-600 hover:bg-green-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-green-500 mr-2">
                                        Approve
                                    </button>
                                    <button onclick="rejectProduct({{ $product->id }})" 
                                            class="inline-flex items-center px-3 py-1 border border-transparent text-xs font-medium rounded-md text-white bg-red-600 hover:bg-red-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-red-500">
                                        Reject
                                    </button>
                                @else
                                    <span class="text-gray-400 text-xs">{{ ucfirst($product->status) }}</span>
                                @endif
                            </td>
                        </tr>
                        @empty
                        <tr>
                            <td colspan="7" class="px-6 py-12 text-center">
                                <div class="text-gray-500 dark:text-gray-400">
                                    <svg class="mx-auto h-12 w-12 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M20 13V6a2 2 0 00-2-2H6a2 2 0 00-2 2v7m14 0v5a2 2 0 01-2 2H6a2 2 0 01-2-2v-5m14 0H6m0 0l3-3m-3 3l3 3m8-6l3 3m-3-3l3-3"></path>
                                    </svg>
                                    <h3 class="mt-2 text-sm font-medium text-gray-900 dark:text-white">No products found</h3>
                                    <p class="mt-1 text-sm text-gray-500 dark:text-gray-400">No retailer products match the current filter.</p>
                                </div>
                            </td>
                        </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
            
            <!-- Pagination -->
            @if($products->hasPages())
                <div class="px-6 py-3 border-t border-gray-200 dark:border-gray-700">
                    {{ $products->appends(request()->query())->links() }}
                </div>
            @endif
        </div>
    </section>

    <script>
        async function approveProduct(productId) {
            if (!confirm('Are you sure you want to approve this product?')) {
                return;
            }

            try {
                const response = await fetch(`/{{ app()->getLocale() }}/admin/retailer-products/${productId}/approve`, {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/json',
                        'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content')
                    }
                });

                const data = await response.json();

                if (data.success) {
                    alert('Product approved successfully!');
                    location.reload();
                } else {
                    alert('Error: ' + data.message);
                }
            } catch (error) {
                alert('An error occurred while approving the product.');
                console.error(error);
            }
        }

        async function rejectProduct(productId) {
            if (!confirm('Are you sure you want to reject this product?')) {
                return;
            }

            try {
                const response = await fetch(`/{{ app()->getLocale() }}/admin/retailer-products/${productId}/reject`, {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/json',
                        'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content')
                    }
                });

                const data = await response.json();

                if (data.success) {
                    alert('Product rejected.');
                    location.reload();
                } else {
                    alert('Error: ' + data.message);
                }
            } catch (error) {
                alert('An error occurred while rejecting the product.');
                console.error(error);
            }
        }
    </script>
</x-admin.admin-layout>
