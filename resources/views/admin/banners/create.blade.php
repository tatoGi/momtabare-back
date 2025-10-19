<x-admin.admin-layout>
    <!-- Professional Header -->
    <div class="bg-gradient-to-r from-purple-600 to-purple-700 rounded-xl shadow-2xl p-8 mb-8">
        <div class="flex items-center justify-between">
            <div class="flex items-center gap-4">
                <div class="bg-white/20 p-4 rounded-lg backdrop-blur-sm">
                    <svg class="w-10 h-10 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 16l4.586-4.586a2 2 0 012.828 0L16 16m-2-2l1.586-1.586a2 2 0 012.828 0L20 14m-6-6h.01M6 20h12a2 2 0 002-2V6a2 2 0 00-2-2H6a2 2 0 00-2 2v12a2 2 0 002 2z"></path>
                    </svg>
                </div>
                <div>
                    <h1 class="text-4xl font-bold text-white mb-2">Create New Banner</h1>
                    <p class="text-purple-100 text-lg">Add a new banner with images and details</p>
                </div>
            </div>
            <a href="{{ route('banners.index', app()->getlocale()) }}" 
               class="flex items-center gap-2 px-6 py-3 bg-white/20 hover:bg-white/30 text-white font-semibold rounded-lg backdrop-blur-sm transition-all duration-300">
                <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 19l-7-7m0 0l7-7m-7 7h18"></path>
                </svg>
                Back to Banners
            </a>
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
    <form action="{{ route('banners.store', ['page_id' => $page_id, app()->getlocale()]) }}" method="POST" enctype="multipart/form-data">
        @csrf
        <input type="hidden" name="author_id" value="{{ auth()->user()->id }}">

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
                                               value="{{ old($locale . '.title') }}"
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
                                               value="{{ old($locale . '.slug') }}"
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
                                              class="w-full px-4 py-3 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-transparent transition-all @error('description_' . $locale) border-red-500 @enderror">{{ old($locale . '.desc') }}</textarea>
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
                    <div class="p-6">
                        <label for="images" class="block text-sm font-semibold text-gray-700 mb-2">
                            Upload Images
                        </label>
                        <div class="relative">
                            <input type="file" 
                                   name="images[]" 
                                   id="images" 
                                   multiple 
                                   accept="image/*"
                                   class="w-full px-4 py-3 border-2 border-dashed border-gray-300 rounded-lg focus:ring-2 focus:ring-pink-500 focus:border-transparent transition-all">
                        </div>
                        <p class="text-sm text-gray-500 mt-2 flex items-center gap-1">
                            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 16h-1v-4h-1m1-4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"></path>
                            </svg>
                            You can select multiple images for the banner
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
                                    <option value="{{ $type['id'] }}">{{ __('bannerTypes.'.$key) }}</option>
                                @endforeach
                            </select>
                            <svg class="absolute right-3 top-3.5 w-5 h-5 text-gray-400 pointer-events-none" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7"></path>
                            </svg>
                        </div>
                        <p class="text-sm text-gray-500 mt-2">Select the banner type</p>
                    </div>
                </div>

                <!-- Status Card -->
                <div class="bg-white rounded-xl shadow-lg overflow-hidden border border-gray-200">
                    <div class="bg-gradient-to-r from-green-500 to-green-600 px-6 py-4">
                        <h2 class="text-xl font-bold text-white flex items-center gap-2">
                            <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"></path>
                            </svg>
                            Status
                        </h2>
                    </div>
                    <div class="p-6">
                        <div class="flex items-center justify-between p-3 bg-gray-50 rounded-lg">
                            <div>
                                <span class="text-sm font-semibold text-gray-700">Active Status</span>
                                <p class="text-xs text-gray-500">Make this banner visible</p>
                            </div>
                            <label class="relative inline-flex cursor-pointer items-center">
                                <input type="checkbox"
                                       id="switch"
                                       class="sr-only peer"
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
                    class="flex items-center justify-center gap-2 px-8 py-3 bg-gradient-to-r from-purple-600 to-purple-700 hover:from-purple-700 hover:to-purple-800 text-white font-bold rounded-lg shadow-lg transition-all duration-300 transform hover:scale-105">
                <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"></path>
                </svg>
                Create Banner
            </button>

            <a href="{{ route('banners.index', app()->getlocale()) }}"
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
               
              
                @csrf

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

                                        <!-- You can use small icons here for each language -->

                                     

                                        <span class="language-name">{{ __('admin.locale_' . $locale) }}</span>

                                    </a>

                                </li>

                            @endforeach

                        </ul>

                        

                    </div>

    

                </div>

                <div id="tab-contents">

                    @foreach (config('app.locales') as $locale)

                        <div id="locale-{{ $locale }}"

                            class=" @if ($locale !== app()->getLocale()) hidden @endif p-4">

                            <div class="flex flex-col w-full items-center justify-center mb-2">

                                <label for="title_{{ $locale }}" class="text-sm font-medium"><span

                                        class="text-red-500">*</span>Title ( {{ __('admin.locale_' . $locale) }})</label>

                                <input type="text" name="{{ $locale }}[title]"

                                    id="title_{{ $locale }}"

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

                                <input type="text" name="{{ $locale }}[slug]"

                                    id="slug_{{ $locale }}"

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

                                <textarea id="description_{{ $locale }}" name="{{ $locale }}[desc]" rows="4"

                                    class="border w-full text-sm text-gray-900 rounded-lg border-gray-300 focus:ring-blue-500

                                     focus:border-blue-500 dark:border-gray-600 dark:placeholder-gray-400 dark:text-black

                                      dark:focus:ring-blue-500 dark:focus:border-blue-500 p-2.5 

                                     @error('description_' . $locale) border-red @enderror"

                                    placeholder="Description"></textarea>

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

                            <select class="border w-full text-sm rounded-lg block  p-2.5 @error('type') danger @enderror " name="type_id" id="typeselect">

                                @foreach ($bannerTypes as $key => $type)

                                <option value="{{ $type['id'] }}" id="typeoption">{{ __('bannerTypes.'.$key) }}</option>

                                @endforeach

                            </select>

                </div>

                

                <!-- sort -->

    

                <!-- active -->

                <div class="flex flex-col mb-2">

                    <label class="text-xl mr-2 mb-2 text-cyan-400 font-bold"> Active </label>

                   

                    <label class="relative inline-flex cursor-pointer items-center">

                        <input id="switch" type="checkbox" class="peer sr-only" value="1" checked />

                        <label for="switch" class="hidden"></label>

                        <div class="peer h-6 w-12 rounded-full border bg-slate-200 

                        after:absolute after:left-[2px] after:top-0.5 after:h-5 after:w-5 

                        after:rounded-full after:border after:border-gray-300 after:bg-cyan-500

                        after:transition-all after:content-['']

                         peer-checked:bg-slate-800 peer-checked:after:translate-x-full

                          peer-checked:after:border-white peer-focus:ring-green-300">

                        </div>

                      </label>

                </div>

    

                <!-- Banner Images -->
                <div class="mb-4">
                    <label for="images" class="block font-medium text-gray-700">Banner Images</label>
                    <input type="file" name="images[]" id="images" multiple accept="image/*" class="border-gray-300 py-2 focus:ring-indigo-500 focus:border-indigo-500 block w-full sm:text-sm border rounded-md shadow-sm p-2">
                    <p class="text-sm text-gray-500 mt-1">You can select multiple images for the banner</p>
                    @error('images.*')
                        <span class="text-red-500 text-sm">{{ $message }}</span>
                    @enderror
                </div>

                <div class="flex justify-between">

                    <div class="mb-4">

                        <button type="submit" class="bg-indigo-500 text-white py-2 px-4 rounded hover:bg-indigo-600">Create Banner</button>

                    </div>

                    <div>

                        <a href="/{{app()->getlocale()}}/admin/banners" class="bg-indigo-500 text-white py-2 px-4 rounded hover:bg-indigo-600">Back</a>

                    </div>

                </div>

               <input type="hidden" name="author_id" value="{{ auth()->user()->id }}">

            </form>

        </div>

    </div>

    <script src="https://code.jquery.com/jquery-3.6.3.min.js"></script>

        <script>

            $(document).ready(function() {

                @foreach (config('app.locales') as $locale)

                    ClassicEditor

                        .create( document.querySelector( '#description_{{ $locale }}' ) )

                        .then( editor => {

                            console.log( editor );

                        } )

                        .catch( error => {

                            console.error( error );

                        } );

                @endforeach

            });

        </script>

</x-admin.admin-layout> 
