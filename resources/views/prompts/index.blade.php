@extends('layouts.app')
@section('title', 'AI Prompt Gallery')
@section('toolbar')
    <div class="gallery-toolbar">
        <nav class="model-tabs" aria-label="Filter prompts by model">
            <a class="model-tab {{ !request('model') || request('model') === 'All' ? 'is-active' : '' }}" href="{{ route('prompts.index', request()->except('model')) }}">All</a>
            @foreach ($models as $model)
                <a class="model-tab" href="{{ route('prompts.index', request()->except('model') + ['model' => $model]) }}" aria-current="{{ request('model') === $model ? 'page' : 'false' }}">
                    <span class="model-glyph glyph-{{ \Illuminate\Support\Str::slug($model) }}">{{ $modelIcons[$model] ?? '◈' }}</span>{{ $model }}
                </a>
            @endforeach
        </nav>
        <form class="toolbar-search" id="prompt-search" action="{{ route('prompts.index') }}" method="GET">
            @foreach (request()->except(['q', 'page', 'focus']) as $key => $value)<input type="hidden" name="{{ $key }}" value="{{ $value }}">@endforeach
            <span class="search-icon">⌕</span><input type="search" name="q" value="{{ request('q') }}" placeholder="Search prompts…" aria-label="Search prompts">
            <button type="submit" aria-label="Submit search">↵</button>
        </form>
        <nav class="sort-tabs" aria-label="Sort prompts">
            @foreach (['random' => 'Discover', 'featured' => 'Featured', 'newest' => 'Newest', 'popular' => 'Popular'] as $value => $label)
                <a class="sort-tab {{ $activeSort === $value ? 'is-active' : '' }}" href="{{ route('prompts.index', request()->except('sort', 'page') + ($value === 'random' ? [] : ['sort' => $value])) }}">{{ $label }}</a>
            @endforeach
        </nav>
    </div>
@endsection
@section('content')
<div class="gallery-page">
    @if (request('category') || request('q') || request('model'))
        <div class="filter-line">
            <div><span class="section-kicker">THE PROMPT LIBRARY</span><strong>{{ request('q') ? 'Results for “'.request('q').'”' : ($categories->firstWhere('slug', request('category'))?->name ?? request('model', 'All prompts')) }}</strong></div>
            <a href="{{ route('prompts.index') }}">Clear filters <span>×</span></a>
        </div>
    @endif

    @if (!request('category') && !request('q') && !request('model') && !request('page') && isset($featuredPrompts) && $featuredPrompts->isNotEmpty())
        @section('preload')
            <link rel="preload" as="image" href="{{ $featuredPrompts->first()->cover_url }}" fetchpriority="high">
        @endsection

        <!-- Prompt of the Day / Featured Carousel -->
        <section class="hero-carousel" x-data="{
            currentIndex: 0,
            isPaused: false,
            total: {{ $featuredPrompts->count() }},
            touchStartX: 0,
            touchEndX: 0,
            touchStartY: 0,
            touchEndY: 0,
            timer: null,
            init() {
                this.timer = setInterval(() => {
                    if (!this.isPaused) {
                        this.next();
                    }
                }, 6500);
            },
            next() {
                this.currentIndex = (this.currentIndex + 1) % this.total;
            },
            prev() {
                this.currentIndex = (this.currentIndex - 1 + this.total) % this.total;
            },
            handleTouchStart(e) {
                if (!e.changedTouches || !e.changedTouches[0]) return;
                this.touchStartX = e.changedTouches[0].screenX;
                this.touchStartY = e.changedTouches[0].screenY;
                this.isPaused = true;
            },
            handleTouchEnd(e) {
                if (!e.changedTouches || !e.changedTouches[0]) return;
                this.touchEndX = e.changedTouches[0].screenX;
                this.touchEndY = e.changedTouches[0].screenY;
                this.isPaused = false;
                const diffX = this.touchStartX - this.touchEndX;
                const diffY = this.touchStartY - this.touchEndY;
                if (Math.abs(diffX) > Math.abs(diffY) && Math.abs(diffX) > 35) {
                    if (diffX > 0) {
                        this.next();
                    } else {
                        this.prev();
                    }
                }
            }
        }"
        @mouseenter="isPaused = true"
        @mouseleave="isPaused = false"
        @touchstart.passive="handleTouchStart($event)"
        @touchend.passive="handleTouchEnd($event)">
            <div class="carousel-stage">
                @foreach ($featuredPrompts as $idx => $slide)
                    @php
                        $authorName = $slide->user?->username ? '@' . $slide->user->username : ($slide->user?->name ?? (($directorySettings['site_name'] ?? 'Prompt') . ' Community'));
                        $authorUrl = $slide->user ? route('creators.show', $slide->user) : null;
                        $authorAvatar = strtoupper(substr($slide->user?->username ?? $slide->user?->name ?? 'P', 0, 1));
                        $discordCopy = '/imagine prompt: ' . addslashes(str_replace(["\r", "\n"], ' ', $slide->prompt_text)) . ' --ar 16:9 --v 6.1';
                    @endphp
                    <div x-show="currentIndex === {{ $idx }}"
                         @if ($idx > 0) x-cloak @endif
                         x-transition:enter="transition ease-out duration-300"
                         x-transition:enter-start="opacity-0 scale-[0.99]"
                         x-transition:enter-end="opacity-100 scale-100"
                         x-transition:leave="transition ease-in duration-200"
                         x-transition:leave-start="opacity-100 scale-100"
                         x-transition:leave-end="opacity-0 scale-[0.99]"
                         class="carousel-slide {{ $idx === 0 ? 'is-first-slide' : '' }}">
                        <!-- Left: Info & Meta & Action Buttons -->
                        <div class="carousel-info">
                            <div>
                                <div class="carousel-badge-row">
                                    <span class="carousel-tag-potd">
                                        <span>✦</span> PROMPT OF THE DAY
                                    </span>
                                    <span class="carousel-pill">
                                        <span>{{ $slide->category->icon }}</span>
                                        <span>{{ $slide->category->name }}</span>
                                    </span>
                                    <span class="carousel-pill">{{ $slide->model }}</span>
                                    <a href="{{ $authorUrl ?? '#' }}" class="carousel-author" style="text-decoration: none; color: #ffffff;">
                                        <span class="avatar avatar-tiny">{{ $authorAvatar }}</span>
                                        <span style="color: #ffffff; font-weight: 600;">{{ $authorName }}</span>
                                        @if ($slide->user?->is_verified)
                                            <span style="display:inline-flex;align-items:center;justify-content:center;width:13px;height:13px;border-radius:50%;background:linear-gradient(135deg,#8b5cf6,#6366f1);color:#fff;font-size:8px;font-weight:700;">✓</span>
                                        @endif
                                    </a>
                                </div>

                                <h2 class="carousel-title">
                                    <a href="{{ route('prompts.show', $slide->slug) }}">{{ $slide->title }}</a>
                                </h2>

                                <div class="carousel-prompt-preview">{{ $slide->prompt_text }}</div>
                            </div>

                            <div class="carousel-actions">
                                <a href="{{ route('prompts.show', $slide->slug) }}" class="carousel-btn-primary">
                                    View prompt <span>↗</span>
                                </a>
                                <button type="button" data-copy="{{ $slide->prompt_text }}" class="carousel-btn-glass" title="Copy prompt">
                                    Copy <span>⧉</span>
                                </button>
                                <button type="button" data-discord-copy="{{ $discordCopy }}" class="carousel-btn-glass carousel-btn-discord" title="Copy Discord /imagine prompt">
                                    /mj
                                </button>
                                <span class="carousel-likes">
                                    <span style="color:#f43f5e;">♥</span>
                                    <span>{{ number_format($slide->likes_count) }}</span>
                                </span>
                            </div>
                        </div>

                        <!-- Right: Cover Image Showcase -->
                        <div class="carousel-visual">
                            @if ($idx === 0)
                                <img src="{{ $slide->cover_url }}" alt="{{ $slide->title }}" fetchpriority="high" loading="eager" decoding="async" width="680" height="420">
                            @else
                                <img src="{{ $slide->cover_url }}" alt="{{ $slide->title }}" loading="lazy" decoding="async" width="680" height="420">
                            @endif
                            <div class="carousel-visual-scrim"></div>
                            <span class="carousel-visual-chip">{{ $slide->model }}</span>
                            <a href="{{ route('prompts.show', $slide->slug) }}" style="position: absolute; inset: 0; z-index: 1;" aria-label="View prompt detail"></a>
                        </div>
                    </div>
                @endforeach
            </div>

            <!-- Carousel Controls -->
            <div class="carousel-controls">
                <button type="button" @click="prev()" class="carousel-nav-btn" aria-label="Previous slide">‹</button>
                <div class="carousel-dots">
                    @foreach ($featuredPrompts as $idx => $slide)
                        <button type="button"
                                @click="currentIndex = {{ $idx }}"
                                class="carousel-dot"
                                :style="currentIndex === {{ $idx }} ? 'background: #ffffff; width: 16px;' : 'background: rgba(255,255,255,0.3); width: 5px;'"
                                aria-label="Go to slide {{ $idx + 1 }}"></button>
                    @endforeach
                </div>
                <span class="carousel-counter" x-text="(currentIndex + 1) + ' / ' + total"></span>
                <button type="button" @click="next()" class="carousel-nav-btn" aria-label="Next slide">›</button>
            </div>
        </section>
    @endif

    <h1 class="sr-only">Free AI prompts gallery</h1>

    @if ($prompts->isEmpty())
        <div class="empty-state"><span>✦</span><h2>No prompts found just yet</h2><p>Try a different category or search term. There’s always something new to discover.</p><a class="primary-button" href="{{ route('prompts.index') }}">Explore all prompts <span>→</span></a></div>
    @else
        <div class="prompt-grid">
            @foreach ($prompts as $prompt)
                @include('partials.prompt-card', ['prompt' => $prompt, 'priority' => $loop->index < 4])
            @endforeach
        </div>
        <div class="pagination-wrap">{{ $prompts->links() }}</div>
    @endif
</div>
@endsection
