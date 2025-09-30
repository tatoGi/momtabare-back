<x-admin.admin-layout>
    <section class="container mx-auto p-6 font-mono">
        <div class="w-full mb-8 overflow-hidden rounded-lg shadow-lg">
            <div class="w-full overflow-x-auto">
                <table class="w-full">
                    <thead>
                        <tr
                            class="text-md font-semibold tracking-wide text-left text-gray-900 bg-gray-100 uppercase border-b border-gray-600">
                            <th class="px-4 py-3">First Name</th>
                            <th class="px-4 py-3">Last Name</th>
                            <th class="px-4 py-3">Email</th>
                            <th class="px-4 py-3">Phone</th>
                            <th class="px-6 py-3">Retailer Status</th>
                            <th class="px-6 py-3">Products Count</th>
                            <th class="px-6 py-3">Products</th>
                            <th class="px-6 py-3">Requested At</th>
                            <th class="px-6 py-3">Created At</th>
                            <th class="px-6 py-3">Comments</th>
                            <th class="px-6 py-3">Actions</th>
                        </tr>
                    </thead>
                    <tbody class="bg-white">
                        @foreach ($webUsers as $webUser)
                            <tr class="text-gray-700">
                                <td class="px-4 py-3 border">{{ $webUser->first_name }}</td>
                                <td class="px-4 py-3 border">{{ $webUser->surname }}</td>
                                <td class="px-4 py-3 border">{{ $webUser->email }}</td>
                                <td class="px-4 py-3 border">{{ $webUser->phone }}</td>
                                <td class="px-4 py-3 border">
                                    @if ($webUser->retailer_status === 'pending')
                                        <span
                                            class="px-2 py-1 text-xs font-semibold rounded-full bg-yellow-100 text-yellow-800">
                                            Pending
                                        </span>
                                    @elseif($webUser->retailer_status === 'approved')
                                        <span
                                            class="px-2 py-1 text-xs font-semibold rounded-full bg-green-100 text-green-800">
                                            Approved
                                        </span>
                                    @elseif($webUser->retailer_status === 'rejected')
                                        <span
                                            class="px-2 py-1 text-xs font-semibold rounded-full bg-red-100 text-red-800">
                                            Rejected
                                        </span>
                                    @else
                                        <span
                                            class="px-2 py-1 text-xs font-semibold rounded-full bg-gray-100 text-gray-800">
                                            Regular User
                                        </span>
                                    @endif
                                </td>
                                <td class="px-4 py-3 border">
                                    @if ($webUser->is_retailer && $webUser->retailer_status === 'approved')
                                        @php
                                            $productCount = $webUser->products()->count();
                                            $pendingCount = $webUser->products()->where('status', 'pending')->count();
                                            $approvedCount = $webUser->products()->where('status', 'approved')->count();
                                        @endphp
                                        <div class="text-xs">
                                            <div class="font-medium">Total: {{ $productCount }}</div>
                                            @if ($pendingCount > 0)
                                                <div class="text-yellow-600">Pending: {{ $pendingCount }}</div>
                                            @endif
                                            @if ($approvedCount > 0)
                                                <div class="text-green-600">Approved: {{ $approvedCount }}</div>
                                            @endif
                                            @if ($productCount > 0)
                                                <a href="/{{ app()->getLocale() }}/admin/retailer-products?retailer_id={{ $webUser->id }}"
                                                    class="text-blue-600 hover:text-blue-800 underline">View
                                                    Products</a>
                                            @endif
                                        </div>
                                    @else
                                        <span class="text-xs text-gray-500">-</span>
                                    @endif
                                </td>
                                <td class="px-4 py-3 border">
                                    {{ $webUser->retailer_requested_at ? $webUser->retailer_requested_at->format('m/d/Y H:i') : '-' }}
                                </td>
                                <td class="px-4 py-3 border">{{ $webUser->created_at->format('m/d/Y') }}</td>
                                <td class="px-4 py-3 border">
                                    @if ($webUser->retailer_status === 'pending')
                                        <div class="flex gap-2">
                                            <button onclick="approveRetailer({{ $webUser->id }})"
                                                class="px-3 py-1 text-xs font-medium text-white bg-green-600 rounded hover:bg-green-700 focus:outline-none focus:ring-2 focus:ring-green-500">
                                                Approve
                                            </button>
                                            <button onclick="rejectRetailer({{ $webUser->id }})"
                                                class="px-3 py-1 text-xs font-medium text-white bg-red-600 rounded hover:bg-red-700 focus:outline-none focus:ring-2 focus:ring-red-500">
                                                Reject
                                            </button>
                                        </div>
                                    @elseif($webUser->retailer_status === 'approved')
                                        <span class="text-xs text-green-600 font-medium">✓ Retailer</span>
                                    @else
                                        <span class="text-xs text-gray-500">-</span>
                                    @endif
                                </td>
                                <td class="px-4 py-3 border">
                                    @php
                                        $totalComments = $webUser->comments()->count();
                                        $pendingComments = $webUser->comments()->where('is_approved', false)->count();
                                        $approvedComments = $webUser->comments()->where('is_approved', true)->count();
                                    @endphp
                                    <div class="text-xs">
                                        <div class="font-medium">Total: {{ $totalComments }}</div>
                                        @if($pendingComments > 0)
                                            <div class="text-yellow-600">Pending: {{ $pendingComments }}</div>
                                        @endif
                                        @if($approvedComments > 0)
                                            <div class="text-green-600">Approved: {{ $approvedComments }}</div>
                                        @endif
                                        @if($totalComments > 0)
                                            <a href="/{{ app()->getLocale() }}/admin/webusers/{{ $webUser->id }}" 
                                               class="text-blue-600 hover:text-blue-800 underline">View Details</a>
                                        @endif
                                    </div>
                                </td>
                                <td class="px-4 py-3 border">
                                    <div class="flex gap-2">
                                        <a href="/{{ app()->getLocale() }}/admin/webusers/{{ $webUser->id }}" 
                                           class="px-3 py-1 text-xs font-medium text-white bg-blue-600 rounded hover:bg-blue-700">
                                            View
                                        </a>
                                        <button onclick="toggleUserStatus({{ $webUser->id }})"
                                                class="px-3 py-1 text-xs font-medium text-white {{ $webUser->is_active ? 'bg-red-600 hover:bg-red-700' : 'bg-green-600 hover:bg-green-700' }} rounded">
                                            {{ $webUser->is_active ? 'Deactivate' : 'Activate' }}
                                        </button>
                                    </div>
                                </td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
        </div>
    </section>

    <script>
        function approveRetailer(userId) {
            if (confirm('Are you sure you want to approve this retailer request?')) {
                fetch(`/{{ app()->getLocale() }}/admin/webusers/${userId}/approve-retailer`, {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/json',
                        'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content')
                    }
                })
                .then(response => response.json())
                .then(data => {
                    if (data.success) {
                        location.reload();
                    } else {
                        alert('Error: ' + data.message);
                    }
                })
                .catch(error => {
                    console.error('Error:', error);
                    alert('An error occurred while approving the retailer.');
                });
            }
        }

        function rejectRetailer(userId) {
            if (confirm('Are you sure you want to reject this retailer request?')) {
                fetch(`/{{ app()->getLocale() }}/admin/webusers/${userId}/reject-retailer`, {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/json',
                        'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content')
                    }
                })
                .then(response => response.json())
                .then(data => {
                    if (data.success) {
                        location.reload();
                    } else {
                        alert('Error: ' + data.message);
                    }
                })
                .catch(error => {
                    console.error('Error:', error);
                    alert('An error occurred while rejecting the retailer.');
                });
            }
        }

        function toggleUserStatus(userId) {
            if (confirm('Are you sure you want to toggle this user\'s status?')) {
                fetch(`/{{ app()->getLocale() }}/admin/webusers/${userId}/toggle-status`, {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/json',
                        'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content')
                    }
                })
                .then(response => response.json())
                .then(data => {
                    if (data.success) {
                        location.reload();
                    } else {
                        alert('Error: ' + data.message);
                    }
                })
                .catch(error => {
                    console.error('Error:', error);
                    alert('An error occurred while toggling user status.');
                });
            }
        }
    </script>
</x-admin.admin-layout>
