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
    @vite(['resources/css/app.css', 'resources/js/app.js'])
</head>
<body>
    @include('partials.sidebar')

    <main class="app-main">
        <header class="topbar">
            <button class="mobile-menu" type="button" aria-label="Open menu" data-sidebar-toggle>☰</button>
            @hasSection('toolbar')
                @yield('toolbar')
                @include('partials.header-account')
            @else
                <div class="topbar-title">@yield('eyebrow', 'MEIGEN COMMUNITY') <span>·</span> @yield('toolbar-title', 'Prompt studio')</div>
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

    <!-- Global Modals & Alpine App State -->
    <div x-data="globalAppModal()" @keydown.window.cmd.k.prevent="openSearch()" @keydown.window.ctrl.k.prevent="openSearch()" @open-tip-modal.window="openTip($event.detail)">
        <!-- 1. Instant Typo-Tolerant Search Modal (CMD+K) -->
        <div x-show="searchOpen" x-cloak style="display: none;" class="fixed inset-0 z-50 flex items-start justify-center pt-16 px-4 bg-black/80 backdrop-blur-md">
            <div @click.away="searchOpen = false" class="bg-zinc-900 border border-zinc-800 rounded-3xl w-full max-w-2xl shadow-2xl overflow-hidden flex flex-col max-h-[80vh]">
                <!-- Search Input Bar -->
                <div class="p-4 border-b border-zinc-800 flex items-center gap-3 bg-zinc-950/60">
                    <svg class="w-5 h-5 text-violet-400 flex-shrink-0" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z"/></svg>
                    <input type="text" x-ref="searchInput" x-model="searchQuery" @input.debounce.200ms="executeSearch()" placeholder="Instant search 1,800+ prompts, styles, keywords..." class="w-full bg-transparent text-sm text-white placeholder-zinc-500 focus:outline-none">
                    <span class="text-[10px] font-mono px-2 py-0.5 rounded bg-zinc-800 text-zinc-400">ESC</span>
                </div>

                <!-- Facet Filter Chips -->
                <div class="px-4 py-2.5 border-b border-zinc-800/80 bg-zinc-900/90 flex flex-wrap items-center gap-1.5 text-xs">
                    <span class="text-[11px] text-zinc-500 mr-1">Vibe:</span>
                    <button type="button" @click="toggleVibe('')" :class="searchVibe === '' ? 'bg-violet-600 text-white font-semibold' : 'bg-zinc-800 text-zinc-400 hover:text-white'" class="px-2.5 py-0.5 rounded-lg text-[11px] transition">All</button>
                    <button type="button" @click="toggleVibe('dark')" :class="searchVibe === 'dark' ? 'bg-violet-600 text-white font-semibold' : 'bg-zinc-800 text-zinc-400 hover:text-white'" class="px-2.5 py-0.5 rounded-lg text-[11px] transition">Dark/Moody</button>
                    <button type="button" @click="toggleVibe('vibrant')" :class="searchVibe === 'vibrant' ? 'bg-violet-600 text-white font-semibold' : 'bg-zinc-800 text-zinc-400 hover:text-white'" class="px-2.5 py-0.5 rounded-lg text-[11px] transition">Vibrant Neon</button>
                    <button type="button" @click="toggleVibe('warm')" :class="searchVibe === 'warm' ? 'bg-violet-600 text-white font-semibold' : 'bg-zinc-800 text-zinc-400 hover:text-white'" class="px-2.5 py-0.5 rounded-lg text-[11px] transition">Warm 35mm</button>
                    <button type="button" @click="toggleVibe('pastel')" :class="searchVibe === 'pastel' ? 'bg-violet-600 text-white font-semibold' : 'bg-zinc-800 text-zinc-400 hover:text-white'" class="px-2.5 py-0.5 rounded-lg text-[11px] transition">Pastel Ghibli</button>
                </div>

                <!-- Live Results List -->
                <div class="p-3 overflow-y-auto flex-1 space-y-1">
                    <template x-if="searchLoading">
                        <div class="py-12 text-center text-xs text-zinc-500">Searching prompts...</div>
                    </template>

                    <template x-if="!searchLoading && searchResults.length === 0 && searchQuery">
                        <div class="py-12 text-center text-xs text-zinc-500">No prompt matches found for "<span x-text="searchQuery"></span>".</div>
                    </template>

                    <template x-for="item in searchResults" :key="item.id">
                        <a :href="item.url" class="flex items-center gap-3 p-2.5 rounded-2xl hover:bg-zinc-800/80 transition group">
                            <div class="w-12 h-12 rounded-xl bg-zinc-950 overflow-hidden flex-shrink-0 border border-zinc-800">
                                <img :src="item.cover_url" class="w-full h-full object-cover">
                            </div>
                            <div class="flex-1 min-w-0">
                                <div class="flex items-center gap-2">
                                    <span class="text-xs font-semibold text-white group-hover:text-violet-400 transition truncate" x-text="item.title"></span>
                                    <span class="px-2 py-0.2 rounded-full bg-violet-500/10 text-violet-400 text-[10px] font-semibold" x-text="item.category"></span>
                                </div>
                                <p class="text-[11px] text-zinc-400 line-clamp-1 mt-0.5" x-text="item.snippet"></p>
                            </div>
                            <span class="text-zinc-600 text-xs flex-shrink-0 font-mono" x-text="'❤️ ' + item.likes_count"></span>
                        </a>
                    </template>
                </div>
            </div>
        </div>

        <!-- 3. Tip Creator Modal -->
        <div x-show="tipOpen" x-cloak style="display: none;" class="fixed inset-0 z-50 flex items-center justify-center p-4 bg-black/80 backdrop-blur-sm">
            <div @click.away="tipOpen = false" class="bg-zinc-900 border border-zinc-800 rounded-3xl p-6 max-w-sm w-full shadow-2xl space-y-4">
                <!-- Header -->
                <div class="flex items-center justify-between">
                    <div>
                        <div class="flex items-center gap-2">
                            <h3 class="text-base font-bold text-white">Tip <span class="text-violet-400" x-text="tipCreatorName"></span></h3>
                            <span class="text-xs font-mono text-zinc-400 bg-zinc-800 px-2 py-0.5 rounded" x-text="tipCreatorHandle"></span>
                        </div>
                        <p class="text-[11px] text-zinc-400 mt-0.5">Show appreciation and support creator prompts.</p>
                    </div>
                    <button type="button" @click="tipOpen = false" class="text-zinc-500 hover:text-white text-lg">&times;</button>
                </div>

                <!-- Success Screen -->
                <template x-if="tipSuccess">
                    <div class="py-4 text-center space-y-3">
                        <div class="w-14 h-14 mx-auto rounded-full bg-emerald-500/20 border border-emerald-500/40 text-emerald-400 flex items-center justify-center text-2xl font-bold">
                            ✓
                        </div>
                        <h4 class="text-base font-bold text-white">Tip Sent Successfully!</h4>
                        <p class="text-xs text-zinc-300" x-text="tipSuccessMessage"></p>
                        <div class="text-[11px] font-mono text-zinc-500 bg-zinc-950 p-2 rounded-xl border border-zinc-800" x-text="'Ref: ' + tipTransactionRef"></div>
                        <button type="button" @click="tipOpen = false" class="w-full py-2.5 rounded-xl bg-zinc-800 hover:bg-zinc-700 text-white text-xs font-semibold transition">
                            Close
                        </button>
                    </div>
                </template>

                <!-- Tip Form -->
                <template x-if="!tipSuccess">
                    <div class="space-y-4">
                        <!-- Creator Goal if set -->
                        <template x-if="tipGoal">
                            <div class="p-2.5 rounded-xl bg-amber-500/10 border border-amber-500/25 text-amber-300 text-xs flex items-center gap-2">
                                <span class="text-sm">🎯</span>
                                <div>
                                    <strong class="block text-[11px] text-amber-400">Creator Goal</strong>
                                    <span x-text="tipGoal"></span>
                                </div>
                            </div>
                        </template>

                        <!-- Preset Amounts -->
                        <div>
                            <label class="block text-[11px] text-zinc-400 mb-1.5 font-medium">Select Amount</label>
                            <div class="grid grid-cols-4 gap-2">
                                <button type="button" @click="tipAmount = 2; tipCustomAmount = ''" :class="tipAmount === 2 && !tipCustomAmount ? 'bg-violet-600 text-white font-bold ring-2 ring-violet-400/30' : 'bg-zinc-950 text-zinc-300 border border-zinc-800 hover:border-zinc-700'" class="py-2 rounded-xl text-xs transition flex flex-col items-center">
                                    <span>$2</span>
                                    <span class="text-[10px] text-zinc-400 font-normal">☕ Coffee</span>
                                </button>
                                <button type="button" @click="tipAmount = 5; tipCustomAmount = ''" :class="tipAmount === 5 && !tipCustomAmount ? 'bg-violet-600 text-white font-bold ring-2 ring-violet-400/30' : 'bg-zinc-950 text-zinc-300 border border-zinc-800 hover:border-zinc-700'" class="py-2 rounded-xl text-xs transition flex flex-col items-center">
                                    <span>$5</span>
                                    <span class="text-[10px] text-zinc-400 font-normal">⚡ Spark</span>
                                </button>
                                <button type="button" @click="tipAmount = 10; tipCustomAmount = ''" :class="tipAmount === 10 && !tipCustomAmount ? 'bg-violet-600 text-white font-bold ring-2 ring-violet-400/30' : 'bg-zinc-950 text-zinc-300 border border-zinc-800 hover:border-zinc-700'" class="py-2 rounded-xl text-xs transition flex flex-col items-center">
                                    <span>$10</span>
                                    <span class="text-[10px] text-zinc-400 font-normal">🎨 Patron</span>
                                </button>
                                <button type="button" @click="tipAmount = 25; tipCustomAmount = ''" :class="tipAmount === 25 && !tipCustomAmount ? 'bg-violet-600 text-white font-bold ring-2 ring-violet-400/30' : 'bg-zinc-950 text-zinc-300 border border-zinc-800 hover:border-zinc-700'" class="py-2 rounded-xl text-xs transition flex flex-col items-center">
                                    <span>$25</span>
                                    <span class="text-[10px] text-zinc-400 font-normal">🚀 Super</span>
                                </button>
                            </div>
                        </div>

                        <!-- Custom Amount Input -->
                        <div>
                            <label class="block text-[11px] text-zinc-400 mb-1 font-medium">Or Custom Amount ($ USD)</label>
                            <input type="number" min="1" max="500" step="1" x-model="tipCustomAmount" placeholder="e.g. 15" class="w-full bg-zinc-950 border border-zinc-800 rounded-xl px-3 py-2 text-xs text-white placeholder-zinc-500 focus:outline-none focus:ring-1 focus:ring-violet-500">
                        </div>

                        <!-- Custom Note -->
                        <div>
                            <label class="block text-[11px] text-zinc-400 mb-1 font-medium">Note to creator (Optional)</label>
                            <input type="text" x-model="tipNote" maxlength="300" placeholder="Thanks for this amazing prompt!" class="w-full bg-zinc-950 border border-zinc-800 rounded-xl px-3 py-2 text-xs text-white placeholder-zinc-500 focus:outline-none focus:ring-1 focus:ring-violet-500">
                        </div>

                        <!-- External Tip Link option if creator configured BuyMeACoffee/Stripe -->
                        <template x-if="tipLink">
                            <a :href="tipLink" target="_blank" rel="noopener noreferrer" class="w-full flex items-center justify-center gap-2 py-2 px-3 rounded-xl border border-amber-500/30 bg-amber-500/10 hover:bg-amber-500/20 text-amber-300 text-xs font-semibold transition">
                                ☕ Tip via Creator's Link (BuyMeACoffee / Stripe) <span>↗</span>
                            </a>
                        </template>

                        <!-- Submit Button -->
                        <button type="button" @click="submitTip()" :disabled="tipSubmitting" class="w-full py-2.5 rounded-xl bg-gradient-to-r from-violet-600 to-indigo-600 hover:from-violet-500 disabled:opacity-50 text-white text-xs font-bold transition shadow-lg shadow-violet-600/20">
                            <span x-show="!tipSubmitting">Send $<span x-text="tipCustomAmount ? tipCustomAmount : tipAmount"></span> Direct Tip</span>
                            <span x-show="tipSubmitting">Processing tip...</span>
                        </button>
                    </div>
                </template>
            </div>
        </div>
    </div>
</body>
</html>

