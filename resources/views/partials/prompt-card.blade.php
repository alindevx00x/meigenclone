<article class="prompt-card" data-prompt-id="{{ $prompt->id }}">
    <a class="prompt-image" href="{{ route('prompts.show', $prompt->slug) }}" aria-label="View {{ $prompt->title }}">
        @if ($prompt->cover_url)
            <img src="{{ $prompt->thumbnail_url ?? $prompt->cover_url }}"
                 alt="{{ $prompt->title }}"
                 loading="{{ ($priority ?? false) ? 'eager' : 'lazy' }}"
                 fetchpriority="{{ ($priority ?? false) ? 'high' : 'auto' }}"
                 decoding="async"
                 width="400"
                 height="500">
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
                        <img src="{{ $prompt->user->avatar_url }}" alt="{{ $prompt->user->handle }}" class="avatar avatar-tiny" width="20" height="20" loading="lazy" decoding="async" style="object-fit: cover; flex-shrink: 0;" onerror="this.style.display='none'; if(this.nextElementSibling){this.nextElementSibling.style.display='grid';}">
                        <span class="avatar avatar-tiny" style="flex-shrink: 0; display: none;">{{ strtoupper(substr(ltrim($prompt->user->handle, '@'), 0, 1)) }}</span>
                    @else
                        <span class="avatar avatar-tiny" style="flex-shrink: 0;">{{ strtoupper(substr(ltrim($prompt->user->handle, '@'), 0, 1)) }}</span>
                    @endif
                    <span class="author-handle" style="font-weight: 600; font-size: 12px; color: #18181b; display: inline-flex; align-items: center; gap: 3px; min-width: 0;">
                        <span style="overflow: hidden; text-overflow: ellipsis; white-space: nowrap;">{{ $prompt->user->handle }}</span>
                        @if($prompt->user->is_verified)
                            <span title="Verified Creator" style="display: inline-flex; align-items: center; justify-content: center; width: 13px; height: 13px; border-radius: 50%; background: linear-gradient(135deg, #8b5cf6, #6366f1); color: #fff; font-size: 8px; font-weight: 700; flex-shrink: 0;">✓</span>
                        @endif
                    </span>
                </a>
            @else
                <span class="avatar avatar-tiny">✦</span>
                <span class="author-handle" style="font-weight: 600; font-size: 12px; color: #18181b;">@community</span>
            @endif
            <form method="POST" action="{{ auth()->check() ? route('prompts.favorite', $prompt->slug) : route('login') }}" class="favorite-form" style="margin: 0; flex-shrink: 0;">
                @csrf
                <button type="submit" class="favorite-button {{ $prompt->is_favorited ? 'is-favorite' : '' }}" aria-label="{{ $prompt->is_favorited ? 'Remove from favorites' : 'Add to favorites' }}" title="{{ $prompt->is_favorited ? 'Saved to favorites' : 'Save to favorites' }}">
                    <svg class="heart-svg" viewBox="0 0 24 24" width="15" height="15" fill="{{ $prompt->is_favorited ? '#ef4444' : 'none' }}" stroke="{{ $prompt->is_favorited ? '#ef4444' : '#71717a' }}" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true">
                        <path d="M20.84 4.61a5.5 5.5 0 0 0-7.78 0L12 5.67l-1.06-1.06a5.5 5.5 0 0 0-7.78 7.78l1.06 1.06L12 21.23l7.78-7.78 1.06-1.06a5.5 5.5 0 0 0 0-7.78z"></path>
                    </svg>
                </button>
            </form>
        </div>
        <a class="prompt-title" href="{{ route('prompts.show', $prompt->slug) }}">{{ $prompt->title }}</a>
        <p class="prompt-excerpt">{{ \Illuminate\Support\Str::limit($prompt->prompt_text, 112) }}</p>
        <div class="prompt-card-footer">
            <div class="card-meta-tags">
                <span class="prompt-category"><span>{{ $prompt->category->icon }}</span> {{ $prompt->category->name }}</span>
                <span class="card-model-label">{{ $prompt->model }}</span>
                <span class="prompt-likes">
                    <svg viewBox="0 0 24 24" width="11" height="11" fill="#e11d48" stroke="#e11d48" stroke-width="1.5" style="display: inline-block; vertical-align: middle; margin-right: 2px;"><path d="M20.84 4.61a5.5 5.5 0 0 0-7.78 0L12 5.67l-1.06-1.06a5.5 5.5 0 0 0-7.78 7.78l1.06 1.06L12 21.23l7.78-7.78 1.06-1.06a5.5 5.5 0 0 0 0-7.78z"></path></svg>{{ number_format($prompt->likes_count) }}
                </span>
            </div>
        </div>
    </div>
</article>
