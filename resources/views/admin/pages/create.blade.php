<x-admin.admin-layout>
    <!-- Header Section -->
    <div class="mb-6">
        <div class="flex items-center justify-between">
            <div>
                <h1 class="text-3xl font-bold text-gray-800 flex items-center gap-3">
                    <svg class="w-8 h-8 text-green-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"></path>
                    </svg>
                    Create New Page
                </h1>
                <p class="text-gray-600 mt-2">Add a new page with multilingual support</p>
            </div>
            <a href="{{ route('pages.index', app()->getlocale()) }}"
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

    <form action="{{ route('pages.store', app()->getlocale()) }}" method="POST" enctype="multipart/form-data">
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
                            Page Content (Translations)
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
                                               value="{{ old($locale . '.title') }}"
                                               class="w-full px-4 py-3 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-transparent transition-all @error('title_' . $locale) border-red-500 @enderror"
                                               placeholder="Enter page title">
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
                                               value="{{ old($locale . '.slug') }}"
                                               class="w-full px-4 py-3 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-transparent transition-all @error('slug_' . $locale) border-red-500 @enderror"
                                               placeholder="page-url-keyword">
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

                                <!-- Keywords Field -->
                                <div class="form-group">
                                    <label for="keywords_{{ $locale }}" class="block text-sm font-semibold text-gray-700 mb-2">
                                        Keywords ({{ __('admin.locale_' . $locale) }})
                                    </label>
                                    <input type="text"
                                           name="{{ $locale }}[keywords]"
                                           id="keywords_{{ $locale }}"
                                           value="{{ old($locale . '.keywords') }}"
                                           class="w-full px-4 py-3 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-transparent transition-all"
                                           placeholder="keyword1, keyword2, keyword3">
                                    <p class="text-sm text-gray-500 mt-1">Enter keywords separated by commas</p>
                                </div>

                                <!-- Description Field -->
                                <div class="form-group">
                                    <label for="description_{{ $locale }}" class="block text-sm font-semibold text-gray-700 mb-2">
                                        <span class="text-red-500">*</span> Description ({{ __('admin.locale_' . $locale) }})
                                    </label>
                                    <textarea id="description_{{ $locale }}"
                                              name="{{ $locale }}[desc]"
                                              class="w-full px-4 py-3 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-transparent transition-all ckeditor @error('description_' . $locale) border-red-500 @enderror"
                                              rows="6"
                                              placeholder="Enter page description">{{ old($locale . '.desc') }}</textarea>
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
            <div class="lg:col-span-1 space-y-6">

                <!-- Page Type Card -->
                <div class="bg-white rounded-xl shadow-lg overflow-hidden border border-gray-200">
                    <div class="bg-gradient-to-r from-purple-500 to-purple-600 px-6 py-4">
                        <h3 class="text-lg font-bold text-white flex items-center gap-2">
                            <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"></path>
                            </svg>
                            Page Type
                        </h3>
                    </div>
                    <div class="p-6">
                        <label for="type_id" class="block text-sm font-semibold text-gray-700 mb-2">
                            Select Page Type
                        </label>
                        <div class="relative">
                            <select id="type_id"
                                    name="type_id"
                                    class="w-full px-4 py-3 border border-gray-300 rounded-lg focus:ring-2 focus:ring-purple-500 focus:border-transparent appearance-none transition-all">
                                <option value="">Choose Type</option>
                                @foreach ($sectionTypes as $key => $type)
                                    <option value="{{ $type['id'] }}" {{ old('type_id') == $type['id'] ? 'selected' : '' }}>
                                        {{ trans('sectionTypes.' . $key) }}
                                    </option>
                                @endforeach
                            </select>
                            <svg class="absolute right-3 top-3.5 w-5 h-5 text-gray-400 pointer-events-none" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7"></path>
                            </svg>
                        </div>
                        <p class="text-sm text-gray-500 mt-2">Select the type of page to create</p>
                    </div>
                </div>

                <!-- Status Card -->
                <div class="bg-white rounded-xl shadow-lg overflow-hidden border border-gray-200">
                    <div class="bg-gradient-to-r from-green-500 to-green-600 px-6 py-4">
                        <h3 class="text-lg font-bold text-white flex items-center gap-2">
                            <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"></path>
                            </svg>
                            Status
                        </h3>
                    </div>
                    <div class="p-6">
                        <div class="flex items-center justify-between p-3 bg-gray-50 rounded-lg">
                            <div>
                                <span class="text-lg font-semibold text-gray-700">Active Status</span>
                                <p class="text-sm text-gray-500">Make this page visible</p>
                            </div>
                            <label class="relative inline-flex cursor-pointer items-center">
                                <input type="checkbox"
                                       class="sr-only peer"
                                       name="active"
                                       value="1"
                                       checked />
                                <div class="w-11 h-6 bg-gray-300 peer-focus:outline-none peer-focus:ring-4 peer-focus:ring-green-300 rounded-full peer peer-checked:after:translate-x-full peer-checked:after:border-white after:content-[''] after:absolute after:top-[2px] after:left-[2px] after:bg-white after:border-gray-300 after:border after:rounded-full after:h-5 after:w-5 after:transition-all peer-checked:bg-green-500"></div>
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
                Create Page
            </button>

            <a href="{{ route('pages.index', app()->getlocale()) }}"
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

            // Language Tab Switching
            $('.language-tab').on('click', function(e) {
                e.preventDefault();
                const locale = $(this).data('locale');

                console.log('Language tab clicked:', locale);

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
        });
    </script>
</x-admin.admin-layout>
