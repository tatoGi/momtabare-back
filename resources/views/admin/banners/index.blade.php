<div class="container-fluid py-4">
    <div class="d-flex justify-content-between align-items-center mb-4">
        <h2 class="h4 mb-0">Banners</h2>
        <a href="/{{ app()->getLocale() }}/admin/banners/create/{{ $page_id ?? '' }}"
           class="btn btn-primary d-flex align-items-center">
            <i class="material-icons-outlined me-2">add</i>
            Create Banner
        </a>
    </div>
    @if(session('message'))
        <div class="alert alert-{{ session('alert-type', 'info') }} alert-dismissible fade show" role="alert">
            {{ session('message') }}
            <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
        </div>
    @endif

    <div class="card shadow-sm">
        <div class="table-responsive">
            <table class="table table-hover align-middle">
                <thead class="table-light">
                    <tr>
                        <th class="text-center">Icon</th>
                        <th>Title</th>
                        <th>Description</th>
                        <th>Parent Banner</th>
                        <th class="text-center">Status</th>
                        <th class="text-end">Actions</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($banners as $banner)
                    <tr>
                        <td class="text-center">
                            @if($banner->icon)
                                <img src="{{ asset('storage/' . $banner->icon) }}"
                                     alt="{{ $banner->title }}"
                                     class="img-thumbnail"
                                     style="width: 50px; height: 50px; object-fit: cover;">
                            @else
                                <span class="text-muted">No icon</span>
                            @endif
                        </td>
                        <td>{{ $banner->title }}</td>
                        <td>{!! Str::limit($banner->translate(app()->getlocale())->desc, 50) !!}</td>
                        <td>{{ $banner->parent ? $banner->parent->title : 'None' }}</td>
                        <td class="text-center">
                            <span class="badge bg-{{ $banner->status ? 'success' : 'secondary' }}">
                                {{ $banner->status ? 'Active' : 'Inactive' }}
                            </span>
                        </td>
                        <td class="text-end">
                            <div class="btn-group" role="group">
                                <a href="{{ route('banners.edit',[ $banner->id, app()->getLocale()]) }}"
                                   class="btn btn-sm btn-outline-primary"
                                   title="Edit">
                                    <i class="material-icons-outlined" style="font-size: 1rem;">edit</i>
                                </a>
                                <form action="{{ route('banners.destroy',[$banner->id, app()->getLocale()]) }}" method="POST" class="d-inline">
                                    @csrf
                                    @method('DELETE')
                                    <button type="submit"
                                            class="btn btn-sm btn-outline-danger"
                                            onclick="return confirm('Are you sure you want to delete this banner?')"
                                            title="Delete">
                                        <i class="material-icons-outlined" style="font-size: 1rem;">delete</i>
                                    </button>
                                </form>
                            </div>
                        </td>
                    </tr>
                    @empty
                    <tr>
                        <td colspan="6" class="text-center">No banners found</td>
                    </tr>
                    @endforelse
                </tbody>
            </table>
        </div>


        <div class="card-footer">
            @include('admin.pagination.pagination', ['paginator' => $banners])
        </div>

    </div>
</div>
<!-- Bootstrap 5 CSS -->
<link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">

<!-- Bootstrap Icons -->
<link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.10.0/font/bootstrap-icons.css" rel="stylesheet">

<!-- Bootstrap 5 JS Bundle with Popper -->
<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>

<!-- Material Icons -->
<link href="https://fonts.googleapis.com/icon?family=Material+Icons+Outlined" rel="stylesheet">
