@extends('layouts.app')
@section('title', $prompt->title)
@section('structured_data')
<script type="application/ld+json">
{
    "@@context": "https://schema.org",
    "@type": "CreativeWork",
    "name": "{{ addslashes($prompt->title) }}",
    "headline": "{{ addslashes($prompt->title) }}",
    "text": "{{ addslashes(Str::limit($prompt->prompt_text, 1000)) }}",
    "datePublished": "{{ $prompt->created_at->toIso8601String() }}",
    "dateModified": "{{ $prompt->updated_at->toIso8601String() }}",
    "author": {
        "@type": "Person",
        "name": "{{ addslashes($prompt->user?->name ?? 'MeiGen Community') }}"
    },
    @if ($prompt->cover_url)
    "image": "{{ $prompt->cover_url }}",
    @endif
    "keywords": "{{ $prompt->category->name }}, {{ $prompt->model }}, AI Image Prompt"
}
</script>
@endsection
@section('content')
<div class="detail-page">
    <a class="back-link" href="{{ url()->previous() !== url()->current() ? url()->previous() : route('prompts.index') }}"><span>←</span> Back to gallery</a>
    <div class="detail-layout">
        <div class="detail-visual">
            @if ($prompt->cover_url)<img src="{{ $prompt->cover_url }}" alt="{{ $prompt->title }}">@else<div class="image-placeholder detail-placeholder"><span>✦</span></div>@endif
            <span class="model-chip">{{ $prompt->model }}</span>
        </div>
        <div class="detail-info">
            <div class="detail-category"><span>{{ $prompt->category->icon }}</span> {{ $prompt->category->name }} <i>·</i> {{ $prompt->model }}</div>
            <h1>{{ $prompt->title }}</h1>
            <div class="detail-author"><span class="avatar">{{ str($prompt->user?->name ?? 'MeiGen Community')->substr(0, 1)->upper() }}</span><span><strong>{{ $prompt->user?->name ?? 'MeiGen Community' }}</strong><small>Shared with the community</small></span><span class="detail-date">{{ $prompt->approved_at?->diffForHumans() }}</span></div>
            <div class="detail-actions">
                <button class="primary-button" type="button" data-copy="{{ $prompt->prompt_text }}">Copy prompt <span>⧉</span></button>
                @auth
                    <form method="POST" action="{{ route('prompts.favorite', $prompt->slug) }}">@csrf<button class="outline-button {{ $prompt->is_favorited ? 'saved' : '' }}" type="submit">{{ $prompt->is_favorited ? '♥ Saved' : '♡ Save' }}</button></form>
                @else
                    <a class="outline-button" href="{{ route('login') }}">♡ Save</a>
                @endauth
            </div>
            <section class="prompt-copy-block"><div class="block-title"><span class="block-title-icon">✦</span><strong>THE PROMPT</strong><span class="copy-hint">Ready to use · {{ number_format(str_word_count($prompt->prompt_text)) }} words</span></div><p class="prompt-full-text">{{ $prompt->prompt_text }}</p><button class="copy-inline" type="button" data-copy="{{ $prompt->prompt_text }}">Copy full prompt <span>⧉</span></button></section>
            <div class="detail-stats"><span>♥ {{ number_format($prompt->likes_count) }} saves</span><span>Added {{ $prompt->created_at->format('M j, Y') }}</span></div>
        </div>
    </div>
    @if ($related->isNotEmpty())
        <section class="related-section"><div class="section-heading"><div><span class="section-kicker">KEEP EXPLORING</span><h2>More like this</h2></div><a href="{{ route('prompts.index', ['category' => $prompt->category->slug]) }}">View category <span>→</span></a></div><div class="prompt-grid related-grid">@foreach ($related as $item) @include('partials.prompt-card', ['prompt' => $item]) @endforeach</div></section>
    @endif
</div>
@endsection
