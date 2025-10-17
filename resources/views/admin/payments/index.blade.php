<x-admin.admin-layout>
    <section class="container mx-auto p-6 font-mono">
        <!-- Statistics Cards -->
        <div class="grid grid-cols-1 md:grid-cols-4 gap-4 mb-6">
            <div class="bg-white rounded-lg shadow p-4">
                <div class="flex items-center justify-between">
                    <div>
                        <p class="text-gray-500 text-sm">Total Payments</p>
                        <p class="text-2xl font-bold">{{ $stats['total_payments'] }}</p>
                    </div>
                    <i class="fas fa-receipt text-blue-500 text-3xl"></i>
                </div>
            </div>
            <div class="bg-white rounded-lg shadow p-4">
                <div class="flex items-center justify-between">
                    <div>
                        <p class="text-gray-500 text-sm">Completed</p>
                        <p class="text-2xl font-bold text-green-600">{{ $stats['completed_payments'] }}</p>
                    </div>
                    <i class="fas fa-check-circle text-green-500 text-3xl"></i>
                </div>
            </div>
            <div class="bg-white rounded-lg shadow p-4">
                <div class="flex items-center justify-between">
                    <div>
                        <p class="text-gray-500 text-sm">Pending</p>
                        <p class="text-2xl font-bold text-yellow-600">{{ $stats['pending_payments'] }}</p>
                    </div>
                    <i class="fas fa-clock text-yellow-500 text-3xl"></i>
                </div>
            </div>
            <div class="bg-white rounded-lg shadow p-4">
                <div class="flex items-center justify-between">
                    <div>
                        <p class="text-gray-500 text-sm">Total Amount</p>
                        <p class="text-2xl font-bold text-blue-600">{{ number_format($stats['total_amount'], 2) }} ₾</p>
                    </div>
                    <i class="fas fa-money-bill-wave text-blue-500 text-3xl"></i>
                </div>
            </div>
        </div>

        <!-- Filters -->
        <div class="w-full mb-6 bg-white rounded-lg shadow-lg p-4">
            <form method="GET" action="{{ route('admin.payments.index') }}" class="grid grid-cols-1 md:grid-cols-4 gap-4">
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-1">Search</label>
                    <input type="text" name="search" value="{{ request('search') }}"
                           placeholder="Order ID, User ID..."
                           class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-blue-500">
                </div>
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-1">Status</label>
                    <select name="status" class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-blue-500">
                        <option value="">All Statuses</option>
                        <option value="completed" {{ request('status') == 'completed' ? 'selected' : '' }}>Completed</option>
                        <option value="pending" {{ request('status') == 'pending' ? 'selected' : '' }}>Pending</option>
                        <option value="failed" {{ request('status') == 'failed' ? 'selected' : '' }}>Failed</option>
                    </select>
                </div>
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-1">Date From</label>
                    <input type="date" name="date_from" value="{{ request('date_from') }}"
                           class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-blue-500">
                </div>
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-1">Date To</label>
                    <input type="date" name="date_to" value="{{ request('date_to') }}"
                           class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-blue-500">
                </div>
                <div class="md:col-span-4 flex gap-2">
                    <button type="submit" class="px-4 py-2 bg-blue-500 text-white rounded-md hover:bg-blue-600">
                        <i class="fas fa-search mr-2"></i>Filter
                    </button>
                    <a href="{{ route('admin.payments.index') }}" class="px-4 py-2 bg-gray-500 text-white rounded-md hover:bg-gray-600">
                        <i class="fas fa-times mr-2"></i>Clear
                    </a>
                    <a href="{{ route('admin.payments.export', request()->all()) }}" class="px-4 py-2 bg-green-500 text-white rounded-md hover:bg-green-600 ml-auto">
                        <i class="fas fa-download mr-2"></i>Export CSV
                    </a>
                </div>
            </form>
        </div>

        <!-- Payments Table -->
        <div class="w-full mb-8 overflow-hidden rounded-lg shadow-lg">
            <div class="w-full overflow-x-auto">
                <table class="w-full">
                    <thead>
                        <tr class="text-md font-semibold tracking-wide text-left text-gray-900 bg-gray-100 uppercase border-b border-gray-600">
                            <th class="px-4 py-3">ID</th>
                            <th class="px-4 py-3">BOG Order ID</th>
                            <th class="px-4 py-3">User</th>
                            <th class="px-4 py-3">Amount</th>
                            <th class="px-4 py-3">Status</th>
                            <th class="px-4 py-3">Products</th>
                            <th class="px-4 py-3">Date</th>
                            <th class="px-4 py-3">Actions</th>
                        </tr>
                    </thead>
                    <tbody class="bg-white">
                        @forelse ($payments as $payment)
                            @php
                                $webUserId = $payment->request_payload['web_user_id'] ?? null;
                                $webUser = $webUserId ? \App\Models\WebUser::find($webUserId) : null;
                            @endphp
                            <tr class="text-gray-700">
                                <td class="px-4 py-3 border">
                                    <span class="font-semibold">#{{ $payment->id }}</span>
                                </td>
                                <td class="px-4 py-3 border">
                                    <span class="text-xs font-mono">{{ $payment->bog_order_id }}</span>
                                </td>
                                <td class="px-4 py-3 border">
                                    @if($webUser)
                                        <div>
                                            <div class="font-semibold">{{ $webUser->first_name }} {{ $webUser->surname }}</div>
                                            <div class="text-xs text-gray-500">{{ $webUser->email }}</div>
                                        </div>
                                    @else
                                        <span class="text-gray-400">Guest</span>
                                    @endif
                                </td>
                                <td class="px-4 py-3 border">
                                    <span class="font-bold text-green-600">{{ number_format($payment->amount, 2) }} {{ $payment->currency }}</span>
                                </td>
                                <td class="px-4 py-3 border">
                                    @if(in_array(strtolower($payment->status), ['completed', 'approved', 'succeeded']))
                                        <span class="px-2 py-1 text-xs font-semibold rounded-full bg-green-100 text-green-800">
                                            {{ ucfirst($payment->status) }}
                                        </span>
                                    @elseif($payment->status === 'pending' || $payment->status === 'created')
                                        <span class="px-2 py-1 text-xs font-semibold rounded-full bg-yellow-100 text-yellow-800">
                                            {{ ucfirst($payment->status) }}
                                        </span>
                                    @else
                                        <span class="px-2 py-1 text-xs font-semibold rounded-full bg-red-100 text-red-800">
                                            {{ ucfirst($payment->status) }}
                                        </span>
                                    @endif
                                </td>
                                <td class="px-4 py-3 border">
                                    <span class="px-2 py-1 bg-blue-100 text-blue-800 rounded-full text-xs font-semibold">
                                        {{ $payment->products->count() }} items
                                    </span>
                                </td>
                                <td class="px-4 py-3 border">
                                    <div class="text-sm">{{ $payment->created_at->format('Y-m-d') }}</div>
                                    <div class="text-xs text-gray-500">{{ $payment->created_at->format('H:i') }}</div>
                                </td>
                                <td class="px-4 py-3 border">
                                    <a href="/{{ app()->getlocale() }}/admin/payments/{{ $payment->id }}"
                                       class="px-3 py-1 text-sm bg-blue-500 text-white rounded hover:bg-blue-600">
                                        <i class="fas fa-eye mr-1"></i>View
                                    </a>
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="8" class="px-4 py-8 text-center text-gray-500">
                                    <i class="fas fa-inbox text-4xl mb-2"></i>
                                    <p>No payments found</p>
                                </td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>

        <!-- Pagination -->
        <div class="mt-4">
            {{ $payments->links() }}
        </div>
    </section>
</x-admin.admin-layout>
