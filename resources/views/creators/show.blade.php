@extends('layouts.app')

@section('title', $user->name . ' (' . $user->handle . ') — Creator Profile')
@section('meta_description', $user->bio ?: 'Discover AI prompt engineering by ' . $user->name . ' on ' . ($directorySettings['site_name'] ?? 'PromptImage') . '.')
@section('eyebrow', 'CREATOR SPOTLIGHT')
@section('toolbar-title', $user->name)

@section('content')
<div class="workspace-page creator-profile-page">
    <a class="back-link" href="{{ route('prompts.index') }}"><span>&larr;</span> Back to prompts gallery</a>

    <!-- Creator Profile Header Card -->
    <div class="panel-card creator-hero-card">
        <div class="creator-hero-layout">
            <!-- Left: Avatar & Identity -->
            <div class="creator-identity-group">
                <div class="creator-avatar-wrap">
                    @if ($user->avatar_url)
                        <img src="{{ $user->avatar_url }}" alt="{{ $user->name }}" class="creator-avatar-img" onerror="this.style.display='none'; if(this.nextElementSibling){this.nextElementSibling.style.display='grid';}">
                        <div class="avatar creator-avatar-fallback" style="display: none;">
                            {{ str($user->name)->substr(0, 1)->upper() }}
                        </div>
                    @else
                        <div class="avatar creator-avatar-fallback">
                            {{ str($user->name)->substr(0, 1)->upper() }}
                        </div>
                    @endif
                </div>

                <div class="creator-details">
                    <div class="creator-name-row">
                        <h1 class="creator-title">{{ $user->name }}</h1>
                        @if ($user->is_verified)
                            <span title="Verified Creator" class="creator-verified-badge" aria-label="Verified Creator">✓</span>
                        @endif
                        <span class="creator-role-badge {{ $user->isAdmin() ? 'is-admin' : '' }}">
                            {{ $user->isAdmin() ? 'Administrator' : ($user->is_verified ? 'Verified Creator' : 'Creator') }}
                        </span>
                    </div>

                    <div class="creator-meta-chips">
                        <span class="creator-handle-chip">{{ $user->handle }}</span>
                        <button type="button" onclick="navigator.clipboard.writeText('{{ route('creators.show', $user) }}'); const el = this.querySelector('.copy-label'); if(el) { el.textContent = 'Copied!'; setTimeout(() => el.textContent = 'Copy link', 2000); }" class="creator-copy-link" title="Copy profile URL">
                            <span>⧉</span> <span class="copy-label">Copy link</span>
                        </button>
                        <span class="meta-separator">&bull;</span>
                        <span class="creator-since">Member since {{ $user->created_at->format('M Y') }}</span>
                        @if ($user->website)
                            <span class="meta-separator">&bull;</span>
                            <a href="{{ $user->website }}" target="_blank" rel="noopener noreferrer" class="creator-website-link">
                                {{ parse_url($user->website, PHP_URL_HOST) ?: 'Portfolio' }} <span>↗</span>
                            </a>
                        @endif
                    </div>

                    @if ($user->bio)
                        <p class="creator-bio">{{ $user->bio }}</p>
                    @endif
                </div>
            </div>

            <!-- Right: Stats & Action Buttons -->
            <div class="creator-side-panel">
                <div class="creator-stats-panel">
                    <div class="creator-stat-item">
                        <strong>{{ number_format($user->approved_prompts_count ?? 0) }}</strong>
                        <span>Prompts</span>
                    </div>
                    <div class="creator-stat-divider"></div>
                    <div class="creator-stat-item">
                        <strong class="likes-stat">♥ {{ number_format($totalLikes) }}</strong>
                        <span>Likes</span>
                    </div>
                    <div class="creator-stat-divider"></div>
                    <div class="creator-stat-item">
                        <strong>{{ number_format($user->followers_count ?? 0) }}</strong>
                        <span>Followers</span>
                    </div>
                </div>

                <div class="creator-hero-actions">
                    @auth
                        @if (auth()->id() === $user->id)
                            <a href="{{ route('dashboard.profile.edit') }}" class="primary-button" style="text-decoration: none;">
                                Edit profile <span>⚙</span>
                            </a>
                        @else
                            <form method="POST" action="{{ route('creators.follow', $user) }}" style="display: inline-flex; width: 100%;">
                                @csrf
                                <button type="submit" class="{{ $isFollowing ? 'outline-button' : 'primary-button' }}" style="width: 100%; min-height: 38px; justify-content: center; font-weight: 600;">
                                    {{ $isFollowing ? '✓ Following' : '+ Follow creator' }}
                                </button>
                            </form>
                            @if (auth()->user()->isAdmin())
                                <a href="{{ route('admin.users.edit', $user) }}" class="outline-button" style="text-decoration: none;">
                                    Admin edit <span>⚙</span>
                                </a>
                            @endif
                        @endif
                    @else
                        <a href="{{ route('login') }}" class="primary-button" style="min-height: 38px; padding: 0 16px; font-weight: 600; text-decoration: none;">
                            + Follow creator
                        </a>
                    @endauth
                </div>
            </div>
        </div>
    </div>

    <!-- Creator Submissions Header & Filters -->
    <div class="creator-filter-toolbar">
        <div>
            <h2 class="creator-section-heading">
                Prompts by {{ $user->name }}
                <span>({{ $prompts->total() }})</span>
            </h2>
        </div>

        <!-- Filter & Sort Bar -->
        <div class="creator-filter-controls">
            @if ($categories->isNotEmpty())
                <div class="creator-category-scroll">
                    <a href="{{ route('creators.show', $user) }}" class="creator-category-chip {{ !request('category') ? 'is-active' : '' }}">
                        All ({{ $user->approved_prompts_count }})
                    </a>
                    @foreach ($categories as $cat)
                        <a href="{{ route('creators.show', [$user, 'category' => $cat->slug]) }}" class="creator-category-chip {{ request('category') === $cat->slug ? 'is-active' : '' }}">
                            <span>{{ $cat->icon }}</span> {{ $cat->name }} ({{ $cat->count }})
                        </a>
                    @endforeach
                </div>
            @endif

            <form method="GET" action="{{ route('creators.show', $user) }}" class="creator-sort-form">
                @if(request('category'))<input type="hidden" name="category" value="{{ request('category') }}">@endif
                <select name="sort" onchange="this.form.submit()" class="creator-sort-select">
                    <option value="newest" @selected(request('sort', 'newest') === 'newest')>Newest first</option>
                    <option value="popular" @selected(request('sort') === 'popular')>Most popular</option>
                    <option value="oldest" @selected(request('sort') === 'oldest')>Oldest first</option>
                </select>
            </form>
        </div>
    </div>

    <!-- Prompts Masonry / Grid -->
    @if ($prompts->isEmpty())
        <div class="empty-panel panel-card" style="padding: 28px 24px; display: flex; align-items: center; justify-content: center; gap: 12px; border-radius: 14px; background: #ffffff; border: 1px solid #ebebf0; flex-wrap: wrap;">
            <span style="display: inline-flex; width: 34px; height: 34px; place-items: center; justify-content: center; align-items: center; border-radius: 50%; background: #f4f1ff; color: #7958ee; font-size: 16px; flex-shrink: 0;">✦</span>
            <div style="display: inline-flex; align-items: center; gap: 6px; flex-wrap: wrap; font-size: 13.5px; color: #52525b;">
                <strong style="color: #18181b; font-weight: 600;">No prompts found.</strong>
                <span>
                    @if (request('category'))
                        No prompts found in this category for {{ $user->name }}. <a href="{{ route('creators.show', $user) }}" style="color: #7958ee; font-weight: 600;">Clear filter</a>
                    @else
                        This creator has not published any public prompts yet. Check back soon!
                    @endif
                </span>
            </div>
        </div>
    @else
        <div class="prompt-grid" style="margin-bottom: 24px;">
            @foreach ($prompts as $prompt)
                @include('partials.prompt-card', ['prompt' => $prompt])
            @endforeach
        </div>

        <div style="display: flex; justify-content: center; margin-top: 32px;">
            {{ $prompts->links() }}
        </div>
    @endif
</div>
@endsection
