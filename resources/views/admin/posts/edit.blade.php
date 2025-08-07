<x-admin.admin-layout>
    <div class="container mx-auto px-4 py-6">
        <div class="flex justify-between items-center mb-6">
            <div>
                <h1 class="text-2xl font-bold text-gray-800">
                    {{ __('admin.Edit') }} {{ $pageTypeConfig['name'] ?? 'Post' }}
                </h1>
                <p class="text-gray-600 mt-1">{{ $page->title }}</p>
            </div>
            <a href="{{ route('admin.pages.posts.index', ['locale' => app()->getLocale(), 'page' => $page->id]) }}"
               class="bg-gray-500 hover:bg-gray-600 text-white px-4 py-2 rounded-lg flex items-center transition-colors">
                <i class="fas fa-arrow-left mr-2"></i>
                {{ __('admin.Back') }}
            </a>
        </div>

        @if($errors->any())
            <div class="bg-red-100 border border-red-400 text-red-700 px-4 py-3 rounded mb-4">
                <ul class="list-disc list-inside">
                    @foreach($errors->all() as $error)
                        <li>{{ $error }}</li>
                    @endforeach
                </ul>
            </div>
        @endif

        <form action="{{ route('admin.pages.posts.update', ['locale' => app()->getLocale(), 'page' => $page->id, 'post' => $post->id]) }}" 
              method="POST" enctype="multipart/form-data" class="space-y-6">
            @csrf
            @method('PUT')

            <div class="bg-white rounded-lg shadow-sm p-6">
                <h3 class="text-lg font-medium text-gray-900 mb-4">{{ __('admin.Basic Information') }}</h3>
                
                <div class="grid grid-cols-1 md:grid-cols-3 gap-4">
                    <div>
                        <label for="active" class="block text-sm font-medium text-gray-700 mb-2">
                            {{ __('admin.Status') }}
                        </label>
                        <select name="active" id="active" class="w-full border border-gray-300 rounded-lg px-3 py-2 focus:ring-2 focus:ring-green-500 focus:border-green-500">
                            <option value="1" {{ old('active', $post->active) == '1' ? 'selected' : '' }}>{{ __('admin.Active') }}</option>
                            <option value="0" {{ old('active', $post->active) == '0' ? 'selected' : '' }}>{{ __('admin.Inactive') }}</option>
                        </select>
                    </div>
                    
                    <div>
                        <label for="sort_order" class="block text-sm font-medium text-gray-700 mb-2">
                            {{ __('admin.Sort Order') }}
                        </label>
                        <input type="number" name="sort_order" id="sort_order" value="{{ old('sort_order', $post->sort_order) }}"
                               class="w-full border border-gray-300 rounded-lg px-3 py-2 focus:ring-2 focus:ring-green-500 focus:border-green-500">
                    </div>
                    
                    <div>
                        <label for="published_at" class="block text-sm font-medium text-gray-700 mb-2">
                            {{ __('admin.Publish Date') }}
                        </label>
                        <input type="datetime-local" name="published_at" id="published_at" 
                               value="{{ old('published_at', $post->published_at ? $post->published_at->format('Y-m-d\TH:i') : now()->format('Y-m-d\TH:i')) }}"
                               class="w-full border border-gray-300 rounded-lg px-3 py-2 focus:ring-2 focus:ring-green-500 focus:border-green-500">
                    </div>
                </div>
            </div>

            {{-- Translatable Attributes --}}
            @if(!empty($translatableAttributes))
            <div class="bg-white rounded-lg shadow-sm p-6">
                <h3 class="text-lg font-medium text-gray-900 mb-4">{{ __('admin.Content') }}</h3>
                
                <!-- Language Tabs -->
                <div class="border-b border-gray-200 mb-6">
                    <nav class="-mb-px flex space-x-8">
                        @foreach(config('app.locales') as $locale)
                            <button type="button" 
                                    class="language-tab py-2 px-1 border-b-2 font-medium text-sm {{ $loop->first ? 'border-green-500 text-green-600' : 'border-transparent text-gray-500 hover:text-gray-700 hover:border-gray-300' }}"
                                    data-locale="{{ $locale }}">
                                {{ __('admin.locale_' . $locale) }}
                                <img src="{{ $locale === 'en' ? asset('storage/flags/united-states.png') : asset('storage/flags/georgia.png') }}" 
                                     alt="{{ $locale }}" class="inline w-4 h-4 ml-1">
                            </button>
                        @endforeach
                    </nav>
                </div>

                @foreach(config('app.locales') as $locale)
                    <div class="language-content {{ !$loop->first ? 'hidden' : '' }}" data-locale="{{ $locale }}">
                        <div class="grid grid-cols-1 gap-4">
                            @foreach($translatableAttributes as $key => $config)
                                <div>
                                    <label for="{{ $locale }}_{{ $key }}" class="block text-sm font-medium text-gray-700 mb-2">
                                        {{ $config['label'] ?? ucfirst($key) }}
                                        @if($config['required'] ?? false)
                                            <span class="text-red-500">*</span>
                                        @endif
                                    </label>
                                    
                                    @if($config['type'] === 'text')
                                        <input type="text" 
                                               name="{{ $locale }}[{{ $key }}]" 
                                               id="{{ $locale }}_{{ $key }}"
                                               value="{{ old($locale . '.' . $key, $post->getAttributeForLocale($key, $locale)) }}"
                                               placeholder="{{ $config['placeholder'] ?? '' }}"
                                               class="w-full border border-gray-300 rounded-lg px-3 py-2 focus:ring-2 focus:ring-green-500 focus:border-green-500">
                                    
                                    @elseif($config['type'] === 'textarea')
                                        <textarea name="{{ $locale }}[{{ $key }}]" 
                                                  id="{{ $locale }}_{{ $key }}"
                                                  rows="3"
                                                  placeholder="{{ $config['placeholder'] ?? '' }}"
                                                  class="w-full border border-gray-300 rounded-lg px-3 py-2 focus:ring-2 focus:ring-green-500 focus:border-green-500">{{ old($locale . '.' . $key, $post->getAttributeForLocale($key, $locale)) }}</textarea>
                                    
                                    @elseif($config['type'] === 'editor')
                                        <textarea name="{{ $locale }}[{{ $key }}]" 
                                                  id="{{ $locale }}_{{ $key }}"
                                                  rows="6"
                                                  placeholder="{{ $config['placeholder'] ?? '' }}"
                                                  class="w-full border border-gray-300 rounded-lg px-3 py-2 focus:ring-2 focus:ring-green-500 focus:border-green-500 editor">{{ old($locale . '.' . $key, $post->getAttributeForLocale($key, $locale)) }}</textarea>
                                    @endif
                                </div>
                            @endforeach
                        </div>
                    </div>
                @endforeach
            </div>
            @endif

            {{-- Non-translatable Attributes --}}
            @if(!empty($nonTranslatableAttributes))
            <div class="bg-white rounded-lg shadow-sm p-6">
                <h3 class="text-lg font-medium text-gray-900 mb-4">{{ __('admin.Additional Information') }}</h3>
                
                <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                    @foreach($nonTranslatableAttributes as $key => $config)
                        <div class="{{ $config['type'] === 'editor' ? 'md:col-span-2' : '' }}">
                            <label for="{{ $key }}" class="block text-sm font-medium text-gray-700 mb-2">
                                {{ $config['label'] ?? ucfirst($key) }}
                                @if($config['required'] ?? false)
                                    <span class="text-red-500">*</span>
                                @endif
                            </label>
                            
                            @if($config['type'] === 'text')
                                <input type="text" 
                                       name="{{ $key }}" 
                                       id="{{ $key }}"
                                       value="{{ old($key, $post->$key) }}"
                                       placeholder="{{ $config['placeholder'] ?? '' }}"
                                       class="w-full border border-gray-300 rounded-lg px-3 py-2 focus:ring-2 focus:ring-green-500 focus:border-green-500">
                            
                            @elseif($config['type'] === 'number')
                                <input type="number" 
                                       name="{{ $key }}" 
                                       id="{{ $key }}"
                                       value="{{ old($key, $post->$key) }}"
                                       placeholder="{{ $config['placeholder'] ?? '' }}"
                                       class="w-full border border-gray-300 rounded-lg px-3 py-2 focus:ring-2 focus:ring-green-500 focus:border-green-500">
                            
                            @elseif($config['type'] === 'textarea')
                                <textarea name="{{ $key }}" 
                                          id="{{ $key }}"
                                          rows="3"
                                          placeholder="{{ $config['placeholder'] ?? '' }}"
                                          class="w-full border border-gray-300 rounded-lg px-3 py-2 focus:ring-2 focus:ring-green-500 focus:border-green-500">{{ old($key, $post->$key) }}</textarea>
                            
                            @elseif($config['type'] === 'select')
                                <select name="{{ $key }}" 
                                        id="{{ $key }}"
                                        class="w-full border border-gray-300 rounded-lg px-3 py-2 focus:ring-2 focus:ring-green-500 focus:border-green-500">
                                    @foreach($config['options'] ?? [] as $value => $label)
                                        <option value="{{ $value }}" {{ old($key, $post->$key) == $value ? 'selected' : '' }}>
                                            {{ $label }}
                                        </option>
                                    @endforeach
                                </select>
                            
                            @elseif($config['type'] === 'image')
                                <div class="space-y-2">
                                    @if($post->$key)
                                        <div class="mb-2">
                                            <img src="{{ asset('storage/' . $post->$key) }}" 
                                                 alt="Current {{ $key }}" 
                                                 class="w-32 h-32 object-cover rounded-lg border">
                                            <p class="text-sm text-gray-600 mt-1">Current image</p>
                                        </div>
                                    @endif
                                    <input type="file" 
                                           name="{{ $key }}" 
                                           id="{{ $key }}"
                                           accept="{{ $config['accept'] ?? 'image/*' }}"
                                           class="w-full border border-gray-300 rounded-lg px-3 py-2 focus:ring-2 focus:ring-green-500 focus:border-green-500">
                                    @if($post->$key)
                                        <p class="text-sm text-gray-600">Upload a new image to replace the current one</p>
                                    @endif
                                </div>
                            
                            @elseif($config['type'] === 'datetime-local')
                                <input type="datetime-local" 
                                       name="{{ $key }}" 
                                       id="{{ $key }}"
                                       value="{{ old($key, $post->$key ? (is_string($post->$key) ? $post->$key : $post->$key->format('Y-m-d\TH:i')) : '') }}"
                                       class="w-full border border-gray-300 rounded-lg px-3 py-2 focus:ring-2 focus:ring-green-500 focus:border-green-500">
                            @endif
                        </div>
                    @endforeach
                </div>
            </div>
            @endif

            <div class="flex justify-end space-x-3">
                <a href="{{ route('admin.pages.posts.index', ['locale' => app()->getLocale(), 'page' => $page->id]) }}"
                   class="bg-gray-500 hover:bg-gray-600 text-white px-6 py-2 rounded-lg transition-colors">
                    {{ __('admin.Cancel') }}
                </a>
                <button type="submit" class="bg-blue-500 hover:bg-blue-600 text-white px-6 py-2 rounded-lg transition-colors">
                    <i class="fas fa-save mr-2"></i>
                    {{ __('admin.Update Post') }}
                </button>
            </div>
        </form>
    </div>

    @push('scripts')
    <script>
        document.addEventListener('DOMContentLoaded', function() {
            // Language tab switching
            document.querySelectorAll('.language-tab').forEach(tab => {
                tab.addEventListener('click', function() {
                    const locale = this.dataset.locale;
                    
                    // Update tab styles
                    document.querySelectorAll('.language-tab').forEach(t => {
                        t.classList.remove('border-green-500', 'text-green-600');
                        t.classList.add('border-transparent', 'text-gray-500');
                    });
                    this.classList.add('border-green-500', 'text-green-600');
                    this.classList.remove('border-transparent', 'text-gray-500');
                    
                    // Show/hide content
                    document.querySelectorAll('.language-content').forEach(content => {
                        content.classList.add('hidden');
                    });
                    const targetContent = document.querySelector(`[data-locale="${locale}"].language-content`);
                    if (targetContent) {
                        targetContent.classList.remove('hidden');
                    }
                });
            });
        });
    </script>
    @endpush
</x-admin.admin-layout>
