<x-admin.admin-layout>

	 <!-- Main Form -->
    <form action="{{ route('about.update', [app()->getlocale()]) }}" method="POST" enctype="multipart/form-data">
        @csrf
        <!-- Language Tabs Card -->
                <div class="bg-white rounded-xl shadow-lg overflow-hidden border border-gray-200">
                    <div class="bg-gradient-to-r from-blue-500 to-blue-600 px-6 py-4">
                        <h2 class="text-xl font-bold text-white flex items-center gap-2">
                            <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 5h12M9 3v2m1.048 9.5A18.022 18.022 0 016.412 9m6.088 9h7M11 21l5-10 5 10M12.751 5C11.783 10.77 8.07 15.61 3 18.129"></path>
                            </svg>
                            about Content (Translations)
                        </h2>
                    </div>

                    <div class="p-6">
                        <!-- Language Selector -->
                        <div class="flex flex-wrap gap-4 mb-6 border-b pb-4 items-center">
                            <div class="flex gap-3">
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
                            <button type="button"
                                    id="auto-translate-btn"
                                    class="ml-auto flex items-center gap-2 px-4 py-2 rounded-lg font-semibold transition-all duration-300 bg-gradient-to-r from-purple-500 to-purple-600 text-white shadow-md hover:from-purple-600 hover:to-purple-700">
                                <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 5h12M9 3v2m1.048 9.5A18.022 18.022 0 016.412 9m6.088 9h7M11 21l5-10 5 10M12.751 5C11.783 10.77 8.07 15.61 3 18.129"></path>
                                </svg>
                                <span>Auto Translate</span>
                            </button>
                        </div>

                        <!-- Language Content -->
                        @foreach (config('app.locales') as $locale)
                            <div id="locale-{{ $locale }}" class="locale-content {{ $locale !== app()->getLocale() ? 'hidden' : '' }} space-y-4">
                                <!-- Description Field -->
                                <div>
                                    <label for="description_{{ $locale }}" class="block text-sm font-semibold text-gray-700 mb-2">
                                        <span class="text-red-500">*</span> Description ({{ __('admin.locale_' . $locale) }})
                                    </label>
                                    <textarea id="description_{{ $locale }}"
                                              name="text_[{{ $locale }}]"
                                              rows="6"
                                              placeholder="Enter banner description..."
                                              class="w-full px-4 py-3 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-transparent transition-all @error('text_' . $locale) border-red-500 @enderror">{{ old($locale . '.text', $about->{'text_' . $locale} ?? '') }}</textarea>
                                    @error('text_' . $locale)
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
                        <!-- Update Button -->
                        <div class="mt-8 flex justify-end">
                            <button type="submit" class="px-6 py-2 bg-blue-600 text-white rounded-lg font-semibold shadow hover:bg-blue-700 transition">
                                {{ __('admin.update') }}
                            </button>
                        </div>
                    </div>
                </div>
    </form>
 <!-- CKEditor CDN -->
    <script src="https://cdn.ckeditor.com/ckeditor5/36.0.1/classic/ckeditor.js"></script>

    <script src="https://code.jquery.com/jquery-3.6.3.min.js"></script>
    <script>
        // Store CKEditor instances
        const editorInstances = {};

        $(document).ready(function() {
            console.log('Script loaded');

            // Initialize CKEditor for all locale textareas (only if ClassicEditor is available)
            if (typeof ClassicEditor !== 'undefined') {
                @foreach (config('app.locales') as $locale)
                    ClassicEditor
                        .create(document.querySelector('#description_{{ $locale }}'))
                        .then(editor => {
                            editorInstances['{{ $locale }}'] = editor;
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

            // Auto Translate button handler
            $('#auto-translate-btn').on('click', function(e) {
                e.preventDefault();
                console.log('Auto translate button clicked');

                const locales = {!! json_encode(config('app.locales')) !!};
                let sourceLocale = null;
                let targetLocale = null;
                let sourceData = {};

                // Find source and target locales
                for (const locale of locales) {
                    let description = '';
                    if (editorInstances[locale]) {
                        description = editorInstances[locale].getData();
                    } else {
                        const textarea = document.querySelector(`#description_${locale}`);
                        description = textarea ? textarea.value : '';
                    }

                    if (description && description.trim() !== '') {
                        sourceLocale = locale;
                        sourceData['text_'] = description;
                        console.log('Source locale:', sourceLocale);
                        break;
                    }
                }

                // Find target locale (the one with empty description)
                for (const locale of locales) {
                    if (locale !== sourceLocale) {
                        let description = '';
                        if (editorInstances[locale]) {
                            description = editorInstances[locale].getData();
                        } else {
                            const textarea = document.querySelector(`#description_${locale}`);
                            description = textarea ? textarea.value : '';
                        }

                        if (!description || description.trim() === '') {
                            targetLocale = locale;
                            console.log('Target locale:', targetLocale);
                            break;
                        }
                    }
                }

                if (!sourceLocale) {
                    alert('Please fill in at least one language first!');
                    return;
                }

                if (!targetLocale) {
                    alert('All languages are already filled!');
                    return;
                }

                console.log('Source data:', sourceData);

                // Show loading indicator
                const loadingHtml = `
                    <div id="translate-loading" class="fixed top-4 right-4 z-50 p-4 bg-blue-600 text-white rounded-lg shadow-2xl">
                        <div class="flex items-center gap-3">
                            <svg class="animate-spin h-6 w-6" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24">
                                <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
                                <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path>
                            </svg>
                            <div>
                                <div class="font-bold">Translating...</div>
                                <div class="text-sm opacity-90">From ${sourceLocale === 'ka' ? 'Georgian' : 'English'} to ${targetLocale === 'ka' ? 'Georgian' : 'English'}</div>
                            </div>
                        </div>
                    </div>
                `;
                $('body').append(loadingHtml);

                // Perform translation
                fetch('/{{ app()->getLocale() }}/admin/about/translate', {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/json',
                        'X-CSRF-TOKEN': '{{ csrf_token() }}'
                    },
                    body: JSON.stringify({
                        source_locale: sourceLocale,
                        target_locale: targetLocale,
                        data: sourceData
                    })
                })
                .then(response => response.json())
                .then(data => {
                    console.log('Translation response:', data);
                    $('#translate-loading').remove();

                    if (data.success && data.translated) {
                        console.log('Translation successful, applying fields...');

                        // Apply translated text
                        if (data.translated['text_']) {
                            if (editorInstances[targetLocale]) {
                                editorInstances[targetLocale].setData(data.translated['text_']);
                                console.log('Set description in CKEditor');
                            } else {
                                const textarea = document.querySelector(`#description_${targetLocale}`);
                                if (textarea) {
                                    textarea.value = data.translated['text_'];
                                    console.log('Set description in textarea');
                                }
                            }
                        }

                        // Show success message
                        const successMsg = `
                            <div id="translate-success" class="fixed top-4 right-4 z-50 p-4 bg-green-600 text-white rounded-lg shadow-2xl">
                                <div class="flex items-center gap-3">
                                    <svg class="h-6 w-6" fill="currentColor" viewBox="0 0 20 20">
                                        <path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zm3.707-9.293a1 1 0 00-1.414-1.414L9 10.586 7.707 9.293a1 1 0 00-1.414 1.414l2 2a1 1 0 001.414 0l4-4z" clip-rule="evenodd"></path>
                                    </svg>
                                    <div class="font-bold">Translation complete!</div>
                                </div>
                            </div>
                        `;
                        $('body').append(successMsg);

                        setTimeout(() => {
                            $('#translate-success').fadeOut(300, function() { $(this).remove(); });
                        }, 3000);
                    } else {
                        throw new Error(data.message || 'Translation failed');
                    }
                })
                .catch(error => {
                    console.error('Translation error:', error);
                    $('#translate-loading').remove();

                    const errorMsg = `
                        <div id="translate-error" class="fixed top-4 right-4 z-50 p-4 bg-red-600 text-white rounded-lg shadow-2xl">
                            <div class="flex items-center gap-3">
                                <svg class="h-6 w-6" fill="currentColor" viewBox="0 0 20 20">
                                    <path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zM8.707 7.293a1 1 0 00-1.414 1.414L8.586 10l-1.293 1.293a1 1 0 101.414 1.414L10 11.414l1.293 1.293a1 1 0 001.414-1.414L11.414 10l1.293-1.293a1 1 0 00-1.414-1.414L10 8.586 8.707 7.293z" clip-rule="evenodd"></path>
                                </svg>
                                <div>
                                    <div class="font-bold">Translation failed</div>
                                    <div class="text-sm">${error.message}</div>
                                </div>
                            </div>
                        </div>
                    `;
                    $('body').append(errorMsg);

                    setTimeout(() => {
                        $('#translate-error').fadeOut(300, function() { $(this).remove(); });
                    }, 5000);
                });
            });
        });
    </script>
</x-admin.admin-layout>
