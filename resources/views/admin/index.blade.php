@extends('layouts.app')
@section('title', 'Admin studio')
@section('eyebrow', 'MEIGEN ADMIN')
@section('toolbar-title', 'Admin studio')
@section('content')
<div class="workspace-page admin-page">
    <div class="page-heading dashboard-heading"><div><span class="section-kicker">COMMUNITY OPERATIONS</span><h1>Welcome to the studio.</h1><p>Keep the prompt library thoughtful, useful, and inspiring.</p></div><a class="primary-button" href="{{ route('admin.prompts.index', ['status' => 'pending']) }}">Review queue <span>→</span></a></div>
    <div class="stats-row admin-stats">
        <a class="stat-card" href="{{ route('admin.prompts.index') }}"><span class="stat-icon stat-violet">✦</span><span class="stat-label">All prompts</span><strong>{{ number_format($stats['prompts']) }}</strong><small>Across the whole library</small></a>
        <a class="stat-card" href="{{ route('admin.prompts.index', ['status' => 'pending']) }}"><span class="stat-icon stat-amber">◷</span><span class="stat-label">Awaiting review</span><strong>{{ number_format($stats['pending']) }}</strong><small>Needs your attention</small></a>
        <a class="stat-card" href="{{ route('admin.prompts.index', ['status' => 'approved']) }}"><span class="stat-icon stat-green">✓</span><span class="stat-label">Published</span><strong>{{ number_format($stats['published']) }}</strong><small>Live in the gallery</small></a>
        <a class="stat-card" href="{{ route('admin.users.index') }}"><span class="stat-icon stat-pink">◎</span><span class="stat-label">Creators</span><strong>{{ number_format($stats['members']) }}</strong><small>Community members</small></a>
    </div>
    <div class="admin-shortcuts"><a href="{{ route('admin.prompts.index') }}"><span class="shortcut-icon">▤</span><span><strong>Manage prompts</strong><small>Approve, feature, or remove submissions</small></span><b>→</b></a><a href="{{ route('admin.categories.index') }}"><span class="shortcut-icon">▦</span><span><strong>Manage categories</strong><small>{{ $stats['categories'] }} collections in your library</small></span><b>→</b></a><a href="{{ route('admin.models.index') }}"><span class="shortcut-icon">◈</span><span><strong>Manage AI models</strong><small>{{ $stats['models'] ?? 0 }} generation engines</small></span><b>→</b></a><a href="{{ route('admin.users.index') }}"><span class="shortcut-icon">◎</span><span><strong>Manage members</strong><small>Review access and creator accounts</small></span><b>→</b></a><a href="{{ route('admin.settings') }}"><span class="shortcut-icon">⚙</span><span><strong>Directory settings</strong><small>Control publishing and gallery options</small></span><b>→</b></a></div>
    <section class="workspace-section admin-queue"><div class="section-heading"><div><span class="section-kicker">INCOMING FROM THE COMMUNITY</span><h2>Ready for review <span class="heading-count">{{ $stats['pending'] }}</span></h2></div><a class="subtle-link" href="{{ route('admin.prompts.index', ['status' => 'pending']) }}">Open review queue <span>→</span></a></div>
        @if ($pendingPrompts->isEmpty())<div class="empty-panel"><span class="empty-icon">✓</span><div><strong>You’re all caught up.</strong><p>New creator submissions will appear here.</p></div></div>
        @else
            <div class="table-wrap"><table class="data-table"><thead><tr><th>Submission</th><th>Creator</th><th>Category</th><th>Submitted</th><th></th></tr></thead><tbody>@foreach ($pendingPrompts as $prompt)<tr><td><div class="table-prompt"><span class="table-thumb">{{ $prompt->category->icon }}</span><div><strong>{{ $prompt->title }}</strong><small>{{ $prompt->model }}</small></div></div></td><td>{{ $prompt->user?->name ?? 'Deleted account' }}</td><td>{{ $prompt->category->name }}</td><td>{{ $prompt->created_at->diffForHumans() }}</td><td><a class="mini-action" href="{{ route('admin.prompts.index', ['status' => 'pending']) }}">Review →</a></td></tr>@endforeach</tbody></table></div>
        @endif
    </section>
</div>
@endsection
