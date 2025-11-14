<x-admin.admin-layout>
    <form action="{{ route('confidential.update', [app()->getlocale()]) }}" method="POST" enctype="multipart/form-data">
        @csrf
        <div class="bg-white rounded-xl shadow-lg overflow-hidden border border-gray-200">
            <div class="bg-gradient-to-r from-blue-500 to-blue-600 px-6 py-4">
                <h2 class="text-xl font-bold text-white flex items-center gap-2">
                    {{ __('admin.confidential') }} (Translations)
                </h2>
            </div>
            <div class="p-6">
                <div class="flex flex-wrap gap-4 mb-6 border-b pb-4 items-center">
                    <div class="flex gap-3">
                        @foreach (config('app.locales') as $locale)
                            <button type="button"
                                    class="language-tab flex items-center gap-2 px-4 py-2 rounded-lg font-semibold transition-all duration-300 {{ $locale === app()->getLocale() ? 'bg-gradient-to-r from-blue-500 to-blue-600 text-white shadow-md' : 'bg-gray-100 text-gray-700 hover:bg-gray-200' }}"
                                    data-locale="{{ $locale }}">
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
                @foreach (config('app.locales') as $locale)
                    <div id="locale-{{ $locale }}" class="locale-content {{ $locale !== app()->getLocale() ? 'hidden' : '' }} space-y-4">
                        <div>
                            <label for="description_{{ $locale }}" class="block text-sm font-semibold text-gray-700 mb-2">
                                <span class="text-red-500">*</span> Description ({{ __('admin.locale_' . $locale) }})
                            </label>
                            <textarea id="description_{{ $locale }}"
                                      name="text_[{{ $locale }}]"
                                      rows="6"
                                      placeholder="Enter confidential description..."
                                      class="w-full px-4 py-3 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-transparent transition-all @error('text_' . $locale) border-red-500 @enderror">{{ old('text_' . $locale, $confidential->{'text_' . $locale} ?? '') }}</textarea>
                            @error('text_' . $locale)
                                <p class="text-red-500 text-sm mt-1 flex items-center gap-1">
                                    {{ $message }}
                                </p>
                            @enderror
                        </div>
                    </div>
                @endforeach
                <div class="mt-8 flex justify-end">
                    <button type="submit" class="px-6 py-2 bg-blue-600 text-white rounded-lg font-semibold shadow hover:bg-blue-700 transition">
                        {{ __('admin.Update') }}
                    </button>
                </div>
            </div>
        </div>
    </form>
    <script src="https://code.jquery.com/jquery-3.6.3.min.js"></script>
    <script>
        $(document).ready(function() {
            $('.language-tab').on('click', function(e) {
                e.preventDefault();
                const locale = $(this).data('locale');
                $('.language-tab').removeClass('bg-gradient-to-r from-blue-500 to-blue-600 text-white shadow-md').addClass('bg-gray-100 text-gray-700 hover:bg-gray-200');
                $(this).addClass('bg-gradient-to-r from-blue-500 to-blue-600 text-white shadow-md').removeClass('bg-gray-100 text-gray-700 hover:bg-gray-200');
                $('.locale-content').addClass('hidden');
                $('#locale-' + locale).removeClass('hidden');
            });

            // Auto Translate button handler
            $('#auto-translate-btn').on('click', function(e) {
                e.preventDefault();

                const locales = {!! json_encode(config('app.locales')) !!};
                let sourceLocale = null;
                let targetLocale = null;
                let sourceData = {};

                // Find source locale
                for (const locale of locales) {
                    const textarea = document.querySelector(`#description_${locale}`);
                    const description = textarea ? textarea.value : '';

                    if (description && description.trim() !== '') {
                        sourceLocale = locale;
                        sourceData['text_'] = description;
                        break;
                    }
                }

                // Find target locale
                for (const locale of locales) {
                    if (locale !== sourceLocale) {
                        const textarea = document.querySelector(`#description_${locale}`);
                        const description = textarea ? textarea.value : '';

                        if (!description || description.trim() === '') {
                            targetLocale = locale;
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

                // Show loading
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

                // Translate
                fetch('/{{ app()->getLocale() }}/admin/confidential/translate', {
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
                    $('#translate-loading').remove();

                    if (data.success && data.translated && data.translated['text_']) {
                        const textarea = document.querySelector(`#description_${targetLocale}`);
                        if (textarea) {
                            textarea.value = data.translated['text_'];
                        }

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
                        setTimeout(() => $('#translate-success').fadeOut(300, function() { $(this).remove(); }), 3000);
                    } else {
                        throw new Error(data.message || 'Translation failed');
                    }
                })
                .catch(error => {
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
                    setTimeout(() => $('#translate-error').fadeOut(300, function() { $(this).remove(); }), 5000);
                });
            });
        });
    </script>
</x-admin.admin-layout>
