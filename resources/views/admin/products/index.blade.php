<div class="container-fluid py-4">
    <div class="d-flex flex-column flex-md-row justify-content-between align-items-center mb-4">
        <h2 class="h4 mb-3 mb-md-0">{{ __('admin.Products') }}</h2>
        <div class="d-flex gap-2">
         
            <a href="/{{ app()->getLocale() }}/admin/products/create/{{ $page->id }}" 
               class="btn btn-primary d-flex align-items-center">
                <i class="fas fa-plus me-1"></i> {{ __('admin.Add Product') }}
            </a>
        </div>
    </div>

    @if(session('success'))
        <div class="alert alert-success alert-dismissible fade show" role="alert">
            {{ session('success') }}
            <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
        </div>
    @endif

    <div class="card">
        <div class="card-body p-0">
            <div class="table-responsive">
                <table class="table table-hover align-middle mb-0">
                    <thead class="table-light">
                        <tr>
                            <th>#</th>
                            <th>{{ __('admin.Title') }}</th>
                            <th>{{ __('admin.Description') }}</th>
                            <th class="text-nowrap">{{ __('admin.Price') }}</th>
                            <th class="text-center">{{ __('admin.Status') }}</th>
                            <th class="text-end">{{ __('admin.Actions') }}</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse($page->products as $product)
                        <tr>
                            <td>{{ $loop->iteration }}</td>
                            <td class="text-nowrap">{{ $product->title }}</td>
                            <td class="text-truncate" style="max-width: 250px;" title="{{ $product->description }}">
                                {{ $product->description }}
                            </td>
                            <td class="text-nowrap">{{ number_format($product->price, 2) }} {{ config('app.currency', '$') }}</td>
                            <td class="text-center">
                                <form action="{{ route('admin.products.toggle-status', $product->id) }}" method="POST" class="d-inline">
                                    @csrf
                                    @method('PATCH')
                                    <input type="hidden" name="page_id" value="{{ $page->id }}">
                                    <button type="submit" class="btn btn-sm btn-status {{ $product->status ? 'btn-success' : 'btn-outline-secondary' }}">
                                        {{ $product->status ? __('admin.Active') : __('admin.Inactive') }}
                                    </button>
                                </form>
                            </td>
                            <td class="text-end">
                                <div class="btn-group" role="group">
                                    <a href="{{ route('admin.products.show', $product->id) }}" 
                                       class="btn btn-sm btn-outline-info"
                                       title="{{ __('admin.View') }}">
                                        <i class="fas fa-eye"></i>
                                    </a>
                                    <a href="{{ route('admin.products.edit', ['product' => $product->id, 'page_id' => $page->id]) }}" 
                                       class="btn btn-sm btn-outline-primary"
                                       title="{{ __('admin.Edit') }}">
                                        <i class="fas fa-edit"></i>
                                    </a>
                                    <button type="button" 
                                            class="btn btn-sm btn-outline-danger delete-product"
                                            data-id="{{ $product->id }}"
                                            data-page-id="{{ $page->id }}"
                                            title="{{ __('admin.Delete') }}">
                                        <i class="fas fa-trash"></i>
                                    </button>
                                </div>
                            </td>
                        </tr>
                        @empty
                        <tr>
                            <td colspan="6" class="text-center py-5">
                                <div class="text-muted">
                                    <i class="fas fa-box-open fa-3x mb-3"></i>
                                    <h5 class="mb-2">{{ __('admin.No products found') }}</h5>
                                    <p class="mb-0">{{ __('admin.Get started by adding your first product') }}</p>
                                </div>
                            </td>
                        </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>
        {{-- @if($page->products->hasPages())
        <div class="card-footer bg-transparent">
            {{ $page->products->links() }}
        </div>
        @endif --}}
    </div>
</div>

<!-- Delete Confirmation Modal -->
<div class="modal fade" id="deleteModal" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title">{{ __('admin.Confirm Deletion') }}</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body">
                <p>{{ __('admin.Are you sure you want to delete this product? This action cannot be undone.') }}</p>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">{{ __('admin.Cancel') }}</button>
                <form id="deleteForm" method="POST" class="d-inline">
                    @csrf
                    @method('DELETE')
                    <input type="hidden" name="page_id" id="deletePageId" value="">
                    <button type="submit" class="btn btn-danger">{{ __('admin.Delete') }}</button>
                </form>
            </div>
        </div>
    </div>
</div>

@push('scripts')
<script>
document.addEventListener('DOMContentLoaded', function() {
    // Delete product with confirmation
    const deleteButtons = document.querySelectorAll('.delete-product');
    const deleteForm = document.getElementById('deleteForm');
    const deletePageId = document.getElementById('deletePageId');
    
    deleteButtons.forEach(button => {
        button.addEventListener('click', function() {
            const productId = this.getAttribute('data-id');
            const pageId = this.getAttribute('data-page-id');
            
            deleteForm.action = `/{{ app()->getLocale() }}/admin/products/${productId}`;
            deletePageId.value = pageId;
            
            const modal = new bootstrap.Modal(document.getElementById('deleteModal'));
            modal.show();
        });
    });

    // Auto-hide success message after 5 seconds
    const alert = document.querySelector('.alert');
    if (alert) {
        setTimeout(() => {
            alert.alert('close');
        }, 5000);
    }
});
</script>
@endpush

<!-- Bootstrap 5 CSS -->
<link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">

<!-- Bootstrap Icons -->
<link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.10.0/font/bootstrap-icons.css" rel="stylesheet">

<!-- Bootstrap 5 JS Bundle with Popper -->
<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>

<!-- Material Icons -->
<link href="https://fonts.googleapis.com/icon?family=Material+Icons+Outlined" rel="stylesheet">