{!! '<'.'?xml version="1.0" encoding="UTF-8"?>' !!}
<urlset xmlns="http://www.sitemaps.org/schemas/sitemap/0.9">
    <!-- Home Page -->
    <url>
        <loc>{{ $baseUrl }}/</loc>
        <changefreq>daily</changefreq>
        <priority>1.0</priority>
    </url>

    <!-- Prompts Discovery & Search -->
    <url>
        <loc>{{ $baseUrl }}/prompts</loc>
        <changefreq>daily</changefreq>
        <priority>0.9</priority>
    </url>
    <url>
        <loc>{{ $baseUrl }}/creators</loc>
        <changefreq>weekly</changefreq>
        <priority>0.8</priority>
    </url>

    <!-- Category Landing Pages -->
    @foreach ($categories as $category)
        <url>
            <loc>{{ $baseUrl }}/prompts/category/{{ $category->slug }}</loc>
            <changefreq>daily</changefreq>
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

    <!-- Public Creator Profiles -->
    @foreach ($creators as $creator)
        <url>
            <loc>{{ $baseUrl . '/creators/@' . ltrim($creator->username ?: $creator->id, '@') }}</loc>
            <changefreq>weekly</changefreq>
            <priority>0.7</priority>
        </url>
    @endforeach

    <!-- Information, Legal & Contact Pages -->
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
</urlset>
