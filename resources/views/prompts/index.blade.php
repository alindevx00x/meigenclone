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
