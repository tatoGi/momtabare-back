<x-admin.admin-layout>
<div class="container mx-auto px-4 py-6">
    <div class="md:flex md:items-center md:justify-between mb-6">
        <h1 class="text-2xl font-bold">Edit Retailer Shop: {{ $shop->name }}</h1>
        <div class="mt-4 md:mt-0 space-x-3">
            <a href="/{{app()->getLocale()}}/retailer-shops/{{ $shop->id }}" class="inline-flex items-center px-4 py-2 border border-gray-300 rounded-md shadow-sm text-sm font-medium text-gray-700 bg-white hover:bg-gray-50 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-indigo-500">
                View Shop
            </a>
            <a href="{{ route('admin.retailer-shops.index') }}" class="inline-flex items-center px-4 py-2 border border-transparent rounded-md shadow-sm text-sm font-medium text-white bg-gray-600 hover:bg-gray-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-gray-500">
                Back to Shops
            </a>
        </div>
    </div>

    <div class="bg-white shadow overflow-hidden sm:rounded-lg">
        <div class="px-4 py-5 sm:p-6">
            @include('admin.retailer-shops._form', ['shop' => $shop])
        </div>
    </div>
</div>
</x-admin.admin-layout>
