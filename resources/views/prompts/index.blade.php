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
            @foreach (['featured' => 'Featured', 'newest' => 'Newest', 'popular' => 'Popular'] as $value => $label)
                <a class="sort-tab {{ $activeSort === $value ? 'is-active' : '' }}" href="{{ route('prompts.index', request()->except('sort', 'page') + ['sort' => $value]) }}">{{ $label }}</a>
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
        <!-- Prompt of the Day / Featured Carousel -->
        <section class="hero-carousel" x-data="{
            currentIndex: 0,
            isPaused: false,
            slides: {{ json_encode($featuredPrompts->map(fn($p) => [
                'id' => $p->id,
                'slug' => $p->slug,
                'title' => $p->title,
                'prompt_text' => $p->prompt_text,
                'cover_url' => $p->cover_url,
                'category' => $p->category->name,
                'category_icon' => $p->category->icon,
                'model' => $p->model,
                'author' => $p->user?->username ? '@' . $p->user->username : ($p->user?->name ?? (($directorySettings['site_name'] ?? 'Prompt') . ' Community')),
                'author_url' => $p->user ? route('creators.show', $p->user) : null,
                'author_verified' => (bool) ($p->user?->is_verified ?? false),
                'author_avatar' => strtoupper(substr($p->user?->username ?? $p->user?->name ?? 'P', 0, 1)),
                'likes' => number_format($p->likes_count),
                'url' => route('prompts.show', $p->slug),
                'discord_copy' => '/imagine prompt: ' . addslashes(str_replace(["\r", "\n"], ' ', $p->prompt_text)) . ' --ar 16:9 --v 6.1',
            ])) }},
            timer: null,
            init() {
                this.timer = setInterval(() => {
                    if (!this.isPaused) {
                        this.next();
                    }
                }, 6500);
            },
            next() {
                this.currentIndex = (this.currentIndex + 1) % this.slides.length;
            },
            prev() {
                this.currentIndex = (this.currentIndex - 1 + this.slides.length) % this.slides.length;
            }
        }" @mouseenter="isPaused = true" @mouseleave="isPaused = false">
            <div class="carousel-stage">
                <template x-for="(slide, idx) in slides" :key="slide.id">
                    <div x-show="currentIndex === idx"
                         x-transition:enter="transition ease-out duration-300"
                         x-transition:enter-start="opacity-0 scale-[0.99]"
                         x-transition:enter-end="opacity-100 scale-100"
                         x-transition:leave="transition ease-in duration-200"
                         x-transition:leave-start="opacity-100 scale-100"
                         x-transition:leave-end="opacity-0 scale-[0.99]"
                         class="carousel-slide">
                        <!-- Left: Info & Meta & Action Buttons -->
                        <div class="carousel-info">
                            <div>
                                <div class="carousel-badge-row">
                                    <span class="carousel-tag-potd">
                                        <span>✦</span> PROMPT OF THE DAY
                                    </span>
                                    <span class="carousel-pill">
                                        <span x-text="slide.category_icon"></span>
                                        <span x-text="slide.category"></span>
                                    </span>
                                    <span class="carousel-pill" x-text="slide.model"></span>
                                    <a :href="slide.author_url || '#'" class="carousel-author" style="text-decoration: none; color: #ffffff;">
                                        <span class="avatar avatar-tiny" x-text="slide.author_avatar"></span>
                                        <span style="color: #ffffff; font-weight: 600;" x-text="slide.author"></span>
                                        <template x-if="slide.author_verified">
                                            <span style="display:inline-flex;align-items:center;justify-content:center;width:13px;height:13px;border-radius:50%;background:linear-gradient(135deg,#8b5cf6,#6366f1);color:#fff;font-size:8px;font-weight:700;">✓</span>
                                        </template>
                                    </a>
                                </div>

                                <h2 class="carousel-title">
                                    <a :href="slide.url" x-text="slide.title"></a>
                                </h2>

                                <div class="carousel-prompt-preview" x-text="slide.prompt_text"></div>
                            </div>

                            <div class="carousel-actions">
                                <a :href="slide.url" class="carousel-btn-primary">
                                    View prompt <span>↗</span>
                                </a>
                                <button type="button" :data-copy="slide.prompt_text" class="carousel-btn-glass" title="Copy prompt">
                                    Copy <span>⧉</span>
                                </button>
                                <button type="button" :data-discord-copy="slide.discord_copy" class="carousel-btn-glass carousel-btn-discord" title="Copy Discord /imagine prompt">
                                    /mj
                                </button>
                                <span class="carousel-likes">
                                    <span style="color:#f43f5e;">♥</span>
                                    <span x-text="slide.likes"></span>
                                </span>
                            </div>
                        </div>

                        <!-- Right: High Resolution Cover Image Showcase -->
                        <div class="carousel-visual">
                            <img :src="slide.cover_url" :alt="slide.title" loading="eager">
                            <div class="carousel-visual-scrim"></div>
                            <span class="carousel-visual-chip" x-text="slide.model"></span>
                            <a :href="slide.url" style="position: absolute; inset: 0; z-index: 1;" aria-label="View prompt detail"></a>
                        </div>
                    </div>
                </template>
            </div>

            <!-- Carousel Controls -->
            <div class="carousel-controls">
                <button type="button" @click="prev()" class="carousel-nav-btn" aria-label="Previous slide">‹</button>
                <div class="carousel-dots">
                    <template x-for="(slide, idx) in slides" :key="'dot-' + slide.id">
                        <button type="button"
                                @click="currentIndex = idx"
                                class="carousel-dot"
                                :style="currentIndex === idx ? 'background: #ffffff; width: 16px;' : 'background: rgba(255,255,255,0.3); width: 5px;'"
                                :aria-label="'Go to slide ' + (idx + 1)"></button>
                    </template>
                </div>
                <span class="carousel-counter" x-text="(currentIndex + 1) + ' / ' + slides.length"></span>
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
                @include('partials.prompt-card', ['prompt' => $prompt])
            @endforeach
        </div>
        <div class="pagination-wrap">{{ $prompts->links() }}</div>
    @endif
</div>
@endsection
