@php
    $isEdit = isset($shop);
    $method = $isEdit ? 'PUT' : 'POST';
    $action = $isEdit ? route('admin.retailer-shops.update', [app()->getLocale(), $shop->id]) : route('admin.retailer-shops.store', app()->getLocale());
    $locales = config('translatable.locales');
@endphp

<form action="{{ $action }}" method="POST" enctype="multipart/form-data" class="space-y-6">
    @csrf
    @method($method)

    <div class="bg-white shadow px-4 py-5 sm:rounded-lg sm:p-6">
        <div class="md:grid md:grid-cols-3 md:gap-6">
            <div class="md:col-span-1">
                <h3 class="text-lg font-medium leading-6 text-gray-900">Basic Information</h3>
                <p class="mt-1 text-sm text-gray-500">Basic shop information and contact details.</p>
            </div>
            <div class="mt-5 md:mt-0 md:col-span-2">
                <div class="grid grid-cols-6 gap-6">
                    <div class="col-span-6 sm:col-span-3">
                        <label for="user_id" class="block text-sm font-medium text-gray-700">Retailer</label>
                        <select id="user_id" name="user_id" required
                            class="mt-1 block w-full py-2 px-3 border border-gray-300 bg-white rounded-md shadow-sm focus:outline-none focus:ring-indigo-500 focus:border-indigo-500 sm:text-sm">
                            <option value="">Select Retailer</option>
                            @foreach($retailers as $retailer)
                                <option value="{{ $retailer->id }}" {{ ($isEdit && $shop->user_id == $retailer->id) ? 'selected' : '' }}>
                                    {{ $retailer->name }} ({{ $retailer->email }})
                                </option>
                            @endforeach
                        </select>
                        @error('user_id')
                            <p class="mt-2 text-sm text-red-600">{{ $message }}</p>
                        @enderror
                    </div>

                    <div class="col-span-6 sm:col-span-3">
                        <label for="location" class="block text-sm font-medium text-gray-700">Location</label>
                        <input type="text" name="location" id="location" required
                            value="{{ $isEdit ? $shop->location : old('location') }}"
                            class="mt-1 focus:ring-indigo-500 focus:border-indigo-500 block w-full shadow-sm sm:text-sm border-gray-300 rounded-md">
                        @error('location')
                            <p class="mt-2 text-sm text-red-600">{{ $message }}</p>
                        @enderror
                    </div>

                    <div class="col-span-6 sm:col-span-3">
                        <label for="contact_person" class="block text-sm font-medium text-gray-700">Contact Person</label>
                        <input type="text" name="contact_person" id="contact_person" required
                            value="{{ $isEdit ? $shop->contact_person : old('contact_person') }}"
                            class="mt-1 focus:ring-indigo-500 focus:border-indigo-500 block w-full shadow-sm sm:text-sm border-gray-300 rounded-md">
                        @error('contact_person')
                            <p class="mt-2 text-sm text-red-600">{{ $message }}</p>
                        @enderror
                    </div>

                    <div class="col-span-6 sm:col-span-3">
                        <label for="contact_phone" class="block text-sm font-medium text-gray-700">Contact Phone</label>
                        <input type="text" name="contact_phone" id="contact_phone" required
                            value="{{ $isEdit ? $shop->contact_phone : old('contact_phone') }}"
                            class="mt-1 focus:ring-indigo-500 focus:border-indigo-500 block w-full shadow-sm sm:text-sm border-gray-300 rounded-md">
                        @error('contact_phone')
                            <p class="mt-2 text-sm text-red-600">{{ $message }}</p>
                        @enderror
                    </div>

                    <div class="col-span-6">
                        <div class="flex items-start">
                            <div class="flex items-center h-5">
                                <input id="is_active" name="is_active" type="checkbox"
                                    {{ ($isEdit && $shop->is_active) || old('is_active') ? 'checked' : '' }}
                                    class="focus:ring-indigo-500 h-4 w-4 text-indigo-600 border-gray-300 rounded">
                            </div>
                            <div class="ml-3 text-sm">
                                <label for="is_active" class="font-medium text-gray-700">Active</label>
                                <p class="text-gray-500">When active, the shop will be visible to customers.</p>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <div class="bg-white shadow px-4 py-5 sm:rounded-lg sm:p-6">
        <div class="md:grid md:grid-cols-3 md:gap-6">
            <div class="md:col-span-1">
                <h3 class="text-lg font-medium leading-6 text-gray-900">Images</h3>
                <p class="mt-1 text-sm text-gray-500">Shop avatar and cover image.</p>
            </div>
            <div class="mt-5 md:mt-0 md:col-span-2 space-y-6">
                <div>
                    <label class="block text-sm font-medium text-gray-700">Avatar</label>
                    <div class="mt-1 flex items-center">
                        <span class="inline-block h-12 w-12 rounded-full overflow-hidden bg-gray-100">
                            @if($isEdit && $shop->avatar)
                                <img src="{{ $shop->avatar_url }}" alt="{{ $shop->name }}" class="h-full w-full object-cover">
                            @else
                                <svg class="h-full w-full text-gray-300" fill="currentColor" viewBox="0 0 24 24">
                                    <path d="M24 20.993V24H0v-2.996A14.977 14.977 0 0112.004 15c4.111 0 7.592 2.543 8.94 6.004h-2.97c-.922-1.651-2.23-2.9-3.97-3.5 1.11-.65 1.87-1.88 1.87-3.29 0-2.07-1.68-3.75-3.75-3.75S9.254 12.18 9.254 14.25c0 1.41.76 2.64 1.87 3.29-1.74.6-3.05 1.85-3.97 3.5H2.065A14.94 14.94 0 0112 15c4.11 0 7.59 2.543 8.94 6.004zM12 12a3.75 3.75 0 100-7.5 3.75 3.75 0 000 7.5z" />
                                </svg>
                            @endif
                        </span>
                        <label for="avatar" class="ml-5">
                            <div class="py-2 px-3 border border-gray-300 rounded-md shadow-sm text-sm font-medium text-gray-700 bg-white hover:bg-gray-50 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-indigo-500 cursor-pointer">
                                Change
                            </div>
                            <input id="avatar" name="avatar" type="file" class="sr-only" accept="image/*">
                        </label>
                    </div>
                    <p class="mt-2 text-sm text-gray-500">Square image recommended (e.g., 500x500px). Max 5MB.</p>
                    @error('avatar')
                        <p class="mt-2 text-sm text-red-600">{{ $message }}</p>
                    @enderror
                </div>

                <div>
                    <label class="block text-sm font-medium text-gray-700">Cover Image</label>
                    <div class="mt-1 flex justify-center px-6 pt-5 pb-6 border-2 border-gray-300 border-dashed rounded-md">
                        <div class="space-y-1 text-center">
                            @if($isEdit && $shop->cover_image)
                                <img src="{{ $shop->cover_image_url }}" alt="{{ $shop->name }} Cover" class="mx-auto h-48 object-cover">
                            @else
                                <svg class="mx-auto h-12 w-12 text-gray-400" stroke="currentColor" fill="none" viewBox="0 0 48 48" aria-hidden="true">
                                    <path d="M28 8H12a4 4 0 00-4 4v20m32-12v8m0 0v8a4 4 0 01-4 4H12a4 4 0 01-4-4v-4m32-4l-3.172-3.172a4 4 0 00-5.656 0L28 28M8 32l9.172-9.172a4 4 0 015.656 0L28 28m0 0l4 4m4-24h8m-4-4v8m-12 4h.02" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" />
                                </svg>
                            @endif
                            <div class="flex text-sm text-gray-600">
                                <label for="cover_image" class="relative cursor-pointer bg-white rounded-md font-medium text-indigo-600 hover:text-indigo-500 focus-within:outline-none focus-within:ring-2 focus-within:ring-offset-2 focus-within:ring-indigo-500">
                                    <span>Upload a file</span>
                                    <input id="cover_image" name="cover_image" type="file" class="sr-only" accept="image/*">
                                </label>
                                <p class="pl-1">or drag and drop</p>
                            </div>
                            <p class="text-xs text-gray-500">PNG, JPG, GIF up to 5MB</p>
                        </div>
                    </div>
                    @error('cover_image')
                        <p class="mt-2 text-sm text-red-600">{{ $message }}</p>
                    @enderror
                </div>
            </div>
        </div>
    </div>

    @foreach($locales as $locale)
        <div class="bg-white shadow px-4 py-5 sm:rounded-lg sm:p-6">
            <div class="md:grid md:grid-cols-3 md:gap-6">
                <div class="md:col-span-1">
                    <h3 class="text-lg font-medium leading-6 text-gray-900">Content ({{ strtoupper($locale) }})</h3>
                    <p class="mt-1 text-sm text-gray-500">Shop name and description in {{ $locale }}.</p>
                </div>
                <div class="mt-5 md:mt-0 md:col-span-2">
                    <div class="grid grid-cols-6 gap-6">
                        <div class="col-span-6">
                            <label for="name_{{ $locale }}" class="block text-sm font-medium text-gray-700">Shop Name</label>
                            <input type="text" name="name_{{ $locale }}" id="name_{{ $locale }}" required
                                value="{{ $isEdit ? ($shop->translate($locale) ? $shop->translate($locale)->name : '') : old('name_'.$locale) }}"
                                class="mt-1 focus:ring-indigo-500 focus:border-indigo-500 block w-full shadow-sm sm:text-sm border-gray-300 rounded-md">
                            @error('name_'.$locale)
                                <p class="mt-2 text-sm text-red-600">{{ $message }}</p>
                            @enderror
                        </div>

                        <div class="col-span-6">
                            <label for="description_{{ $locale }}" class="block text-sm font-medium text-gray-700">Description</label>
                            <textarea id="description_{{ $locale }}" name="description_{{ $locale }}" rows="3"
                                class="shadow-sm focus:ring-indigo-500 focus:border-indigo-500 mt-1 block w-full sm:text-sm border border-gray-300 rounded-md">{{ $isEdit ? ($shop->translate($locale) ? $shop->translate($locale)->description : '') : old('description_'.$locale) }}</textarea>
                            @error('description_'.$locale)
                                <p class="mt-2 text-sm text-red-600">{{ $message }}</p>
                            @enderror
                        </div>
                    </div>
                </div>
            </div>
        </div>
    @endforeach

    <div class="flex justify-end">
        <a href="{{ route('admin.retailer-shops.index') }}" class="bg-white py-2 px-4 border border-gray-300 rounded-md shadow-sm text-sm font-medium text-gray-700 hover:bg-gray-50 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-indigo-500">
            Cancel
        </a>
        <button type="submit" class="ml-3 inline-flex justify-center py-2 px-4 border border-transparent shadow-sm text-sm font-medium rounded-md text-white bg-indigo-600 hover:bg-indigo-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-indigo-500">
            {{ $isEdit ? 'Update' : 'Create' }} Shop
        </button>
    </div>
</form>
