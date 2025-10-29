<x-admin.admin-layout>
    <form action="{{ route('privacy.update', [app()->getlocale()]) }}" method="POST" enctype="multipart/form-data">
        @csrf
        <div class="bg-white rounded-xl shadow-lg overflow-hidden border border-gray-200">
            <div class="bg-gradient-to-r from-blue-500 to-blue-600 px-6 py-4">
                <h2 class="text-xl font-bold text-white flex items-center gap-2">
                    {{ __('admin.privacy') }} (Translations)
                </h2>
            </div>
            <div class="p-6">
                <div class="flex gap-3 mb-6 border-b pb-4">
                    @foreach (config('app.locales') as $locale)
                        <button type="button"
                                class="language-tab flex items-center gap-2 px-4 py-2 rounded-lg font-semibold transition-all duration-300 {{ $locale === app()->getLocale() ? 'bg-gradient-to-r from-blue-500 to-blue-600 text-white shadow-md' : 'bg-gray-100 text-gray-700 hover:bg-gray-200' }}"
                                data-locale="{{ $locale }}">
                            <span>{{ __('admin.locale_' . $locale) }}</span>
                        </button>
                    @endforeach
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
                                      placeholder="Enter privacy description..."
                                      class="w-full px-4 py-3 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-transparent transition-all @error('text_' . $locale) border-red-500 @enderror">{{ old('text_' . $locale, $privacy->{'text_' . $locale} ?? '') }}</textarea>
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
        });
    </script>
</x-admin.admin-layout>
