<!DOCTYPE html>
<html lang="en">
<head>
    @php
        $seoSiteTitle = trim($directorySettings['seo_title'] ?? '') ?: $directorySettings['site_name'];
        $pageTitle = trim($__env->yieldContent('title', '')) ?: 'AI prompts for your next idea';
        $customMetaTitle = trim($__env->yieldContent('meta_title', ''));
        $titleTemplate = trim($directorySettings['seo_title_template'] ?? '') ?: '%page% — %site%';
        $metaTitle = $customMetaTitle !== ''
            ? $customMetaTitle
            : str_replace(['%page%', '%site%'], [$pageTitle, $seoSiteTitle], $titleTemplate);
        $metaDescription = trim($__env->yieldContent('meta_description', '')) ?: (trim($directorySettings['seo_description'] ?? '') ?: $directorySettings['site_tagline']);
        $canonicalBase = trim($directorySettings['seo_canonical_base'] ?? '');
        $canonicalUrl = $canonicalBase !== ''
            ? rtrim($canonicalBase, '/').'/'.ltrim(request()->getPathInfo(), '/')
            : url()->current();
        $socialImage = trim($__env->yieldContent('og_image') ?: ($directorySettings['seo_og_image'] ?? ''));
        $robotsPolicy = request()->routeIs('admin.*', 'dashboard.*', 'login', 'register')
            ? 'noindex,nofollow'
            : $directorySettings['seo_robots'];
    @endphp
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>{{ $metaTitle }}</title>
    @if (!empty($directorySettings['favicon_url']))
        <link rel="icon" href="{{ $directorySettings['favicon_url'] }}">
        <link rel="apple-touch-icon" href="{{ $directorySettings['favicon_url'] }}">
    @else
        <link rel="icon" href="data:image/svg+xml,<svg xmlns='http://www.w3.org/2000/svg' viewBox='0 0 24 24'><path d='M2 18.7 8.15 6.4l3.15 6.1 2.1-3.6L22 18.7H2Z' fill='%237c3aed'/></svg>">
    @endif
    <meta name="description" content="{{ $metaDescription }}">
    @if (trim($directorySettings['seo_keywords'] ?? '') !== '')<meta name="keywords" content="{{ $directorySettings['seo_keywords'] }}">@endif
    <meta name="robots" content="{{ $robotsPolicy }}">
    <link rel="canonical" href="{{ $canonicalUrl }}">
    <meta property="og:type" content="website">
    <meta property="og:site_name" content="{{ $seoSiteTitle }}">
    <meta property="og:title" content="{{ $metaTitle }}">
    <meta property="og:description" content="{{ $metaDescription }}">
    <meta property="og:url" content="{{ $canonicalUrl }}">
    @if ($socialImage !== '')<meta property="og:image" content="{{ $socialImage }}"><meta name="twitter:card" content="summary_large_image">@else<meta name="twitter:card" content="summary">@endif
    <meta name="twitter:title" content="{{ $metaTitle }}">
    <meta name="twitter:description" content="{{ $metaDescription }}">
    <script type="application/ld+json">
    {
        "@@context": "https://schema.org",
        "@type": "WebSite",
        "name": "{{ $seoSiteTitle }}",
        "url": "{{ url('/') }}",
        "description": "{{ $metaDescription }}",
        "potentialAction": {
            "@type": "SearchAction",
            "target": "{{ url('/') }}?q={search_term_string}",
            "query-input": "required name=search_term_string"
        }
    }
    </script>
    <script type="application/ld+json">
    {
        "@@context": "https://schema.org",
        "@type": "Organization",
        "name": "{{ $seoSiteTitle }}",
        "url": "{{ url('/') }}",
        @if (!empty($directorySettings['logo_url']))
        "logo": "{{ url($directorySettings['logo_url']) }}",
        @endif
        "description": "{{ $metaDescription }}",
        "sameAs": [
            "https://twitter.com/promptimage"
        ]
    }
    </script>
    @yield('structured_data')
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700&family=Poppins:wght@400;500;600;700&display=swap" rel="stylesheet">
    @yield('preload')
    <style>[x-cloak] { display: none !important; }</style>
    @if (file_exists(public_path('build/manifest.json')))
        @vite(['resources/css/app.css', 'resources/js/app.js'])
    @endif
    @if (!empty($directorySettings['custom_head_code']))
        {!! $directorySettings['custom_head_code'] !!}
    @endif
</head>
<body x-data="globalAppModal()" @keydown.window.cmd.k.prevent="openSearch()" @keydown.window.ctrl.k.prevent="openSearch()">
    @include('partials.sidebar')

    <main class="app-main">
        <header class="topbar">
            <div class="topbar-nav-left">
                <button class="mobile-menu" type="button" aria-label="Open menu" data-sidebar-toggle>☰</button>
                <a class="mobile-brand" href="{{ route('prompts.index') }}" aria-label="{{ $directorySettings['site_name'] }} home">
                    @if (!empty($directorySettings['logo_url']))
                        <span class="brand-mark mobile-brand-mark"><img src="{{ $directorySettings['logo_url'] }}" alt="{{ $directorySettings['site_name'] }}"></span>
                    @else
                        <span class="brand-mark mobile-brand-mark"><svg viewBox="0 0 24 24" fill="none" aria-hidden="true"><path d="M2 18.7 8.15 6.4l3.15 6.1 2.1-3.6L22 18.7H2Z" fill="currentColor"/><path d="m7.7 14.2 2.8 1.65 2.35-4.05 3.55 6.9" stroke="#fff" stroke-width=".75" opacity=".75"/></svg></span>
                    @endif
                    <span class="brand-name mobile-brand-name">{{ $directorySettings['site_name'] }}</span>
                </a>
            </div>
            @hasSection('toolbar')
                @yield('toolbar')
                @include('partials.header-account')
            @else
                <div class="topbar-title">@yield('eyebrow', strtoupper($directorySettings['site_name'] ?? 'PROMPT') . ' COMMUNITY') <span>·</span> @yield('toolbar-title', 'Prompt studio')</div>
                <form class="top-search" action="{{ route('prompts.index') }}" method="GET">
                    <span class="search-icon">⌕</span>
                    <input type="search" name="q" placeholder="Search prompts, categories, creators…" value="{{ request('q') }}" aria-label="Search prompts, categories, and creators">
                    <kbd>⌘ K</kbd>
                </form>
                <button type="button" class="mobile-search-btn" @click="openSearch()" aria-label="Search">⌕</button>
                @include('partials.header-account')
            @endif
        </header>

        @if (session('success'))
            <div class="flash flash-success" role="status"><span>✦</span> {{ session('success') }}<button type="button" aria-label="Dismiss" data-dismiss>×</button></div>
        @endif
        @if ($errors->any())
            <div class="flash flash-error" role="alert"><span>!</span> {{ $errors->first() }}<button type="button" aria-label="Dismiss" data-dismiss>×</button></div>
        @endif

        @yield('content')

        @include('partials.footer')
    </main>
    <div class="sidebar-scrim" data-sidebar-toggle></div>
    <div class="toast" id="copy-toast" role="status">Prompt copied to clipboard</div>

    <!-- 1. Instant Typo-Tolerant Search Modal (CMD+K) -->
    <div x-show="searchOpen" x-cloak style="display: none;" class="app-modal-overlay">
        <div @click.away="searchOpen = false" class="app-modal-search-card">
            <!-- Search Input Bar -->
            <div style="padding: 14px 16px; border-bottom: 1px solid #27272e; display: flex; align-items: center; gap: 10px; background: rgba(14, 14, 18, 0.7);">
                <svg style="width: 18px; height: 18px; color: #a78bfa; flex-shrink: 0;" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z"/></svg>
                <input type="text" x-ref="searchInput" x-model="searchQuery" @input.debounce.200ms="executeSearch()" placeholder="Instant search prompts, styles, keywords…" style="width: 100%; background: transparent; border: 0; outline: none; font-size: 13.5px; color: #ffffff;">
                <span style="font-size: 10px; font-family: monospace; padding: 2px 6px; border-radius: 4px; background: #27272e; color: #a1a1aa;">ESC</span>
            </div>

            <!-- Facet Filter Chips -->
            <div style="padding: 9px 16px; border-bottom: 1px solid rgba(255, 255, 255, 0.08); background: #14141a; display: flex; flex-wrap: wrap; align-items: center; gap: 6px; font-size: 11.5px;">
                <span style="color: #71717a; margin-right: 4px;">Vibe:</span>
                <button type="button" @click="toggleVibe('')" :style="searchVibe === '' ? 'background: #7c3aed; color: #fff; font-weight: 600;' : 'background: #27272e; color: #a1a1aa;'" style="padding: 3px 9px; border-radius: 6px; border: 0; cursor: pointer;">All</button>
                <button type="button" @click="toggleVibe('dark')" :style="searchVibe === 'dark' ? 'background: #7c3aed; color: #fff; font-weight: 600;' : 'background: #27272e; color: #a1a1aa;'" style="padding: 3px 9px; border-radius: 6px; border: 0; cursor: pointer;">Dark/Moody</button>
                <button type="button" @click="toggleVibe('vibrant')" :style="searchVibe === 'vibrant' ? 'background: #7c3aed; color: #fff; font-weight: 600;' : 'background: #27272e; color: #a1a1aa;'" style="padding: 3px 9px; border-radius: 6px; border: 0; cursor: pointer;">Vibrant Neon</button>
                <button type="button" @click="toggleVibe('warm')" :style="searchVibe === 'warm' ? 'background: #7c3aed; color: #fff; font-weight: 600;' : 'background: #27272e; color: #a1a1aa;'" style="padding: 3px 9px; border-radius: 6px; border: 0; cursor: pointer;">Warm 35mm</button>
                <button type="button" @click="toggleVibe('pastel')" :style="searchVibe === 'pastel' ? 'background: #7c3aed; color: #fff; font-weight: 600;' : 'background: #27272e; color: #a1a1aa;'" style="padding: 3px 9px; border-radius: 6px; border: 0; cursor: pointer;">Pastel Ghibli</button>
            </div>

            <!-- Live Results List -->
            <div style="padding: 12px; overflow-y: auto; flex: 1;">
                <template x-if="searchLoading">
                    <div style="padding: 40px 0; text-align: center; font-size: 12px; color: #71717a;">Searching prompts…</div>
                </template>

                <template x-if="!searchLoading && searchResults.length === 0 && searchQuery">
                    <div style="padding: 40px 0; text-align: center; font-size: 12px; color: #71717a;">No prompt matches found for "<span x-text="searchQuery"></span>".</div>
                </template>

                <template x-for="item in searchResults" :key="item.id">
                    <a :href="item.url" style="display: flex; align-items: center; gap: 12px; padding: 10px; border-radius: 12px; text-decoration: none; color: inherit; transition: background 0.15s;" onmouseover="this.style.background='#23232c'" onmouseout="this.style.background='transparent'">
                        <div style="width: 48px; height: 48px; border-radius: 10px; overflow: hidden; background: #0e0e12; flex-shrink: 0; border: 1px solid #27272e;">
                            <img :src="item.cover_url" style="width: 100%; height: 100%; object-fit: cover;">
                        </div>
                        <div style="flex: 1; min-width: 0;">
                            <div style="display: flex; align-items: center; gap: 6px;">
                                <span style="font-size: 13px; font-weight: 600; color: #ffffff; overflow: hidden; text-overflow: ellipsis; white-space: nowrap;" x-text="item.title"></span>
                                <span style="padding: 1px 6px; border-radius: 99px; background: rgba(124, 58, 237, 0.18); color: #c4b5fd; font-size: 10px; font-weight: 600;" x-text="item.category"></span>
                            </div>
                            <p style="font-size: 11.5px; color: #a1a1aa; margin: 3px 0 0; overflow: hidden; text-overflow: ellipsis; white-space: nowrap;" x-text="item.snippet"></p>
                        </div>
                        <span style="font-size: 11.5px; color: #71717a; flex-shrink: 0;" x-text="'♥ ' + item.likes_count"></span>
                    </a>
                </template>
            </div>
        </div>
    </div>
    @if (!empty($directorySettings['custom_body_code']))
        {!! $directorySettings['custom_body_code'] !!}
    @endif
</body>
</html>

