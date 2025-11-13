<x-admin.admin-layout>
    <!-- Header Section -->
    <div class="mb-6">
        <div class="flex items-center justify-between">
            <div>
                <h1 class="text-3xl font-bold text-gray-800 flex items-center gap-3">
                    <svg class="w-8 h-8 text-green-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"></path>
                    </svg>
                    Create New Category
                </h1>
                <p class="text-gray-600 mt-2">Add a new category with multilingual support</p>
            </div>
            <a href="{{ route('categories.index', app()->getlocale()) }}"
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

    <form action="{{ route('categories.store', app()->getlocale()) }}" method="POST" enctype="multipart/form-data" data-parsley-validate novalidate>
        @csrf

        <div class="grid grid-cols-1 lg:grid-cols-3 gap-6">
            <!-- Main Content Area (Left Side - 2/3) -->
            <div class="lg:col-span-2 space-y-6">

                <!-- Language Tabs Card -->
                <div class="bg-white rounded-xl shadow-lg overflow-hidden border border-gray-200">
                    <div class="bg-gradient-to-r from-blue-500 to-blue-600 px-6 py-4">
                        <h2 class="text-xl font-bold text-white flex items-center gap-2">
                            <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 5h12M9 3v2m1.048 9.5A18.022 18.022 0 016.412 9m6.088 9h7M11 21l5-10 5 10M12.751 5C11.783 10.77 8.07 15.61 3 18.129"></path>
                            </svg>
                            Translations
                        </h2>
                    </div>

                    <div class="p-6">
                        <!-- Language Selector -->
                        <div class="flex gap-3 mb-6 border-b pb-4">
                            @foreach (config('app.locales') as $locale)
                                <button type="button"
                                        class="language-tab flex items-center gap-2 px-4 py-2 rounded-lg font-semibold transition-all duration-300
                                               {{ $locale === app()->getLocale() ? 'bg-gradient-to-r from-blue-500 to-blue-600 text-white shadow-md' : 'bg-gray-100 text-gray-700 hover:bg-gray-200' }}"
                                        data-locale="{{ $locale }}">
                                    <img src="{{ $locale === 'en' ? asset('storage/flags/united-states.png') : asset('storage/flags/georgia.png') }}"
                                         alt="{{ $locale }}"
                                         class="w-5 h-5 rounded-full border-2 border-white shadow-sm">
                                    <span>{{ __('admin.locale_' . $locale) }}</span>
                                </button>
                            @endforeach
                        </div>

                        <!-- Language Content -->
                        @foreach (config('app.locales') as $locale)
                            <div id="locale-{{ $locale }}" class="locale-content {{ $locale !== app()->getLocale() ? 'hidden' : '' }} space-y-4">

                                <!-- Title Field -->
                                <div class="form-group">
                                    <label for="title_{{ $locale }}" class="block text-sm font-semibold text-gray-700 mb-2">
                                        <span class="text-red-500">*</span> Title ({{ __('admin.locale_' . $locale) }})
                                    </label>
                                    <div class="relative">
                                        <input type="text"
                                               name="{{ $locale }}[title]"
                                               id="title_{{ $locale }}"
                                               class="w-full px-4 py-3 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-transparent transition-all @error('title_' . $locale) border-red-500 @enderror"
                                               placeholder="Enter category title">
                                        <svg class="absolute right-3 top-3.5 w-5 h-5 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M7 7h.01M7 3h5c.512 0 1.024.195 1.414.586l7 7a2 2 0 010 2.828l-7 7a2 2 0 01-2.828 0l-7-7A1.994 1.994 0 013 12V7a4 4 0 014-4z"></path>
                                        </svg>
                                    </div>
                                    @error('title_' . $locale)
                                        <p class="text-red-500 text-sm mt-1 flex items-center gap-1">
                                            <svg class="w-4 h-4" fill="currentColor" viewBox="0 0 20 20">
                                                <path fill-rule="evenodd" d="M18 10a8 8 0 11-16 0 8 8 0 0116 0zm-7 4a1 1 0 11-2 0 1 1 0 012 0zm-1-9a1 1 0 00-1 1v4a1 1 0 102 0V6a1 1 0 00-1-1z" clip-rule="evenodd"></path>
                                            </svg>
                                            {{ $message }}
                                        </p>
                                    @enderror
                                </div>

                                <!-- Slug Field -->
                                <div class="form-group">
                                    <label for="slug_{{ $locale }}" class="block text-sm font-semibold text-gray-700 mb-2">
                                        <span class="text-red-500">*</span> URL Keyword ({{ __('admin.locale_' . $locale) }})
                                    </label>
                                    <div class="relative">
                                        <input type="text"
                                               name="{{ $locale }}[slug]"
                                               id="slug_{{ $locale }}"
                                               class="w-full px-4 py-3 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-transparent transition-all @error('slug_' . $locale) border-red-500 @enderror"
                                               placeholder="category-url-keyword">
                                        <svg class="absolute right-3 top-3.5 w-5 h-5 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13.828 10.172a4 4 0 00-5.656 0l-4 4a4 4 0 105.656 5.656l1.102-1.101m-.758-4.899a4 4 0 005.656 0l4-4a4 4 0 00-5.656-5.656l-1.1 1.1"></path>
                                        </svg>
                                    </div>
                                    @error('slug_' . $locale)
                                        <p class="text-red-500 text-sm mt-1 flex items-center gap-1">
                                            <svg class="w-4 h-4" fill="currentColor" viewBox="0 0 20 20">
                                                <path fill-rule="evenodd" d="M18 10a8 8 0 11-16 0 8 8 0 0116 0zm-7 4a1 1 0 11-2 0 1 1 0 012 0zm-1-9a1 1 0 00-1 1v4a1 1 0 102 0V6a1 1 0 00-1-1z" clip-rule="evenodd"></path>
                                            </svg>
                                            {{ $message }}
                                        </p>
                                    @enderror
                                </div>

                                <!-- Description Field -->
                                <div class="form-group">
                                    <label for="description_{{ $locale }}" class="block text-sm font-semibold text-gray-700 mb-2">
                                        <span class="text-red-500">*</span> Description ({{ __('admin.locale_' . $locale) }})
                                    </label>
                                    <textarea id="description_{{ $locale }}"
                                              name="{{ $locale }}[description]"
                                              class="w-full px-4 py-3 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-transparent transition-all ckeditor @error('description_' . $locale) border-red-500 @enderror"
                                              rows="6"
                                              placeholder="Enter detailed description"></textarea>
                                    @error('description_' . $locale)
                                        <p class="text-red-500 text-sm mt-1 flex items-center gap-1">
                                            <svg class="w-4 h-4" fill="currentColor" viewBox="0 0 20 20">
                                                <path fill-rule="evenodd" d="M18 10a8 8 0 11-16 0 8 8 0 0116 0zm-7 4a1 1 0 11-2 0 1 1 0 012 0zm-1-9a1 1 0 00-1 1v4a1 1 0 102 0V6a1 1 0 00-1-1z" clip-rule="evenodd"></path>
                                            </svg>
                                            {{ $message }}
                                        </p>
                                    @enderror
                                </div>

                            </div>
                        @endforeach
                    </div>
                </div>

            </div>

            <!-- Sidebar (Right Side - 1/3) -->
            <div class="space-y-6">

                <!-- Parent Category Card -->
                <div class="bg-white rounded-xl shadow-lg overflow-hidden border border-gray-200">
                    <div class="bg-gradient-to-r from-purple-500 to-purple-600 px-6 py-4">
                        <h2 class="text-xl font-bold text-white flex items-center gap-2">
                            <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 7v10a2 2 0 002 2h14a2 2 0 002-2V9a2 2 0 00-2-2h-6l-2-2H5a2 2 0 00-2 2z"></path>
                            </svg>
                            Category Hierarchy
                        </h2>
                    </div>
                    <div class="p-6">
                        <label for="category" class="block text-sm font-semibold text-gray-700 mb-2">
                            Parent Category
                        </label>
                        <div class="relative">
                            <select id="category"
                                    name="parent_id"
                                    class="w-full px-4 py-3 border border-gray-300 rounded-lg focus:ring-2 focus:ring-purple-500 focus:border-transparent appearance-none transition-all">
                                <option value="">Root Category (No Parent)</option>
                                @foreach ($categories as $category)
                                    <option value="{{ $category->id }}">{{ $category->title ?? '' }}</option>
                                @endforeach
                            </select>
                            <svg class="absolute right-3 top-3.5 w-5 h-5 text-gray-400 pointer-events-none" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7"></path>
                            </svg>
                        </div>
                        <p class="text-sm text-gray-500 mt-2">Select a parent category to create a subcategory</p>
                    </div>
                </div>

                <!-- Icon Upload Card -->
                <div class="bg-white rounded-xl shadow-lg overflow-hidden border border-gray-200">
                    <div class="bg-gradient-to-r from-green-500 to-green-600 px-6 py-4">
                        <h2 class="text-xl font-bold text-white flex items-center gap-2">
                            <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 16l4.586-4.586a2 2 0 012.828 0L16 16m-2-2l1.586-1.586a2 2 0 012.828 0L20 14m-6-6h.01M6 20h12a2 2 0 002-2V6a2 2 0 00-2-2H6a2 2 0 00-2 2v12a2 2 0 002 2z"></path>
                            </svg>
                            Category Icon
                        </h2>
                    </div>
                    <div class="p-6">
                        <div class="flex flex-col items-center">
                            <!-- Upload Area -->
                            <label id="icon-upload-label"
                                   class="w-full flex flex-col items-center px-6 py-8 bg-gradient-to-br from-green-50 to-blue-50 border-2 border-dashed border-green-300 rounded-xl cursor-pointer hover:border-green-500 hover:bg-green-50 transition-all duration-300">
                                <svg class="w-12 h-12 text-green-500 mb-3" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M7 16a4 4 0 01-.88-7.903A5 5 0 1115.9 6L16 6a5 5 0 011 9.9M15 13l-3-3m0 0l-3 3m3-3v12"></path>
                                </svg>
                                <span id="icon-upload-text" class="text-sm font-semibold text-gray-700 mb-1">
                                    Upload Icon
                                </span>
                                <span class="text-xs text-gray-500">PNG, JPG, SVG up to 2MB</span>
                                <input id="icon-upload-input"
                                       type="file"
                                       name="icon"
                                       class="hidden"
                                       accept="image/*"
                                       onchange="previewFile(this, '#icon-preview', '#icon-upload-text', 'Change Icon')" />
                            </label>
                            <div id="icon-preview" class="mt-4 w-full"></div>
                        </div>
                    </div>
                </div>

                <!-- Image Upload Card -->
                <div class="bg-white rounded-xl shadow-lg overflow-hidden border border-gray-200">
                    <div class="bg-gradient-to-r from-teal-500 to-teal-600 px-6 py-4">
                        <h2 class="text-xl font-bold text-white flex items-center gap-2">
                            <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 5h12m-2-2l4 4-4 4M5 19h14a2 2 0 002-2V7a2 2 0 00-2-2h-5l-2-2H7a2 2 0 00-2 2v12a2 2 0 002 2z"></path>
                            </svg>
                            Category Image
                        </h2>
                    </div>
                    <div class="p-6">
                        <div class="flex flex-col items-center">
                            <label id="image-upload-label"
                                   class="w-full flex flex-col items-center px-6 py-8 bg-gradient-to-br from-teal-50 to-cyan-50 border-2 border-dashed border-teal-300 rounded-xl cursor-pointer hover:border-teal-500 hover:bg-teal-50 transition-all duration-300">
                                <svg class="w-12 h-12 text-teal-500 mb-3" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 7v10a2 2 0 002 2h14a2 2 0 002-2V9a2 2 0 00-2-2h-6l-2-2H5a2 2 0 00-2 2z"></path>
                                </svg>
                                <span id="image-upload-text" class="text-sm font-semibold text-gray-700 mb-1">
                                    Upload Image
                                </span>
                                <span class="text-xs text-gray-500">PNG, JPG up to 5MB</span>
                                <input id="image-upload-input"
                                       type="file"
                                       name="image"
                                       class="hidden"
                                       accept="image/*"
                                       onchange="previewFile(this, '#category-image-preview', '#image-upload-text')" />
                            </label>
                            <div id="category-image-preview" class="mt-4 w-full"></div>
                        </div>
                    </div>
                </div>

                <!-- Status Card -->
                <div class="bg-white rounded-xl shadow-lg overflow-hidden border border-gray-200">
                    <div class="bg-gradient-to-r from-orange-500 to-orange-600 px-6 py-4">
                        <h2 class="text-xl font-bold text-white flex items-center gap-2">
                            <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"></path>
                            </svg>
                            Status
                        </h2>
                    </div>
                    <div class="p-6">
                        <div class="flex items-center justify-between">
                            <div>
                                <span class="text-lg font-semibold text-gray-700">Active Status</span>
                                <p class="text-sm text-gray-500">Make this category visible</p>
                            </div>
                            <label for="active-switch" class="relative inline-block cursor-pointer" style="width: 56px; height: 28px;">
                                <input id="active-switch"
                                       type="checkbox"
                                       class="sr-only"
                                       name="active"
                                       value="1"
                                       checked />
                                <div class="toggle-bg" style="width: 56px; height: 28px; background: #d1d5db; border-radius: 9999px; box-shadow: inset 0 2px 4px rgba(0,0,0,0.1); transition: all 0.3s;"></div>
                                <div class="toggle-dot" style="position: absolute; left: 4px; top: 4px; width: 20px; height: 20px; background: white; border-radius: 9999px; box-shadow: 0 2px 4px rgba(0,0,0,0.2); transition: transform 0.3s;"></div>
                            </label>
                        </div>
                    </div>
                </div>

            </div>
        </div>

        <!-- Action Buttons - Centered Below Form -->
        <div class="mt-8 flex items-center justify-center gap-4">
            <button type="submit"
                    class="flex items-center justify-center gap-2 px-8 py-3 bg-gradient-to-r from-green-600 to-green-700 hover:from-green-700 hover:to-green-800 text-white font-bold rounded-lg shadow-lg transition-all duration-300 transform hover:scale-105">
                <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"></path>
                </svg>
                Create Category
            </button>

            <a href="{{ route('categories.index', app()->getlocale()) }}"
               class="flex items-center justify-center gap-2 px-8 py-3 bg-gray-200 hover:bg-gray-300 text-gray-700 font-bold rounded-lg shadow-md transition-all duration-300">
                <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"></path>
                </svg>
                Cancel
            </a>
        </div>
    </form>

    <!-- Scripts -->
    <!-- CKEditor CDN -->
    <script src="https://cdn.ckeditor.com/ckeditor5/36.0.1/classic/ckeditor.js"></script>

    <script src="https://code.jquery.com/jquery-3.6.3.min.js"></script>
    <script>
        $(document).ready(function() {
            console.log('Script loaded');

            // Initialize CKEditor for all locale textareas (only if ClassicEditor is available)
            if (typeof ClassicEditor !== 'undefined') {
                @foreach (config('app.locales') as $locale)
                    ClassicEditor
                        .create(document.querySelector('#description_{{ $locale }}'))
                        .then(editor => {
                            console.log('CKEditor initialized for {{ $locale }}');
                        })
                        .catch(error => {
                            console.error('CKEditor error for {{ $locale }}:', error);
                        });
                @endforeach
            } else {
                console.warn('ClassicEditor is not loaded - description fields will be plain textareas');
            }

            // Active Status Switcher
            function updateToggle() {
                const checkbox = $('#active-switch');
                const toggleBg = checkbox.siblings('.toggle-bg');
                const toggleDot = checkbox.siblings('.toggle-dot');

                console.log('Checkbox checked:', checkbox.is(':checked'));

                if (checkbox.is(':checked')) {
                    toggleBg.css('background', 'linear-gradient(to right, #f97316, #ea580c)');
                    toggleDot.css('transform', 'translateX(28px)');
                } else {
                    toggleBg.css('background', '#d1d5db');
                    toggleDot.css('transform', 'translateX(0)');
                }
            }

            // Initialize toggle state
            updateToggle();

            // Handle toggle click
            $('#active-switch').on('change', function() {
                console.log('Toggle changed');
                updateToggle();
            });

            // Language Tab Switching
            $('.language-tab').on('click', function(e) {
                e.preventDefault();
                const locale = $(this).data('locale');

                console.log('Language tab clicked:', locale);
                console.log('Locale content exists:', $('#locale-' + locale).length);

                // Update tab styles - use attr to set full class
                $('.language-tab').each(function() {
                    $(this).attr('class', 'language-tab flex items-center gap-2 px-4 py-2 rounded-lg font-semibold transition-all duration-300 bg-gray-100 text-gray-700 hover:bg-gray-200');
                });

                // Add gradient to clicked tab
                $(this).attr('class', 'language-tab flex items-center gap-2 px-4 py-2 rounded-lg font-semibold transition-all duration-300 bg-gradient-to-r from-blue-500 to-blue-600 text-white shadow-md');

                // Show/hide content
                $('.locale-content').each(function() {
                    $(this).addClass('hidden');
                });
                $('#locale-' + locale).removeClass('hidden');

                console.log('Content visibility changed');
            });

            // Generic file preview helper so we can reuse it for multiple upload widgets
            window.previewFile = function(input, previewSelector, textSelector, changeLabel = 'Change Image') {
                if (input.files && input.files[0]) {
                    const reader = new FileReader();
                    reader.onload = function(e) {
                        $(previewSelector).html(
                            '<div class="relative"><img src="' + e.target.result + '" class="w-full h-32 object-cover rounded-lg border-2 border-green-300 shadow-md mt-2"></div>'
                        );
                        if (textSelector) {
                            $(textSelector).text(changeLabel);
                        }
                    };
                    reader.readAsDataURL(input.files[0]);
                }
            };
        });
    </script>
</x-admin.admin-layout>
