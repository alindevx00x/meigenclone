<article class="prompt-card">
    <a class="prompt-image" href="{{ route('prompts.show', $prompt->slug) }}" aria-label="View {{ $prompt->title }}">
        @if ($prompt->cover_url)
            <img src="{{ $prompt->cover_url }}" alt="{{ $prompt->title }}" loading="lazy">
        @else
            <div class="image-placeholder"><span>✦</span><small>AI PROMPT</small></div>
        @endif
        <span class="image-open">↗</span>
    </a>
    <div class="prompt-card-body">
        <div class="prompt-card-author">
            <span class="avatar avatar-tiny">{{ str($prompt->user?->name ?? 'MeiGen Community')->substr(0, 1)->upper() }}</span>
            <span class="author-name">{{ $prompt->user?->name ?? 'MeiGen Community' }}</span>
            <span class="author-handle">{{ $prompt->user ? '@'.str($prompt->user->name)->slug('_') : 'Community pick' }}</span>
            <form method="POST" action="{{ auth()->check() ? route('prompts.favorite', $prompt->slug) : route('login') }}" class="favorite-form">
                @csrf
                @if (auth()->check())
                    <button type="submit" class="favorite-button {{ $prompt->is_favorited ? 'is-favorite' : '' }}" aria-label="Save prompt">{{ $prompt->is_favorited ? '♥' : '♡' }}</button>
                @else
                    <a class="favorite-button" href="{{ route('login') }}" aria-label="Log in to save">♡</a>
                @endif
            </form>
        </div>
        <a class="prompt-title" href="{{ route('prompts.show', $prompt->slug) }}">{{ $prompt->title }}</a>
        <p class="prompt-excerpt">{{ \Illuminate\Support\Str::limit($prompt->prompt_text, 112) }}</p>
        <div class="prompt-card-footer">
            <span class="prompt-category"><span>{{ $prompt->category->icon }}</span> {{ $prompt->category->name }}</span>
            <span class="card-model-label">{{ $prompt->model }}</span>
            <span class="prompt-likes">♥ {{ number_format($prompt->likes_count) }}</span>
            <button class="copy-button" type="button" data-copy="{{ $prompt->prompt_text }}" aria-label="Copy prompt">Copy <span>⧉</span></button>
            <button class="copy-button" type="button" @click="$dispatch('open-save-collection', { promptId: {{ $prompt->id }} })" title="Save to Moodboard" style="padding: 4px 8px; margin-left: 2px;">📌</button>
            <a href="{{ route('studio.remix', ['from' => $prompt->slug]) }}" title="Remix Prompt" class="copy-button" style="text-decoration: none; padding: 4px 8px; margin-left: 2px;">🪄</a>
        </div>
    </div>
</article>
