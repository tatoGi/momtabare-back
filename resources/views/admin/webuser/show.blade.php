<x-admin.admin-layout>
    <section class="container px-4 mx-auto">
        <div class="sm:flex sm:items-center sm:justify-between">
            <div>
                <div class="flex items-center gap-x-3">
                    <h2 class="text-lg font-medium text-gray-800">Web User Details</h2>
                </div>
                <p class="mt-1 text-sm text-gray-500">Manage user information, products, and comments</p>
            </div>
            <div class="flex items-center mt-4 gap-x-3">
                <a href="/{{ app()->getLocale() }}/admin/webusers" 
                   class="flex items-center justify-center w-1/2 px-5 py-2 text-sm text-gray-700 transition-colors duration-200 bg-white border rounded-lg gap-x-2 sm:w-auto hover:bg-gray-100">
                    <svg class="w-5 h-5 rtl:rotate-180" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M6.75 15.75L3 12m0 0l3.75-3.75M3 12h18" />
                    </svg>
                    <span>Back to Users</span>
                </a>
            </div>
        </div>

        <!-- User Information Card -->
        <div class="mt-6 bg-white shadow-sm rounded-lg border border-gray-200">
            <div class="px-6 py-4 border-b border-gray-200">
                <h3 class="text-lg font-medium text-gray-900">User Information</h3>
            </div>
            <div class="px-6 py-4">
                <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                    <div>
                        <label class="block text-sm font-medium text-gray-700">Name</label>
                        <p class="mt-1 text-sm text-gray-900">{{ $webUser->first_name }} {{ $webUser->surname }}</p>
                    </div>
                    <div>
                        <label class="block text-sm font-medium text-gray-700">Email</label>
                        <p class="mt-1 text-sm text-gray-900">{{ $webUser->email }}</p>
                    </div>
                    <div>
                        <label class="block text-sm font-medium text-gray-700">Phone</label>
                        <p class="mt-1 text-sm text-gray-900">{{ $webUser->phone ?? 'N/A' }}</p>
                    </div>
                    <div>
                        <label class="block text-sm font-medium text-gray-700">Status</label>
                        <span class="mt-1 inline-flex px-2 py-1 text-xs font-medium rounded-full {{ $webUser->is_active ? 'bg-green-100 text-green-800' : 'bg-red-100 text-red-800' }}">
                            {{ $webUser->is_active ? 'Active' : 'Inactive' }}
                        </span>
                    </div>
                    <div>
                        <label class="block text-sm font-medium text-gray-700">Retailer Status</label>
                        @if($webUser->retailer_status === 'pending')
                            <span class="mt-1 inline-flex px-2 py-1 text-xs font-medium rounded-full bg-yellow-100 text-yellow-800">Pending</span>
                        @elseif($webUser->retailer_status === 'approved')
                            <span class="mt-1 inline-flex px-2 py-1 text-xs font-medium rounded-full bg-green-100 text-green-800">Approved</span>
                        @else
                            <span class="mt-1 inline-flex px-2 py-1 text-xs font-medium rounded-full bg-gray-100 text-gray-800">Not Requested</span>
                        @endif
                    </div>
                    <div>
                        <label class="block text-sm font-medium text-gray-700">Joined</label>
                        <p class="mt-1 text-sm text-gray-900">{{ $webUser->created_at->format('M d, Y') }}</p>
                    </div>
                </div>
            </div>
        </div>

        <!-- Statistics Dashboard -->
        <div class="mt-6 grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-6">
            <!-- User Status Card -->
            <div class="bg-white shadow-sm rounded-lg border border-gray-200 p-6">
                <div class="flex items-center">
                    <div class="flex-shrink-0">
                        <div class="w-8 h-8 bg-blue-100 rounded-full flex items-center justify-center">
                            <svg class="w-4 h-4 text-blue-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z"></path>
                            </svg>
                        </div>
                    </div>
                    <div class="ml-4">
                        <h3 class="text-sm font-medium text-gray-900">User Status</h3>
                        <div class="mt-2 space-y-1">
                            @if($stats['user']['retailer_status'] === 'pending')
                                <span class="inline-flex px-2 py-1 text-xs font-semibold rounded-full bg-yellow-100 text-yellow-800">Retailer Pending</span>
                            @elseif($stats['user']['retailer_status'] === 'approved')
                                <span class="inline-flex px-2 py-1 text-xs font-semibold rounded-full bg-green-100 text-green-800">Approved Retailer</span>
                            @elseif($stats['user']['retailer_status'] === 'rejected')
                                <span class="inline-flex px-2 py-1 text-xs font-semibold rounded-full bg-red-100 text-red-800">Rejected</span>
                            @else
                                <span class="inline-flex px-2 py-1 text-xs font-semibold rounded-full bg-gray-100 text-gray-800">Regular User</span>
                            @endif
                            @if($stats['user']['email_verified'])
                                <span class="inline-flex px-2 py-1 text-xs font-semibold rounded-full bg-green-100 text-green-800">✓ Verified</span>
                            @else
                                <span class="inline-flex px-2 py-1 text-xs font-semibold rounded-full bg-red-100 text-red-800">⚠ Unverified</span>
                            @endif
                        </div>
                    </div>
                </div>
            </div>

            <!-- Comments Statistics Card -->
            <div class="bg-white shadow-sm rounded-lg border border-gray-200 p-6">
                <div class="flex items-center">
                    <div class="flex-shrink-0">
                        <div class="w-8 h-8 bg-purple-100 rounded-full flex items-center justify-center">
                            <svg class="w-4 h-4 text-purple-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 12h.01M12 12h.01M16 12h.01M21 12c0 4.418-4.03 8-9 8a9.863 9.863 0 01-4.255-.949L3 20l1.395-3.72C3.512 15.042 3 13.574 3 12c0-4.418 4.03-8 9-8s9 3.582 9 8z"></path>
                            </svg>
                        </div>
                    </div>
                    <div class="ml-4">
                        <h3 class="text-sm font-medium text-gray-900">Comments</h3>
                        <div class="mt-2">
                            <div class="text-2xl font-bold text-gray-900">{{ $stats['comments']['total'] }}</div>
                            <div class="text-xs text-gray-500 space-x-2">
                                @if($stats['comments']['pending'] > 0)
                                    <span class="text-yellow-600">{{ $stats['comments']['pending'] }} pending</span>
                                @endif
                                @if($stats['comments']['approved'] > 0)
                                    <span class="text-green-600">{{ $stats['comments']['approved'] }} approved</span>
                                @endif
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Products Statistics Card -->
            <div class="bg-white shadow-sm rounded-lg border border-gray-200 p-6">
                <div class="flex items-center">
                    <div class="flex-shrink-0">
                        <div class="w-8 h-8 bg-green-100 rounded-full flex items-center justify-center">
                            <svg class="w-4 h-4 text-green-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M20 7l-8-4-8 4m16 0l-8 4m8-4v10l-8 4m0-10L4 7m8 4v10M4 7v10l8 4"></path>
                            </svg>
                        </div>
                    </div>
                    <div class="ml-4">
                        <h3 class="text-sm font-medium text-gray-900">Products</h3>
                        <div class="mt-2">
                            <div class="text-2xl font-bold text-gray-900">{{ $stats['products']['total'] }}</div>
                            <div class="text-xs text-gray-500 space-x-2">
                                @if($stats['products']['pending'] > 0)
                                    <span class="text-yellow-600">{{ $stats['products']['pending'] }} pending</span>
                                @endif
                                @if($stats['products']['approved'] > 0)
                                    <span class="text-green-600">{{ $stats['products']['approved'] }} approved</span>
                                @endif
                                @if($stats['products']['active'] > 0)
                                    <span class="text-blue-600">{{ $stats['products']['active'] }} active</span>
                                @endif
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Account Info Card -->
            <div class="bg-white shadow-sm rounded-lg border border-gray-200 p-6">
                <div class="flex items-center">
                    <div class="flex-shrink-0">
                        <div class="w-8 h-8 bg-orange-100 rounded-full flex items-center justify-center">
                            <svg class="w-4 h-4 text-orange-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z"></path>
                            </svg>
                        </div>
                    </div>
                    <div class="ml-4">
                        <h3 class="text-sm font-medium text-gray-900">Account Info</h3>
                        <div class="mt-2">
                            <div class="text-sm font-medium text-gray-900">{{ $stats['user']['created_at']->format('M d, Y') }}</div>
                            <div class="text-xs text-gray-500">Member since</div>
                            @if($stats['user']['retailer_requested_at'])
                                <div class="text-xs text-gray-500 mt-1">
                                    Retailer request: {{ $stats['user']['retailer_requested_at']->format('M d, Y') }}
                                </div>
                            @endif
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- Quick Actions -->
        @if($webUser->retailer_status === 'pending')
        <div class="mt-6 bg-yellow-50 border border-yellow-200 rounded-lg p-6">
            <div class="flex items-center justify-between">
                <div>
                    <h3 class="text-lg font-medium text-yellow-800">Retailer Request Pending</h3>
                    <p class="mt-1 text-sm text-yellow-700">This user has requested retailer status and is waiting for approval.</p>
                </div>
                <div class="flex gap-3">
                    <button onclick="approveRetailer({{ $webUser->id }})"
                            class="px-4 py-2 text-sm font-medium text-white bg-green-600 rounded-lg hover:bg-green-700 focus:outline-none focus:ring-2 focus:ring-green-500">
                        Approve Retailer
                    </button>
                    <button onclick="rejectRetailer({{ $webUser->id }})"
                            class="px-4 py-2 text-sm font-medium text-white bg-red-600 rounded-lg hover:bg-red-700 focus:outline-none focus:ring-2 focus:ring-red-500">
                        Reject Request
                    </button>
                </div>
            </div>
        </div>
        @endif

        <!-- Products Section -->
        @if($webUser->products->count() > 0)
        <div class="mt-6 bg-white shadow-sm rounded-lg border border-gray-200">
            <div class="px-6 py-4 border-b border-gray-200">
                <h3 class="text-lg font-medium text-gray-900">Products ({{ $webUser->products->count() }})</h3>
            </div>
            <div class="px-6 py-4">
                <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-4">
                    @foreach($webUser->products->take(6) as $product)
                    <div class="border border-gray-200 rounded-lg p-4">
                        <h4 class="font-medium text-gray-900">{{ $product->title }}</h4>
                        <p class="text-sm text-gray-600 mt-1">{{ Str::limit($product->description, 100) }}</p>
                        <div class="mt-2 flex justify-between items-center">
                            <span class="text-sm font-medium text-green-600">${{ number_format($product->price, 2) }}</span>
                            <span class="text-xs px-2 py-1 rounded-full {{ $product->is_active ? 'bg-green-100 text-green-800' : 'bg-red-100 text-red-800' }}">
                                {{ $product->is_active ? 'Active' : 'Inactive' }}
                            </span>
                        </div>
                    </div>
                    @endforeach
                </div>
                @if($webUser->products->count() > 6)
                <p class="mt-4 text-sm text-gray-600">And {{ $webUser->products->count() - 6 }} more products...</p>
                @endif
            </div>
        </div>
        @endif

        <!-- Comments Section -->
        <div class="mt-6 bg-white shadow-sm rounded-lg border border-gray-200">
            <div class="px-6 py-4 border-b border-gray-200 flex justify-between items-center">
                <h3 class="text-lg font-medium text-gray-900">Comments ({{ $comments->total() }})</h3>
                <div class="flex gap-2">
                    <a href="?status=pending" class="px-3 py-1 text-xs font-medium rounded {{ request('status') === 'pending' ? 'bg-yellow-100 text-yellow-800' : 'bg-gray-100 text-gray-700 hover:bg-gray-200' }}">
                        Pending ({{ $pendingCount }})
                    </a>
                    <a href="?status=approved" class="px-3 py-1 text-xs font-medium rounded {{ request('status') === 'approved' ? 'bg-green-100 text-green-800' : 'bg-gray-100 text-gray-700 hover:bg-gray-200' }}">
                        Approved ({{ $approvedCount }})
                    </a>
                    <a href="{{ request()->url() }}" class="px-3 py-1 text-xs font-medium rounded {{ !request('status') ? 'bg-blue-100 text-blue-800' : 'bg-gray-100 text-gray-700 hover:bg-gray-200' }}">
                        All
                    </a>
                </div>
            </div>
            
            @if($comments->count() > 0)
            <div class="divide-y divide-gray-200">
                @foreach($comments as $comment)
                <div class="px-6 py-4">
                    <div class="flex justify-between items-start">
                        <div class="flex-1">
                            <div class="flex items-center gap-2 mb-2">
                                <h4 class="font-medium text-gray-900">{{ $comment->product->title }}</h4>
                                @if($comment->rating)
                                <div class="flex items-center">
                                    @for($i = 1; $i <= 5; $i++)
                                        <svg class="w-4 h-4 {{ $i <= $comment->rating ? 'text-yellow-400' : 'text-gray-300' }}" fill="currentColor" viewBox="0 0 20 20">
                                            <path d="M9.049 2.927c.3-.921 1.603-.921 1.902 0l1.07 3.292a1 1 0 00.95.69h3.462c.969 0 1.371 1.24.588 1.81l-2.8 2.034a1 1 0 00-.364 1.118l1.07 3.292c.3.921-.755 1.688-1.54 1.118l-2.8-2.034a1 1 0 00-1.175 0l-2.8 2.034c-.784.57-1.838-.197-1.539-1.118l1.07-3.292a1 1 0 00-.364-1.118L2.98 8.72c-.783-.57-.38-1.81.588-1.81h3.461a1 1 0 00.951-.69l1.07-3.292z"/>
                                        </svg>
                                    @endfor
                                    <span class="ml-1 text-sm text-gray-600">({{ $comment->rating }}/5)</span>
                                </div>
                                @endif
                            </div>
                            <p class="text-gray-700 mb-2">{{ $comment->comment }}</p>
                            <div class="flex items-center gap-4 text-xs text-gray-500">
                                <span>{{ $comment->created_at->format('M d, Y H:i') }}</span>
                                <span class="inline-flex px-2 py-1 rounded-full {{ $comment->is_approved ? 'bg-green-100 text-green-800' : 'bg-yellow-100 text-yellow-800' }}">
                                    {{ $comment->is_approved ? 'Approved' : 'Pending' }}
                                </span>
                                @if($comment->is_approved && $comment->approved_at)
                                <span>Approved {{ $comment->approved_at->format('M d, Y') }}</span>
                                @endif
                            </div>
                        </div>
                        <div class="flex gap-2 ml-4">
                            @if(!$comment->is_approved)
                            <button onclick="approveComment({{ $comment->id }})"
                                    class="px-3 py-1 text-xs font-medium text-white bg-green-600 rounded hover:bg-green-700">
                                Approve
                            </button>
                            @endif
                            <button onclick="rejectComment({{ $comment->id }})"
                                    class="px-3 py-1 text-xs font-medium text-white bg-red-600 rounded hover:bg-red-700">
                                {{ $comment->is_approved ? 'Delete' : 'Reject' }}
                            </button>
                        </div>
                    </div>
                </div>
                @endforeach
            </div>
            
            <!-- Pagination -->
            @if($comments->hasPages())
            <div class="px-6 py-4 border-t border-gray-200">
                {{ $comments->appends(request()->query())->links() }}
            </div>
            @endif
            @else
            <div class="px-6 py-8 text-center">
                <p class="text-gray-500">No comments found.</p>
            </div>
            @endif
        </div>
    </section>

    <script>
        function approveComment(commentId) {
            if (confirm('Are you sure you want to approve this comment?')) {
                fetch(`/{{ app()->getLocale() }}/admin/comments/${commentId}/approve`, {
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
                    alert('An error occurred while approving the comment.');
                });
            }
        }

        function rejectComment(commentId) {
            if (confirm('Are you sure you want to reject/delete this comment?')) {
                fetch(`/{{ app()->getLocale() }}/admin/comments/${commentId}/reject`, {
                    method: 'DELETE',
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
                    alert('An error occurred while rejecting the comment.');
                });
            }
        }

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
    </script>
</x-admin.admin-layout>
