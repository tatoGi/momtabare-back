<x-admin.admin-layout>
    <!-- Professional Header -->
    <div class="bg-gradient-to-r from-purple-600 to-purple-700 rounded-xl shadow-2xl p-8 mb-8">
        <div class="flex items-center justify-between">
            <div class="flex items-center gap-4">
                <div class="bg-white/20 p-4 rounded-lg backdrop-blur-sm">
                    <svg class="w-10 h-10 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z"></path>
                    </svg>
                </div>
                <div>
                    <h1 class="text-4xl font-bold text-white mb-2">Edit Banner</h1>
                    <p class="text-purple-100 text-lg">Update banner details and images</p>
                </div>
            </div>
            @if(request('page_id'))
                <a href="{{ route('admin.pages.management.manage', ['locale' => app()->getLocale(), 'page' => request('page_id')]) }}"
                   class="flex items-center gap-2 px-6 py-3 bg-white/20 hover:bg-white/30 text-white font-semibold rounded-lg backdrop-blur-sm transition-all duration-300">
                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 19l-7-7m0 0l7-7m-7 7h18"></path>
                    </svg>
                    Back to Page
                </a>
            @else
                <a href="{{ route('banners.index', app()->getlocale()) }}"
                   class="flex items-center gap-2 px-6 py-3 bg-white/20 hover:bg-white/30 text-white font-semibold rounded-lg backdrop-blur-sm transition-all duration-300">
                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 19l-7-7m0 0l7-7m-7 7h18"></path>
                    </svg>
                    Back to Banners
                </a>
            @endif
        </div>
    </div>

    <!-- Error Display -->
    @if ($errors->any())
        <div class="bg-white rounded-xl shadow-lg border-l-4 border-red-500 p-6 mb-8">
            <div class="flex items-start gap-4">
                <div class="flex-shrink-0">
                    <svg class="w-6 h-6 text-red-500" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4m0 4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"></path>
                    </svg>
                </div>
                <div class="flex-1">
                    <h3 class="text-lg font-bold text-red-600 mb-2">Please fix the following errors:</h3>
                    <ul class="list-disc list-inside space-y-1">
                        @foreach ($errors->all() as $error)
                            <li class="text-red-600">{{ $error }}</li>
                        @endforeach
                    </ul>
                </div>
            </div>
        </div>
    @endif

    <!-- Main Form -->
    <form action="{{ route('banners.update', [app()->getlocale(), $banner->id]) }}" method="POST" enctype="multipart/form-data">
        @csrf
        @method('PUT')
        <input type="hidden" name="author_id" value="{{ auth()->user()->id }}">
        @if(request('page_id'))
            <input type="hidden" name="page_id" value="{{ request('page_id') }}">
        @endif

        <!-- 3-Column Grid Layout -->
        <div class="grid grid-cols-1 lg:grid-cols-3 gap-6">

            <!-- Main Content (Left Side - 2/3) -->
            <div class="lg:col-span-2 space-y-6">

                <!-- Language Tabs Card -->
                <div class="bg-white rounded-xl shadow-lg overflow-hidden border border-gray-200">
                    <div class="bg-gradient-to-r from-blue-500 to-blue-600 px-6 py-4">
                        <h2 class="text-xl font-bold text-white flex items-center gap-2">
                            <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 5h12M9 3v2m1.048 9.5A18.022 18.022 0 016.412 9m6.088 9h7M11 21l5-10 5 10M12.751 5C11.783 10.77 8.07 15.61 3 18.129"></path>
                            </svg>
                            Banner Content (Translations)
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
                                <div>
                                    <label for="title_{{ $locale }}" class="block text-sm font-semibold text-gray-700 mb-2">
                                        <span class="text-red-500">*</span> Title ({{ __('admin.locale_' . $locale) }})
                                    </label>
                                    <div class="relative">
                                        <input type="text"
                                               name="{{ $locale }}[title]"
                                               id="title_{{ $locale }}"
                                               value="{{ old($locale . '.title', $banner->title) }}"
                                               class="w-full px-4 py-3 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-transparent transition-all @error('title_' . $locale) border-red-500 @enderror"
                                               placeholder="Enter banner title">
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
                                <div>
                                    <label for="slug_{{ $locale }}" class="block text-sm font-semibold text-gray-700 mb-2">
                                        <span class="text-red-500">*</span> URL Keyword ({{ __('admin.locale_' . $locale) }})
                                    </label>
                                    <div class="relative">
                                        <input type="text"
                                               name="{{ $locale }}[slug]"
                                               id="slug_{{ $locale }}"
                                               value="{{ old($locale . '.slug', $banner->slug) }}"
                                               class="w-full px-4 py-3 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-transparent transition-all @error('slug_' . $locale) border-red-500 @enderror"
                                               placeholder="enter-url-keyword">
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
                                <div>
                                    <label for="description_{{ $locale }}" class="block text-sm font-semibold text-gray-700 mb-2">
                                        <span class="text-red-500">*</span> Description ({{ __('admin.locale_' . $locale) }})
                                    </label>
                                    <textarea id="description_{{ $locale }}"
                                              name="{{ $locale }}[desc]"
                                              rows="6"
                                              placeholder="Enter banner description..."
                                              class="w-full px-4 py-3 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-transparent transition-all @error('description_' . $locale) border-red-500 @enderror">{{ old($locale . '.desc', $banner->desc) }}</textarea>
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

                <!-- Banner Images Card -->
                <div class="bg-white rounded-xl shadow-lg overflow-hidden border border-gray-200">
                    <div class="bg-gradient-to-r from-pink-500 to-pink-600 px-6 py-4">
                        <h2 class="text-xl font-bold text-white flex items-center gap-2">
                            <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 16l4.586-4.586a2 2 0 012.828 0L16 16m-2-2l1.586-1.586a2 2 0 012.828 0L20 14m-6-6h.01M6 20h12a2 2 0 002-2V6a2 2 0 00-2-2H6a2 2 0 00-2 2v12a2 2 0 002 2z"></path>
                            </svg>
                            Banner Images
                        </h2>
                    </div>
                    <div class="p-6 space-y-4">
                        <!-- Upload new images -->
                        <div>
                            <label for="images" class="block text-sm font-semibold text-gray-700 mb-2">
                                Add More Images
                            </label>
                            <input type="file"
                                   name="images[]"
                                   id="images"
                                   multiple
                                   accept="image/*"
                                   class="w-full px-4 py-3 border-2 border-dashed border-gray-300 rounded-lg focus:ring-2 focus:ring-pink-500 focus:border-transparent transition-all">
                            <p class="text-sm text-gray-500 mt-2 flex items-center gap-1">
                                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 16h-1v-4h-1m1-4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"></path>
                                </svg>
                                Select multiple images to add to the banner
                            </p>
                            @error('images.*')
                                <p class="text-red-500 text-sm mt-1 flex items-center gap-1">
                                    <svg class="w-4 h-4" fill="currentColor" viewBox="0 0 20 20">
                                        <path fill-rule="evenodd" d="M18 10a8 8 0 11-16 0 8 8 0 0116 0zm-7 4a1 1 0 11-2 0 1 1 0 012 0zm-1-9a1 1 0 00-1 1v4a1 1 0 102 0V6a1 1 0 00-1-1z" clip-rule="evenodd"></path>
                                    </svg>
                                    {{ $message }}
                                </p>
                            @enderror
                        </div>

                        <!-- Current Images -->
                        @if($banner->images->count() > 0)
                            <div class="border-t pt-4">
                                <h3 class="text-sm font-semibold text-gray-700 mb-3 flex items-center gap-2">
                                    <svg class="w-5 h-5 text-pink-500" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 16l4.586-4.586a2 2 0 012.828 0L16 16m-2-2l1.586-1.586a2 2 0 012.828 0L20 14m-6-6h.01M6 20h12a2 2 0 002-2V6a2 2 0 00-2-2H6a2 2 0 00-2 2v12a2 2 0 002 2z"></path>
                                    </svg>
                                    Current Banner Images ({{ $banner->images->count() }})
                                </h3>
                                <div class="grid grid-cols-2 md:grid-cols-3 lg:grid-cols-4 gap-4">
                                    @foreach ($banner->images as $image)
                                        <div class="relative group">
                                            <img src="{{ asset('storage/' . $image->image_name) }}"
                                                 alt="Banner Image"
                                                 class="w-full h-32 object-cover rounded-lg border-2 border-gray-200 transition-all group-hover:border-pink-400">
                                            <button type="button"
                                                    data-route="{{ route('banners.images.delete', [app()->getLocale(), $image->id]) }}"
                                                    data-id="{{ $image->id }}"
                                                    data-token="{{ csrf_token() }}"
                                                    class="delete-image absolute top-2 right-2 bg-red-500 hover:bg-red-600 text-white rounded-full w-8 h-8 flex items-center justify-center shadow-lg transition-all opacity-0 group-hover:opacity-100"
                                                    id="delete_image_{{ $image->id }}">
                                                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"></path>
                                                </svg>
                                            </button>
                                        </div>
                                    @endforeach
                                </div>
                            </div>
                        @else
                            <div class="text-center py-8 bg-gray-50 rounded-lg border-2 border-dashed border-gray-300">
                                <svg class="w-16 h-16 mx-auto text-gray-400 mb-3" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 16l4.586-4.586a2 2 0 012.828 0L16 16m-2-2l1.586-1.586a2 2 0 012.828 0L20 14m-6-6h.01M6 20h12a2 2 0 002-2V6a2 2 0 00-2-2H6a2 2 0 00-2 2v12a2 2 0 002 2z"></path>
                                </svg>
                                <p class="text-sm text-gray-500 italic">No banner images yet. Upload some above!</p>
                            </div>
                        @endif
                    </div>
                </div>

            </div>

            <!-- Sidebar (Right Side - 1/3) -->
            <div class="lg:col-span-1 space-y-6">

                <!-- Banner Type Card -->
                <div class="bg-white rounded-xl shadow-lg overflow-hidden border border-gray-200">
                    <div class="bg-gradient-to-r from-indigo-500 to-indigo-600 px-6 py-4">
                        <h2 class="text-xl font-bold text-white flex items-center gap-2">
                            <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M7 7h.01M7 3h5c.512 0 1.024.195 1.414.586l7 7a2 2 0 010 2.828l-7 7a2 2 0 01-2.828 0l-7-7A1.994 1.994 0 013 12V7a4 4 0 014-4z"></path>
                            </svg>
                            Banner Type
                        </h2>
                    </div>
                    <div class="p-6">
                        <label for="typeselect" class="block text-sm font-semibold text-gray-700 mb-2">
                            <span class="text-red-500">*</span> {{ __('admin.type') }}
                        </label>
                        <div class="relative">
                            <select id="typeselect"
                                    name="type_id"
                                    class="w-full px-4 py-3 border border-gray-300 rounded-lg focus:ring-2 focus:ring-indigo-500 focus:border-transparent appearance-none transition-all @error('type') border-red-500 @enderror">
                                @foreach ($bannerTypes as $key => $type)
                                    <option value="{{ $type['id'] }}" {{ $type['id'] == $banner->type_id ? 'selected' : '' }}>
                                        {{ __('bannerTypes.'.$key) }}
                                    </option>
                                @endforeach
                            </select>
                            <svg class="absolute right-3 top-3.5 w-5 h-5 text-gray-400 pointer-events-none" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7"></path>
                            </svg>
                        </div>
                        <p class="text-sm text-gray-500 mt-2">Current banner type</p>
                    </div>
                </div>

            </div>
        </div>

        <!-- Action Buttons - Centered Below Form -->
        <div class="mt-8 flex items-center justify-center gap-4">
            <button type="submit"
                    class="flex items-center justify-center gap-2 px-8 py-3 bg-gradient-to-r from-blue-600 to-blue-700 hover:from-blue-700 hover:to-blue-800 text-white font-bold rounded-lg shadow-lg transition-all duration-300 transform hover:scale-105">
                <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"></path>
                </svg>
                Update Banner
            </button>

            @if(request('page_id'))
                <a href="{{ route('admin.pages.management.manage', ['locale' => app()->getLocale(), 'page' => request('page_id')]) }}"
                   class="flex items-center justify-center gap-2 px-8 py-3 bg-gray-200 hover:bg-gray-300 text-gray-700 font-bold rounded-lg shadow-md transition-all duration-300">
                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"></path>
                    </svg>
                    Cancel
                </a>
            @else
                <a href="{{ route('banners.index', app()->getlocale()) }}"
                   class="flex items-center justify-center gap-2 px-8 py-3 bg-gray-200 hover:bg-gray-300 text-gray-700 font-bold rounded-lg shadow-md transition-all duration-300">
                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"></path>
                    </svg>
                    Cancel
                </a>
            @endif
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

                // Update tab styles
                $('.language-tab').each(function() {
                    $(this).attr('class', 'language-tab flex items-center gap-2 px-4 py-2 rounded-lg font-semibold transition-all duration-300 bg-gray-100 text-gray-700 hover:bg-gray-200');
                });

                $(this).attr('class', 'language-tab flex items-center gap-2 px-4 py-2 rounded-lg font-semibold transition-all duration-300 bg-gradient-to-r from-blue-500 to-blue-600 text-white shadow-md');

                // Show/hide content
                $('.locale-content').addClass('hidden');
                $('#locale-' + locale).removeClass('hidden');
            });

            // Delete image functionality
            $('.delete-image').on('click', function(e) {
                e.preventDefault();

                if (!confirm('Are you sure you want to delete this image?')) {
                    return;
                }

                const button = $(this);
                const route = button.data('route');
                const token = button.data('token');

                fetch(route, {
                    method: 'DELETE',
                    headers: {
                        'X-CSRF-TOKEN': token,
                        'Accept': 'application/json',
                        'Content-Type': 'application/json'
                    }
                })
                .then(response => response.json())
                .then(data => {
                    if (data.success) {
                        button.closest('.group').fadeOut(300, function() {
                            $(this).remove();
                        });
                    } else {
                        alert('Error deleting image: ' + (data.message || 'Unknown error'));
                    }
                })
                .catch(error => {
                    console.error('Error:', error);
                    alert('Error deleting image');
                });
            });
        });
    </script>
</x-admin.admin-layout>

                <!-- category_id -->

                <div class="rounded  w-full mx-auto mt-4">

                    <!-- Tabs -->



                    <div class="language-selector">

                        <ul id="tabs" class="language-selector-list">

                            @foreach (config('app.locales') as $locale)

                            @if($locale === 'en')

                            <li class="language-selector-item border-cyan-500">

                                @elseif($locale === 'ka')

                            <li class="language-selector-item border-red-600">

                                @endif

                                <a href="#locale-{{ $locale }}" class="language-selector-link">


                                    <span class="language-name">{{ __('admin.locale_' . $locale) }}</span>

                                </a>

                            </li>

                            @endforeach

                        </ul>



                    </div>



                </div>

                <div id="tab-contents">

                    @foreach (config('app.locales') as $locale)

                    <div id="locale-{{ $locale }}" class=" @if ($locale !== app()->getLocale()) hidden @endif p-4">

                        <div class="flex flex-col w-full items-center justify-center mb-2">

                            <label for="title_{{ $locale }}" class="text-sm font-medium"><span
                                    class="text-red-500">*</span>Title ( {{ __('admin.locale_' . $locale) }})</label>

                            <input type="text" name="{{ $locale }}[title]" id="title_{{ $locale }}"
                                value="{{ $banner->title }}"
                                class="border w-full text-sm rounded-lg block p-2.5 @error('title_' . $locale) border-red-500 @enderror"
                                placeholder="Title">

                            @error('title_' . $locale)

                            <span class="text-red-500 text-sm">{{ $message }}</span>

                            @enderror

                        </div>



                        <div class="flex w-fulls items-center justify-center flex-col mb-2">

                            <label for="slug_{{ $locale }}" class="text-sm font-medium"><span
                                    class="text-red-500">*</span>URL Keyword

                                ( {{ __('admin.locale_' . $locale) }})</label>

                            <input type="text" name="{{ $locale }}[slug]" id="slug_{{ $locale }}"
                                value="{{ $banner->slug }}"
                                class="border w-full text-sm rounded-lg block  p-2.5 @error('slug_' . $locale) border-red-500 @enderror"
                                placeholder="URL Keyword">

                            @error('slug_' . $locale)

                            <span class="text-red-500 text-sm">{{ $message }}</span>

                            @enderror

                        </div>



                        <div class="flex w-full items-center justify-center flex-col mb-2">

                            <label for="description_{{ $locale }}"
                                class="text-sm font-medium text-gray-900 dark:text-gray-400"><span
                                    class="text-red-500">*</span>Description

                                ( {{ __('admin.locale_' . $locale) }})</label>

                            <textarea id="description_{{ $locale }}" name="{{ $locale }}[desc]" rows="4" class="border w-full text-sm text-gray-900 rounded-lg border-gray-300 focus:ring-blue-500

                                     focus:border-blue-500 dark:border-gray-600 dark:placeholder-gray-400 dark:text-black

                                      dark:focus:ring-blue-500 dark:focus:border-blue-500 p-2.5

                                     @error('description_' . $locale) border-red @enderror"
                                placeholder="Description">{!! $banner->desc !!}</textarea>

                            @error('description_' . $locale)

                            <span class="text-red-500 text-sm">{{ $message }}</span>

                            @enderror

                        </div>

                    </div>

                    @endforeach

                </div>

                <div class="flex flex-col w-full items-center justify-center mb-2">

                    <label for="title_{{ $locale }}" class="text-sm font-medium"><span
                            class="text-red-500">*</span>{{ __('admin.type') }}</label>

                    <select class="border w-full text-sm rounded-lg block  p-2.5 @error('type') danger @enderror "
                        name="type_id" id="typeselect">

                        @foreach ($bannerTypes as $key => $type)

                        <option value="{{ $type['id'] }}" id="typeoption">{{ __('bannerTypes.'.$key) }}</option>

                        @endforeach

                    </select>

                </div>



                <!-- sort -->



                <!-- Banner Images -->
                <div class="mt-4 border border-dashed border-gray-400 rounded-lg p-4">
                    <label for="images" class="block font-medium text-gray-700 mb-2">Banner Images</label>
                    <input type="file" name="images[]" id="images" multiple accept="image/*" class="border-gray-300 py-2 focus:ring-indigo-500 focus:border-indigo-500 block w-full sm:text-sm border rounded-md shadow-sm p-2 mb-3">
                    <p class="text-sm text-gray-500 mb-3">Add more images to the banner</p>

                    @if($banner->images->count() > 0)
                        <div class="flex flex-wrap imagePreview">
                            <p class="w-full text-sm text-gray-600 mb-2">Current banner images:</p>
                            @foreach ($banner->images as $image)
                            <div class="w-1/2 md:w-1/4 lg:w-1/3 p-2">
                                <div class="trash relative">
                                    <img src="{{ asset('storage/' . $image->image_name) }}" alt="Banner Image" class="w-full h-24 object-cover rounded border">
                                    {{-- Delete Button --}}
                                    <button type="button" data-route="{{ route('banners.images.delete', [app()->getLocale(), $image->id]) }}" data-id="{{ $image->id }}" data-token="{{ csrf_token() }}" class="absolute top-1 right-1 bg-red-500 text-white rounded-full w-6 h-6 flex items-center justify-center text-xs hover:bg-red-600 delete-image" id="delete_image_{{ $image->id }}">
                                        <i class="fas fa-times"></i>
                                    </button>
                                </div>
                            </div>
                            @endforeach
                        </div>
                    @else
                        <p class="text-sm text-gray-500 italic">No banner images yet.</p>
                    @endif

                    @error('images.*')
                        <span class="text-red-500 text-sm">{{ $message }}</span>
                    @enderror
                </div>



                <div class="flex justify-between">

                    <div class="mb-4">

                        <button type="submit"
                            class="bg-indigo-500 text-white py-2 px-4 rounded hover:bg-indigo-600">update
                            Banner</button>

                    </div>

                    <div>

                        @if(request('page_id'))
                            <a href="{{ route('admin.pages.management.manage', ['locale' => app()->getLocale(), 'page' => request('page_id')]) }}"
                               class="bg-gray-500 text-white py-2 px-4 rounded hover:bg-gray-600">Back to Page</a>
                        @else
                            <a href="{{ route('banners.index', ['locale' => app()->getLocale()]) }}"
                               class="bg-indigo-500 text-white py-2 px-4 rounded hover:bg-indigo-600">Back</a>
                        @endif

                    </div>

                </div>

                <input type="hidden" name="author_id" value="{{ auth()->user()->id }}">
                @if(request('page_id'))
                    <input type="hidden" name="page_id" value="{{ request('page_id') }}">
                @endif

            </form>

        </div>

    </div>

    <script src="https://code.jquery.com/jquery-3.6.3.min.js"></script>

    <script>
        $(document).ready(function () {

            @foreach(config('app.locales') as $locale)

            ClassicEditor

                .create(document.querySelector('#description_{{ $locale }}'))

                .then(editor => {

                    console.log(editor);

                })

                .catch(error => {

                    console.error(error);

                });

            @endforeach

        });

    </script>

</x-admin.admin-layout>
