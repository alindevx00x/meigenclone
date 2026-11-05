@extends('layouts.app')

@section('title', $user->name . ' (@' . str($user->name)->slug('_') . ') — Creator Profile')
@section('meta_description', $user->bio ?: 'Discover AI prompt engineering by ' . $user->name . ' on MeiGen.')
@section('eyebrow', 'CREATOR SPOTLIGHT')
@section('toolbar-title', $user->name)

@section('content')
<div class="workspace-page creator-profile-page" style="max-width: 1240px; margin: 0 auto; padding: 24px 20px 60px;">
    <a class="back-link" href="{{ route('prompts.index') }}"><span>&larr;</span> Back to prompts gallery</a>

    <!-- Creator Profile Header Card -->
    <div class="panel-card creator-hero-card" style="padding: 28px 32px; border-radius: 18px; background: #ffffff; border: 1px solid #ebebf0; box-shadow: 0 4px 20px rgba(0,0,0,0.03); margin-bottom: 28px;">
        <div style="display: flex; align-items: flex-start; justify-content: space-between; gap: 24px; flex-wrap: wrap;">
            <!-- Left: Avatar & Identity -->
            <div style="display: flex; gap: 20px; align-items: flex-start; flex: 1 1 450px;">
                <div style="flex-shrink: 0;">
                    @if ($user->avatar_url)
                        <img src="{{ $user->avatar_url }}" alt="{{ $user->name }}" style="width: 84px; height: 84px; border-radius: 50%; object-fit: cover; border: 3px solid #ffffff; box-shadow: 0 4px 14px rgba(121, 88, 238, 0.15);">
                    @else
                        <div class="avatar" style="width: 84px; height: 84px; font-size: 32px; font-weight: 700; border-radius: 50%; background: linear-gradient(135deg, #ede9ff, #f3efff); color: #654bd1; border: 3px solid #ffffff; box-shadow: 0 4px 14px rgba(121, 88, 238, 0.12);">
                            {{ str($user->name)->substr(0, 1)->upper() }}
                        </div>
                    @endif
                </div>

                <div style="flex: 1; min-width: 0;">
                    <div style="display: flex; align-items: center; gap: 8px; flex-wrap: wrap;">
                        <h1 style="font-size: 24px; font-weight: 700; color: #17171b; margin: 0; line-height: 1.2;">
                            {{ $user->name }}
                        </h1>
                        @if ($user->is_verified)
                            <span title="Verified Creator" style="display: inline-flex; align-items: center; justify-content: center; width: 20px; height: 20px; border-radius: 50%; background: linear-gradient(135deg, #8b5cf6, #6366f1); color: #ffffff; font-size: 11px; font-weight: 700;" aria-label="Verified Creator">✓</span>
                        @endif
                        <span style="display: inline-block; padding: 2px 8px; border-radius: 99px; background: {{ $user->isAdmin() ? '#f2efff' : '#f4f4f6' }}; color: {{ $user->isAdmin() ? '#6d52d9' : '#52525c' }}; font-size: 11px; font-weight: 600;">
                            {{ $user->isAdmin() ? 'Administrator' : ($user->is_verified ? 'Verified Creator' : 'Creator') }}
                        </span>
                    </div>

                    <div style="display: flex; align-items: center; gap: 12px; margin-top: 6px; font-size: 13px; color: #52525c; flex-wrap: wrap;">
                        <span style="font-weight: 500; color: #71717a;">{{ '@'.str($user->name)->slug('_') }}</span>
                        <span>&bull;</span>
                        <span>Member since {{ $user->created_at->format('M Y') }}</span>
                        @if ($user->website)
                            <span>&bull;</span>
                            <a href="{{ $user->website }}" target="_blank" rel="noopener noreferrer" style="color: #7958ee; font-weight: 600; text-decoration: none; display: inline-flex; align-items: center; gap: 3px;">
                                {{ parse_url($user->website, PHP_URL_HOST) ?: 'Portfolio' }} <span>↗</span>
                            </a>
                        @endif
                    </div>

                    @if ($user->bio)
                        <p style="margin: 12px 0 0; font-size: 14px; line-height: 1.6; color: #3f3f46; max-width: 650px;">
                            {{ $user->bio }}
                        </p>
                    @endif
                </div>
            </div>

            <!-- Right: Stats & Action Buttons -->
            <div style="display: flex; flex-direction: column; align-items: flex-end; gap: 16px; flex-shrink: 0;">
                <div style="display: flex; align-items: center; gap: 20px; background: #fafafc; padding: 12px 20px; border-radius: 12px; border: 1px solid #eeeeef;">
                    <div style="text-align: center;">
                        <strong style="display: block; font-size: 20px; font-weight: 700; color: #18181b;">{{ number_format($user->approved_prompts_count ?? 0) }}</strong>
                        <span style="font-size: 11.5px; color: #64646e; font-weight: 500;">Prompts</span>
                    </div>
                    <div style="width: 1px; height: 28px; background: #e4e4e7;"></div>
                    <div style="text-align: center;">
                        <strong style="display: block; font-size: 20px; font-weight: 700; color: #e11d48;">♥ {{ number_format($totalLikes) }}</strong>
                        <span style="font-size: 11.5px; color: #64646e; font-weight: 500;">Likes</span>
                    </div>
                    <div style="width: 1px; height: 28px; background: #e4e4e7;"></div>
                    <div style="text-align: center;">
                        <strong style="display: block; font-size: 20px; font-weight: 700; color: #18181b;">{{ number_format($user->followers_count ?? 0) }}</strong>
                        <span style="font-size: 11.5px; color: #64646e; font-weight: 500;">Followers</span>
                    </div>
                </div>

                <div style="display: flex; align-items: center; gap: 8px; flex-wrap: wrap;">
                    @auth
                        @if (auth()->id() === $user->id)
                            <a href="{{ route('dashboard.profile.edit') }}" class="primary-button" style="text-decoration: none;">
                                Edit profile <span>⚙</span>
                            </a>
                        @else
                            <form method="POST" action="{{ route('creators.follow', $user) }}" style="display: inline;">
                                @csrf
                                <button type="submit" class="{{ $isFollowing ? 'outline-button' : 'primary-button' }}" style="min-height: 36px; padding: 0 16px; font-weight: 600;">
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
                        <a href="{{ route('login') }}" class="primary-button" style="min-height: 36px; padding: 0 16px; font-weight: 600; text-decoration: none;">
                            + Follow creator
                        </a>
                    @endauth
                </div>
            </div>
        </div>
    </div>

    <!-- Creator Submissions Header & Filters -->
    <div style="display: flex; align-items: center; justify-content: space-between; gap: 16px; margin-bottom: 20px; flex-wrap: wrap;">
        <div>
            <h2 style="font-size: 20px; font-weight: 700; color: #17171b; margin: 0;">
                Prompts by {{ $user->name }}
                <span style="font-size: 14px; font-weight: 500; color: #71717a; margin-left: 6px;">({{ $prompts->total() }})</span>
            </h2>
        </div>

        <!-- Filter & Sort Bar -->
        <div style="display: flex; align-items: center; gap: 10px; flex-wrap: wrap;">
            @if ($categories->isNotEmpty())
                <div style="display: flex; align-items: center; gap: 4px; overflow-x: auto;">
                    <a href="{{ route('creators.show', $user) }}" class="category-pill {{ !request('category') ? 'is-active' : '' }}" style="display: inline-flex; align-items: center; padding: 6px 12px; border-radius: 8px; font-size: 12px; font-weight: 600; text-decoration: none; {{ !request('category') ? 'background: #17171b; color: #ffffff;' : 'background: #f4f4f6; color: #52525c;' }}">
                        All ({{ $user->approved_prompts_count }})
                    </a>
                    @foreach ($categories as $cat)
                        <a href="{{ route('creators.show', [$user, 'category' => $cat->slug]) }}" class="category-pill {{ request('category') === $cat->slug ? 'is-active' : '' }}" style="display: inline-flex; align-items: center; gap: 5px; padding: 6px 12px; border-radius: 8px; font-size: 12px; font-weight: 600; text-decoration: none; {{ request('category') === $cat->slug ? 'background: #17171b; color: #ffffff;' : 'background: #f4f4f6; color: #52525c;' }}">
                            <span>{{ $cat->icon }}</span> {{ $cat->name }} ({{ $cat->count }})
                        </a>
                    @endforeach
                </div>
            @endif

            <form method="GET" action="{{ route('creators.show', $user) }}" style="display: inline-flex; align-items: center; margin: 0;">
                @if(request('category'))<input type="hidden" name="category" value="{{ request('category') }}">@endif
                <select name="sort" onchange="this.form.submit()" style="min-height: 34px; padding: 0 10px; border-radius: 8px; border: 1px solid #e4e4e7; font-size: 12px; color: #3f3f46; background: #ffffff;">
                    <option value="newest" @selected(request('sort', 'newest') === 'newest')>Newest first</option>
                    <option value="popular" @selected(request('sort') === 'popular')>Most popular</option>
                    <option value="oldest" @selected(request('sort') === 'oldest')>Oldest first</option>
                </select>
            </form>
        </div>
    </div>

    <!-- Prompts Masonry / Grid -->
    @if ($prompts->isEmpty())
        <div class="empty-panel panel-card" style="padding: 48px 24px; text-align: center; border-radius: 16px; background: #ffffff; border: 1px solid #ebebf0;">
            <span style="display: grid; width: 48px; height: 48px; margin: 0 auto 14px; place-items: center; border-radius: 50%; background: #f4f1ff; color: #7958ee; font-size: 24px;">✦</span>
            <strong style="font-size: 17px; color: #18181b;">No prompts found.</strong>
            <p style="margin: 6px 0 0; font-size: 13.5px; color: #64646e;">
                @if (request('category'))
                    No prompts found in this category for {{ $user->name }}. <a href="{{ route('creators.show', $user) }}" style="color: #7958ee; font-weight: 600;">Clear filter</a>
                @else
                    This creator has not published any public prompts yet. Check back soon!
                @endif
            </p>
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
