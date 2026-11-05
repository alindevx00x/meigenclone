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
            <div class="detail-author">
                @if ($prompt->user)
                    <a href="{{ route('creators.show', $prompt->user) }}" style="display: flex; align-items: center; gap: 10px; text-decoration: none; color: inherit;">
                        @if ($prompt->user->avatar_url)
                            <img src="{{ $prompt->user->avatar_url }}" alt="{{ $prompt->user->name }}" class="avatar" style="object-fit: cover;">
                        @else
                            <span class="avatar">{{ str($prompt->user->name)->substr(0, 1)->upper() }}</span>
                        @endif
                        <span>
                            <strong style="color: #18181b;">
                                {{ $prompt->user->name }}
                                @if($prompt->user->is_verified)
                                    <span title="Verified Creator" style="display: inline-flex; align-items: center; justify-content: center; width: 15px; height: 15px; border-radius: 50%; background: linear-gradient(135deg, #8b5cf6, #6366f1); color: #fff; font-size: 9.5px; font-weight: 700; vertical-align: middle; margin-left: 2px;">✓</span>
                                @endif
                            </strong>
                            <small style="color: #64646e;">{{ $prompt->user->handle }} &bull; View creator profile &rarr;</small>
                        </span>
                    </a>
                @else
                    <span class="avatar">M</span>
                    <span>
                        <strong style="color: #18181b;">MeiGen Community</strong>
                        <small style="color: #64646e;">Shared with the community</small>
                    </span>
                @endif
                @if($prompt->user && auth()->check() && auth()->id() !== $prompt->user_id)
                    <form method="POST" action="{{ route('creators.follow', $prompt->user_id) }}" style="margin-left: 8px;">
                        @csrf
                        <button type="submit" class="outline-button" style="min-height: 28px; padding: 0 10px; font-size: 11px; border-radius: 99px;">
                            {{ auth()->user()->isFollowing($prompt->user) ? '✓ Following' : '+ Follow' }}
                        </button>
                    </form>
                @endif
                <span class="detail-date">{{ $prompt->approved_at?->diffForHumans() }}</span>
            </div>
            <div class="detail-actions" style="flex-wrap: wrap; gap: 8px;">
                <button class="primary-button" type="button" data-prompt-id="{{ $prompt->id }}" data-copy="{{ $prompt->prompt_text }}">Copy prompt <span>⧉</span></button>
                <button class="outline-button" type="button" data-prompt-id="{{ $prompt->id }}" data-discord-copy="/imagine prompt: {{ addslashes($prompt->prompt_text) }} --ar 16:9 --v 6.1" style="color: #5865f2; border-color: #dce1fc; background: #f8f9ff;">Copy Discord /mj <span>🎮</span></button>
                <a class="outline-button" style="text-decoration: none;" href="{{ route('studio.remix', ['from' => $prompt->slug]) }}">Remix <span>🪄</span></a>
                <button class="outline-button" type="button" @click="$dispatch('open-save-collection', { promptId: {{ $prompt->id }} })">Board <span>📌</span></button>
                @if($prompt->user && $prompt->user->monetization_enabled)
                <button class="outline-button" type="button" @click="$dispatch('open-tip-modal', {
                    creatorId: '{{ $prompt->user_id }}',
                    creatorName: '{{ addslashes($prompt->user->name) }}',
                    creatorHandle: '{{ addslashes($prompt->user->handle) }}',
                    tipGoal: '{{ addslashes($prompt->user->tip_goal ?? '') }}',
                    tipLink: '{{ addslashes($prompt->user->tip_link ?? '') }}',
                    promptId: {{ $prompt->id }}
                })" style="color: #b45309; border-color: #fde68a; background: #fffbeb;">Tip <span>☕</span></button>
                @endif
                @auth
                    <form method="POST" action="{{ route('prompts.favorite', $prompt->slug) }}" class="favorite-form" style="display:inline-flex;">@csrf<button class="outline-button {{ $prompt->is_favorited ? 'saved' : '' }}" type="submit">{{ $prompt->is_favorited ? '♥ Saved' : '♡ Save' }}</button></form>
                @else
                    <a class="outline-button" href="{{ route('login') }}">♡ Save</a>
                @endauth
            </div>
            <section class="prompt-copy-block"><div class="block-title"><span class="block-title-icon">✦</span><strong>THE PROMPT</strong><span class="copy-hint">Ready to use &middot; {{ number_format(str_word_count($prompt->prompt_text)) }} words</span></div><p class="prompt-full-text">{{ $prompt->prompt_text }}</p><button class="copy-inline" type="button" data-prompt-id="{{ $prompt->id }}" data-copy="{{ $prompt->prompt_text }}">Copy full prompt <span>⧉</span></button></section>
            <div class="detail-stats" style="flex-wrap: wrap; gap: 14px;">
                <span>👁 {{ number_format($prompt->views_count) }} views</span>
                <span>⧉ {{ number_format($prompt->copies_count) }} copies</span>
                <span>♥ {{ number_format($prompt->likes_count) }} saves</span>
                <span>Added {{ $prompt->created_at->format('M j, Y') }}</span>
            </div>
        </div>
    </div>
    @if ($related->isNotEmpty())
        <section class="related-section"><div class="section-heading"><div><span class="section-kicker">KEEP EXPLORING</span><h2>More like this</h2></div><a href="{{ route('prompts.index', ['category' => $prompt->category->slug]) }}">View category <span>→</span></a></div><div class="prompt-grid related-grid">@foreach ($related as $item) @include('partials.prompt-card', ['prompt' => $item]) @endforeach</div></section>
    @endif
</div>
@endsection
