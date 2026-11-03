{!! '<'.'?xml version="1.0" encoding="UTF-8"?>' !!}
<urlset xmlns="http://www.sitemaps.org/schemas/sitemap/0.9"
        xmlns:image="http://www.google.com/schemas/sitemap-image/1.1">
    <!-- Home Page -->
    <url>
        <loc>{{ url('/') }}</loc>
        <changefreq>daily</changefreq>
        <priority>1.0</priority>
    </url>

    <!-- Categories -->
    @foreach ($categories as $category)
        <url>
            <loc>{{ route('prompts.index', ['category' => $category->slug]) }}</loc>
            <changefreq>weekly</changefreq>
            <priority>0.8</priority>
        </url>
    @endforeach

    <!-- Prompts -->
    @foreach ($prompts as $prompt)
        <url>
            <loc>{{ route('prompts.show', $prompt->slug) }}</loc>
            <lastmod>{{ ($prompt->approved_at ?? $prompt->updated_at)->toAtomString() }}</lastmod>
            <changefreq>monthly</changefreq>
            <priority>0.7</priority>
            @if ($prompt->cover_url)
                <image:image>
                    <image:loc>{{ $prompt->cover_url }}</image:loc>
                    <image:title>{{ htmlspecialchars($prompt->title, ENT_XML1, 'UTF-8') }}</image:title>
                </image:image>
            @endif
        </url>
    @endforeach
</urlset>
