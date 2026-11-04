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
            slides: {{ json_encode($featuredPrompts->map(fn($p) => [
                'id' => $p->id,
                'slug' => $p->slug,
                'title' => $p->title,
                'prompt_text' => $p->prompt_text,
                'cover_url' => $p->cover_url,
                'category' => $p->category->name,
                'category_icon' => $p->category->icon,
                'model' => $p->model,
                'author' => $p->user?->name ?? 'MeiGen Community',
                'likes' => number_format($p->likes_count),
                'url' => route('prompts.show', $p->slug),
                'remix_url' => route('studio.remix', ['from' => $p->slug]),
            ])) }},
            timer: null,
            init() {
                this.timer = setInterval(() => {
                    this.currentIndex = (this.currentIndex + 1) % this.slides.length;
                }, 7000);
            },
            next() {
                this.currentIndex = (this.currentIndex + 1) % this.slides.length;
            },
            prev() {
                this.currentIndex = (this.currentIndex - 1 + this.slides.length) % this.slides.length;
            }
        }" style="position: relative; margin-bottom: 28px; border-radius: 20px; overflow: hidden; background: #0f0e17; color: #fff; box-shadow: 0 12px 36px rgba(0,0,0,0.12);">
            <template x-for="(slide, idx) in slides" :key="slide.id">
                <div x-show="currentIndex === idx" x-transition:enter="transition ease-out duration-300" x-transition:enter-start="opacity-0 transform translate-x-4" x-transition:enter-end="opacity-100 transform translate-x-0" style="display: grid; grid-template-columns: minmax(0, 1.2fr) minmax(280px, 1fr); min-height: 290px; align-items: stretch;" class="carousel-slide">
                    <!-- Left: Meta & Prompt Text -->
                    <div style="padding: 32px 36px; display: flex; flex-direction: column; justify-content: space-between; z-index: 2;">
                        <div>
                            <div style="display: flex; align-items: center; gap: 8px; margin-bottom: 12px;">
                                <span style="display: inline-flex; align-items: center; gap: 5px; padding: 4px 10px; border-radius: 99px; background: rgba(124, 58, 237, 0.35); border: 1px solid rgba(139, 92, 246, 0.4); color: #c4b5fd; font-size: 11px; font-weight: 700; text-transform: uppercase; letter-spacing: 0.5px;">
                                    <span>✦</span> PROMPT OF THE DAY
                                </span>
                                <span style="font-size: 11px; color: #a1a1aa;" x-text="slide.category_icon + ' ' + slide.category"></span>
                                <span style="color: #52525b;">•</span>
                                <span style="font-size: 11px; color: #a1a1aa;" x-text="slide.model"></span>
                            </div>
                            <h2 style="font-size: 22px; font-weight: 700; color: #ffffff; line-height: 1.3; margin: 0 0 10px;" x-text="slide.title"></h2>
                            <p style="font-size: 13px; line-height: 1.6; color: #d4d4d8; display: -webkit-box; -webkit-line-clamp: 3; -webkit-box-orient: vertical; overflow: hidden; margin: 0 0 20px;" x-text="slide.prompt_text"></p>
                        </div>
                        <div style="display: flex; align-items: center; gap: 10px; flex-wrap: wrap;">
                            <a :href="slide.url" class="primary-button" style="background: #ffffff; color: #000; min-height: 36px; padding: 0 16px; font-weight: 600;">
                                View prompt <span>↗</span>
                            </a>
                            <button type="button" :data-copy="slide.prompt_text" class="outline-button" style="background: rgba(255,255,255,0.1); border-color: rgba(255,255,255,0.2); color: #fff; min-height: 36px; padding: 0 14px;">
                                Copy prompt <span>⧉</span>
                            </button>
                            <a :href="slide.remix_url" class="outline-button" style="background: rgba(124,58,237,0.2); border-color: rgba(139,92,246,0.3); color: #c4b5fd; min-height: 36px; padding: 0 14px;">
                                Remix <span>🪄</span>
                            </a>
                        </div>
                    </div>

                    <!-- Right: Cover Image -->
                    <div style="position: relative; overflow: hidden; background: #000;">
                        <img :src="slide.cover_url" :alt="slide.title" style="width: 100%; height: 100%; object-fit: cover; opacity: 0.95;">
                        <div style="position: absolute; inset: 0; background: linear-gradient(to right, #0f0e17 0%, transparent 40%);"></div>
                    </div>
                </div>
            </template>

            <!-- Carousel Navigation Dots & Arrows -->
            <div style="position: absolute; bottom: 16px; right: 20px; display: flex; align-items: center; gap: 8px; z-index: 10;">
                <button type="button" @click="prev()" style="width: 28px; height: 28px; border-radius: 50%; background: rgba(0,0,0,0.5); border: 1px solid rgba(255,255,255,0.2); color: #fff; cursor: pointer; display: grid; place-items: center; font-size: 11px;">‹</button>
                <template x-for="(slide, idx) in slides" :key="'dot-' + slide.id">
                    <button type="button" @click="currentIndex = idx" :style="currentIndex === idx ? 'background: #fff; width: 18px;' : 'background: rgba(255,255,255,0.35); width: 6px;'" style="height: 6px; border-radius: 99px; border: 0; padding: 0; cursor: pointer; transition: all 0.2s;"></button>
                </template>
                <button type="button" @click="next()" style="width: 28px; height: 28px; border-radius: 50%; background: rgba(0,0,0,0.5); border: 1px solid rgba(255,255,255,0.2); color: #fff; cursor: pointer; display: grid; place-items: center; font-size: 11px;">›</button>
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
