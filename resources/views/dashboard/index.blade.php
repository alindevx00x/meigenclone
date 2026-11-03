@extends('layouts.app')
@section('title', 'My creator studio')
@section('eyebrow', 'YOUR CREATOR SPACE')
@section('toolbar-title', 'My studio')
@section('content')
<div class="workspace-page">
    <div class="page-heading dashboard-heading"><div><span class="section-kicker">CREATOR DASHBOARD</span><h1>Good ideas live here.</h1><p>Keep track of your prompts and the inspiration you’ve saved.</p></div>@if ($directorySettings['allow_submissions'] === '1')<a class="primary-button" href="{{ route('dashboard.prompts.create') }}"><span>＋</span> Submit a prompt</a>@endif</div>
    <div class="stats-row user-stats">
        <div class="stat-card"><span class="stat-icon stat-violet">✦</span><span class="stat-label">Total submissions</span><strong>{{ $counts['total'] }}</strong><small>All your shared ideas</small></div>
        <div class="stat-card"><span class="stat-icon stat-green">✓</span><span class="stat-label">Published prompts</span><strong>{{ $counts['approved'] }}</strong><small>Visible in the gallery</small></div>
        <div class="stat-card"><span class="stat-icon stat-amber">◷</span><span class="stat-label">In review</span><strong>{{ $counts['pending'] }}</strong><small>Usually reviewed in 1–2 days</small></div>
        <div class="stat-card"><span class="stat-icon stat-pink">♡</span><span class="stat-label">Saved prompts</span><strong>{{ $counts['favorites'] }}</strong><small>Your personal collection</small></div>
    </div>

    <section class="workspace-section" id="my-prompts">
        <div class="section-heading"><div><span class="section-kicker">YOUR CONTRIBUTIONS</span><h2>My prompts</h2></div>@if ($directorySettings['allow_submissions'] === '1')<a class="subtle-link" href="{{ route('dashboard.prompts.create') }}">Add a new prompt <span>→</span></a>@endif</div>
        @if ($prompts->isEmpty())
            <div class="empty-panel"><span class="empty-icon">✦</span><div><strong>Your first idea belongs here.</strong><p>Share a prompt that helped you make something you love.</p></div>@if ($directorySettings['allow_submissions'] === '1')<a class="outline-button" href="{{ route('dashboard.prompts.create') }}">Create your first prompt <span>→</span></a>@endif</div>
        @else
            <div class="table-wrap"><table class="data-table"><thead><tr><th>Prompt</th><th>Category</th><th>Submitted</th><th>Status</th><th></th></tr></thead><tbody>
                @foreach ($prompts as $prompt)
                    <tr><td><div class="table-prompt"><span class="table-thumb">{{ $prompt->category->icon }}</span><div><strong>{{ $prompt->title }}</strong><small>{{ $prompt->model }}</small>@if ($prompt->admin_note)<em>{{ $prompt->admin_note }}</em>@endif</div></div></td><td>{{ $prompt->category->name }}</td><td>{{ $prompt->created_at->format('M j, Y') }}</td><td><span class="status-pill status-{{ $prompt->status }}"><i></i>{{ ucfirst($prompt->status) }}</span></td><td><div class="row-actions">@if ($prompt->status === 'approved')<a href="{{ route('prompts.show', $prompt->slug) }}">View</a>@endif @if ($directorySettings['allow_submissions'] === '1')<a href="{{ route('dashboard.prompts.edit', $prompt) }}">Edit</a>@endif<form method="POST" action="{{ route('dashboard.prompts.destroy', $prompt) }}" onsubmit="return confirm('Delete this prompt?')">@csrf @method('DELETE')<button type="submit">Delete</button></form></div></td></tr>
                @endforeach
            </tbody></table></div>
        @endif
    </section>

    <section class="workspace-section" id="favorites">
        <div class="section-heading"><div><span class="section-kicker">YOUR SAVED COLLECTION</span><h2>Favorites <span class="heading-count">{{ $counts['favorites'] }}</span></h2></div><a class="subtle-link" href="{{ route('prompts.index', ['sort' => 'popular']) }}">Discover more <span>→</span></a></div>
        @if ($favorites->isEmpty())
            <div class="empty-panel"><span class="empty-icon">♡</span><div><strong>Keep the ideas that move you.</strong><p>Tap the heart on any prompt to save it here for later.</p></div><a class="outline-button" href="{{ route('prompts.index') }}">Explore the gallery <span>→</span></a></div>
        @else
            <div class="prompt-grid dashboard-favorites">@foreach ($favorites as $prompt) @php($prompt->is_favorited = true) @include('partials.prompt-card', ['prompt' => $prompt]) @endforeach</div>
        @endif
    </section>
</div>
@endsection
