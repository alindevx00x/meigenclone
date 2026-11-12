@extends('layouts.app')
@section('title', 'Manage prompts')
@section('eyebrow', 'ADMIN STUDIO')
@section('toolbar-title', 'Prompt management')
@section('content')
<div class="workspace-page admin-page">
    <div class="page-heading list-heading" style="display: flex; justify-content: space-between; align-items: flex-start; flex-wrap: wrap; gap: 16px;">
        <div>
            <span class="section-kicker">LIBRARY MANAGEMENT</span>
            <h1>Prompt submissions</h1>
            <p>Review community posts, search the library, and curate the public gallery.</p>
        </div>
        <div style="display: flex; gap: 10px; align-items: center; flex-wrap: wrap;">
            <a class="outline-button" href="{{ route('admin.prompts.pinterest_export_zip') }}" style="min-height: 38px; display: inline-flex; align-items: center; gap: 8px; font-weight: 600; border-color: #cbd5e1; background: #fff;">
                <span style="color: #e60023; font-size: 16px; line-height: 1;">📌</span>
                <span>Pinterest Bulk Pins (All 25 Parts Zip)</span>
            </a>
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
            <input type="search" name="q" value="{{ request('q') }}" placeholder="Search by title, prompt text, creator…" class="table-filter-input" style="flex: 1 1 220px; min-height: 38px;" aria-label="Search prompts">
            
            <select name="category_id" class="table-filter-select" style="min-height: 38px; width: 150px;" aria-label="Filter by category">
                <option value="">All Categories</option>
                @foreach ($categories as $cat)
                    <option value="{{ $cat->id }}" @selected(request('category_id') == $cat->id)>{{ $cat->icon }} {{ $cat->name }}</option>
                @endforeach
            </select>

            <select name="model" class="table-filter-select" style="min-height: 38px; width: 140px;" aria-label="Filter by AI model">
                <option value="">All Models</option>
                @foreach ($models as $mod)
                    <option value="{{ $mod->name }}" @selected(request('model') === $mod->name)>{{ $mod->icon }} {{ $mod->name }}</option>
                @endforeach
            </select>

            <select name="featured" class="table-filter-select" style="min-height: 38px; width: 130px;" aria-label="Filter by featured status">
                <option value="">Featured: Any</option>
                <option value="1" @selected(request('featured') === '1')>Featured only</option>
                <option value="0" @selected(request('featured') === '0')>Standard only</option>
            </select>

            <select name="sort" class="table-filter-select" style="min-height: 38px; width: 140px;" aria-label="Sort prompts">
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

    <!-- Table of Prompts with Bulk Actions -->
    <div x-data="{
        selected: [],
        allSelected: false,
        selectedAction: '',
        targetCategory: '',
        toggleAll(e) {
            const checkboxes = Array.from(document.querySelectorAll('.prompt-checkbox'));
            if (e.target.checked) {
                this.selected = checkboxes.map(cb => cb.value);
                this.allSelected = true;
            } else {
                this.selected = [];
                this.allSelected = false;
            }
        },
        updateRowSelection() {
            const checkboxes = Array.from(document.querySelectorAll('.prompt-checkbox'));
            this.allSelected = checkboxes.length > 0 && this.selected.length === checkboxes.length;
        },
        confirmSubmit(e) {
            if (!this.selectedAction) {
                alert('Please choose an action from the dropdown.');
                e.preventDefault();
                return false;
            }
            if (this.selected.length === 0) {
                alert('Please select at least one prompt using the checkboxes.');
                e.preventDefault();
                return false;
            }
            if (this.selectedAction === 'delete') {
                if (!confirm(`Are you sure you want to permanently delete ${this.selected.length} prompt(s)?`)) {
                    e.preventDefault();
                    return false;
                }
            }
            if (this.selectedAction === 'change_category' && !this.targetCategory) {
                alert('Please select a target category.');
                e.preventDefault();
                return false;
            }
            return true;
        }
    }">
        <!-- Floating Bulk Action Bar -->
        <div x-show="selected.length > 0" x-cloak style="position: sticky; top: 80px; z-index: 50; background: #18181b; color: #fff; padding: 12px 20px; border-radius: 12px; margin-bottom: 16px; display: flex; flex-wrap: wrap; align-items: center; justify-content: space-between; gap: 12px; box-shadow: 0 10px 25px -5px rgba(0,0,0,0.4); border: 1px solid #3f3f46;">
            <div style="display: flex; align-items: center; gap: 12px;">
                <span style="font-weight: 700; font-size: 13px; background: #4f46e5; color: #fff; padding: 3px 12px; border-radius: 99px;" x-text="selected.length + ' prompt' + (selected.length > 1 ? 's' : '') + ' selected'"></span>
                <button type="button" @click="selected = []; allSelected = false" style="background: none; border: none; color: #a1a1aa; font-size: 12.5px; cursor: pointer; text-decoration: underline;">Clear selection</button>
            </div>
            
            <form method="POST" action="{{ route('admin.prompts.bulk') }}" @submit="confirmSubmit($event)" style="display: flex; flex-wrap: wrap; align-items: center; gap: 10px; margin: 0;">
                @csrf
                <template x-for="id in selected" :key="id">
                    <input type="hidden" name="prompt_ids[]" :value="id">
                </template>

                <select name="action" x-model="selectedAction" style="background: #27272a; color: #f4f4f5; border: 1px solid #52525b; border-radius: 8px; padding: 7px 12px; font-size: 13px; min-width: 170px;" required>
                    <option value="">Choose bulk action...</option>
                    <option value="approve">✓ Approve Selected</option>
                    <option value="reject">✗ Decline / Reject</option>
                    <option value="feature">★ Mark as Featured</option>
                    <option value="unfeature">☆ Remove Featured</option>
                    <option value="change_category">🏷 Move to Category</option>
                    <option value="delete">🗑 Permanently Delete</option>
                </select>

                <select name="target_category_id" x-show="selectedAction === 'change_category'" x-model="targetCategory" style="background: #27272a; color: #f4f4f5; border: 1px solid #52525b; border-radius: 8px; padding: 7px 12px; font-size: 13px;">
                    <option value="">Select category...</option>
                    @foreach ($categories as $cat)
                        <option value="{{ $cat->id }}">{{ $cat->icon }} {{ $cat->name }}</option>
                    @endforeach
                </select>

                <button type="submit" class="primary-button" style="background: #4f46e5; color: #fff; padding: 7px 18px; font-size: 13px; font-weight: 600; border-radius: 8px; border: none; cursor: pointer;">
                    Apply to Selected
                </button>
            </form>
        </div>

        <div class="table-wrap admin-table-wrap">
            <table class="data-table">
                <thead>
                    <tr>
                        <th style="width: 40px; text-align: center;">
                            <input type="checkbox" :checked="allSelected" @change="toggleAll($event)" aria-label="Select all prompts on page" style="cursor: pointer; width: 16px; height: 16px;">
                        </th>
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
                            <td style="width: 40px; text-align: center;">
                                <input type="checkbox" class="prompt-checkbox" value="{{ $prompt->id }}" x-model="selected" @change="updateRowSelection()" aria-label="Select prompt" style="cursor: pointer; width: 16px; height: 16px;">
                            </td>
                            <td>
                                <div class="table-prompt" style="align-items: center;">
                                    @if ($prompt->cover_url)
                                        <img src="{{ $prompt->thumbnail_url ?? $prompt->cover_url }}" alt="{{ $prompt->title }}" style="width: 44px; height: 44px; object-fit: cover; border-radius: 8px; flex: 0 0 44px; border: 1px solid #ebebf0;" loading="lazy">
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
                                <div class="admin-actions-group" style="display: inline-flex; align-items: center; gap: 5px; justify-content: flex-end;">
                                    <a href="{{ route('admin.prompts.edit', $prompt) }}" class="admin-btn-action admin-btn-edit" title="Edit prompt details">
                                        <svg viewBox="0 0 20 20" fill="currentColor" style="width: 13px; height: 13px;"><path d="M13.586 3.586a2 2 0 112.828 2.828l-.793.793-2.828-2.828.793-.793zM11.379 5.793L3 14.172V17h2.828l8.38-8.379-2.83-2.828z"/></svg>
                                        <span>Edit</span>
                                    </a>
                                    
                                    <form method="POST" action="{{ route('admin.prompts.feature', $prompt) }}" style="display: inline-flex; margin: 0;">
                                        @csrf
                                        @method('PATCH')
                                        <button type="submit" class="admin-btn-action {{ $prompt->is_featured ? 'admin-btn-unfeature' : 'admin-btn-feature' }}" title="{{ $prompt->is_featured ? 'Remove from featured' : 'Feature prompt' }}">
                                            <svg viewBox="0 0 20 20" fill="currentColor" style="width: 13px; height: 13px;"><path d="M9.049 2.927c.3-.921 1.603-.921 1.902 0l1.07 3.292a1 1 0 00.95.69h3.462c.969 0 1.371 1.24.588 1.81l-2.8 2.034a1 1 0 00-.364 1.118l1.07 3.292c.3.921-.755 1.688-1.54 1.118l-2.8-2.034a1 1 0 00-1.175 0l-2.8 2.034c-.784.57-1.838-.197-1.539-1.118l1.07-3.292a1 1 0 00-.364-1.118L2.98 8.72c-.783-.57-.38-1.81.588-1.81h3.461a1 1 0 00.951-.69l1.07-3.292z"/></svg>
                                            <span>{{ $prompt->is_featured ? 'Unfeature' : 'Feature' }}</span>
                                        </button>
                                    </form>

                                    @if ($prompt->status !== 'approved')
                                        <form method="POST" action="{{ route('admin.prompts.approve', $prompt) }}" style="display: inline-flex; margin: 0;">
                                            @csrf
                                            @method('PATCH')
                                            <button class="admin-btn-action admin-btn-approve" type="submit" title="Approve prompt">
                                                <svg viewBox="0 0 20 20" fill="currentColor" style="width: 13px; height: 13px;"><path fill-rule="evenodd" d="M16.707 5.293a1 1 0 010 1.414l-8 8a1 1 0 01-1.414 0l-4-4a1 1 0 011.414-1.414L8 12.586l7.293-7.293a1 1 0 011.414 0z" clip-rule="evenodd"/></svg>
                                                <span>Approve</span>
                                            </button>
                                        </form>
                                    @endif

                                    @if ($prompt->status !== 'rejected')
                                        <form method="POST" action="{{ route('admin.prompts.reject', $prompt) }}" style="display: inline-flex; margin: 0;">
                                            @csrf
                                            @method('PATCH')
                                            <input type="hidden" name="admin_note" value="Please refine this prompt and resubmit.">
                                            <button type="submit" class="admin-btn-action admin-btn-decline" title="Decline prompt">
                                                <svg viewBox="0 0 20 20" fill="currentColor" style="width: 13px; height: 13px;"><path fill-rule="evenodd" d="M4.293 4.293a1 1 0 011.414 0L10 8.586l4.293-4.293a1 1 0 111.414 1.414L11.414 10l4.293 4.293a1 1 0 01-1.414 1.414L10 11.414l-4.293 4.293a1 1 0 01-1.414-1.414L8.586 10 4.293 5.707a1 1 0 010-1.414z" clip-rule="evenodd"/></svg>
                                                <span>Decline</span>
                                            </button>
                                        </form>
                                    @endif

                                    <form method="POST" action="{{ route('admin.prompts.destroy', $prompt) }}" style="display: inline-flex; margin: 0;" onsubmit="return confirm('Permanently delete this prompt?')">
                                        @csrf
                                        @method('DELETE')
                                        <button class="admin-btn-action admin-btn-delete" type="submit" title="Permanently delete prompt">
                                            <svg viewBox="0 0 20 20" fill="currentColor" style="width: 13px; height: 13px;"><path fill-rule="evenodd" d="M9 2a1 1 0 00-.894.553L7.382 4H4a1 1 0 000 2v10a2 2 0 002 2h8a2 2 0 002-2V6a1 1 0 100-2h-3.382l-.724-1.447A1 1 0 0011 2H9zM7 8a1 1 0 012 0v6a1 1 0 11-2 0V8zm5-1a1 1 0 00-1 1v6a1 1 0 102 0V8a1 1 0 00-1-1z" clip-rule="evenodd"/></svg>
                                            <span>Delete</span>
                                        </button>
                                    </form>
                                </div>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="7" class="table-empty">
                                No prompts match your search and filter criteria.
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>

    <div class="pagination-wrap" style="margin-top: 20px;">
        {{ $prompts->links() }}
    </div>
</div>
@endsection
