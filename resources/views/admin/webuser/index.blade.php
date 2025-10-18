<x-admin.admin-layout>
    <section class="container mx-auto p-6">
        <!-- Page Header -->
        <div class="mb-6">
            <h1 class="text-3xl font-bold text-gray-800">Web Users Management</h1>
            <p class="text-gray-600 mt-2">Manage all website users, retailers, and their activities</p>
        </div>

        <!-- Stats Cards -->
        <div class="grid grid-cols-1 md:grid-cols-4 gap-4 mb-6">
            @php
                $totalUsers = $webUsers->count();
                $activeUsers = $webUsers->where('is_active', true)->count();
                $retailers = $webUsers->where('is_retailer', true)->count();
                $pendingRetailers = $webUsers->where('retailer_status', 'pending')->count();
            @endphp

            <div class="bg-white rounded-lg shadow p-6">
                <div class="flex items-center">
                    <div class="flex-shrink-0 bg-blue-500 rounded-md p-3">
                        <svg class="h-6 w-6 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4.354a4 4 0 110 5.292M15 21H3v-1a6 6 0 0112 0v1zm0 0h6v-1a6 6 0 00-9-5.197M13 7a4 4 0 11-8 0 4 4 0 018 0z"></path>
                        </svg>
                    </div>
                    <div class="ml-4">
                        <p class="text-sm font-medium text-gray-500">Total Users</p>
                        <p class="text-2xl font-semibold text-gray-900">{{ $totalUsers }}</p>
                    </div>
                </div>
            </div>

            <div class="bg-white rounded-lg shadow p-6">
                <div class="flex items-center">
                    <div class="flex-shrink-0 bg-green-500 rounded-md p-3">
                        <svg class="h-6 w-6 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"></path>
                        </svg>
                    </div>
                    <div class="ml-4">
                        <p class="text-sm font-medium text-gray-500">Active Users</p>
                        <p class="text-2xl font-semibold text-gray-900">{{ $activeUsers }}</p>
                    </div>
                </div>
            </div>

            <div class="bg-white rounded-lg shadow p-6">
                <div class="flex items-center">
                    <div class="flex-shrink-0 bg-purple-500 rounded-md p-3">
                        <svg class="h-6 w-6 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M16 11V7a4 4 0 00-8 0v4M5 9h14l1 12H4L5 9z"></path>
                        </svg>
                    </div>
                    <div class="ml-4">
                        <p class="text-sm font-medium text-gray-500">Retailers</p>
                        <p class="text-2xl font-semibold text-gray-900">{{ $retailers }}</p>
                    </div>
                </div>
            </div>

            <div class="bg-white rounded-lg shadow p-6">
                <div class="flex items-center">
                    <div class="flex-shrink-0 bg-yellow-500 rounded-md p-3">
                        <svg class="h-6 w-6 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z"></path>
                        </svg>
                    </div>
                    <div class="ml-4">
                        <p class="text-sm font-medium text-gray-500">Pending Requests</p>
                        <p class="text-2xl font-semibold text-gray-900">{{ $pendingRetailers }}</p>
                    </div>
                </div>
            </div>
        </div>

        <!-- Users List -->
        <div class="bg-white rounded-lg shadow overflow-hidden">
            <div class="overflow-x-auto">
                <table class="min-w-full divide-y divide-gray-200">
                    <thead class="bg-gray-50">
                        <tr>
                            <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                                User Info
                            </th>
                            <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                                Status
                            </th>
                            <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                                Activity & Stats
                            </th>
                            <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                                Retailer Info
                            </th>
                            <th scope="col" class="px-6 py-3 text-center text-xs font-medium text-gray-500 uppercase tracking-wider">
                                Actions
                            </th>
                        </tr>
                    </thead>
                    <tbody class="bg-white divide-y divide-gray-200">
                        @foreach ($webUsers as $webUser)
                            @php
                                // Calculate stats
                                $productCount = $webUser->products()->count();
                                $pendingProducts = $webUser->products()->where('status', 'pending')->count();
                                $approvedProducts = $webUser->products()->where('status', 'approved')->count();
                                $rejectedProducts = $webUser->products()->where('status', 'rejected')->count();

                                $totalComments = $webUser->comments()->count();
                                $pendingComments = $webUser->comments()->where('is_approved', false)->count();
                                $approvedComments = $webUser->comments()->where('is_approved', true)->count();
                            @endphp

                            <tr class="hover:bg-gray-50 transition-colors">
                                <!-- User Info -->
                                <td class="px-6 py-4">
                                    <div class="flex items-center">
                                        <div class="flex-shrink-0 h-12 w-12">
                                            @if($webUser->avatar)
                                                <img class="h-12 w-12 rounded-full object-cover" src="{{ asset('storage/' . $webUser->avatar) }}" alt="{{ $webUser->first_name }}">
                                            @else
                                                <div class="h-12 w-12 rounded-full bg-gradient-to-br from-blue-400 to-blue-600 flex items-center justify-center text-white font-bold text-lg">
                                                    {{ strtoupper(substr($webUser->first_name, 0, 1)) }}{{ strtoupper(substr($webUser->surname, 0, 1)) }}
                                                </div>
                                            @endif
                                        </div>
                                        <div class="ml-4">
                                            <div class="text-sm font-medium text-gray-900">
                                                {{ $webUser->first_name }} {{ $webUser->surname }}
                                            </div>
                                            <div class="text-sm text-gray-500">
                                                <span class="inline-flex items-center">
                                                    <svg class="w-3 h-3 mr-1" fill="currentColor" viewBox="0 0 20 20">
                                                        <path d="M2.003 5.884L10 9.882l7.997-3.998A2 2 0 0016 4H4a2 2 0 00-1.997 1.884z"></path>
                                                        <path d="M18 8.118l-8 4-8-4V14a2 2 0 002 2h12a2 2 0 002-2V8.118z"></path>
                                                    </svg>
                                                    {{ $webUser->email }}
                                                </span>
                                            </div>
                                            @if($webUser->phone)
                                                <div class="text-sm text-gray-500">
                                                    <span class="inline-flex items-center">
                                                        <svg class="w-3 h-3 mr-1" fill="currentColor" viewBox="0 0 20 20">
                                                            <path d="M2 3a1 1 0 011-1h2.153a1 1 0 01.986.836l.74 4.435a1 1 0 01-.54 1.06l-1.548.773a11.037 11.037 0 006.105 6.105l.774-1.548a1 1 0 011.059-.54l4.435.74a1 1 0 01.836.986V17a1 1 0 01-1 1h-2C7.82 18 2 12.18 2 5V3z"></path>
                                                        </svg>
                                                        {{ $webUser->phone }}
                                                    </span>
                                                </div>
                                            @endif
                                            <div class="text-xs text-gray-400 mt-1">
                                                Joined {{ $webUser->created_at->format('M d, Y') }}
                                            </div>
                                        </div>
                                    </div>
                                </td>

                                <!-- Status -->
                                <td class="px-6 py-4">
                                    <div class="flex flex-col space-y-2">
                                        <!-- Account Status -->
                                        @if($webUser->is_active)
                                            <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-green-100 text-green-800">
                                                <svg class="w-3 h-3 mr-1" fill="currentColor" viewBox="0 0 20 20">
                                                    <path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zm3.707-9.293a1 1 0 00-1.414-1.414L9 10.586 7.707 9.293a1 1 0 00-1.414 1.414l2 2a1 1 0 001.414 0l4-4z" clip-rule="evenodd"></path>
                                                </svg>
                                                Active
                                            </span>
                                        @else
                                            <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-red-100 text-red-800">
                                                <svg class="w-3 h-3 mr-1" fill="currentColor" viewBox="0 0 20 20">
                                                    <path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zM8.707 7.293a1 1 0 00-1.414 1.414L8.586 10l-1.293 1.293a1 1 0 101.414 1.414L10 11.414l1.293 1.293a1 1 0 001.414-1.414L11.414 10l1.293-1.293a1 1 0 00-1.414-1.414L10 8.586 8.707 7.293z" clip-rule="evenodd"></path>
                                                </svg>
                                                Inactive
                                            </span>
                                        @endif

                                        <!-- Email Verification -->
                                        @if($webUser->hasVerifiedEmail())
                                            <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-blue-100 text-blue-800">
                                                <svg class="w-3 h-3 mr-1" fill="currentColor" viewBox="0 0 20 20">
                                                    <path fill-rule="evenodd" d="M2.166 4.999A11.954 11.954 0 0010 1.944 11.954 11.954 0 0017.834 5c.11.65.166 1.32.166 2.001 0 5.225-3.34 9.67-8 11.317C5.34 16.67 2 12.225 2 7c0-.682.057-1.35.166-2.001zm11.541 3.708a1 1 0 00-1.414-1.414L9 10.586 7.707 9.293a1 1 0 00-1.414 1.414l2 2a1 1 0 001.414 0l4-4z" clip-rule="evenodd"></path>
                                                </svg>
                                                Verified
                                            </span>
                                        @else
                                            <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-yellow-100 text-yellow-800">
                                                <svg class="w-3 h-3 mr-1" fill="currentColor" viewBox="0 0 20 20">
                                                    <path fill-rule="evenodd" d="M8.257 3.099c.765-1.36 2.722-1.36 3.486 0l5.58 9.92c.75 1.334-.213 2.98-1.742 2.98H4.42c-1.53 0-2.493-1.646-1.743-2.98l5.58-9.92zM11 13a1 1 0 11-2 0 1 1 0 012 0zm-1-8a1 1 0 00-1 1v3a1 1 0 002 0V6a1 1 0 00-1-1z" clip-rule="evenodd"></path>
                                                </svg>
                                                Unverified
                                            </span>
                                        @endif

                                        <!-- Retailer Status -->
                                        @if($webUser->retailer_status === 'pending')
                                            <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-yellow-100 text-yellow-800">
                                                <svg class="w-3 h-3 mr-1 animate-pulse" fill="currentColor" viewBox="0 0 20 20">
                                                    <path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zm1-12a1 1 0 10-2 0v4a1 1 0 00.293.707l2.828 2.829a1 1 0 101.415-1.415L11 9.586V6z" clip-rule="evenodd"></path>
                                                </svg>
                                                Retailer Pending
                                            </span>
                                        @elseif($webUser->retailer_status === 'approved')
                                            <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-purple-100 text-purple-800">
                                                <svg class="w-3 h-3 mr-1" fill="currentColor" viewBox="0 0 20 20">
                                                    <path fill-rule="evenodd" d="M10 2a4 4 0 00-4 4v1H5a1 1 0 00-.994.89l-1 9A1 1 0 004 18h12a1 1 0 00.994-1.11l-1-9A1 1 0 0015 7h-1V6a4 4 0 00-4-4zm2 5V6a2 2 0 10-4 0v1h4zm-6 3a1 1 0 112 0 1 1 0 01-2 0zm7-1a1 1 0 100 2 1 1 0 000-2z" clip-rule="evenodd"></path>
                                                </svg>
                                                Retailer
                                            </span>
                                        @elseif($webUser->retailer_status === 'rejected')
                                            <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-red-100 text-red-800">
                                                <svg class="w-3 h-3 mr-1" fill="currentColor" viewBox="0 0 20 20">
                                                    <path fill-rule="evenodd" d="M13.477 14.89A6 6 0 015.11 6.524l8.367 8.368zm1.414-1.414L6.524 5.11a6 6 0 018.367 8.367zM18 10a8 8 0 11-16 0 8 8 0 0116 0z" clip-rule="evenodd"></path>
                                                </svg>
                                                Rejected
                                            </span>
                                        @endif
                                    </div>
                                </td>

                                <!-- Activity & Stats -->
                                <td class="px-6 py-4">
                                    <div class="space-y-3">
                                        <!-- Comments -->
                                        @if($totalComments > 0)
                                            <div class="bg-gray-50 rounded-lg p-3">
                                                <div class="flex items-center justify-between mb-2">
                                                    <span class="text-xs font-semibold text-gray-700 uppercase flex items-center">
                                                        <svg class="w-4 h-4 mr-1" fill="currentColor" viewBox="0 0 20 20">
                                                            <path fill-rule="evenodd" d="M18 10c0 3.866-3.582 7-8 7a8.841 8.841 0 01-4.083-.98L2 17l1.338-3.123C2.493 12.767 2 11.434 2 10c0-3.866 3.582-7 8-7s8 3.134 8 7zM7 9H5v2h2V9zm8 0h-2v2h2V9zM9 9h2v2H9V9z" clip-rule="evenodd"></path>
                                                        </svg>
                                                        Comments
                                                    </span>
                                                    <span class="text-lg font-bold text-gray-900">{{ $totalComments }}</span>
                                                </div>
                                                <div class="flex items-center justify-between text-xs">
                                                    @if($approvedComments > 0)
                                                        <span class="text-green-600">
                                                            ✓ {{ $approvedComments }} Approved
                                                        </span>
                                                    @endif
                                                    @if($pendingComments > 0)
                                                        <span class="text-yellow-600">
                                                            ⏳ {{ $pendingComments }} Pending
                                                        </span>
                                                    @endif
                                                </div>
                                            </div>
                                        @else
                                            <div class="text-xs text-gray-400 italic">No comments yet</div>
                                        @endif

                                        <!-- Products (for retailers) -->
                                        @if($webUser->is_retailer && $productCount > 0)
                                            <div class="bg-purple-50 rounded-lg p-3">
                                                <div class="flex items-center justify-between mb-2">
                                                    <span class="text-xs font-semibold text-purple-700 uppercase flex items-center">
                                                        <svg class="w-4 h-4 mr-1" fill="currentColor" viewBox="0 0 20 20">
                                                            <path fill-rule="evenodd" d="M10 2a4 4 0 00-4 4v1H5a1 1 0 00-.994.89l-1 9A1 1 0 004 18h12a1 1 0 00.994-1.11l-1-9A1 1 0 0015 7h-1V6a4 4 0 00-4-4zm2 5V6a2 2 0 10-4 0v1h4zm-6 3a1 1 0 112 0 1 1 0 01-2 0zm7-1a1 1 0 100 2 1 1 0 000-2z" clip-rule="evenodd"></path>
                                                        </svg>
                                                        Products
                                                    </span>
                                                    <span class="text-lg font-bold text-purple-900">{{ $productCount }}</span>
                                                </div>
                                                <div class="grid grid-cols-3 gap-1 text-xs">
                                                    @if($approvedProducts > 0)
                                                        <span class="text-green-600">✓ {{ $approvedProducts }}</span>
                                                    @endif
                                                    @if($pendingProducts > 0)
                                                        <span class="text-yellow-600">⏳ {{ $pendingProducts }}</span>
                                                    @endif
                                                    @if($rejectedProducts > 0)
                                                        <span class="text-red-600">✗ {{ $rejectedProducts }}</span>
                                                    @endif
                                                </div>
                                            </div>
                                        @endif
                                    </div>
                                </td>

                                <!-- Retailer Info -->
                                <td class="px-6 py-4">
                                    @if($webUser->retailer_status === 'pending')
                                        <div class="bg-yellow-50 border border-yellow-200 rounded-lg p-3">
                                            <div class="flex items-center mb-2">
                                                <svg class="w-4 h-4 mr-1 text-yellow-600 animate-pulse" fill="currentColor" viewBox="0 0 20 20">
                                                    <path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zm1-12a1 1 0 10-2 0v4a1 1 0 00.293.707l2.828 2.829a1 1 0 101.415-1.415L11 9.586V6z" clip-rule="evenodd"></path>
                                                </svg>
                                                <span class="text-xs font-semibold text-yellow-800">Pending Review</span>
                                            </div>
                                            <div class="text-xs text-gray-600 mb-3">
                                                Requested: {{ $webUser->retailer_requested_at ? $webUser->retailer_requested_at->format('M d, Y H:i') : 'N/A' }}
                                            </div>
                                            <div class="flex gap-2">
                                                <button onclick="approveRetailer({{ $webUser->id }})"
                                                    class="flex-1 px-3 py-1.5 text-xs font-medium text-white bg-green-600 rounded hover:bg-green-700 focus:outline-none focus:ring-2 focus:ring-green-500 transition-colors">
                                                    ✓ Approve
                                                </button>
                                                <button onclick="rejectRetailer({{ $webUser->id }})"
                                                    class="flex-1 px-3 py-1.5 text-xs font-medium text-white bg-red-600 rounded hover:bg-red-700 focus:outline-none focus:ring-2 focus:ring-red-500 transition-colors">
                                                    ✗ Reject
                                                </button>
                                            </div>
                                        </div>
                                    @elseif($webUser->retailer_status === 'approved')
                                        <div class="bg-green-50 border border-green-200 rounded-lg p-3 space-y-2">
                                            <div class="flex items-center justify-between">
                                                <span class="text-xs font-semibold text-green-800">✓ Approved Retailer</span>
                                                @if($productCount > 0)
                                                    <span class="inline-flex items-center px-2 py-0.5 rounded-full text-xs font-bold bg-purple-600 text-white">
                                                        {{ $productCount }} {{ $productCount === 1 ? 'Product' : 'Products' }}
                                                    </span>
                                                @endif
                                            </div>

                                            <!-- Retailer Shop Info -->
                                            @if($webUser->retailerShop)
                                                <div class="border-t border-green-200 pt-2 mt-2">
                                                    <div class="flex items-start mb-2">
                                                        <svg class="w-4 h-4 mr-1 text-green-700 mt-0.5" fill="currentColor" viewBox="0 0 20 20">
                                                            <path fill-rule="evenodd" d="M10 2a4 4 0 00-4 4v1H5a1 1 0 00-.994.89l-1 9A1 1 0 004 18h12a1 1 0 00.994-1.11l-1-9A1 1 0 0015 7h-1V6a4 4 0 00-4-4zm2 5V6a2 2 0 10-4 0v1h4zm-6 3a1 1 0 112 0 1 1 0 01-2 0zm7-1a1 1 0 100 2 1 1 0 000-2z" clip-rule="evenodd"></path>
                                                        </svg>
                                                        <div class="flex-1">
                                                            <div class="text-xs font-semibold text-gray-900">
                                                                {{ $webUser->retailerShop->translate(app()->getLocale())->name ?? 'Shop Name N/A' }}
                                                            </div>
                                                            @if($webUser->retailerShop->translate(app()->getLocale())->address)
                                                                <div class="text-xs text-gray-600 flex items-start mt-1">
                                                                    <svg class="w-3 h-3 mr-1 mt-0.5" fill="currentColor" viewBox="0 0 20 20">
                                                                        <path fill-rule="evenodd" d="M5.05 4.05a7 7 0 119.9 9.9L10 18.9l-4.95-4.95a7 7 0 010-9.9zM10 11a2 2 0 100-4 2 2 0 000 4z" clip-rule="evenodd"></path>
                                                                    </svg>
                                                                    {{ Str::limit($webUser->retailerShop->translate(app()->getLocale())->address, 40) }}
                                                                </div>
                                                            @endif
                                                        </div>
                                                    </div>
                                                </div>
                                            @endif

                                            <!-- Action Buttons -->
                                            <div class="flex gap-2">
                                                @if($productCount > 0)
                                                    <a href="/{{ app()->getLocale() }}/admin/retailer-products?retailer_id={{ $webUser->id }}"
                                                       class="flex-1 inline-flex items-center justify-center px-3 py-1.5 text-xs font-medium text-white bg-purple-600 rounded hover:bg-purple-700 transition-colors">
                                                        <svg class="w-3 h-3 mr-1" fill="currentColor" viewBox="0 0 20 20">
                                                            <path d="M10 12a2 2 0 100-4 2 2 0 000 4z"></path>
                                                            <path fill-rule="evenodd" d="M.458 10C1.732 5.943 5.522 3 10 3s8.268 2.943 9.542 7c-1.274 4.057-5.064 7-9.542 7S1.732 14.057.458 10zM14 10a4 4 0 11-8 0 4 4 0 018 0z" clip-rule="evenodd"></path>
                                                        </svg>
                                                        View Products
                                                    </a>
                                                @endif
                                                @if($webUser->retailerShop)
                                                    <a href="/{{ app()->getLocale() }}/admin/retailer-shops/{{ $webUser->retailerShop->id }}"
                                                       class="flex-1 inline-flex items-center justify-center px-3 py-1.5 text-xs font-medium text-white bg-blue-600 rounded hover:bg-blue-700 transition-colors">
                                                        <svg class="w-3 h-3 mr-1" fill="currentColor" viewBox="0 0 20 20">
                                                            <path fill-rule="evenodd" d="M10 2a4 4 0 00-4 4v1H5a1 1 0 00-.994.89l-1 9A1 1 0 004 18h12a1 1 0 00.994-1.11l-1-9A1 1 0 0015 7h-1V6a4 4 0 00-4-4zm2 5V6a2 2 0 10-4 0v1h4zm-6 3a1 1 0 112 0 1 1 0 01-2 0zm7-1a1 1 0 100 2 1 1 0 000-2z" clip-rule="evenodd"></path>
                                                        </svg>
                                                        Shop Details
                                                    </a>
                                                @endif
                                            </div>
                                        </div>
                                    @elseif($webUser->retailer_status === 'rejected')
                                        <div class="bg-red-50 border border-red-200 rounded-lg p-3">
                                            <div class="text-xs font-semibold text-red-800">✗ Request Rejected</div>
                                        </div>
                                    @else
                                        <div class="text-xs text-gray-400 italic">Regular User</div>
                                    @endif
                                </td>

                                <!-- Actions -->
                                <td class="px-6 py-4">
                                    <div class="flex flex-col gap-2">
                                        <a href="/{{ app()->getLocale() }}/admin/webusers/{{ $webUser->id }}"
                                           class="inline-flex items-center justify-center px-4 py-2 text-xs font-medium text-white bg-blue-600 rounded-lg hover:bg-blue-700 transition-colors">
                                            <svg class="w-4 h-4 mr-1" fill="currentColor" viewBox="0 0 20 20">
                                                <path d="M10 12a2 2 0 100-4 2 2 0 000 4z"></path>
                                                <path fill-rule="evenodd" d="M.458 10C1.732 5.943 5.522 3 10 3s8.268 2.943 9.542 7c-1.274 4.057-5.064 7-9.542 7S1.732 14.057.458 10zM14 10a4 4 0 11-8 0 4 4 0 018 0z" clip-rule="evenodd"></path>
                                            </svg>
                                            View Details
                                        </a>

                                        <button onclick="toggleUserStatus({{ $webUser->id }})"
                                                class="inline-flex items-center justify-center px-4 py-2 text-xs font-medium text-white rounded-lg transition-colors {{ $webUser->is_active ? 'bg-red-600 hover:bg-red-700' : 'bg-green-600 hover:bg-green-700' }}">
                                            @if($webUser->is_active)
                                                <svg class="w-4 h-4 mr-1" fill="currentColor" viewBox="0 0 20 20">
                                                    <path fill-rule="evenodd" d="M13.477 14.89A6 6 0 015.11 6.524l8.367 8.368zm1.414-1.414L6.524 5.11a6 6 0 018.367 8.367zM18 10a8 8 0 11-16 0 8 8 0 0116 0z" clip-rule="evenodd"></path>
                                                </svg>
                                                Deactivate
                                            @else
                                                <svg class="w-4 h-4 mr-1" fill="currentColor" viewBox="0 0 20 20">
                                                    <path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zm3.707-9.293a1 1 0 00-1.414-1.414L9 10.586 7.707 9.293a1 1 0 00-1.414 1.414l2 2a1 1 0 001.414 0l4-4z" clip-rule="evenodd"></path>
                                                </svg>
                                                Activate
                                            @endif
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
