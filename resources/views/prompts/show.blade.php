@extends('layouts.app')
@section('title', $prompt->title)
@section('og_image', route('prompts.og_image', $prompt->slug))
@section('structured_data')
@php
    $catName = $prompt->category?->name ?? (is_string($prompt->category) ? $prompt->category : 'AI Prompts');
    $catSlug = $prompt->category?->slug ?? \Illuminate\Support\Str::slug($catName);

    $creativeWorkData = [
        '@context' => 'https://schema.org',
        '@type' => 'CreativeWork',
        'mainEntityOfPage' => [
            '@type' => 'WebPage',
            '@id' => url()->current(),
        ],
        'name' => $prompt->title,
        'headline' => $prompt->title,
        'text' => Str::limit($prompt->prompt_text, 1000),
        'datePublished' => $prompt->created_at?->toIso8601String() ?? now()->toIso8601String(),
        'dateModified' => $prompt->updated_at?->toIso8601String() ?? now()->toIso8601String(),
        'author' => [
            '@type' => 'Person',
            'name' => $prompt->user?->name ?? (($directorySettings['site_name'] ?? 'Prompt') . ' Community'),
        ],
        'keywords' => "{$catName}, {$prompt->model}, AI Image Prompt",
    ];

    if ($prompt->cover_url) {
        $creativeWorkData['image'] = [
            '@type' => 'ImageObject',
            'url' => $prompt->cover_url,
            'contentUrl' => $prompt->cover_url,
            'caption' => $prompt->title,
        ];
    }

    $breadcrumbData = [
        '@context' => 'https://schema.org',
        '@type' => 'BreadcrumbList',
        'itemListElement' => [
            [
                '@type' => 'ListItem',
                'position' => 1,
                'name' => 'Home',
                'item' => url('/'),
            ],
            [
                '@type' => 'ListItem',
                'position' => 2,
                'name' => $catName,
                'item' => route('prompts.category_landing', $catSlug),
            ],
            [
                '@type' => 'ListItem',
                'position' => 3,
                'name' => $prompt->title,
                'item' => url()->current(),
            ],
        ],
    ];
@endphp
<script type="application/ld+json">
{!! json_encode($creativeWorkData, JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE | JSON_PRETTY_PRINT) !!}
</script>
<script type="application/ld+json">
{!! json_encode($breadcrumbData, JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE | JSON_PRETTY_PRINT) !!}
</script>
@endsection
@section('content')
<div class="detail-page" x-data="{ lightboxOpen: false }">
    <a class="back-link" href="{{ url()->previous() !== url()->current() ? url()->previous() : route('prompts.index') }}"><span>←</span> Back to gallery</a>
    <div class="detail-layout">
        <div class="detail-visual">
            @if ($prompt->cover_url)
                <img src="{{ $prompt->cover_url }}" alt="{{ $prompt->title }}" @click="lightboxOpen = true" style="cursor: zoom-in;" title="Click to enlarge image">
                <button type="button" @click="lightboxOpen = true" class="detail-zoom-trigger" aria-label="Enlarge image" title="Click to view full size">
                    <svg viewBox="0 0 24 24" width="16" height="16" fill="none" stroke="currentColor" stroke-width="2"><path d="M15 3h6v6M9 21H3v-6M21 3l-7 7M3 21l7-7"/></svg>
                    <span>Full size</span>
                </button>
            @else
                <div class="image-placeholder detail-placeholder"><span>✦</span></div>
            @endif
            <span class="model-chip">{{ $prompt->model }}</span>
        </div>

        {{-- Full-screen Image Lightbox Modal with X Close Button --}}
        @if ($prompt->cover_url)
            <div x-show="lightboxOpen"
                 x-cloak
                 @keydown.escape.window="lightboxOpen = false"
                 class="prompt-lightbox-overlay"
                 @click.self="lightboxOpen = false"
                 role="dialog"
                 aria-modal="true"
                 aria-label="{{ $prompt->title }} - Full View">
                <button type="button"
                        class="prompt-lightbox-close"
                        @click="lightboxOpen = false"
                        aria-label="Close image preview"
                        title="Close (Esc)">
                    ✕
                </button>
                <div class="prompt-lightbox-content" @click.self="lightboxOpen = false">
                    <img src="{{ $prompt->cover_url }}" alt="{{ $prompt->title }}" class="prompt-lightbox-img">
                    <div class="prompt-lightbox-footer">
                        <span class="prompt-lightbox-title">{{ $prompt->title }}</span>
                        <span class="prompt-lightbox-model">{{ $prompt->model }}</span>
                    </div>
                </div>
            </div>
        @endif
        <div class="detail-info">
            <div class="detail-category"><span>{{ $prompt->category->icon }}</span> {{ $prompt->category->name }} <i>·</i> {{ $prompt->model }}</div>
            <h1>{{ $prompt->title }}</h1>
            <div class="detail-author">
                @if ($prompt->user)
                    <a href="{{ route('creators.show', $prompt->user) }}" style="display: flex; align-items: center; gap: 10px; text-decoration: none; color: inherit;">
                        @if ($prompt->user->avatar_url)
                            <img src="{{ $prompt->user->avatar_url }}" alt="{{ $prompt->user->name }}" class="avatar" style="object-fit: cover;" onerror="this.style.display='none'; if(this.nextElementSibling){this.nextElementSibling.style.display='grid';}">
                            <span class="avatar" style="display: none;">{{ str($prompt->user->name)->substr(0, 1)->upper() }}</span>
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
                    <span class="avatar">✦</span>
                    <span>
                        <strong style="color: #18181b;">{{ ($directorySettings['site_name'] ?? 'Prompt') . ' Community' }}</strong>
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
                <button class="outline-button" type="button" data-prompt-id="{{ $prompt->id }}" data-discord-copy="/imagine prompt: {{ addslashes($prompt->prompt_text) }} --ar 16:9 --v 6.1" style="color: #4f46e5; border-color: #c7d2fe; background: #eef2ff; font-weight: 600;" title="Copy Midjourney Discord command">/mj <span>⧉</span></button>
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
