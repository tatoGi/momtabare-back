@extends('admin.layouts.app')

@section('content')
<div class="container mx-auto px-4 py-6">
    <div class="flex justify-between items-center mb-6">
        <h1 class="text-2xl font-bold">Retailer Shops</h1>
        <a href="{{ route('admin.retailer-shops.create') }}" class="bg-blue-500 hover:bg-blue-600 text-white px-4 py-2 rounded">
            Add New Shop
        </a>
    </div>

    <div class="bg-white rounded-lg shadow overflow-hidden">
        <div class="overflow-x-auto">
            <table class="min-w-full divide-y divide-gray-200">
                <thead class="bg-gray-50">
                    <tr>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Shop Name</th>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Retailer</th>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Location</th>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Status</th>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Actions</th>
                    </tr>
                </thead>
                <tbody class="bg-white divide-y divide-gray-200">
                    @forelse($shops as $shop)
                        <tr>
                            <td class="px-6 py-4 whitespace-nowrap">
                                <div class="flex items-center">
                                    <div class="flex-shrink-0 h-10 w-10">
                                        @if($shop->avatar)
                                            <img class="h-10 w-10 rounded-full" src="{{ $shop->avatar_url }}" alt="{{ $shop->name }}">
                                        @else
                                            <div class="h-10 w-10 rounded-full bg-gray-200 flex items-center justify-center">
                                                <svg class="h-6 w-6 text-gray-400" fill="currentColor" viewBox="0 0 24 24">
                                                    <path d="M12 12a5 5 0 110-10 5 5 0 010 10zm0-2a3 3 0 100-6 3 3 0 000 6zm8 11a1 1 0 01-2 0v-2a3 3 0 00-3-3H9a3 3 0 00-3 3v2a1 1 0 11-2 0v-2a5 5 0 015-5h6a5 5 0 015 5v2z" />
                                                </svg>
                                            </div>
                                        @endif
                                    </div>
                                    <div class="ml-4">
                                        <div class="text-sm font-medium text-gray-900">{{ $shop->name }}</div>
                                        <div class="text-sm text-gray-500">{{ $shop->products_count ?? 0 }} products</div>
                                    </div>
                                </div>
                            </td>
                            <td class="px-6 py-4 whitespace-nowrap">
                                <div class="text-sm text-gray-900">{{ $shop->user->name ?? 'N/A' }}</div>
                                <div class="text-sm text-gray-500">{{ $shop->user->email ?? '' }}</div>
                            </td>
                            <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500">
                                {{ $shop->location }}
                            </td>
                            <td class="px-6 py-4 whitespace-nowrap">
                                <span class="px-2 inline-flex text-xs leading-5 font-semibold rounded-full {{ $shop->is_active ? 'bg-green-100 text-green-800' : 'bg-red-100 text-red-800' }}">
                                    {{ $shop->is_active ? 'Active' : 'Inactive' }}
                                </span>
                            </td>
                            <td class="px-6 py-4 whitespace-nowrap text-sm font-medium">
                                <a href="{{ route('admin.retailer-shops.edit', $shop) }}" class="text-indigo-600 hover:text-indigo-900 mr-3">Edit</a>
                                <form action="{{ route('admin.retailer-shops.destroy', $shop) }}" method="POST" class="inline" onsubmit="return confirm('Are you sure you want to delete this shop?');">
                                    @csrf
                                    @method('DELETE')
                                    <button type="submit" class="text-red-600 hover:text-red-900">Delete</button>
                                </form>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="5" class="px-6 py-4 text-center text-sm text-gray-500">
                                No shops found. Create your first shop to get started.
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
        @if($shops->hasPages())
            <div class="px-6 py-4">
                {{ $shops->links() }}
            </div>
        @endif
    </div>
</div>
@endsection
