<x-admin.admin-layout>
    <section class="container mx-auto p-6 font-mono">
        <!-- Back Button -->
        <div class="mb-4">
            <a href="{{ route('admin.payments.index') }}" class="text-blue-500 hover:text-blue-700">
                <i class="fas fa-arrow-left mr-2"></i>Back to Payments
            </a>
        </div>

        <!-- Payment Header -->
        <div class="bg-white rounded-lg shadow-lg p-6 mb-6">
            <div class="flex justify-between items-start mb-4">
                <div>
                    <h1 class="text-2xl font-bold mb-2">Payment #{{ $payment->id }}</h1>
                    <p class="text-gray-500 text-sm font-mono">BOG Order: {{ $payment->bog_order_id }}</p>
                </div>
                <div class="text-right">
                    @if(in_array(strtolower($payment->status), ['completed', 'approved', 'succeeded']))
                        <span class="px-4 py-2 text-sm font-semibold rounded-full bg-green-100 text-green-800">
                            {{ ucfirst($payment->status) }}
                        </span>
                    @elseif($payment->status === 'pending' || $payment->status === 'created')
                        <span class="px-4 py-2 text-sm font-semibold rounded-full bg-yellow-100 text-yellow-800">
                            {{ ucfirst($payment->status) }}
                        </span>
                    @else
                        <span class="px-4 py-2 text-sm font-semibold rounded-full bg-red-100 text-red-800">
                            {{ ucfirst($payment->status) }}
                        </span>
                    @endif
                    <div class="mt-2">
                        <p class="text-3xl font-bold text-green-600">{{ number_format($payment->amount, 2) }} {{ $payment->currency }}</p>
                    </div>
                </div>
            </div>

            <!-- Payment Info Grid -->
            <div class="grid grid-cols-1 md:grid-cols-3 gap-4 mt-6">
                <div class="border-l-4 border-blue-500 pl-4">
                    <p class="text-gray-500 text-sm">External Order ID</p>
                    <p class="font-semibold">{{ $payment->external_order_id ?? 'N/A' }}</p>
                </div>
                <div class="border-l-4 border-purple-500 pl-4">
                    <p class="text-gray-500 text-sm">Created At</p>
                    <p class="font-semibold">{{ $payment->created_at->format('Y-m-d H:i:s') }}</p>
                </div>
                <div class="border-l-4 border-green-500 pl-4">
                    <p class="text-gray-500 text-sm">Verified At</p>
                    <p class="font-semibold">{{ $payment->verified_at ? $payment->verified_at->format('Y-m-d H:i:s') : 'Not verified' }}</p>
                </div>
            </div>
        </div>

        <!-- Customer Information -->
        @if($webUser)
        <div class="bg-white rounded-lg shadow-lg p-6 mb-6">
            <h2 class="text-xl font-bold mb-4 flex items-center">
                <i class="fas fa-user mr-2 text-blue-500"></i>Customer Information
            </h2>
            <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                <div>
                    <p class="text-gray-500 text-sm">Name</p>
                    <p class="font-semibold">{{ $webUser->first_name }} {{ $webUser->surname }}</p>
                </div>
                <div>
                    <p class="text-gray-500 text-sm">Email</p>
                    <p class="font-semibold">{{ $webUser->email }}</p>
                </div>
                <div>
                    <p class="text-gray-500 text-sm">Phone</p>
                    <p class="font-semibold">{{ $webUser->phone ?? 'N/A' }}</p>
                </div>
                <div>
                    <p class="text-gray-500 text-sm">User ID</p>
                    <p class="font-semibold">#{{ $webUser->id }}</p>
                </div>
            </div>
        </div>
        @endif

        <!-- Products Table -->
        <div class="bg-white rounded-lg shadow-lg p-6 mb-6">
            <h2 class="text-xl font-bold mb-4 flex items-center">
                <i class="fas fa-shopping-cart mr-2 text-blue-500"></i>Ordered Products
            </h2>
            <div class="overflow-x-auto">
                <table class="w-full">
                    <thead>
                        <tr class="text-sm font-semibold tracking-wide text-left text-gray-900 bg-gray-100 uppercase border-b">
                            <th class="px-4 py-3">Product</th>
                            <th class="px-4 py-3">Quantity</th>
                            <th class="px-4 py-3">Unit Price</th>
                            <th class="px-4 py-3">Total</th>
                            <th class="px-4 py-3">Rental Duration</th>
                            <th class="px-4 py-3">Status</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse ($productsWithRental as $product)
                            <tr class="border-b">
                                <td class="px-4 py-3">
                                    <div>
                                        <p class="font-semibold">{{ $product['name_ka'] }}</p>
                                        <p class="text-xs text-gray-500">{{ $product['name_en'] }}</p>
                                        <a href="{{ url('/products/' . $product['slug']) }}" target="_blank" class="text-xs text-blue-500 hover:underline">
                                            View Product →
                                        </a>
                                    </div>
                                </td>
                                <td class="px-4 py-3">
                                    <span class="px-2 py-1 bg-gray-100 rounded">x{{ $product['quantity'] }}</span>
                                </td>
                                <td class="px-4 py-3">
                                    <span class="font-semibold">{{ number_format($product['unit_price'], 2) }} ₾</span>
                                </td>
                                <td class="px-4 py-3">
                                    <span class="font-bold text-green-600">{{ number_format($product['total_price'], 2) }} ₾</span>
                                </td>
                                <td class="px-4 py-3">
                                    @if($product['rental_info'])
                                        <div class="text-sm">
                                            <div class="flex items-center mb-1">
                                                <i class="fas fa-calendar-alt text-blue-500 mr-2"></i>
                                                <span class="font-semibold">{{ $product['rental_info']['duration_text'] }}</span>
                                            </div>
                                            <div class="text-xs text-gray-500">
                                                <i class="fas fa-clock mr-1"></i>
                                                {{ $product['rental_info']['start_date'] }}
                                            </div>
                                            <div class="text-xs text-gray-500">
                                                <i class="fas fa-clock mr-1"></i>
                                                {{ $product['rental_info']['end_date'] }}
                                            </div>
                                        </div>
                                    @else
                                        <span class="text-gray-400 text-sm">Purchase (No Rental)</span>
                                    @endif
                                </td>
                                <td class="px-4 py-3">
                                    @if($product['is_ordered'])
                                        <span class="px-2 py-1 text-xs font-semibold rounded-full bg-green-100 text-green-800">
                                            <i class="fas fa-check-circle mr-1"></i>Ordered
                                        </span>
                                        @if($product['ordered_at'])
                                            <div class="text-xs text-gray-500 mt-1">
                                                {{ \Carbon\Carbon::parse($product['ordered_at'])->format('Y-m-d H:i') }}
                                            </div>
                                        @endif
                                    @else
                                        <span class="px-2 py-1 text-xs font-semibold rounded-full bg-gray-100 text-gray-800">
                                            Not Ordered
                                        </span>
                                    @endif
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="6" class="px-4 py-8 text-center text-gray-500">
                                    No products found in this payment
                                </td>
                            </tr>
                        @endforelse
                    </tbody>
                    <tfoot>
                        <tr class="bg-gray-50 font-bold">
                            <td colspan="3" class="px-4 py-3 text-right">Total Amount:</td>
                            <td class="px-4 py-3 text-green-600 text-lg">
                                {{ number_format($payment->amount, 2) }} {{ $payment->currency }}
                            </td>
                            <td colspan="2"></td>
                        </tr>
                    </tfoot>
                </table>
            </div>
        </div>

        <!-- Payment Details JSON (for debugging) -->
        <div class="bg-white rounded-lg shadow-lg p-6">
            <h2 class="text-xl font-bold mb-4 flex items-center">
                <i class="fas fa-code mr-2 text-blue-500"></i>Technical Details
            </h2>
            <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                <div>
                    <h3 class="font-semibold mb-2">Request Payload</h3>
                    <pre class="bg-gray-100 p-4 rounded text-xs overflow-auto max-h-64">{{ json_encode($payment->request_payload, JSON_PRETTY_PRINT) }}</pre>
                </div>
                <div>
                    <h3 class="font-semibold mb-2">Response Data</h3>
                    <pre class="bg-gray-100 p-4 rounded text-xs overflow-auto max-h-64">{{ json_encode($payment->response_data, JSON_PRETTY_PRINT) }}</pre>
                </div>
            </div>
        </div>
    </section>
</x-admin.admin-layout>
