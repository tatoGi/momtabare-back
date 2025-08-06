<x-admin.admin-layout>

    <div class="flex flex-wrap my-5 -mx-2 bg-slate-100 rounded-md">

        <div class="container mx-auto">

            <div class="card-box ">

                <form action="{{ route('products.update', [app()->getlocale(), $product->id]) }}" method="POST"
                    enctype="multipart/form-data" data-parsley-validate novalidate>

                    @csrf

                    @method('PUT')



                    <div class="w-1/2 relative bg-white mt-2 mb-2 rounded-md p-4 mx-auto">

                        <div class="rounded absolute   w-full mx-auto mt-4">

                            <!-- Tabs -->

                            <div class="language-selector">

                                <ul id="tabs" class="language-selector-list">

                                    @foreach (config('app.locales') as $locale)
                                    @if ($locale === 'en')
                                    <li class="language-selector-item mb-2   rounded-md bg-cyan-500 border-cyan-500">
                                        @elseif($locale === 'ka')
                                    <li class="language-selector-item bg-red-500   rounded-md border-red-600">
                                        @endif

                                        <a href="#locale-{{ $locale }}" class="language-selector-link">

                                            <!-- You can use small icons here for each language -->

                                            <img src="{{ $locale === 'en' ? asset('storage/flags/united-states.png') : asset('storage/flags/georgia.png') }}"
                                                alt="{{ $locale }}" class="language-icon">

                                            <span class="language-name">{{ __('admin.locale_' . $locale) }}</span>

                                        </a>

                                    </li>
                                    @endforeach

                                </ul>



                            </div>



                        </div>

                        <div class="flex flex-col w-full mb-4">
                            <div class="flex justify-between gap-4">
                                <div class="w-full">
                                    <label for="category" class="block text-sm font-medium text-gray-900 dark:text-gray-400 mb-1">
                                        Category <span class="text-red-500">*</span>
                                    </label>
                                    <select id="category" name="category_id" required
                                        class="border w-full border-gray-300 text-gray-900 text-sm rounded-lg focus:ring-blue-500 focus:border-blue-500 block p-2.5 dark:border-gray-600 dark:placeholder-gray-400 dark:text-black dark:focus:ring-blue-500 dark:focus:border-blue-500">
                                        <option value="">Choose a Category</option>
                                        @foreach ($categories as $category)
                                            <option value="{{ $category->id }}" {{ $category->id == $product->category_id ? 'selected' : '' }}>
                                                {{ $category->title ?? '' }}
                                            </option>
                                        @endforeach
                                    </select>
                                    @error('category_id')
                                        <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                                    @enderror
                                </div>
                               
                            </div>
                        </div>

                       
                        <div id="tab-contents">

                            @foreach (config('app.locales') as $locale)
                            <div id="locale-{{ $locale }}"
                                class=" @if ($locale !== app()->getLocale()) hidden @endif p-4">

                                <div class="flex flex-col w-full items-center justify-center mb-2">

                                    <label for="title_{{ $locale }}" class="text-sm font-medium"><span
                                            class="text-red-500">*</span>Title (
                                        {{ __('admin.locale_' . $locale) }})</label>

                                    <input type="text" name="{{ $locale }}[title]" id="title_{{ $locale }}"
                                        value="{{ $product->translate($locale)->title }}"
                                        class="border w-full text-sm rounded-lg block p-2.5 @error('title') border-red-500 @enderror"
                                        placeholder="Title">

                                    @error('title')
                                    <span class="text-red-500 text-sm">{{ $message }}</span>
                                    @enderror

                                </div>



                                <div class="flex w-fulls items-center justify-center flex-col mb-2">

                                    <label for="slug_{{ $locale }}" class="text-sm font-medium"><span
                                            class="text-red-500">*</span>URL Keyword

                                        ({{ __('admin.locale_' . $locale) }})
                                    </label>

                                    <input type="text" name="{{ $locale }}[slug]" id="slug_{{ $locale }}"
                                        value="{{ $product->translate($locale)->slug }}"
                                        class="border w-full text-sm rounded-lg block  p-2.5 @error('slug') border-red-500 @enderror"
                                        placeholder="URL Keyword">

                                    @error('slug')
                                    <span class="text-red-500 text-sm">{{ $message }}</span>
                                    @enderror

                                </div>

                                <div class="flex w-full items-center justify-center flex-col mb-2">

                                    <label for="style_{{ $locale }}" class="text-sm font-medium"><span
                                            class="text-red-500">*</span>Style

                                        ( {{ __('admin.locale_' . $locale) }})</label>

                                    <input type="text" name="{{ $locale }}[style]" id="style_{{ $locale }}"
                                        value="{{ $product->translate($locale)->style }}"
                                        class="border w-full text-sm rounded-lg block  p-2.5 @error('style') border-red-500 @enderror"
                                        placeholder="Style">

                                    @error('style')
                                    <span class="text-red-500 text-sm">{{ $message }}</span>
                                    @enderror

                                </div>

                                <div class="flex w-full items-center justify-center flex-col mb-2">

                                    <label for="description_{{ $locale }}"
                                        class="text-sm font-medium text-gray-900 dark:text-gray-900"><span
                                            class="text-red-500">*</span>Description

                                        ( {{ __('admin.locale_' . $locale) }})</label>

                                    <div id="editor" class="editor">



                                        <textarea id="description_{{ $locale }}" name="{{ $locale }}[description]"
                                            class="border  w-full ckeditor text-sm text-gray-900 rounded-lg border-gray-300 focus:ring-blue-500

                                             focus:border-blue-500 dark:border-gray-600 dark:placeholder-gray-400 dark:text-black

                                              dark:focus:ring-blue-500 dark:focus:border-blue-500 p-2.5

                                             @error('description') border-red @enderror"
                                            placeholder="Description">{!! $product->translate($locale)->description ?? '' !!}</textarea>

                                        @error('description')
                                        <span class="text-red-500 text-sm">{{ $message }}</span>
                                        @enderror

                                    </div>





                                </div>

                            </div>
                            @endforeach

                        </div>







                        <!-- price -->

                        <div class="mb-4">

                            <label for="price" class="block font-medium text-gray-700">Price</label>

                            <input type="text" name="price" id="price" value="{{ $product->price }}"
                                class="border-gray-300 py-2 focus:ring-indigo-500 focus:border-indigo-500 block w-full sm:text-sm border rounded-md shadow-sm p-2">

                            @error('price')
                            <span class="text-red-500 text-sm">{{ $message }}</span>
                            @enderror

                        </div>

                        <!-- rating -->

                        <div class="flex w-1/2 flex-col mb-2">
                            <label class="text-xl mr-2 mb-2 text-cyan-400 font-bold">Active</label>
                            <label class="relative inline-flex cursor-pointer items-center">
                                <input id="switch" type="checkbox" class="peer sr-only" name="active" value="1" {{ $product->active ? 'checked' : '' }} />
                                <div class="peer h-6 w-12 rounded-full border bg-slate-200
                                    after:absolute after:left-[2px] after:top-0.5 after:h-5 after:w-5
                                    after:rounded-full after:border after:border-gray-300 after:bg-cyan-500
                                    after:transition-all after:content-['']
                                    peer-checked:bg-slate-800 peer-checked:after:translate-x-full
                                    peer-checked:after:border-white peer-focus:ring-green-300">
                                </div>
                            </label>
                        </div>

                        <div class="mt-2 border border-dashed border-gray-400 rounded-lg p-4">
                            <div class="flex flex-wrap imagePreview">
                                <label for="images" class="block font-medium text-gray-700">Images</label>

                                {{-- Debug information --}}
                                @if($product->images->isEmpty())
                                    <div class="w-full p-2 text-red-500">No images found for this product</div>
                                @else
                                    <div class="w-full p-2 text-green-500">Found {{ $product->images->count() }} images</div>
                                @endif

                                <input type="file" name="images[]" id="images" multiple
                                    class="border-gray-300 py-2 focus:ring-indigo-500 focus:border-indigo-500 block w-full sm:text-sm border rounded-md shadow-sm p-2">

                                @foreach ($product->images as $image)
                                <div class="w-1/2 md:w-1/4 lg:w-1/3 p-2">
                                    <div class="trash">
                                        {{-- Debug image path --}}
                                        <div class="text-xs text-gray-500 mb-1">Path: {{ $image->image_name }}</div>

                                        <img src="{{ asset('storage/' . $image->image_name) }}"
                                            alt="Product Image" class="w-full h-full mr-2 object-cover"
                                            onerror="this.onerror=null; this.src='{{ asset('images/placeholder.jpg') }}'; console.log('Image failed to load:', '{{ asset('storage/' . $image->image_name) }}');">

                                        {{-- Delete Button --}}

                                        <button type="button"
                                            data-route="{{ route('products.images.delete', [app()->getlocale(), $image->id]) }}"
                                            data-id="{{ $image->id }}" data-token="{{ csrf_token() }}"
                                            class="text-red-500 hover:text-red-700 focus:outline-none focus:text-red-700 delete-image"
                                            id="delete_image_{{ $image->id }}">

                                            <i class="fas fa-trash"></i>

                                        </button>

                                    </div>
                                </div>
                                @endforeach

                            </div>

                        </div>

                        @if ($errors->any())

                        <div class="mb-4">

                            <ul class="list-disc list-inside text-red-500">

                                @foreach ($errors->all() as $error)
                                <li>{{ $error }}</li>
                                @endforeach

                            </ul>

                        </div>

                        @endif

                        <div class="flex justify-between">

                            <div class="mb-4">

                                <button type="submit"
                                    class="bg-indigo-500 text-white py-2 px-4 rounded hover:bg-indigo-600">Update
                                    Product</button>

                            </div>

                            <div>

                                <a href="/{{ app()->getlocale() }}/admin/products"
                                    class="bg-indigo-500 text-white py-2 px-4 rounded hover:bg-indigo-600">Back</a>

                            </div>

                        </div>

                    </div>



                </form>

            </div>

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
