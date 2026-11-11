{!! '<'.'?xml version="1.0" encoding="UTF-8"?>' !!}
<urlset xmlns="http://www.sitemaps.org/schemas/sitemap/0.9"
        xmlns:image="http://www.google.com/schemas/sitemap-image/1.1">
    <!-- Home Page -->
    <url>
        <loc>{{ $baseUrl }}/</loc>
        <changefreq>daily</changefreq>
        <priority>1.0</priority>
    </url>

    <!-- Categories -->
    @foreach ($categories as $category)
        <url>
            <loc>{{ $baseUrl }}/prompts/category/{{ $category->slug }}</loc>
            <changefreq>weekly</changefreq>
            <priority>0.85</priority>
        </url>
    @endforeach

    <!-- Popular Style Landing Pages -->
    @foreach (['cyberpunk', 'photorealistic', 'anime', 'cinematic', 'minimalist', 'fantasy', 'vintage', '3d-render'] as $vibe)
        <url>
            <loc>{{ $baseUrl }}/prompts/style/{{ $vibe }}</loc>
            <changefreq>weekly</changefreq>
            <priority>0.8</priority>
        </url>
    @endforeach

    <!-- Public Creators -->
    @if (isset($creators))
        @foreach ($creators as $creator)
            <url>
                <loc>{{ $baseUrl . '/creators/@' . ltrim($creator->username, '@') }}</loc>
                <changefreq>weekly</changefreq>
                <priority>0.7</priority>
            </url>
        @endforeach
    @endif

    <!-- Information & Contact Pages -->
    <url>
        <loc>{{ $baseUrl }}/contact</loc>
        <changefreq>monthly</changefreq>
        <priority>0.5</priority>
    </url>
    <url>
        <loc>{{ $baseUrl }}/privacy</loc>
        <changefreq>monthly</changefreq>
        <priority>0.4</priority>
    </url>
    <url>
        <loc>{{ $baseUrl }}/terms</loc>
        <changefreq>monthly</changefreq>
        <priority>0.4</priority>
    </url>
    <url>
        <loc>{{ $baseUrl }}/gdpr</loc>
        <changefreq>monthly</changefreq>
        <priority>0.4</priority>
    </url>
    <url>
        <loc>{{ $baseUrl }}/cookies</loc>
        <changefreq>monthly</changefreq>
        <priority>0.4</priority>
    </url>

    <!-- Prompts with Google Images XML Schema -->
    @foreach ($prompts as $prompt)
        <url>
            <loc>{{ $baseUrl }}/prompts/{{ $prompt->slug }}</loc>
            <lastmod>{{ ($prompt->approved_at ?? $prompt->updated_at)->toAtomString() }}</lastmod>
            <changefreq>weekly</changefreq>
            <priority>0.8</priority>
            @if ($prompt->cover_url)
                <image:image>
                    <image:loc>{{ $prompt->cover_url }}</image:loc>
                    <image:title>{{ htmlspecialchars($prompt->title, ENT_XML1, 'UTF-8') }}</image:title>
                    <image:caption>{{ htmlspecialchars(\Illuminate\Support\Str::limit($prompt->prompt_text, 300), ENT_XML1, 'UTF-8') }}</image:caption>
                </image:image>
            @endif
        </url>
    @endforeach
</urlset>
