@extends('layouts.app')
@section('title', 'Manage prompts')
@section('eyebrow', 'MEIGEN ADMIN')
@section('toolbar-title', 'Prompt management')
@section('content')
<div class="workspace-page admin-page">
    <div class="page-heading list-heading"><div><span class="section-kicker">LIBRARY MANAGEMENT</span><h1>Prompt submissions</h1><p>Review community posts and curate the public gallery.</p></div></div>
    <div class="admin-filter-tabs">@foreach (['all' => 'All', 'pending' => 'Needs review', 'approved' => 'Published', 'rejected' => 'Declined'] as $value => $label)<a class="{{ ($status ?: 'all') === $value ? 'is-active' : '' }}" href="{{ route('admin.prompts.index', $value === 'all' ? [] : ['status' => $value]) }}">{{ $label }} <span>{{ $value === 'pending' ? \App\Models\Prompt::where('status', 'pending')->count() : '' }}</span></a>@endforeach</div>
    <div class="table-wrap admin-table-wrap"><table class="data-table"><thead><tr><th>Prompt</th><th>Creator</th><th>Category</th><th>Submitted</th><th>Status</th><th>Actions</th></tr></thead><tbody>
        @forelse ($prompts as $prompt)
            <tr><td><div class="table-prompt"><span class="table-thumb">{{ $prompt->category->icon }}</span><div><strong>{{ $prompt->title }}</strong><small>{{ $prompt->model }} · {{ \Illuminate\Support\Str::limit($prompt->prompt_text, 64) }}</small></div></div></td><td>{{ $prompt->user?->name ?? 'Deleted account' }}</td><td>{{ $prompt->category->name }}</td><td>{{ $prompt->created_at->format('M j') }}</td><td><span class="status-pill status-{{ $prompt->status }}"><i></i>{{ ucfirst($prompt->status) }}</span></td><td><div class="row-actions admin-actions">
                <a href="{{ route('admin.prompts.edit', $prompt) }}">Edit</a>
                <form method="POST" action="{{ route('admin.prompts.feature', $prompt) }}">@csrf @method('PATCH')<button type="submit">{{ $prompt->is_featured ? 'Unfeature' : 'Feature' }}</button></form>
                @if ($prompt->status !== 'approved')<form method="POST" action="{{ route('admin.prompts.approve', $prompt) }}">@csrf @method('PATCH')<button class="approve-action" type="submit">Approve</button></form>@endif
                @if ($prompt->status !== 'rejected')<form method="POST" action="{{ route('admin.prompts.reject', $prompt) }}">@csrf @method('PATCH')<input type="hidden" name="admin_note" value="Please refine this prompt and resubmit."><button type="submit">Decline</button></form>@endif
                <form method="POST" action="{{ route('admin.prompts.destroy', $prompt) }}" onsubmit="return confirm('Permanently delete this prompt?')">@csrf @method('DELETE')<button class="delete-action" type="submit">Delete</button></form>
            </div></td></tr>
        @empty
            <tr><td colspan="6" class="table-empty">No submissions in this view.</td></tr>
        @endforelse
    </tbody></table></div>
    <div class="pagination-wrap">{{ $prompts->links() }}</div>
</div>
@endsection
