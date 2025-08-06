@if(count($availableLocales) > 1)
    @php
        // Get current URL without the locale prefix
        $currentPath = request()->path();
        $currentPath = preg_replace('/^[a-z]{2}(?:-[A-Z]{2})?\//', '', $currentPath);
        $currentPath = $currentPath === '' ? '/' : "/$currentPath";
    @endphp
    
    <div x-data="{ open: false }" class="relative inline-block text-left">
        <div>
            <button @click="open = !open" type="button" class="inline-flex justify-center w-full rounded-md border border-gray-300 shadow-sm px-4 py-2 bg-white text-sm font-medium text-gray-700 hover:bg-gray-50 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-indigo-500" id="language-menu" aria-expanded="true" aria-haspopup="true">
                {{ strtoupper(app()->getLocale()) }}
                <svg class="-mr-1 ml-2 h-5 w-5" xmlns="http://www.w3.org/2000/svg" viewBox="0 0 20 20" fill="currentColor" aria-hidden="true">
                    <path fill-rule="evenodd" d="M5.293 7.293a1 1 0 011.414 0L10 10.586l3.293-3.293a1 1 0 111.414 1.414l-4 4a1 1 0 01-1.414 0l-4-4a1 1 0 010-1.414z" clip-rule="evenodd" />
                </svg>
            </button>
        </div>

        <div x-show="open" @click.away="open = false" 
             x-transition:enter="transition ease-out duration-100" 
             x-transition:enter-start="transform opacity-0 scale-95" 
             x-transition:enter-end="transform opacity-100 scale-100" 
             x-transition:leave="transition ease-in duration-75" 
             x-transition:leave-start="transform opacity-100 scale-100" 
             x-transition:leave-end="transform opacity-0 scale-95"
             class="origin-top-right absolute right-0 mt-2 w-20 rounded-md shadow-lg bg-white ring-1 ring-black ring-opacity-5 focus:outline-none z-50" 
             role="menu" 
             aria-orientation="vertical" 
             aria-labelledby="language-menu" 
             tabindex="-1">
            <div class="py-1" role="none">
                @foreach($availableLocales as $code => $language)
                    @if($code !== app()->getLocale())
                        <a href="{{ url("/change-locale/$code?redirect=$currentPath") }}" 
                           class="block px-4 py-2 text-sm text-gray-700 hover:bg-gray-100 hover:text-gray-900" 
                           role="menuitem" 
                           tabindex="-1" 
                           id="language-{{ $code }}">
                            {{ strtoupper($code) }}
                        </a>
                    @endif
                @endforeach
            </div>
        </div>
    </div>
@endif
