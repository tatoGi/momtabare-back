<x-Admin.AdminLayout>
    <div class="container mx-auto px-4 py-8">
            <div class="flex justify-between items-center mb-6">
                <h1 class="text-2xl font-bold">{{ __('admin.Manage_Page') }}: {{ $page->title }}</h1>
                <div class="flex gap-2">
                    <a href="{{ route('admin.pages.management.index', [app()->getlocale(), $page->id]) }}" class="btn btn-primary">
                        {{ __('admin.Back_to_Pages') }}
                    </a>

                </div>
            </div>

            @if(session('success'))
                <div class="bg-green-100 border-l-4 border-green-500 text-green-700 p-4 mb-6" role="alert">
                    <p>{{ session('success') }}</p>
                </div>
            @endif

            @if(session('error'))
                <div class="bg-red-100 border-l-4 border-red-500 text-red-700 p-4 mb-6" role="alert">
                    <p>{{ session('error') }}</p>
                </div>
            @endif

            <div class="container">
                <div class="row">
                    <div class="bg-white rounded-lg col-md-12">

                        @include('admin.banners.index', [
                            'page_id' => $page->id,
                            'banners' => $banners
                        ])
                    </div>
                    @if($page->type_id == 2 || $page->type_id == 1)
                    <div class="bg-white rounded-lg col-md-12">

                        @include('admin.products.index', [
                            'page_id' => $page->id,
                            'products' => $products
                        ])
                    </div>
                    @endif
                </div>

            </div>

    </div>
</x-Admin.AdminLayout>

@push('scripts')
<script>
    // Add banner to the list
    function addBanner() {
        const select = document.getElementById('new_banner_id');
        const bannerId = select.value;
        const bannerTitle = select.options[select.selectedIndex].text;
        const sortOrder = document.getElementById('banner_sort_order').value || 0;

        if (!bannerId) {
            alert('Please select a banner');
            return;
        }

        // Check if banner already exists in the list
        if (document.querySelector(`input[name="banners[${bannerId}][id]"]`)) {
            alert('This banner is already in the list');
            return;
        }

        // Create banner item
        const bannerHtml = `
            <div id="banner-${bannerId}" class="flex items-center justify-between p-2 bg-gray-50 rounded mb-2">
                <span>${bannerTitle} (Sort: ${sortOrder})</span>
                <input type="hidden" name="banners[${bannerId}][id]" value="${bannerId}">
                <input type="hidden" name="banners[${bannerId}][sort]" value="${sortOrder}" class="sort-input" data-id="banner-${bannerId}">
                <button type="button" onclick="removeItem('banner', ${bannerId})" class="text-red-600 hover:text-red-900">
                    {{ __('admin.Remove') }}
                </button>
            </div>
        `;

        // Add to the list
        document.getElementById('banner-items').insertAdjacentHTML('beforeend', bannerHtml);

        // Reset the form
        select.value = '';
        document.getElementById('banner_sort_order').value = '';
    }

    // Add product to the list
    function addProduct() {
        const select = document.getElementById('new_product_id');
        const productId = select.value;
        const productTitle = select.options[select.selectedIndex].text;
        const sortOrder = document.getElementById('product_sort_order').value || 0;

        if (!productId) {
            alert('Please select a product');
            return;
        }

        // Check if product already exists in the list
        if (document.querySelector(`input[name="products[${productId}][id]"]`)) {
            alert('This product is already in the list');
            return;
        }

        // Create product item
        const productHtml = `
            <div id="product-${productId}" class="flex items-center justify-between p-2 bg-gray-50 rounded mb-2">
                <span>${productTitle} (Sort: ${sortOrder})</span>
                <input type="hidden" name="products[${productId}][id]" value="${productId}">
                <input type="hidden" name="products[${productId}][sort]" value="${sortOrder}" class="sort-input" data-id="product-${productId}">
                <button type="button" onclick="removeItem('product', ${productId})" class="text-red-600 hover:text-red-900">
                    {{ __('admin.Remove') }}
                </button>
            </div>
        `;

        // Add to the list
        document.getElementById('product-items').insertAdjacentHTML('beforeend', productHtml);

        // Reset the form
        select.value = '';
        document.getElementById('product_sort_order').value = '';
    }

    // Remove item from the list
    function removeItem(type, id) {
        const element = document.getElementById(`${type}-${id}`);
        if (element) {
            element.remove();
        }
    }

    // Form submission handler
    document.addEventListener('DOMContentLoaded', function() {
        const form = document.getElementById('pageContentForm');
        if (form) {
            form.addEventListener('submit', function(e) {
                // Prevent default form submission
                e.preventDefault();

                // Collect all banner data
                const banners = [];
                document.querySelectorAll('[name^="banners["]').forEach(input => {
                    const match = input.name.match(/banners\[(\d+)\]\[(\w+)\]/);
                    if (match) {
                        const id = match[1];
                        const field = match[2];
                        let banner = banners.find(b => b.id === id);
                        if (!banner) {
                            banner = { id };
                            banners.push(banner);
                        }
                        banner[field] = input.value;
                    }
                });

                // Collect all product data
                const products = [];
                document.querySelectorAll('[name^="products["]').forEach(input => {
                    const match = input.name.match(/products\[(\d+)\]\[(\w+)\]/);
                    if (match) {
                        const id = match[1];
                        const field = match[2];
                        let product = products.find(p => p.id === id);
                        if (!product) {
                            product = { id };
                            products.push(product);
                        }
                        product[field] = input.value;
                    }
                });

                // Update hidden inputs
                document.getElementById('bannersInput').value = JSON.stringify(banners);
                document.getElementById('productsInput').value = JSON.stringify(products);

                // Submit the form
                this.submit();
            });
        }
    });
</script>
@endpush
