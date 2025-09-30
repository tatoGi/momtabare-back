@extends('admin.layouts.app')

@section('content')
<div class="container mx-auto px-4 py-6">
    <div class="md:flex md:items-center md:justify-between mb-6">
        <div>
            <h1 class="text-2xl font-bold">{{ $retailerShop->name }}</h1>
            <p class="mt-1 text-sm text-gray-500">Retailer Shop Details</p>
        </div>
        <div class="mt-4 md:mt-0 flex space-x-3">
            <a href="{{ route('admin.retailer-shops.edit', $retailerShop) }}" class="inline-flex items-center px-4 py-2 border border-transparent rounded-md shadow-sm text-sm font-medium text-white bg-indigo-600 hover:bg-indigo-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-indigo-500">
                Edit Shop
            </a>
            <a href="{{ route('admin.retailer-shops.index') }}" class="inline-flex items-center px-4 py-2 border border-gray-300 rounded-md shadow-sm text-sm font-medium text-gray-700 bg-white hover:bg-gray-50 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-indigo-500">
                Back to Shops
            </a>
        </div>
    </div>

    <div class="bg-white shadow overflow-hidden sm:rounded-lg mb-6">
        <div class="px-4 py-5 sm:px-6">
            <h3 class="text-lg leading-6 font-medium text-gray-900">Shop Information</h3>
            <p class="mt-1 max-w-2xl text-sm text-gray-500">Details about the retailer shop.</p>
        </div>
        <div class="border-t border-gray-200">
            <dl>
                <div class="bg-gray-50 px-4 py-5 sm:grid sm:grid-cols-3 sm:gap-4 sm:px-6">
                    <dt class="text-sm font-medium text-gray-500">Status</dt>
                    <dd class="mt-1 text-sm text-gray-900 sm:mt-0 sm:col-span-2">
                        <span class="px-2 inline-flex text-xs leading-5 font-semibold rounded-full {{ $retailerShop->is_active ? 'bg-green-100 text-green-800' : 'bg-red-100 text-red-800' }}">
                            {{ $retailerShop->is_active ? 'Active' : 'Inactive' }}
                        </span>
                    </dd>
                </div>
                <div class="bg-white px-4 py-5 sm:grid sm:grid-cols-3 sm:gap-4 sm:px-6">
                    <dt class="text-sm font-medium text-gray-500">Retailer</dt>
                    <dd class="mt-1 text-sm text-gray-900 sm:mt-0 sm:col-span-2">
                        {{ $retailerShop->user->name ?? 'N/A' }}
                        @if($retailerShop->user)
                            <p class="text-gray-500">{{ $retailerShop->user->email }}</p>
                        @endif
                    </dd>
                </div>
                <div class="bg-gray-50 px-4 py-5 sm:grid sm:grid-cols-3 sm:gap-4 sm:px-6">
                    <dt class="text-sm font-medium text-gray-500">Location</dt>
                    <dd class="mt-1 text-sm text-gray-900 sm:mt-0 sm:col-span-2">
                        {{ $retailerShop->location ?? 'Not specified' }}
                    </dd>
                </div>
                <div class="bg-white px-4 py-5 sm:grid sm:grid-cols-3 sm:gap-4 sm:px-6">
                    <dt class="text-sm font-medium text-gray-500">Contact Person</dt>
                    <dd class="mt-1 text-sm text-gray-900 sm:mt-0 sm:col-span-2">
                        {{ $retailerShop->contact_person }}
                    </dd>
                </div>
                <div class="bg-gray-50 px-4 py-5 sm:grid sm:grid-cols-3 sm:gap-4 sm:px-6">
                    <dt class="text-sm font-medium text-gray-500">Contact Phone</dt>
                    <dd class="mt-1 text-sm text-gray-900 sm:mt-0 sm:col-span-2">
                        {{ $retailerShop->contact_phone }}
                    </dd>
                </div>
                <div class="bg-white px-4 py-5 sm:grid sm:grid-cols-3 sm:gap-4 sm:px-6">
                    <dt class="text-sm font-medium text-gray-500">Description</dt>
                    <dd class="mt-1 text-sm text-gray-900 sm:mt-0 sm:col-span-2">
                        {{ $retailerShop->description ?? 'No description provided.' }}
                    </dd>
                </div>
                <div class="bg-gray-50 px-4 py-5 sm:grid sm:grid-cols-3 sm:gap-4 sm:px-6">
                    <dt class="text-sm font-medium text-gray-500">Created At</dt>
                    <dd class="mt-1 text-sm text-gray-900 sm:mt-0 sm:col-span-2">
                        {{ $retailerShop->created_at->format('M d, Y H:i') }}
                    </dd>
                </div>
                <div class="bg-white px-4 py-5 sm:grid sm:grid-cols-3 sm:gap-4 sm:px-6">
                    <dt class="text-sm font-medium text-gray-500">Last Updated</dt>
                    <dd class="mt-1 text-sm text-gray-900 sm:mt-0 sm:col-span-2">
                        {{ $retailerShop->updated_at->format('M d, Y H:i') }}
                    </dd>
                </div>
            </dl>
        </div>
    </div>

    <div class="bg-white shadow overflow-hidden sm:rounded-lg">
        <div class="px-4 py-5 sm:px-6">
            <h3 class="text-lg leading-6 font-medium text-gray-900">Shop Images</h3>
            <p class="mt-1 max-w-2xl text-sm text-gray-500">Shop avatar and cover image.</p>
        </div>
        <div class="border-t border-gray-200 px-4 py-5 sm:p-0">
            <dl class="sm:divide-y sm:divide-gray-200">
                <div class="py-4 sm:py-5 sm:grid sm:grid-cols-3 sm:gap-4 sm:px-6">
                    <dt class="text-sm font-medium text-gray-500">Avatar</dt>
                    <dd class="mt-1 text-sm text-gray-900 sm:mt-0 sm:col-span-2">
                        @if($retailerShop->avatar)
                            <img src="{{ $retailerShop->avatar_url }}" alt="{{ $retailerShop->name }} Avatar" class="h-24 w-24 rounded-full object-cover">
                        @else
                            <div class="h-24 w-24 rounded-full bg-gray-200 flex items-center justify-center">
                                <svg class="h-12 w-12 text-gray-400" fill="currentColor" viewBox="0 0 24 24">
                                    <path d="M24 20.993V24H0v-2.996A14.977 14.977 0 0112.004 15c4.111 0 7.592 2.543 8.94 6.004h-2.97c-.922-1.651-2.23-2.9-3.97-3.5 1.11-.65 1.87-1.88 1.87-3.29 0-2.07-1.68-3.75-3.75-3.75S9.254 12.18 9.254 14.25c0 1.41.76 2.64 1.87 3.29-1.74.6-3.05 1.85-3.97 3.5H2.065A14.94 14.94 0 0112 15c4.11 0 7.59 2.543 8.94 6.004zM12 12a3.75 3.75 0 100-7.5 3.75 3.75 0 000 7.5z" />
                                </svg>
                            </div>
                        @endif
                    </dd>
                </div>
                <div class="py-4 sm:py-5 sm:grid sm:grid-cols-3 sm:gap-4 sm:px-6">
                    <dt class="text-sm font-medium text-gray-500">Cover Image</dt>
                    <dd class="mt-1 text-sm text-gray-900 sm:mt-0 sm:col-span-2">
                        @if($retailerShop->cover_image)
                            <img src="{{ $retailerShop->cover_image_url }}" alt="{{ $retailerShop->name }} Cover" class="h-48 w-full object-cover rounded-lg">
                        @else
                            <div class="h-48 w-full bg-gray-200 rounded-lg flex items-center justify-center">
                                <svg class="h-12 w-12 text-gray-400" stroke="currentColor" fill="none" viewBox="0 0 48 48" aria-hidden="true">
                                    <path d="M28 8H12a4 4 0 00-4 4v20m32-12v8m0 0v8a4 4 0 01-4 4H12a4 4 0 01-4-4v-4m32-4l-3.172-3.172a4 4 0 00-5.656 0L28 28M8 32l9.172-9.172a4 4 0 015.656 0L28 28m0 0l4 4m4-24h8m-4-4v8m-12 4h.02" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" />
                                </svg>
                            </div>
                        @endif
                    </dd>
                </div>
            </dl>
        </div>
    </div>

    @if($retailerShop->products_count > 0)
        <div class="mt-6 bg-white shadow overflow-hidden sm:rounded-lg">
            <div class="px-4 py-5 sm:px-6">
                <h3 class="text-lg leading-6 font-medium text-gray-900">Products ({{ $retailerShop->products_count }})</h3>
                <p class="mt-1 max-w-2xl text-sm text-gray-500">Products available in this shop.</p>
            </div>
            <div class="border-t border-gray-200">
                <div class="overflow-x-auto">
                    <table class="min-w-full divide-y divide-gray-200">
                        <thead class="bg-gray-50">
                            <tr>
                                <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Product</th>
                                <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Price</th>
                                <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Status</th>
                                <th scope="col" class="relative px-6 py-3">
                                    <span class="sr-only">Actions</span>
                                </th>
                            </tr>
                        </thead>
                        <tbody class="bg-white divide-y divide-gray-200">
                            @foreach($retailerShop->products as $product)
                                <tr>
                                    <td class="px-6 py-4 whitespace-nowrap">
                                        <div class="flex items-center">
                                            <div class="flex-shrink-0 h-10 w-10">
                                                @if($product->images->isNotEmpty())
                                                    <img class="h-10 w-10 rounded-full object-cover" src="{{ $product->images->first()->image_url }}" alt="{{ $product->title }}">
                                                @else
                                                    <div class="h-10 w-10 rounded-full bg-gray-200 flex items-center justify-center">
                                                        <svg class="h-6 w-6 text-gray-400" fill="currentColor" viewBox="0 0 24 24">
                                                            <path d="M24 20.993V24H0v-2.996A14.977 14.977 0 0112.004 15c4.111 0 7.592 2.543 8.94 6.004h-2.97c-.922-1.651-2.23-2.9-3.97-3.5 1.11-.65 1.87-1.88 1.87-3.29 0-2.07-1.68-3.75-3.75-3.75S9.254 12.18 9.254 14.25c0 1.41.76 2.64 1.87 3.29-1.74.6-3.05 1.85-3.97 3.5H2.065A14.94 14.94 0 0112 15c4.11 0 7.59 2.543 8.94 6.004zM12 12a3.75 3.75 0 100-7.5 3.75 3.75 0 000 7.5z" />
                                                        </svg>
                                                    </div>
                                                @endif
                                            </div>
                                            <div class="ml-4">
                                                <div class="text-sm font-medium text-gray-900">{{ $product->title }}</div>
                                                <div class="text-sm text-gray-500">{{ $product->product_identify_id }}</div>
                                            </div>
                                        </div>
                                    </td>
                                    <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500">
                                        {{ number_format($product->price, 2) }} {{ config('settings.currency_symbol', '$') }}
                                    </td>
                                    <td class="px-6 py-4 whitespace-nowrap">
                                        <span class="px-2 inline-flex text-xs leading-5 font-semibold rounded-full {{ $product->is_active ? 'bg-green-100 text-green-800' : 'bg-red-100 text-red-800' }}">
                                            {{ $product->is_active ? 'Active' : 'Inactive' }}
                                        </span>
                                    </td>
                                    <td class="px-6 py-4 whitespace-nowrap text-right text-sm font-medium">
                                        <a href="{{ route('admin.products.edit', $product) }}" class="text-indigo-600 hover:text-indigo-900">View</a>
                                    </td>
                                </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    @endif
</div>
@endsection
