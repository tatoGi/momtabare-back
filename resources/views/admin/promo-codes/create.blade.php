<x-admin.admin-layout>
    <!-- Header Section -->
    <div class="mb-6">
        <div class="flex items-center justify-between">
            <div>
                <h1 class="text-3xl font-bold text-gray-800 flex items-center gap-3">
                    <svg class="w-8 h-8 text-green-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"></path>
                    </svg>
                    Create New Promo Code
                </h1>
                <p class="text-gray-600 mt-2">Add a new promotional discount code</p>
            </div>
            <a href="{{ route('promo-codes.index', app()->getlocale()) }}"
               class="inline-flex items-center px-4 py-2 bg-gray-600 hover:bg-gray-700 text-white font-semibold rounded-lg shadow-md transition duration-300 ease-in-out transform hover:scale-105">
                <svg class="w-5 h-5 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 19l-7-7m0 0l7-7m-7 7h18"></path>
                </svg>
                Back to List
            </a>
        </div>
    </div>

    <!-- Error Display -->
    @if ($errors->any())
        <div class="mb-6 bg-red-50 border-l-4 border-red-500 rounded-lg p-4 shadow-md">
            <div class="flex items-start">
                <svg class="w-6 h-6 text-red-500 mr-3 flex-shrink-0" fill="currentColor" viewBox="0 0 20 20">
                    <path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zM8.707 7.293a1 1 0 00-1.414 1.414L8.586 10l-1.293 1.293a1 1 0 101.414 1.414L10 11.414l1.293 1.293a1 1 0 001.414-1.414L11.414 10l1.293-1.293a1 1 0 00-1.414-1.414L10 8.586 8.707 7.293z" clip-rule="evenodd"></path>
                </svg>
                <div>
                    <h3 class="text-red-800 font-semibold mb-2">Please fix the following errors:</h3>
                    <ul class="list-disc list-inside text-red-700 space-y-1">
                        @foreach ($errors->all() as $error)
                            <li>{{ $error }}</li>
                        @endforeach
                    </ul>
                </div>
            </div>
        </div>
    @endif

    <form action="{{ route('promo-codes.store', app()->getlocale()) }}" method="POST">
        @csrf

        <div class="grid grid-cols-1 lg:grid-cols-3 gap-6">
            <!-- Main Content Area (Left Side - 2/3) -->
            <div class="lg:col-span-2 space-y-6">

                <!-- Basic Information Card -->
                <div class="bg-white rounded-xl shadow-lg overflow-hidden border border-gray-200">
                    <div class="bg-gradient-to-r from-blue-500 to-blue-600 px-6 py-4">
                        <h2 class="text-xl font-bold text-white flex items-center gap-2">
                            <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M7 7h.01M7 3h5c.512 0 1.024.195 1.414.586l7 7a2 2 0 010 2.828l-7 7a2 2 0 01-2.828 0l-7-7A1.994 1.994 0 013 12V7a4 4 0 014-4z"></path>
                            </svg>
                            Basic Information
                        </h2>
                    </div>

                    <div class="p-6 space-y-4">
                        <!-- Promo Code -->
                        <div class="form-group">
                            <label for="code" class="block text-sm font-semibold text-gray-700 mb-2">
                                <span class="text-red-500">*</span> Promo Code
                            </label>
                            <div class="relative">
                                <input type="text"
                                       name="code"
                                       id="code"
                                       value="{{ old('code') }}"
                                       class="w-full px-4 py-3 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-transparent transition-all uppercase @error('code') border-red-500 @enderror"
                                       placeholder="Enter promo code (e.g., SAVE20)"
                                       required>
                                <svg class="absolute right-3 top-3.5 w-5 h-5 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M7 20l4-16m2 16l4-16M6 9h14M4 15h14"></path>
                                </svg>
                            </div>
                            @error('code')
                                <p class="text-red-500 text-sm mt-1">{{ $message }}</p>
                            @enderror
                        </div>

                        <!-- Discount Percentage -->
                        <div class="form-group">
                            <label for="discount_percentage" class="block text-sm font-semibold text-gray-700 mb-2">
                                <span class="text-red-500">*</span> Discount Percentage (%)
                            </label>
                            <div class="relative">
                                <input type="number"
                                       name="discount_percentage"
                                       id="discount_percentage"
                                       value="{{ old('discount_percentage') }}"
                                       min="0"
                                       max="100"
                                       step="0.01"
                                       class="w-full px-4 py-3 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-transparent transition-all @error('discount_percentage') border-red-500 @enderror"
                                       placeholder="Enter discount percentage (0-100)"
                                       required>
                                <div class="absolute right-3 top-3.5 text-gray-500 font-semibold">%</div>
                            </div>
                            @error('discount_percentage')
                                <p class="text-red-500 text-sm mt-1">{{ $message }}</p>
                            @enderror
                        </div>

                        <!-- Description -->
                        <div class="form-group">
                            <label for="description" class="block text-sm font-semibold text-gray-700 mb-2">
                                Description
                            </label>
                            <textarea name="description"
                                      id="description"
                                      rows="3"
                                      class="w-full px-4 py-3 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-transparent transition-all @error('description') border-red-500 @enderror"
                                      placeholder="Optional description for internal use">{{ old('description') }}</textarea>
                            @error('description')
                                <p class="text-red-500 text-sm mt-1">{{ $message }}</p>
                            @enderror
                        </div>
                    </div>
                </div>

                <!-- Usage Limits Card -->
                <div class="bg-white rounded-xl shadow-lg overflow-hidden border border-gray-200">
                    <div class="bg-gradient-to-r from-purple-500 to-purple-600 px-6 py-4">
                        <h2 class="text-xl font-bold text-white flex items-center gap-2">
                            <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2"></path>
                            </svg>
                            Usage Limits
                        </h2>
                    </div>

                    <div class="p-6 space-y-4">
                        <!-- Max Uses -->
                        <div class="form-group">
                            <label for="max_uses" class="block text-sm font-semibold text-gray-700 mb-2">
                                Maximum Total Uses
                            </label>
                            <input type="number"
                                   name="max_uses"
                                   id="max_uses"
                                   value="{{ old('max_uses') }}"
                                   min="0"
                                   class="w-full px-4 py-3 border border-gray-300 rounded-lg focus:ring-2 focus:ring-purple-500 focus:border-transparent transition-all @error('max_uses') border-red-500 @enderror"
                                   placeholder="Leave empty for unlimited">
                            <p class="text-sm text-gray-500 mt-1">Total number of times this code can be used across all users</p>
                            @error('max_uses')
                                <p class="text-red-500 text-sm mt-1">{{ $message }}</p>
                            @enderror
                        </div>

                        <!-- Max Uses Per User -->
                        <div class="form-group">
                            <label for="max_uses_per_user" class="block text-sm font-semibold text-gray-700 mb-2">
                                Maximum Uses Per User
                            </label>
                            <input type="number"
                                   name="max_uses_per_user"
                                   id="max_uses_per_user"
                                   value="{{ old('max_uses_per_user') }}"
                                   min="0"
                                   class="w-full px-4 py-3 border border-gray-300 rounded-lg focus:ring-2 focus:ring-purple-500 focus:border-transparent transition-all @error('max_uses_per_user') border-red-500 @enderror"
                                   placeholder="Leave empty for unlimited">
                            <p class="text-sm text-gray-500 mt-1">Maximum times each user can use this code</p>
                            @error('max_uses_per_user')
                                <p class="text-red-500 text-sm mt-1">{{ $message }}</p>
                            @enderror
                        </div>
                    </div>
                </div>

                <!-- Validity Period Card -->
                <div class="bg-white rounded-xl shadow-lg overflow-hidden border border-gray-200">
                    <div class="bg-gradient-to-r from-green-500 to-green-600 px-6 py-4">
                        <h2 class="text-xl font-bold text-white flex items-center gap-2">
                            <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z"></path>
                            </svg>
                            Validity Period
                        </h2>
                    </div>

                    <div class="p-6 space-y-4">
                        <!-- Start Date -->
                        <div class="form-group">
                            <label for="valid_from" class="block text-sm font-semibold text-gray-700 mb-2">
                                Valid From
                            </label>
                            <input type="datetime-local"
                                   name="valid_from"
                                   id="valid_from"
                                   value="{{ old('valid_from') }}"
                                   class="w-full px-4 py-3 border border-gray-300 rounded-lg focus:ring-2 focus:ring-green-500 focus:border-transparent transition-all @error('valid_from') border-red-500 @enderror">
                            <p class="text-sm text-gray-500 mt-1">Leave empty to make active immediately</p>
                            @error('valid_from')
                                <p class="text-red-500 text-sm mt-1">{{ $message }}</p>
                            @enderror
                        </div>

                        <!-- End Date -->
                        <div class="form-group">
                            <label for="valid_until" class="block text-sm font-semibold text-gray-700 mb-2">
                                Valid Until
                            </label>
                            <input type="datetime-local"
                                   name="valid_until"
                                   id="valid_until"
                                   value="{{ old('valid_until') }}"
                                   class="w-full px-4 py-3 border border-gray-300 rounded-lg focus:ring-2 focus:ring-green-500 focus:border-transparent transition-all @error('valid_until') border-red-500 @enderror">
                            <p class="text-sm text-gray-500 mt-1">Leave empty for no expiration date</p>
                            @error('valid_until')
                                <p class="text-red-500 text-sm mt-1">{{ $message }}</p>
                            @enderror
                        </div>
                    </div>
                </div>

                <!-- Product Selection Card -->
                <div class="bg-white rounded-xl shadow-lg overflow-hidden border border-gray-200">
                    <div class="bg-gradient-to-r from-orange-500 to-orange-600 px-6 py-4">
                        <h2 class="text-xl font-bold text-white flex items-center gap-2">
                            <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M20 7l-8-4-8 4m16 0l-8 4m8-4v10l-8 4m0-10L4 7m8 4v10M4 7v10l8 4"></path>
                            </svg>
                            Applicable Products (Optional)
                        </h2>
                    </div>

                    <div class="p-6">
                        <p class="text-sm text-gray-600 mb-4">
                            Select specific products for this promo code. Leave empty to apply to all products.
                        </p>
                        <select name="product_ids[]"
                                id="product_ids"
                                multiple
                                class="w-full select2-products">
                            @foreach($products as $product)
                                <option value="{{ $product->id }}" {{ in_array($product->id, old('product_ids', [])) ? 'selected' : '' }}>
                                    {{ $product->title }} - ₾{{ number_format($product->price, 2) }}
                                </option>
                            @endforeach
                        </select>
                        <p class="text-sm text-gray-500 mt-2">Search and select multiple products</p>
                    </div>
                </div>

                <!-- Category Selection Card -->
                <div class="bg-white rounded-xl shadow-lg overflow-hidden border border-gray-200">
                    <div class="bg-gradient-to-r from-pink-500 to-pink-600 px-6 py-4">
                        <h2 class="text-xl font-bold text-white flex items-center gap-2">
                            <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M7 7h.01M7 3h5c.512 0 1.024.195 1.414.586l7 7a2 2 0 010 2.828l-7 7a2 2 0 01-2.828 0l-7-7A1.994 1.994 0 013 12V7a4 4 0 014-4z"></path>
                            </svg>
                            Applicable Categories (Optional)
                        </h2>
                    </div>

                    <div class="p-6">
                        <p class="text-sm text-gray-600 mb-4">
                            Select specific categories for this promo code. Leave empty to apply to all categories.
                        </p>
                        <select name="category_ids[]"
                                id="category_ids"
                                multiple
                                class="w-full select2-categories">
                            @foreach($categories as $category)
                                <option value="{{ $category->id }}" {{ in_array($category->id, old('category_ids', [])) ? 'selected' : '' }}>
                                    {{ $category->title }}
                                </option>
                            @endforeach
                        </select>
                        <p class="text-sm text-gray-500 mt-2">Search and select multiple categories</p>
                    </div>
                </div>

                <!-- User Assignment Card -->
                <div class="bg-white rounded-xl shadow-lg overflow-hidden border border-gray-200">
                    <div class="bg-gradient-to-r from-indigo-500 to-indigo-600 px-6 py-4">
                        <h2 class="text-xl font-bold text-white flex items-center gap-2">
                            <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4.354a4 4 0 110 5.292M15 21H3v-1a6 6 0 0112 0v1zm0 0h6v-1a6 6 0 00-9-5.197M13 7a4 4 0 11-8 0 4 4 0 018 0z"></path>
                            </svg>
                            Assign to Users (Optional)
                        </h2>
                    </div>

                    <div class="p-6">
                        <p class="text-sm text-gray-600 mb-4">
                            Assign this promo code to specific users. Leave empty to make it available for all users.
                        </p>
                        <select name="user_ids[]"
                                id="user_ids"
                                multiple
                                class="w-full select2-users">
                            @foreach($users as $user)
                                <option value="{{ $user->id }}" {{ in_array($user->id, old('user_ids', [])) ? 'selected' : '' }}>
                                    {{ $user->first_name }} ({{ $user->email }})
                                </option>
                            @endforeach
                        </select>
                        <p class="text-sm text-gray-500 mt-2">Search and select multiple users</p>
                    </div>
                </div>

            </div>

            <!-- Sidebar (Right Side - 1/3) -->
            <div class="lg:col-span-1 space-y-6">

                <!-- Status Card -->
                <div class="bg-white rounded-xl shadow-lg overflow-hidden border border-gray-200 sticky top-6">
                    <div class="bg-gradient-to-r from-indigo-500 to-indigo-600 px-6 py-4">
                        <h2 class="text-xl font-bold text-white flex items-center gap-2">
                            <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"></path>
                            </svg>
                            Status
                        </h2>
                    </div>

                    <div class="p-6 space-y-4">
                        <!-- Active Status -->
                        <div class="form-group">
                            <label class="flex items-center cursor-pointer group">
                                <div class="relative">
                                    <input type="checkbox"
                                           name="is_active"
                                           id="is_active"
                                           value="1"
                                           {{ old('is_active', true) ? 'checked' : '' }}
                                           class="sr-only peer">
                                    <div class="w-14 h-7 bg-gray-300 rounded-full peer peer-checked:bg-green-500 transition-colors duration-300"></div>
                                    <div class="absolute left-1 top-1 w-5 h-5 bg-white rounded-full transition-transform duration-300 peer-checked:translate-x-7 shadow-md"></div>
                                </div>
                                <span class="ml-3 text-sm font-semibold text-gray-700 group-hover:text-gray-900">Active</span>
                            </label>
                            <p class="text-xs text-gray-500 mt-2">Enable or disable this promo code</p>
                        </div>

                        <!-- Action Buttons -->
                        <div class="pt-4 border-t space-y-3">
                            <button type="submit"
                                    class="w-full inline-flex items-center justify-center px-6 py-3 bg-gradient-to-r from-green-500 to-green-600 hover:from-green-600 hover:to-green-700 text-white font-bold rounded-lg shadow-lg transition duration-300 ease-in-out transform hover:scale-105">
                                <svg class="w-5 h-5 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"></path>
                                </svg>
                                Create Promo Code
                            </button>

                            <a href="{{ route('promo-codes.index', app()->getlocale()) }}"
                               class="w-full inline-flex items-center justify-center px-6 py-3 bg-gray-200 hover:bg-gray-300 text-gray-700 font-semibold rounded-lg transition duration-300">
                                <svg class="w-5 h-5 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"></path>
                                </svg>
                                Cancel
                            </a>
                        </div>

                        <!-- Help Info -->
                        <div class="mt-6 p-4 bg-blue-50 rounded-lg border border-blue-200">
                            <div class="flex items-start gap-3">
                                <svg class="w-5 h-5 text-blue-600 flex-shrink-0 mt-0.5" fill="currentColor" viewBox="0 0 20 20">
                                    <path fill-rule="evenodd" d="M18 10a8 8 0 11-16 0 8 8 0 0116 0zm-7-4a1 1 0 11-2 0 1 1 0 012 0zM9 9a1 1 0 000 2v3a1 1 0 001 1h1a1 1 0 100-2v-3a1 1 0 00-1-1H9z" clip-rule="evenodd"></path>
                                </svg>
                                <div class="text-sm text-blue-800">
                                    <p class="font-semibold mb-1">Tips:</p>
                                    <ul class="space-y-1 text-xs">
                                        <li>• Use uppercase letters for promo codes</li>
                                        <li>• Set validity dates for limited-time offers</li>
                                        <li>• Leave products/categories empty for site-wide discounts</li>
                                    </ul>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

            </div>
        </div>
    </form>

    <style>
        /* Custom Select2 styling to match form design */
        .select2-container--default .select2-selection--multiple {
            border: 1px solid #d1d5db !important;
            border-radius: 0.5rem !important;
            padding: 0.5rem !important;
            min-height: 3rem !important;
        }
        .select2-container--default.select2-container--focus .select2-selection--multiple {
            border-color: #3b82f6 !important;
            box-shadow: 0 0 0 3px rgba(59, 130, 246, 0.1) !important;
        }
        .select2-container--default .select2-selection--multiple .select2-selection__choice {
            background-color: #3b82f6 !important;
            border: none !important;
            color: white !important;
            border-radius: 0.375rem !important;
            padding: 0.25rem 0.5rem !important;
            margin: 0.125rem !important;
        }
        .select2-container--default .select2-selection--multiple .select2-selection__choice__remove {
            color: white !important;
            margin-right: 0.25rem !important;
        }
        .select2-container--default .select2-selection--multiple .select2-selection__choice__remove:hover {
            color: #dbeafe !important;
        }
        .select2-dropdown {
            border-color: #d1d5db !important;
            border-radius: 0.5rem !important;
        }
        .select2-container--default .select2-results__option--highlighted[aria-selected] {
            background-color: #3b82f6 !important;
        }
    </style>

    <script>
        document.addEventListener('DOMContentLoaded', function() {
            // Auto-uppercase the promo code input
            document.getElementById('code').addEventListener('input', function() {
                this.value = this.value.toUpperCase();
            });

            // Check if jQuery and Select2 are loaded
            if (typeof jQuery !== 'undefined' && typeof jQuery.fn.select2 !== 'undefined') {
                // Initialize Select2 for products
                jQuery('.select2-products').select2({
                    placeholder: 'Search and select products',
                    allowClear: true,
                    width: '100%',
                    theme: 'default'
                });

                // Initialize Select2 for categories
                jQuery('.select2-categories').select2({
                    placeholder: 'Search and select categories',
                    allowClear: true,
                    width: '100%',
                    theme: 'default'
                });

                // Initialize Select2 for users
                jQuery('.select2-users').select2({
                    placeholder: 'Search and select users',
                    allowClear: true,
                    width: '100%',
                    theme: 'default'
                });
            } else {
                console.error('jQuery or Select2 is not loaded');
            }
        });
    </script>

</x-admin.admin-layout>
