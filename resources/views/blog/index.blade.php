@extends('layouts.app')

@php
    $pageTitle = $activeCategory 
        ? $activeCategory->name . ' — AI Prompts Blog & Guides' 
        : 'AI Prompt Guides, Tutorials & Creative Workflows — Blog';
    $metaDescription = $activeCategory && $activeCategory->description
        ? $activeCategory->description
        : 'Explore in-depth AI prompt engineering guides, Midjourney v6 tips, Flux.1 workflows, and commercial visual prompt breakdowns. Written for creators and prompt artists.';
@endphp

@section('title', $pageTitle)
@section('meta_title', $pageTitle . ' | ' . $directorySettings['site_name'])
@section('meta_description', $metaDescription)
@section('eyebrow', strtoupper($directorySettings['site_name'] ?? 'PROMPT') . ' BLOG')
@section('toolbar-title', 'Articles & Guides')

@section('structured_data')
<script type="application/ld+json">
{
    "@@context": "https://schema.org",
    "@type": "Blog",
    "name": "{{ $directorySettings['site_name'] }} Blog",
    "url": "{{ route('blog.index') }}",
    "description": "{{ $metaDescription }}",
    "publisher": {
        "@type": "Organization",
        "name": "{{ $directorySettings['site_name'] }}",
        "url": "{{ url('/') }}"
    }
}
</script>
@endsection

@section('content')
<div class="workspace-page blog-page" style="max-width: 1280px; margin: 0 auto; padding: 24px 20px 60px;">

    <!-- Blog Hero Header -->
    <header style="text-align: center; max-width: 760px; margin: 0 auto 36px; padding: 10px 0;">
        <span style="display: inline-block; padding: 4px 12px; background: #eef2ff; color: #4f46e5; border-radius: 9999px; font-size: 12px; font-weight: 700; text-transform: uppercase; letter-spacing: 0.06em; margin-bottom: 12px;">
            ✦ PROMPT ENGINEERING &amp; GUIDES
        </span>
        <h1 style="font-size: clamp(28px, 4vw, 42px); font-weight: 800; color: #0f172a; line-height: 1.18; letter-spacing: -0.025em; margin-bottom: 14px;">
            Master Modern AI Visual Prompting
        </h1>
        <p style="font-size: clamp(15px, 2vw, 17px); color: #64748b; line-height: 1.6; margin: 0 auto;">
            Actionable prompt formulas, model comparisons (Midjourney v6.1, Flux.1, Seedance), and commercial workflows curated by prompt artists.
        </p>
    </header>

    <!-- Search & Category Filter Navigation -->
    <div style="margin-bottom: 32px; display: flex; flex-direction: column; gap: 14px;">
        <div style="display: flex; justify-content: space-between; align-items: center; flex-wrap: wrap; gap: 12px;">
            <!-- Category Pills -->
            <div style="display: flex; gap: 8px; flex-wrap: wrap; align-items: center;">
                <a href="{{ route('blog.index') }}" 
                   style="padding: 7px 16px; border-radius: 9999px; font-size: 13px; font-weight: 600; text-decoration: none; transition: all 0.15s ease; {{ !$activeCategory ? 'background: #0f172a; color: #fff; box-shadow: 0 2px 6px rgba(15,23,42,0.15);' : 'background: #f1f5f9; color: #475569; border: 1.5px solid #e2e8f0;' }}">
                    All Articles
                </a>
                @foreach ($categories as $cat)
                    <a href="{{ route('blog.category', $cat->slug) }}" 
                       style="padding: 7px 16px; border-radius: 9999px; font-size: 13px; font-weight: 600; text-decoration: none; transition: all 0.15s ease; {{ $activeCategory && $activeCategory->id === $cat->id ? 'background: #6366f1; color: #fff; box-shadow: 0 2px 6px rgba(99,102,241,0.25);' : 'background: #f1f5f9; color: #475569; border: 1.5px solid #e2e8f0;' }}">
                        {{ $cat->name }} <span style="font-size: 11px; opacity: 0.85;">({{ $cat->posts_count }})</span>
                    </a>
                @endforeach
            </div>

            <!-- Search Form -->
            <form action="{{ route('blog.index') }}" method="GET" style="display: flex; gap: 8px; align-items: center;">
                @if ($activeCategory)
                    <input type="hidden" name="category" value="{{ $activeCategory->slug }}">
                @endif
                <div style="position: relative;">
                    <input type="search" name="q" value="{{ request('q') }}" placeholder="Search articles…" 
                           style="height: 38px; width: 220px; padding: 0 12px 0 32px; border: 1.5px solid #cbd5e1; border-radius: 9999px; font-size: 13px; color: #1e293b; background: #fff;">
                    <span style="position: absolute; left: 11px; top: 10px; color: #94a3b8; font-size: 13px;">🔍</span>
                </div>
            </form>
        </div>
    </div>

    <!-- Featured Post Hero Card (Only shown on Page 1 when not searching) -->
    @if ($featuredPost && !request('category'))
        <article class="panel-card" style="margin-bottom: 36px; padding: 0; overflow: hidden; border: 1.5px solid #e2e8f0; border-radius: 14px; background: #fff; display: grid; grid-template-columns: repeat(auto-fit, minmax(320px, 1fr)); align-items: stretch; transition: transform 0.2s ease, box-shadow 0.2s ease;">
            <a href="{{ route('blog.show', $featuredPost->slug) }}" style="display: block; position: relative; min-height: 280px; overflow: hidden; background: #0f172a;">
                @if ($featuredPost->cover_image_url)
                    <img src="{{ $featuredPost->cover_image_url }}" alt="{{ $featuredPost->title }}" 
                         style="width: 100%; height: 100%; object-fit: cover; transition: transform 0.35s ease;"
                         onmouseover="this.style.transform='scale(1.04)'" onmouseout="this.style.transform='scale(1)'">
                @else
                    <div style="width: 100%; height: 100%; display: grid; place-items: center; background: linear-gradient(135deg, #1e1e2e, #312e81); color: #a5b4fc; font-size: 48px;">
                        ✦
                    </div>
                @endif
                <span style="position: absolute; top: 16px; left: 16px; background: rgba(15, 23, 42, 0.85); backdrop-filter: blur(8px); color: #38bdf8; font-size: 11px; font-weight: 700; padding: 4px 10px; border-radius: 9999px; text-transform: uppercase; letter-spacing: 0.05em; border: 1px solid rgba(255,255,255,0.15);">
                    Featured Guide
                </span>
            </a>

            <div style="padding: 32px 28px; display: flex; flex-direction: column; justify-content: space-between;">
                <div>
                    <div style="display: flex; gap: 10px; align-items: center; margin-bottom: 12px; font-size: 12px; color: #64748b;">
                        @if ($featuredPost->category)
                            <a href="{{ route('blog.category', $featuredPost->category->slug) }}" style="color: #6366f1; font-weight: 700; text-decoration: none; text-transform: uppercase; letter-spacing: 0.04em;">
                                {{ $featuredPost->category->name }}
                            </a>
                            <span>&bull;</span>
                        @endif
                        <span>⏱ {{ $featuredPost->reading_time_minutes }} min read</span>
                        <span>&bull;</span>
                        <span>{{ $featuredPost->published_at ? $featuredPost->published_at->format('M d, Y') : '' }}</span>
                    </div>

                    <h2 style="font-size: clamp(20px, 2.4vw, 26px); font-weight: 800; color: #0f172a; line-height: 1.25; margin-bottom: 14px; letter-spacing: -0.015em;">
                        <a href="{{ route('blog.show', $featuredPost->slug) }}" style="color: inherit; text-decoration: none;" onmouseover="this.style.color='#6366f1'" onmouseout="this.style.color='inherit'">
                            {{ $featuredPost->title }}
                        </a>
                    </h2>

                    <p style="font-size: 14.5px; color: #475569; line-height: 1.6; margin-bottom: 20px;">
                        {{ $featuredPost->excerpt ?: \Illuminate\Support\Str::limit(strip_tags($featuredPost->content), 180) }}
                    </p>
                </div>

                <div style="display: flex; justify-content: space-between; align-items: center; border-top: 1px solid #f1f5f9; padding-top: 18px;">
                    <div style="display: flex; align-items: center; gap: 10px;">
                        <span style="width: 32px; height: 32px; border-radius: 50%; background: #6366f1; color: #fff; font-weight: 700; font-size: 13px; display: grid; place-items: center;">
                            {{ $featuredPost->author ? substr($featuredPost->author->name, 0, 1) : 'P' }}
                        </span>
                        <div>
                            <div style="font-size: 12.5px; font-weight: 600; color: #1e293b;">{{ $featuredPost->author->name ?? 'Prompt Editor' }}</div>
                            <div style="font-size: 11px; color: #94a3b8;">Staff Writer &amp; Prompt Engineer</div>
                        </div>
                    </div>

                    <a href="{{ route('blog.show', $featuredPost->slug) }}" class="primary-button" style="font-size: 13px; padding: 7px 16px; border-radius: 9999px;">
                        Read Guide <span>→</span>
                    </a>
                </div>
            </div>
        </article>
    @endif

    <!-- Articles Grid -->
    <div style="display: grid; grid-template-columns: repeat(auto-fill, minmax(320px, 1fr)); gap: 24px; margin-bottom: 40px;">
        @forelse ($posts as $post)
            @if ($featuredPost && !request('category') && $post->id === $featuredPost->id)
                @continue
            @endif
            <article class="panel-card" style="padding: 0; overflow: hidden; border: 1.5px solid #e2e8f0; border-radius: 12px; background: #fff; display: flex; flex-direction: column; transition: transform 0.18s ease, box-shadow 0.18s ease;">
                <a href="{{ route('blog.show', $post->slug) }}" style="display: block; position: relative; height: 190px; overflow: hidden; background: #0f172a;">
                    @if ($post->cover_image_url)
                        <img src="{{ $post->cover_image_url }}" alt="{{ $post->title }}" 
                             loading="lazy"
                             style="width: 100%; height: 100%; object-fit: cover; transition: transform 0.3s ease;"
                             onmouseover="this.style.transform='scale(1.05)'" onmouseout="this.style.transform='scale(1)'">
                    @else
                        <div style="width: 100%; height: 100%; display: grid; place-items: center; background: linear-gradient(135deg, #1e1e2e, #2d3748); color: #a5b4fc; font-size: 32px;">
                            ✦
                        </div>
                    @endif
                    @if ($post->category)
                        <span style="position: absolute; bottom: 12px; left: 12px; background: rgba(15, 23, 42, 0.85); backdrop-filter: blur(6px); color: #fff; font-size: 11px; font-weight: 600; padding: 3px 9px; border-radius: 4px; border: 1px solid rgba(255,255,255,0.1);">
                            {{ $post->category->name }}
                        </span>
                    @endif
                </a>

                <div style="padding: 20px; display: flex; flex-direction: column; flex: 1; justify-content: space-between;">
                    <div>
                        <div style="display: flex; gap: 8px; align-items: center; margin-bottom: 8px; font-size: 11.5px; color: #94a3b8;">
                            <span>⏱ {{ $post->reading_time_minutes }} min read</span>
                            <span>&bull;</span>
                            <span>{{ $post->published_at ? $post->published_at->format('M d, Y') : '' }}</span>
                        </div>

                        <h3 style="font-size: 17px; font-weight: 700; color: #0f172a; line-height: 1.35; margin-bottom: 10px;">
                            <a href="{{ route('blog.show', $post->slug) }}" style="color: inherit; text-decoration: none;" onmouseover="this.style.color='#6366f1'" onmouseout="this.style.color='inherit'">
                                {{ $post->title }}
                            </a>
                        </h3>

                        <p style="font-size: 13.5px; color: #475569; line-height: 1.55; margin-bottom: 16px;">
                            {{ $post->excerpt ?: \Illuminate\Support\Str::limit(strip_tags($post->content), 120) }}
                        </p>
                    </div>

                    <div style="display: flex; justify-content: space-between; align-items: center; border-top: 1px solid #f1f5f9; padding-top: 14px; margin-top: auto;">
                        <span style="font-size: 12px; font-weight: 500; color: #64748b;">
                            By {{ $post->author->name ?? 'Prompt Team' }}
                        </span>
                        <a href="{{ route('blog.show', $post->slug) }}" style="font-size: 12.5px; font-weight: 600; color: #6366f1; text-decoration: none; display: inline-flex; align-items: center; gap: 4px;">
                            Read <span>→</span>
                        </a>
                    </div>
                </div>
            </article>
        @empty
            <div style="grid-column: 1 / -1; text-align: center; padding: 48px 20px; background: #fff; border: 1.5px dashed #cbd5e1; border-radius: 12px;">
                <p style="font-size: 16px; font-weight: 600; color: #1e293b; margin-bottom: 8px;">No articles found</p>
                <p style="font-size: 14px; color: #64748b; margin-bottom: 16px;">Try adjusting your search terms or view all categories.</p>
                <a href="{{ route('blog.index') }}" class="primary-button">View All Articles</a>
            </div>
        @endforelse
    </div>

    <!-- Pagination -->
    <div>
        {{ $posts->links() }}
    </div>

    <!-- Newsletter In-feed Section -->
    <section class="panel-card" style="margin-top: 48px; padding: 36px 28px; background: linear-gradient(135deg, #0f172a, #1e1b4b); border: none; border-radius: 16px; color: #fff; text-align: center;">
        <span style="font-size: 12px; font-weight: 700; color: #a5b4fc; text-transform: uppercase; letter-spacing: 0.05em; display: inline-block; margin-bottom: 8px;">
            STAY AHEAD OF AI GENERATION
        </span>
        <h2 style="font-size: clamp(22px, 3vw, 30px); font-weight: 800; color: #fff; margin-bottom: 10px;">
            Get The Weekly Prompt Breakdown
        </h2>
        <p style="max-width: 540px; margin: 0 auto 24px; color: #cbd5e1; font-size: 14.5px; line-height: 1.6;">
            Join thousands of creators receiving our hand-tested Midjourney formulas, Flux.1 workflows, and new visual styles every Friday.
        </p>
        <form action="{{ route('newsletter.subscribe') }}" method="POST" style="display: flex; gap: 10px; max-width: 460px; margin: 0 auto; flex-wrap: wrap;">
            @csrf
            <input type="email" name="email" required placeholder="Enter your email address…" 
                   style="flex: 1 1 240px; height: 42px; border: 1px solid rgba(255,255,255,0.25); border-radius: 8px; padding: 0 14px; background: rgba(255,255,255,0.08); color: #fff; font-size: 14px;">
            <button type="submit" class="primary-button" style="height: 42px; padding: 0 20px; border-radius: 8px; background: #6366f1; border-color: #6366f1;">
                Subscribe Free
            </button>
        </form>
    </section>

</div>
@endsection
