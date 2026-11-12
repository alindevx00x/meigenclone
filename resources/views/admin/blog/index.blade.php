@extends('layouts.app')
@section('title', 'Manage blog articles')
@section('eyebrow', 'ADMIN STUDIO')
@section('toolbar-title', 'Blog management')
@section('content')
<div class="workspace-page admin-page">
    <div class="page-heading list-heading" style="display: flex; justify-content: space-between; align-items: flex-start; flex-wrap: wrap; gap: 16px;">
        <div>
            <span class="section-kicker">EDITORIAL STUDIO</span>
            <h1>Blog &amp; Knowledge Base</h1>
            <p>Publish prompt engineering guides, AI model comparisons, and tutorials to drive organic search traffic.</p>
        </div>
        <div style="display: flex; gap: 10px; align-items: center; flex-wrap: wrap;">
            <a class="outline-button" href="{{ route('admin.blog.categories.index') }}" style="min-height: 38px; display: inline-flex; align-items: center; gap: 6px;">
                <span>🏷️</span> Blog Categories
            </a>
            <a class="primary-button" href="{{ route('admin.blog.create') }}" style="min-height: 38px; display: inline-flex; align-items: center; gap: 6px;">
                <span>＋</span> Write New Article
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

    <!-- Metrics Cards -->
    <div style="display: grid; grid-template-columns: repeat(auto-fit, minmax(180px, 1fr)); gap: 12px; margin-bottom: 20px;">
        <div class="panel-card" style="padding: 14px 18px;">
            <div style="font-size: 11px; text-transform: uppercase; font-weight: 600; color: #64748b; letter-spacing: 0.04em;">Total Articles</div>
            <div style="font-size: 24px; font-weight: 700; color: #0f172a; margin-top: 4px;">{{ number_format($stats['total']) }}</div>
        </div>
        <div class="panel-card" style="padding: 14px 18px;">
            <div style="font-size: 11px; text-transform: uppercase; font-weight: 600; color: #16a34a; letter-spacing: 0.04em;">Published</div>
            <div style="font-size: 24px; font-weight: 700; color: #16a34a; margin-top: 4px;">{{ number_format($stats['published']) }}</div>
        </div>
        <div class="panel-card" style="padding: 14px 18px;">
            <div style="font-size: 11px; text-transform: uppercase; font-weight: 600; color: #f59e0b; letter-spacing: 0.04em;">Drafts</div>
            <div style="font-size: 24px; font-weight: 700; color: #f59e0b; margin-top: 4px;">{{ number_format($stats['draft']) }}</div>
        </div>
        <div class="panel-card" style="padding: 14px 18px;">
            <div style="font-size: 11px; text-transform: uppercase; font-weight: 600; color: #6366f1; letter-spacing: 0.04em;">Total Article Views</div>
            <div style="font-size: 24px; font-weight: 700; color: #6366f1; margin-top: 4px;">{{ number_format($stats['views']) }}</div>
        </div>
    </div>

    <!-- Filters Bar -->
    <form class="panel-card" method="GET" action="{{ route('admin.blog.index') }}" style="margin-bottom: 20px; display: flex; flex-wrap: wrap; gap: 10px; align-items: center;">
        <input type="search" name="q" value="{{ request('q') }}" placeholder="Search articles by title or content…" class="table-filter-input" style="flex: 1 1 240px; min-height: 38px;">
        
        <select name="category" class="table-filter-select" style="min-height: 38px; width: 170px;">
            <option value="">All Categories</option>
            @foreach ($categories as $cat)
                <option value="{{ $cat->id }}" @selected(request('category') == $cat->id)>{{ $cat->name }}</option>
            @endforeach
        </select>

        <select name="status" class="table-filter-select" style="min-height: 38px; width: 140px;">
            <option value="all">Status: All</option>
            <option value="published" @selected(request('status') === 'published')>Published</option>
            <option value="draft" @selected(request('status') === 'draft')>Drafts</option>
        </select>

        <button class="primary-button" type="submit" style="min-height: 38px; padding: 0 16px;">Filter <span>→</span></button>
        @if (request()->hasAny(['q', 'category', 'status']))
            <a class="outline-button" href="{{ route('admin.blog.index') }}" style="min-height: 38px; display: inline-flex; align-items: center;">Clear</a>
        @endif
    </form>

    <!-- Articles List Table -->
    <div class="panel-card" style="padding: 0; overflow: hidden;">
        <div style="overflow-x: auto;">
            <table class="admin-table" style="width: 100%; border-collapse: collapse; text-align: left; font-size: 13.5px;">
                <thead>
                    <tr style="background: #f8fafc; border-bottom: 1.5px solid #e2e8f0; color: #475569; font-size: 11.5px; text-transform: uppercase; letter-spacing: 0.05em;">
                        <th style="padding: 12px 16px; width: 64px;">Cover</th>
                        <th style="padding: 12px 16px;">Article</th>
                        <th style="padding: 12px 16px; width: 140px;">Category</th>
                        <th style="padding: 12px 16px; width: 90px; text-align: center;">Views</th>
                        <th style="padding: 12px 16px; width: 110px;">Status</th>
                        <th style="padding: 12px 16px; width: 120px;">Published</th>
                        <th style="padding: 12px 16px; width: 140px; text-align: right;">Actions</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse ($posts as $post)
                        <tr style="border-bottom: 1px solid #f1f5f9; transition: background 0.15s ease;">
                            <td style="padding: 12px 16px;">
                                @if ($post->cover_image_url)
                                    <img src="{{ $post->cover_image_url }}" alt="{{ $post->title }}" style="width: 52px; height: 38px; object-fit: cover; border-radius: 6px; border: 1px solid #e2e8f0; display: block;">
                                @else
                                    <div style="width: 52px; height: 38px; background: #f1f5f9; border-radius: 6px; display: grid; place-items: center; color: #94a3b8; font-size: 11px; border: 1px dashed #cbd5e1;">No Img</div>
                                @endif
                            </td>
                            <td style="padding: 12px 16px;">
                                <div style="font-weight: 600; color: #0f172a; margin-bottom: 2px;">
                                    <a href="{{ route('admin.blog.edit', $post) }}" style="color: inherit; text-decoration: none;" onmouseover="this.style.color='#6366f1'" onmouseout="this.style.color='inherit'">
                                        {{ $post->title }}
                                    </a>
                                </div>
                                <div style="font-size: 11.5px; color: #64748b; display: flex; gap: 8px; align-items: center;">
                                    <span>/blog/{{ $post->slug }}</span>
                                    <span>&bull;</span>
                                    <span>⏱ {{ $post->reading_time_minutes }} min read</span>
                                </div>
                            </td>
                            <td style="padding: 12px 16px;">
                                @if ($post->category)
                                    <span style="display: inline-block; padding: 2px 8px; background: #eef2ff; color: #4338ca; border-radius: 4px; font-size: 11px; font-weight: 600;">
                                        {{ $post->category->name }}
                                    </span>
                                @else
                                    <span style="color: #94a3b8; font-size: 11px;">Uncategorized</span>
                                @endif
                            </td>
                            <td style="padding: 12px 16px; text-align: center; font-weight: 600; color: #334155;">
                                {{ number_format($post->views_count) }}
                            </td>
                            <td style="padding: 12px 16px;">
                                @if ($post->status === 'published')
                                    <span style="display: inline-flex; align-items: center; gap: 5px; padding: 2px 8px; background: #dcfce7; color: #15803d; border-radius: 4px; font-size: 11px; font-weight: 600;">
                                        <span style="width: 6px; height: 6px; border-radius: 50%; background: #16a34a;"></span> Published
                                    </span>
                                @else
                                    <span style="display: inline-flex; align-items: center; gap: 5px; padding: 2px 8px; background: #fef3c7; color: #b45309; border-radius: 4px; font-size: 11px; font-weight: 600;">
                                        <span style="width: 6px; height: 6px; border-radius: 50%; background: #d97706;"></span> Draft
                                    </span>
                                @endif
                            </td>
                            <td style="padding: 12px 16px; font-size: 12px; color: #64748b;">
                                {{ $post->published_at ? $post->published_at->format('M d, Y') : '—' }}
                            </td>
                            <td style="padding: 12px 16px; text-align: right;">
                                <div style="display: inline-flex; gap: 6px; align-items: center;">
                                    @if ($post->status === 'published')
                                        <a href="{{ route('blog.show', $post->slug) }}" target="_blank" class="outline-button" style="padding: 4px 8px; font-size: 11.5px;" title="View Live">
                                            👁
                                        </a>
                                    @endif
                                    <a href="{{ route('admin.blog.edit', $post) }}" class="outline-button" style="padding: 4px 10px; font-size: 11.5px;">
                                        Edit
                                    </a>
                                    <form method="POST" action="{{ route('admin.blog.destroy', $post) }}" onsubmit="return confirm('Delete this article permanently?');" style="display: inline;">
                                        @csrf
                                        @method('DELETE')
                                        <button type="submit" class="icon-delete" style="padding: 4px 8px; font-size: 13px;" title="Delete post">×</button>
                                    </form>
                                </div>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="7" style="padding: 36px 16px; text-align: center; color: #64748b;">
                                <p style="font-size: 15px; font-weight: 500; margin-bottom: 8px;">No blog articles found.</p>
                                <a href="{{ route('admin.blog.create') }}" class="primary-button" style="display: inline-flex; align-items: center; gap: 6px; font-size: 13px;">
                                    <span>＋</span> Write your first article
                                </a>
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>

    <div style="margin-top: 20px;">
        {{ $posts->links() }}
    </div>
</div>
@endsection
