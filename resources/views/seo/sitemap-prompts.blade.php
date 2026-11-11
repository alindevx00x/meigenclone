{!! '<'.'?xml version="1.0" encoding="UTF-8"?>' !!}
<urlset xmlns="http://www.sitemaps.org/schemas/sitemap/0.9"
        xmlns:image="http://www.google.com/schemas/sitemap-image/1.1">
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
