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
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700&family=Poppins:wght@400;500;600;700&display=swap" rel="stylesheet">
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
    </main>
    <div class="sidebar-scrim" data-sidebar-toggle></div>
    <div class="toast" id="copy-toast" role="status">Prompt copied to clipboard</div>
</body>
</html>
