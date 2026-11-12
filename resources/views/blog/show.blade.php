@extends('layouts.app')

@section('title', $post->meta_title ?: $post->title)
@section('meta_title', ($post->meta_title ?: $post->title) . ' | ' . $directorySettings['site_name'])
@section('meta_description', $post->meta_description ?: ($post->excerpt ?: \Illuminate\Support\Str::limit(strip_tags($post->content), 160)))
@section('og_image', $post->cover_image_url)
@section('eyebrow', strtoupper($directorySettings['site_name'] ?? 'PROMPT') . ' BLOG')
@section('toolbar-title', $post->category ? $post->category->name : 'Guide')

@section('structured_data')
<script type="application/ld+json">
{
    "@@context": "https://schema.org",
    "@type": "BlogPosting",
    "mainEntityOfPage": {
        "@type": "WebPage",
        "@id": "{{ route('blog.show', $post->slug) }}"
    },
    "headline": "{{ addslashes($post->title) }}",
    "description": "{{ addslashes($post->meta_description ?: ($post->excerpt ?: \Illuminate\Support\Str::limit(strip_tags($post->content), 160))) }}",
    @if ($post->cover_image_url)
    "image": [
        "{{ $post->cover_image_url }}"
    ],
    @endif
    "datePublished": "{{ $post->published_at ? $post->published_at->toIso8601String() : $post->created_at->toIso8601String() }}",
    "dateModified": "{{ $post->updated_at->toIso8601String() }}",
    "author": {
        "@type": "Person",
        "name": "{{ addslashes($post->author->name ?? 'PromptImage Editorial Team') }}"
    },
    "publisher": {
        "@type": "Organization",
        "name": "{{ $directorySettings['site_name'] }}",
        "url": "{{ url('/') }}",
        @if (!empty($directorySettings['logo_url']))
        "logo": {
            "@type": "ImageObject",
            "url": "{{ $directorySettings['logo_url'] }}"
        }
        @endif
    }
}
</script>
@endsection

@section('content')
<div class="workspace-page blog-article-page" style="max-width: 1100px; margin: 0 auto; padding: 20px 20px 80px;">

    <!-- Breadcrumbs -->
    <nav aria-label="Breadcrumb" style="margin-bottom: 20px; font-size: 12.5px; color: #64748b; display: flex; align-items: center; gap: 8px; flex-wrap: wrap;">
        <a href="{{ route('prompts.index') }}" style="color: #64748b; text-decoration: none;">Home</a>
        <span>/</span>
        <a href="{{ route('blog.index') }}" style="color: #64748b; text-decoration: none;">Blog</a>
        @if ($post->category)
            <span>/</span>
            <a href="{{ route('blog.category', $post->category->slug) }}" style="color: #6366f1; font-weight: 500; text-decoration: none;">{{ $post->category->name }}</a>
        @endif
        <span>/</span>
        <span style="color: #0f172a; font-weight: 500; max-width: 320px; overflow: hidden; text-overflow: ellipsis; white-space: nowrap;">{{ $post->title }}</span>
    </nav>

    <!-- Article Header -->
    <header style="max-width: 820px; margin-bottom: 30px;">
        <div style="display: flex; gap: 10px; align-items: center; margin-bottom: 14px; flex-wrap: wrap;">
            @if ($post->category)
                <a href="{{ route('blog.category', $post->category->slug) }}" 
                   style="display: inline-block; padding: 3px 10px; background: #eef2ff; color: #4338ca; border-radius: 9999px; font-size: 11.5px; font-weight: 700; text-transform: uppercase; letter-spacing: 0.05em; text-decoration: none;">
                    {{ $post->category->name }}
                </a>
            @endif
            <span style="font-size: 12px; color: #94a3b8;">&bull;</span>
            <span style="font-size: 12.5px; color: #64748b;">⏱ {{ $post->reading_time_minutes }} min read</span>
            <span style="font-size: 12px; color: #94a3b8;">&bull;</span>
            <span style="font-size: 12.5px; color: #64748b;">{{ $post->published_at ? $post->published_at->format('M d, Y') : '' }}</span>
        </div>

        <h1 style="font-size: clamp(26px, 3.8vw, 38px); font-weight: 800; color: #0f172a; line-height: 1.2; letter-spacing: -0.02em; margin-bottom: 16px;">
            {{ $post->title }}
        </h1>

        @if ($post->excerpt)
            <p style="font-size: clamp(15px, 2vw, 17.5px); color: #475569; line-height: 1.6; margin-bottom: 22px;">
                {{ $post->excerpt }}
            </p>
        @endif

        <!-- Author & Social Share Bar -->
        <div style="display: flex; justify-content: space-between; align-items: center; border-top: 1px solid #f1f5f9; border-bottom: 1px solid #f1f5f9; padding: 14px 0; flex-wrap: wrap; gap: 14px;">
            <div style="display: flex; align-items: center; gap: 10px;">
                <span style="width: 36px; height: 36px; border-radius: 50%; background: #6366f1; color: #fff; font-weight: 700; font-size: 14px; display: grid; place-items: center;">
                    {{ $post->author ? substr($post->author->name, 0, 1) : 'P' }}
                </span>
                <div>
                    <div style="font-size: 13px; font-weight: 600; color: #0f172a;">{{ $post->author->name ?? 'Prompt Team' }}</div>
                    <div style="font-size: 11.5px; color: #64748b;">Prompt Engineer &amp; Visual Curator</div>
                </div>
            </div>

            <!-- Share buttons -->
            <div style="display: flex; gap: 8px; align-items: center;">
                <button type="button" onclick="navigator.clipboard.writeText(window.location.href); alert('Article link copied to clipboard!');" 
                        class="outline-button" style="padding: 5px 10px; font-size: 12px; display: inline-flex; align-items: center; gap: 5px;" title="Copy Article URL">
                    🔗 Copy Link
                </button>
                <a href="https://twitter.com/intent/tweet?url={{ urlencode(url()->current()) }}&text={{ urlencode($post->title) }}" target="_blank" rel="noopener"
                   class="outline-button" style="padding: 5px 10px; font-size: 12px;" title="Share on X">
                    𝕏 Share
                </a>
                <a href="https://pinterest.com/pin/create/button/?url={{ urlencode(url()->current()) }}&media={{ urlencode($post->cover_image_url ?? '') }}&description={{ urlencode($post->title) }}" target="_blank" rel="noopener"
                   class="outline-button" style="padding: 5px 10px; font-size: 12px;" title="Pin to Pinterest">
                    📌 Pin
                </a>
            </div>
        </div>
    </header>

    <!-- Featured Cover Image Banner -->
    @if ($post->cover_image_url)
        <div style="margin-bottom: 36px; border-radius: 12px; overflow: hidden; border: 1.5px solid #e2e8f0; background: #0f172a; max-height: 480px;">
            <img src="{{ $post->cover_image_url }}" alt="{{ $post->title }}" style="width: 100%; max-height: 480px; object-fit: cover; display: block;">
        </div>
    @endif

    <!-- Content & Sticky TOC Layout -->
    <div style="display: grid; grid-template-columns: minmax(0, 1fr) @if(!empty($toc)) 260px @endif; gap: 36px; align-items: start;">
        
        <!-- Main Article Content Body -->
        <article class="blog-article-content panel-card" style="padding: clamp(20px, 4vw, 40px); background: #fff; border-radius: 12px; border: 1.5px solid #e2e8f0; font-size: 16px; line-height: 1.8; color: #1e293b; overflow-wrap: break-word;">
            {!! $contentWithAnchors !!}

            <!-- Author Card at Bottom -->
            <div style="margin-top: 48px; padding-top: 24px; border-top: 1.5px solid #f1f5f9; display: flex; gap: 16px; align-items: center;">
                <span style="width: 48px; height: 48px; border-radius: 50%; background: #6366f1; color: #fff; font-weight: 700; font-size: 18px; display: grid; place-items: center; flex-shrink: 0;">
                    {{ $post->author ? substr($post->author->name, 0, 1) : 'P' }}
                </span>
                <div>
                    <h3 style="font-size: 15px; font-weight: 700; color: #0f172a; margin: 0 0 4px;">Written by {{ $post->author->name ?? 'Prompt Team' }}</h3>
                    <p style="font-size: 13px; color: #64748b; margin: 0; line-height: 1.5;">
                        Specializing in visual prompt generation, negative prompt optimization, and AI tool benchmarking across Midjourney, Flux.1, and modern diffusion models.
                    </p>
                </div>
            </div>
        </article>

        <!-- Sticky Table of Contents Sidebar -->
        @if (!empty($toc))
            <aside style="position: sticky; top: 80px; display: flex; flex-direction: column; gap: 20px;">
                <div class="panel-card" style="padding: 18px; border-radius: 10px; border: 1.5px solid #e2e8f0; background: #f8fafc;">
                    <div style="font-size: 11.5px; font-weight: 700; text-transform: uppercase; letter-spacing: 0.05em; color: #475569; margin-bottom: 12px; display: flex; align-items: center; gap: 6px;">
                        <span>📑</span> Table of Contents
                    </div>
                    <nav style="display: flex; flex-direction: column; gap: 8px;">
                        @foreach ($toc as $item)
                            <a href="#{{ $item['anchor'] }}" 
                               style="font-size: {{ $item['level'] === 2 ? '12.5px' : '11.5px' }}; color: #475569; text-decoration: none; padding-left: {{ $item['level'] === 3 ? '12px' : '0' }}; line-height: 1.4; border-left: {{ $item['level'] === 2 ? '2px solid transparent' : 'none' }};"
                               onmouseover="this.style.color='#6366f1'" onmouseout="this.style.color='#475569'">
                                {{ $item['title'] }}
                            </a>
                        @endforeach
                    </nav>
                </div>

                <!-- Quick CTA -->
                <div class="panel-card" style="padding: 18px; border-radius: 10px; border: 1.5px solid #e2e8f0; background: #fff; text-align: center;">
                    <span style="font-size: 20px;">✦</span>
                    <div style="font-size: 13px; font-weight: 700; color: #0f172a; margin: 6px 0;">Looking for prompts?</div>
                    <p style="font-size: 11.5px; color: #64748b; margin-bottom: 12px; line-height: 1.4;">Explore over 4,900+ ready-to-copy AI art prompts.</p>
                    <a href="{{ route('prompts.index') }}" class="primary-button" style="font-size: 11.5px; padding: 6px 14px; width: 100%; justify-content: center;">
                        Browse Library
                    </a>
                </div>
            </aside>
        @endif
    </div>

    <!-- Related Articles Section -->
    @if ($relatedPosts->isNotEmpty())
        <section style="margin-top: 60px; padding-top: 36px; border-top: 1.5px solid #e2e8f0;">
            <div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 24px; flex-wrap: wrap; gap: 10px;">
                <div>
                    <span style="font-size: 11px; font-weight: 700; text-transform: uppercase; color: #6366f1; letter-spacing: 0.05em;">CONTINUE LEARNING</span>
                    <h2 style="font-size: 22px; font-weight: 800; color: #0f172a; margin-top: 2px;">Related Guides &amp; Tutorials</h2>
                </div>
                <a href="{{ route('blog.index') }}" class="outline-button" style="font-size: 12px;">All Articles →</a>
            </div>

            <div style="display: grid; grid-template-columns: repeat(auto-fit, minmax(280px, 1fr)); gap: 20px;">
                @foreach ($relatedPosts as $rel)
                    <article class="panel-card" style="padding: 0; overflow: hidden; border: 1.5px solid #e2e8f0; border-radius: 10px; background: #fff; display: flex; flex-direction: column;">
                        <a href="{{ route('blog.show', $rel->slug) }}" style="display: block; height: 160px; background: #0f172a; overflow: hidden;">
                            @if ($rel->cover_image_url)
                                <img src="{{ $rel->cover_image_url }}" alt="{{ $rel->title }}" style="width: 100%; height: 100%; object-fit: cover;">
                            @else
                                <div style="width: 100%; height: 100%; display: grid; place-items: center; color: #a5b4fc; font-size: 28px;">✦</div>
                            @endif
                        </a>
                        <div style="padding: 16px; display: flex; flex-direction: column; flex: 1; justify-content: space-between;">
                            <div>
                                <span style="font-size: 11px; color: #6366f1; font-weight: 600;">{{ $rel->category ? $rel->category->name : 'Guide' }}</span>
                                <h3 style="font-size: 15px; font-weight: 700; color: #0f172a; margin: 6px 0; line-height: 1.35;">
                                    <a href="{{ route('blog.show', $rel->slug) }}" style="color: inherit; text-decoration: none;">
                                        {{ $rel->title }}
                                    </a>
                                </h3>
                            </div>
                            <span style="font-size: 11.5px; color: #94a3b8; margin-top: 10px;">⏱ {{ $rel->reading_time_minutes }} min read</span>
                        </div>
                    </article>
                @endforeach
            </div>
        </section>
    @endif

</div>
@endsection
