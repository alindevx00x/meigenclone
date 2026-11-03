@extends('layouts.app')
@section('title', 'Manage prompts')
@section('eyebrow', 'MEIGEN ADMIN')
@section('toolbar-title', 'Prompt management')
@section('content')
<div class="workspace-page admin-page">
    <div class="page-heading list-heading">
        <div>
            <span class="section-kicker">LIBRARY MANAGEMENT</span>
            <h1>Prompt submissions</h1>
            <p>Review community posts, search the library, and curate the public gallery.</p>
        </div>
    </div>

    @if (session('success'))
        <div class="alert-box alert-success" style="margin-bottom: 20px;">
            <p>{{ session('success') }}</p>
        </div>
    @endif
    @if ($errors->any())
        <div class="alert-box alert-error" style="margin-bottom: 20px;">
            @foreach ($errors->all() as $error)
                <p>{{ $error }}</p>
            @endforeach
        </div>
    @endif

    <!-- Status Tabs -->
    <div class="admin-filter-tabs">
        @foreach (['all' => 'All', 'pending' => 'Needs review', 'approved' => 'Published', 'rejected' => 'Declined'] as $value => $label)
            @php
                $tabParams = request()->except(['status', 'page']);
                if ($value !== 'all') {
                    $tabParams['status'] = $value;
                }
            @endphp
            <a class="{{ ($status ?: 'all') === $value ? 'is-active' : '' }}" href="{{ route('admin.prompts.index', $tabParams) }}">
                {{ $label }}
                <span>({{ number_format($counts[$value] ?? 0) }})</span>
            </a>
        @endforeach
    </div>

    <!-- Search & Filters Bar -->
    <form class="panel-card" method="GET" action="{{ route('admin.prompts.index') }}" style="margin-bottom: 20px; display: flex; flex-wrap: wrap; gap: 10px; align-items: center; justify-content: space-between;">
        @if ($status && $status !== 'all')
            <input type="hidden" name="status" value="{{ $status }}">
        @endif

        <div style="display: flex; flex-wrap: wrap; gap: 10px; align-items: center; flex: 1 1 300px;">
            <input type="search" name="q" value="{{ request('q') }}" placeholder="Search by title, prompt text, creator…" style="flex: 1 1 220px; min-height: 38px;" aria-label="Search prompts">
            
            <select name="category_id" style="min-height: 38px; width: 150px;" aria-label="Filter by category">
                <option value="">All Categories</option>
                @foreach ($categories as $cat)
                    <option value="{{ $cat->id }}" @selected(request('category_id') == $cat->id)>{{ $cat->icon }} {{ $cat->name }}</option>
                @endforeach
            </select>

            <select name="model" style="min-height: 38px; width: 140px;" aria-label="Filter by AI model">
                <option value="">All Models</option>
                @foreach ($models as $mod)
                    <option value="{{ $mod->name }}" @selected(request('model') === $mod->name)>{{ $mod->icon }} {{ $mod->name }}</option>
                @endforeach
            </select>

            <select name="featured" style="min-height: 38px; width: 130px;" aria-label="Filter by featured status">
                <option value="">Featured: Any</option>
                <option value="1" @selected(request('featured') === '1')>Featured only</option>
                <option value="0" @selected(request('featured') === '0')>Standard only</option>
            </select>

            <select name="sort" style="min-height: 38px; width: 140px;" aria-label="Sort prompts">
                <option value="newest" @selected(request('sort', 'newest') === 'newest')>Newest first</option>
                <option value="oldest" @selected(request('sort') === 'oldest')>Oldest first</option>
                <option value="likes" @selected(request('sort') === 'likes')>Most likes</option>
                <option value="title" @selected(request('sort') === 'title')>Title (A–Z)</option>
            </select>

            <button class="primary-button" type="submit" style="min-height: 38px; padding: 0 16px;">Filter <span>→</span></button>
            @if (request()->hasAny(['q', 'category_id', 'model', 'featured', 'sort']))
                <a class="outline-button" href="{{ route('admin.prompts.index', $status && $status !== 'all' ? ['status' => $status] : []) }}" style="min-height: 38px; display: inline-flex; align-items: center;">Clear</a>
            @endif
        </div>
    </form>

    <!-- Table of Prompts -->
    <div class="table-wrap admin-table-wrap">
        <table class="data-table">
            <thead>
                <tr>
                    <th>Prompt Preview</th>
                    <th>Creator</th>
                    <th>Category &amp; Model</th>
                    <th>Stats</th>
                    <th>Status</th>
                    <th style="text-align: right;">Actions</th>
                </tr>
            </thead>
            <tbody>
                @forelse ($prompts as $prompt)
                    <tr>
                        <td>
                            <div class="table-prompt" style="align-items: center;">
                                @if ($prompt->cover_url)
                                    <img src="{{ $prompt->cover_url }}" alt="{{ $prompt->title }}" style="width: 44px; height: 44px; object-fit: cover; border-radius: 8px; flex: 0 0 44px; border: 1px solid #ebebf0;" loading="lazy">
                                @else
                                    <span class="table-thumb" style="width: 44px; height: 44px; border-radius: 8px;">{{ $prompt->category->icon }}</span>
                                @endif
                                <div style="min-width: 0;">
                                    <div style="display: flex; align-items: center; gap: 6px;">
                                        <a href="{{ route('prompts.show', $prompt->slug) }}" target="_blank" style="font-weight: 600; color: #1e1e24; text-decoration: none;" title="{{ $prompt->title }}">
                                            {{ Str::limit($prompt->title, 42) }}
                                        </a>
                                        @if ($prompt->is_featured)
                                            <span style="display: inline-flex; font-size: 10px; background: #fef3c7; color: #b45309; padding: 1px 6px; border-radius: 4px; font-weight: 600;">Featured</span>
                                        @endif
                                    </div>
                                    <small style="display: block; color: #7f7f88; margin-top: 2px;">
                                        {{ Str::limit($prompt->prompt_text, 68) }}
                                    </small>
                                </div>
                            </div>
                        </td>
                        <td>
                            <span style="font-size: 12.5px; font-weight: 500; color: #333;">{{ $prompt->user?->name ?? 'Community Pick' }}</span>
                            <small style="display: block; color: #999; font-size: 10.5px;">{{ $prompt->created_at->format('M j, Y') }}</small>
                        </td>
                        <td>
                            <div>
                                <span style="font-size: 12px; font-weight: 500; color: #444;">{{ $prompt->category->icon }} {{ $prompt->category->name }}</span>
                                <small style="display: block; color: #888; font-size: 11px;">{{ $prompt->model }}</small>
                            </div>
                        </td>
                        <td>
                            <span style="font-size: 12px; color: #666;">♥ {{ number_format($prompt->likes_count) }}</span>
                        </td>
                        <td>
                            <span class="status-pill status-{{ $prompt->status }}">
                                <i></i>{{ ucfirst($prompt->status) }}
                            </span>
                        </td>
                        <td>
                            <div class="row-actions admin-actions" style="justify-content: flex-end;">
                                <a href="{{ route('admin.prompts.edit', $prompt) }}">Edit</a>
                                
                                <form method="POST" action="{{ route('admin.prompts.feature', $prompt) }}">
                                    @csrf
                                    @method('PATCH')
                                    <button type="submit">{{ $prompt->is_featured ? 'Unfeature' : 'Feature' }}</button>
                                </form>

                                @if ($prompt->status !== 'approved')
                                    <form method="POST" action="{{ route('admin.prompts.approve', $prompt) }}">
                                        @csrf
                                        @method('PATCH')
                                        <button class="approve-action" type="submit">Approve</button>
                                    </form>
                                @endif

                                @if ($prompt->status !== 'rejected')
                                    <form method="POST" action="{{ route('admin.prompts.reject', $prompt) }}">
                                        @csrf
                                        @method('PATCH')
                                        <input type="hidden" name="admin_note" value="Please refine this prompt and resubmit.">
                                        <button type="submit">Decline</button>
                                    </form>
                                @endif

                                <form method="POST" action="{{ route('admin.prompts.destroy', $prompt) }}" onsubmit="return confirm('Permanently delete this prompt?')">
                                    @csrf
                                    @method('DELETE')
                                    <button class="delete-action" type="submit">Delete</button>
                                </form>
                            </div>
                        </td>
                    </tr>
                @empty
                    <tr>
                        <td colspan="6" class="table-empty">
                            No prompts match your search and filter criteria.
                        </td>
                    </tr>
                @endforelse
            </tbody>
        </table>
    </div>

    <div class="pagination-wrap" style="margin-top: 20px;">
        {{ $prompts->links() }}
    </div>
</div>
@endsection
