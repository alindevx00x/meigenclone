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
        $socialImage = trim($directorySettings['seo_og_image'] ?? '');
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
    @yield('structured_data')
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700&family=Poppins:wght@400;500;600;700&display=swap" rel="stylesheet">
    <style>[x-cloak] { display: none !important; }</style>
    @if (file_exists(public_path('build/manifest.json')))
        @vite(['resources/css/app.css', 'resources/js/app.js'])
    @endif
</head>
<body x-data="globalAppModal()" @keydown.window.cmd.k.prevent="openSearch()" @keydown.window.ctrl.k.prevent="openSearch()" @open-tip-modal.window="openTip($event.detail)">
    @include('partials.sidebar')

    <main class="app-main">
        <header class="topbar">
            <button class="mobile-menu" type="button" aria-label="Open menu" data-sidebar-toggle>☰</button>
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

    <!-- 2. Tip Creator Modal -->
    <div x-show="tipOpen" x-cloak style="display: none;" class="app-modal-overlay">
        <div @click.away="tipOpen = false" class="app-modal-card">
            <!-- Header -->
            <div class="app-modal-header">
                <div>
                    <div style="display: flex; align-items: center; gap: 8px;">
                        <h3 style="font-size: 16px; font-weight: 700; color: #ffffff; margin: 0;">Tip <span style="color: #a78bfa;" x-text="tipCreatorName"></span></h3>
                        <span style="font-size: 11px; font-family: monospace; color: #a1a1aa; background: #27272e; padding: 2px 6px; border-radius: 6px;" x-text="tipCreatorHandle"></span>
                    </div>
                    <p style="font-size: 11.5px; color: #a1a1aa; margin: 4px 0 0;">Show appreciation and support creator prompts.</p>
                </div>
                <button type="button" @click="tipOpen = false" class="app-modal-close" aria-label="Close modal">&times;</button>
            </div>

            <!-- Success Screen -->
            <template x-if="tipSuccess">
                <div style="padding: 16px 0; text-align: center;">
                    <div style="width: 52px; height: 52px; margin: 0 auto 12px; border-radius: 50%; background: rgba(16, 185, 129, 0.2); border: 1px solid rgba(16, 185, 129, 0.4); color: #34d399; display: flex; align-items: center; justify-content: center; font-size: 24px; font-weight: 700;">
                        ✓
                    </div>
                    <h4 style="font-size: 16px; font-weight: 700; color: #ffffff; margin: 0 0 6px;">Tip Sent Successfully!</h4>
                    <p style="font-size: 12.5px; color: #d4d4d8; margin: 0 0 12px;" x-text="tipSuccessMessage"></p>
                    <div style="font-size: 11px; font-family: monospace; color: #71717a; background: #0e0e12; padding: 8px; border-radius: 8px; border: 1px solid #27272e; margin-bottom: 16px;" x-text="'Ref: ' + tipTransactionRef"></div>
                    <button type="button" @click="tipOpen = false" class="tip-submit-btn" style="background: #27272e; box-shadow: none;">
                        Close
                    </button>
                </div>
            </template>

            <!-- Tip Form -->
            <template x-if="!tipSuccess">
                <div>
                    <!-- Creator Goal if set -->
                    <template x-if="tipGoal">
                        <div style="padding: 10px 12px; border-radius: 10px; background: rgba(245, 158, 11, 0.12); border: 1px solid rgba(245, 158, 11, 0.3); color: #fde68a; font-size: 12px; display: flex; align-items: center; gap: 8px; margin-bottom: 14px;">
                            <span style="font-size: 16px;">🎯</span>
                            <div>
                                <strong style="display: block; font-size: 11px; color: #fbbf24;">Creator Goal</strong>
                                <span x-text="tipGoal"></span>
                            </div>
                        </div>
                    </template>

                    <!-- Preset Amounts -->
                    <div style="margin-bottom: 14px;">
                        <label style="display: block; font-size: 11.5px; font-weight: 600; color: #a1a1aa; margin-bottom: 6px;">Select Amount</label>
                        <div class="tip-amount-grid">
                            <button type="button" @click="tipAmount = 2; tipCustomAmount = ''" :class="tipAmount === 2 && !tipCustomAmount ? 'is-active' : ''" class="tip-amount-btn">
                                <span class="tip-amount-val">$2</span>
                                <span class="tip-amount-sub">☕ Coffee</span>
                            </button>
                            <button type="button" @click="tipAmount = 5; tipCustomAmount = ''" :class="tipAmount === 5 && !tipCustomAmount ? 'is-active' : ''" class="tip-amount-btn">
                                <span class="tip-amount-val">$5</span>
                                <span class="tip-amount-sub">⚡ Spark</span>
                            </button>
                            <button type="button" @click="tipAmount = 10; tipCustomAmount = ''" :class="tipAmount === 10 && !tipCustomAmount ? 'is-active' : ''" class="tip-amount-btn">
                                <span class="tip-amount-val">$10</span>
                                <span class="tip-amount-sub">🎨 Patron</span>
                            </button>
                            <button type="button" @click="tipAmount = 25; tipCustomAmount = ''" :class="tipAmount === 25 && !tipCustomAmount ? 'is-active' : ''" class="tip-amount-btn">
                                <span class="tip-amount-val">$25</span>
                                <span class="tip-amount-sub">🚀 Super</span>
                            </button>
                        </div>
                    </div>

                    <!-- Custom Amount Input -->
                    <div class="tip-field">
                        <label>Or Custom Amount ($ USD)</label>
                        <input type="number" min="1" max="500" step="1" x-model="tipCustomAmount" placeholder="e.g. 15">
                    </div>

                    <!-- Custom Note -->
                    <div class="tip-field">
                        <label>Note to creator (Optional)</label>
                        <input type="text" x-model="tipNote" maxlength="300" placeholder="Thanks for this amazing prompt!">
                    </div>

                    <!-- Payment Method Selection -->
                    <div class="tip-field" style="margin-bottom: 14px;">
                        <label>Payment Method</label>
                        <div style="display: grid; grid-template-columns: 1fr 1fr; gap: 8px;">
                            <button type="button" @click="tipGateway = 'stripe'" :style="tipGateway === 'stripe' ? 'border-color: #8b5cf6; background: rgba(139, 92, 246, 0.15); color: #fff;' : 'border-color: #27272e; background: #14141a; color: #a1a1aa;'" style="display: flex; align-items: center; justify-content: center; gap: 6px; padding: 10px; border-radius: 8px; border-width: 1.5px; border-style: solid; font-size: 12px; font-weight: 600; cursor: pointer; transition: all 0.15s;">
                                <span>💳</span> Card (Stripe)
                            </button>
                            <button type="button" @click="tipGateway = 'paypal'" :style="tipGateway === 'paypal' ? 'border-color: #3b82f6; background: rgba(59, 130, 246, 0.15); color: #fff;' : 'border-color: #27272e; background: #14141a; color: #a1a1aa;'" style="display: flex; align-items: center; justify-content: center; gap: 6px; padding: 10px; border-radius: 8px; border-width: 1.5px; border-style: solid; font-size: 12px; font-weight: 600; cursor: pointer; transition: all 0.15s;">
                                <span>🅿️</span> PayPal
                            </button>
                        </div>
                    </div>

                    <!-- External Tip Link option if creator configured BuyMeACoffee/Stripe -->
                    <template x-if="tipLink">
                        <a :href="tipLink" target="_blank" rel="noopener noreferrer" class="tip-external-btn">
                            ☕ Creator's External Tip Link (BuyMeACoffee / Stripe) <span>↗</span>
                        </a>
                    </template>

                    <!-- Submit Button -->
                    <button type="button" @click="submitTip()" :disabled="tipSubmitting" class="tip-submit-btn">
                        <span x-show="!tipSubmitting">Tip $<span x-text="tipCustomAmount ? tipCustomAmount : tipAmount"></span> with <span x-text="tipGateway === 'paypal' ? 'PayPal' : 'Stripe'"></span> <span>→</span></span>
                        <span x-show="tipSubmitting">Connecting to secure checkout…</span>
                    </button>
                </div>
            </template>
        </div>
    </div>
</body>
</html>

