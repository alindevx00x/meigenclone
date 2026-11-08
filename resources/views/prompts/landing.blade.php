@extends('layouts.app')

@section('title', $title)
@section('meta_title', $metaTitle)
@section('meta_description', $metaDescription)
@section('eyebrow', 'PROGRAMMATIC DIRECTORY')
@section('toolbar-title', $title)

@section('structured_data')
<script type="application/ld+json">
{
    "@@context": "https://schema.org",
    "@@type": "CollectionPage",
    "name": "{{ $title }}",
    "description": "{{ $metaDescription }}",
    "url": "{{ url()->current() }}",
    "mainEntity": {
        "@@type": "ItemList",
        "numberOfItems": {{ $prompts->total() }},
        "itemListElement": [
            @foreach($prompts->take(6) as $index => $item)
            {
                "@@type": "ListItem",
                "position": {{ $index + 1 }},
                "url": "{{ route('prompts.show', $item->slug) }}",
                "name": "{{ addslashes($item->title) }}"
            }@if (! $loop->last),@endif
            @endforeach
        ]
    }
}
</script>
<script type="application/ld+json">
{
    "@@context": "https://schema.org",
    "@@type": "FAQPage",
    "mainEntity": [
        @foreach($faqs as $faq)
        {
            "@@type": "Question",
            "name": "{{ addslashes($faq['question']) }}",
            "acceptedAnswer": {
                "@@type": "Answer",
                "text": "{{ addslashes($faq['answer']) }}"
            }
        }@if (! $loop->last),@endif
        @endforeach
    ]
}
</script>
@endsection

@section('content')
<div class="workspace-page" style="padding-bottom: 60px;">
    <!-- Programmatic SEO Hero Header -->
    <div class="panel-card" style="padding: 36px 32px; margin-bottom: 28px; background: radial-gradient(ellipse 65% 50% at 95% -10%, rgba(124, 58, 237, 0.12), transparent 70%), #ffffff;">
        <div style="max-width: 860px;">
            <div style="display: flex; align-items: center; gap: 8px; margin-bottom: 12px;">
                <span class="tag-pill" style="display: inline-flex; align-items: center; gap: 5px; padding: 4px 10px; border-radius: 99px; background: #ede9fe; color: #6d28d9; font-size: 11px; font-weight: 700; text-transform: uppercase;">
                    <span>✦</span> KEYWORD CLUSTER
                </span>
                <span style="font-size: 12px; color: #6b7280;">{{ number_format($prompts->total()) }} Curated Prompts</span>
                <span style="color: #d1d5db;">•</span>
                <span style="font-size: 12px; color: #6b7280;">Updated Daily for Midjourney v6 &amp; Flux</span>
            </div>
            <h1 style="font-size: 32px; font-weight: 800; color: #111827; letter-spacing: -0.8px; margin: 0 0 12px; line-height: 1.25;">
                {{ $title }}
            </h1>
            <p style="font-size: 15px; line-height: 1.6; color: #4b5563; margin: 0 0 20px;">
                Explore ready-to-use prompts, verified generation parameters, and lighting styles designed to create stunning commercial-grade visuals with zero setup friction.
            </p>

            <!-- Quick Filter & Action Chips -->
            <div style="display: flex; flex-wrap: wrap; gap: 8px; align-items: center;">
                <a href="{{ route('prompts.index') }}" class="primary-button" style="min-height: 36px;">
                    <span>✦</span> Browse Gallery
                </a>
                <a href="{{ route('dashboard.prompts.create') }}" class="outline-button" style="min-height: 36px;">
                    <span>+</span> Submit Prompt
                </a>
                @if(isset($category))
                    <span style="font-size: 12px; color: #6b7280; margin-left: 8px;">Top models:</span>
                    @foreach($topModels as $tm)
                        <span style="font-size: 11.5px; padding: 3px 8px; border-radius: 6px; background: #f3f4f6; color: #374151;">{{ $tm->model }} ({{ $tm->count }})</span>
                    @endforeach
                @endif
            </div>
        </div>
    </div>

    <!-- Related Keyword Clusters Navigation -->
    <div style="display: flex; align-items: center; gap: 8px; overflow-x: auto; padding-bottom: 12px; margin-bottom: 24px;" class="model-tabs">
        <span style="font-size: 12px; font-weight: 600; color: #6b7280; white-space: nowrap;">Popular Styles:</span>
        @foreach(['cyberpunk' => 'Cyberpunk Neon', 'minimalist' => 'Minimalist Studio', 'cinematic' => '35mm Film', 'anime' => 'Anime Cel-Shading', 'isometric' => '3D Isometric'] as $vKey => $vLabel)
            <a href="{{ route('prompts.style_landing', $vKey) }}" class="model-tab {{ request()->route('vibe') === $vKey ? 'is-active' : '' }}" style="text-decoration: none;">
                {{ $vLabel }}
            </a>
        @endforeach
    </div>

    <!-- Prompts Grid -->
    @if ($prompts->isEmpty())
        <div class="empty-state">
            <span>✧</span>
            <h2>No prompts currently matched in this cluster</h2>
            <p>Check back shortly as our automated ingestion imports fresh Midjourney &amp; Flux prompts daily.</p>
            <a class="primary-button" href="{{ route('prompts.index') }}">Explore Full Catalog →</a>
        </div>
    @else
        <div class="prompt-grid">
            @foreach ($prompts as $prompt)
                @include('partials.prompt-card', ['prompt' => $prompt])
            @endforeach
        </div>
        <div class="pagination-wrap" style="margin-top: 36px;">
            {{ $prompts->links() }}
        </div>
    @endif

    <!-- Programmatic SEO FAQ & Knowledge Block (Schema-Optimized) -->
    <div class="panel-card" style="padding: 36px; margin-top: 48px;">
        <span class="section-kicker">FREQUENTLY ASKED QUESTIONS</span>
        <h2 style="font-size: 20px; font-weight: 700; color: #111827; margin: 4px 0 20px;">Everything you need to know about this collection</h2>

        <div style="display: flex; flex-direction: column; gap: 18px;">
            @foreach($faqs as $faq)
                <div style="border-bottom: 1px solid #f3f4f6; padding-bottom: 16px;">
                    <h3 style="font-size: 15px; font-weight: 600; color: #1f2937; margin: 0 0 6px;">{{ $faq['question'] }}</h3>
                    <p style="font-size: 13.5px; line-height: 1.6; color: #6b7280; margin: 0;">{{ $faq['answer'] }}</p>
                </div>
            @endforeach
        </div>
    </div>
</div>
@endsection
