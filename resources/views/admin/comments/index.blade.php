<x-admin.admin-layout>
    <section class="container px-4 mx-auto">
        <div class="sm:flex sm:items-center sm:justify-between">
            <div>
                <div class="flex items-center gap-x-3">
                    <h2 class="text-lg font-medium text-gray-800">Product Comments</h2>
                    <span class="px-3 py-1 text-xs text-blue-600 bg-blue-100 rounded-full">{{ $comments->total() }} total</span>
                </div>
                <p class="mt-1 text-sm text-gray-500">Manage and moderate product comments</p>
            </div>
            <div class="flex items-center mt-4 gap-x-3">
                <div class="flex gap-2">
                    <a href="?status=pending" class="px-3 py-2 text-sm font-medium rounded {{ request('status') === 'pending' ? 'bg-yellow-100 text-yellow-800' : 'bg-gray-100 text-gray-700 hover:bg-gray-200' }}">
                        Pending ({{ $pendingCount }})
                    </a>
                    <a href="?status=approved" class="px-3 py-2 text-sm font-medium rounded {{ request('status') === 'approved' ? 'bg-green-100 text-green-800' : 'bg-gray-100 text-gray-700 hover:bg-gray-200' }}">
                        Approved ({{ $approvedCount }})
                    </a>
                    <a href="{{ request()->url() }}" class="px-3 py-2 text-sm font-medium rounded {{ !request('status') ? 'bg-blue-100 text-blue-800' : 'bg-gray-100 text-gray-700 hover:bg-gray-200' }}">
                        All
                    </a>
                </div>
            </div>
        </div>

        <div class="mt-6 bg-white shadow-sm rounded-lg border border-gray-200">
            @if($comments->count() > 0)
            <div class="divide-y divide-gray-200">
                @foreach($comments as $comment)
                <div class="px-6 py-4">
                    <div class="flex justify-between items-start">
                        <div class="flex-1">
                            <div class="flex items-center gap-2 mb-2">
                                <h4 class="font-medium text-gray-900">{{ $comment->product->title }}</h4>
                                @if($comment->rating)
                                <div class="flex items-center">
                                    @for($i = 1; $i <= 5; $i++)
                                        <svg class="w-4 h-4 {{ $i <= $comment->rating ? 'text-yellow-400' : 'text-gray-300' }}" fill="currentColor" viewBox="0 0 20 20">
                                            <path d="M9.049 2.927c.3-.921 1.603-.921 1.902 0l1.07 3.292a1 1 0 00.95.69h3.462c.969 0 1.371 1.24.588 1.81l-2.8 2.034a1 1 0 00-.364 1.118l1.07 3.292c.3.921-.755 1.688-1.54 1.118l-2.8-2.034a1 1 0 00-1.175 0l-2.8 2.034c-.784.57-1.838-.197-1.539-1.118l1.07-3.292a1 1 0 00-.364-1.118L2.98 8.72c-.783-.57-.38-1.81.588-1.81h3.461a1 1 0 00.951-.69l1.07-3.292z"/>
                                        </svg>
                                    @endfor
                                    <span class="ml-1 text-sm text-gray-600">({{ $comment->rating }}/5)</span>
                                </div>
                                @endif
                                <span class="inline-flex px-2 py-1 text-xs rounded-full {{ $comment->is_approved ? 'bg-green-100 text-green-800' : 'bg-yellow-100 text-yellow-800' }}">
                                    {{ $comment->is_approved ? 'Approved' : 'Pending' }}
                                </span>
                            </div>
                            <p class="text-gray-700 mb-2">{{ $comment->comment }}</p>
                            <div class="flex items-center gap-4 text-xs text-gray-500">
                                <span>By: <a href="/{{ app()->getLocale() }}/admin/webusers/{{ $comment->user->id }}" class="text-blue-600 hover:text-blue-800 underline">{{ $comment->user->first_name }} {{ $comment->user->surname }}</a></span>
                                <span>{{ $comment->created_at->format('M d, Y H:i') }}</span>
                                @if($comment->is_approved && $comment->approved_at)
                                <span>Approved {{ $comment->approved_at->format('M d, Y') }}</span>
                                @endif
                            </div>
                        </div>
                        <div class="flex gap-2 ml-4">
                            @if(!$comment->is_approved)
                            <button onclick="approveComment({{ $comment->id }})"
                                    class="px-3 py-1 text-xs font-medium text-white bg-green-600 rounded hover:bg-green-700">
                                Approve
                            </button>
                            @endif
                            <button onclick="rejectComment({{ $comment->id }})"
                                    class="px-3 py-1 text-xs font-medium text-white bg-red-600 rounded hover:bg-red-700">
                                {{ $comment->is_approved ? 'Delete' : 'Reject' }}
                            </button>
                        </div>
                    </div>
                </div>
                @endforeach
            </div>
            
            <!-- Pagination -->
            @if($comments->hasPages())
            <div class="px-6 py-4 border-t border-gray-200">
                {{ $comments->appends(request()->query())->links() }}
            </div>
            @endif
            @else
            <div class="px-6 py-8 text-center">
                <svg class="mx-auto h-12 w-12 text-gray-400" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M7 8h10m0 0V6a2 2 0 00-2-2H9a2 2 0 00-2 2v2m0 0v10a2 2 0 002 2h6a2 2 0 002-2V8m0 0V6a2 2 0 00-2-2H9a2 2 0 00-2 2v2m0 0v10a2 2 0 002 2h6a2 2 0 002-2V8" />
                </svg>
                <h3 class="mt-2 text-sm font-medium text-gray-900">No comments</h3>
                <p class="mt-1 text-sm text-gray-500">No comments found matching your criteria.</p>
            </div>
            @endif
        </div>
    </section>

    <script>
        function approveComment(commentId) {
            if (confirm('Are you sure you want to approve this comment?')) {
                fetch(`/{{ app()->getLocale() }}/admin/comments/${commentId}/approve`, {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/json',
                        'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content')
                    }
                })
                .then(response => response.json())
                .then(data => {
                    if (data.success) {
                        location.reload();
                    } else {
                        alert('Error: ' + data.message);
                    }
                })
                .catch(error => {
                    console.error('Error:', error);
                    alert('An error occurred while approving the comment.');
                });
            }
        }

        function rejectComment(commentId) {
            if (confirm('Are you sure you want to reject/delete this comment?')) {
                fetch(`/{{ app()->getLocale() }}/admin/comments/${commentId}/reject`, {
                    method: 'DELETE',
                    headers: {
                        'Content-Type': 'application/json',
                        'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content')
                    }
                })
                .then(response => response.json())
                .then(data => {
                    if (data.success) {
                        location.reload();
                    } else {
                        alert('Error: ' + data.message);
                    }
                })
                .catch(error => {
                    console.error('Error:', error);
                    alert('An error occurred while rejecting the comment.');
                });
            }
        }
    </script>
</x-admin.admin-layout>
