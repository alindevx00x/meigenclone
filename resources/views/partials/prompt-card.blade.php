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
            @if ($prompt->user)
                <a href="{{ route('creators.show', $prompt->user) }}" class="author-profile-link" style="display: flex; align-items: center; gap: 6px; text-decoration: none; min-width: 0; flex: 1 1 auto; color: inherit;">
                    @if ($prompt->user->avatar_url)
                        <img src="{{ $prompt->user->avatar_url }}" alt="{{ $prompt->user->name }}" class="avatar avatar-tiny" style="object-fit: cover;">
                    @else
                        <span class="avatar avatar-tiny">{{ str($prompt->user->name)->substr(0, 1)->upper() }}</span>
                    @endif
                    <span class="author-name">
                        {{ $prompt->user->name }}
                        @if($prompt->user->is_verified)
                            <span title="Verified Creator" style="display: inline-flex; align-items: center; justify-content: center; width: 14px; height: 14px; border-radius: 50%; background: linear-gradient(135deg, #8b5cf6, #6366f1); color: #fff; font-size: 9px; font-weight: 700; vertical-align: middle; margin-left: 2px;">✓</span>
                        @endif
                    </span>
                    <span class="author-handle">{{ '@'.str($prompt->user->name)->slug('_') }}</span>
                </a>
            @else
                <span class="avatar avatar-tiny">M</span>
                <span class="author-name">MeiGen Community</span>
                <span class="author-handle">Community pick</span>
            @endif
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
            <button class="copy-button" type="button" data-discord-copy="/imagine prompt: {{ addslashes($prompt->prompt_text) }} --ar 16:9 --v 6.1" title="Copy Midjourney Discord command (/imagine prompt: ...)" style="padding: 4px 7px; margin-left: 2px; font-weight: 600; color: #5865f2; background: #f0f2ff; border-color: #dce1fc;">/mj</button>
            <button class="copy-button" type="button" @click="$dispatch('open-save-collection', { promptId: {{ $prompt->id }} })" title="Save to Moodboard" style="padding: 4px 8px; margin-left: 2px;">📌</button>
            <a href="{{ route('studio.remix', ['from' => $prompt->slug]) }}" title="Remix Prompt" class="copy-button" style="text-decoration: none; padding: 4px 8px; margin-left: 2px;">🪄</a>
        </div>
    </div>
</article>
