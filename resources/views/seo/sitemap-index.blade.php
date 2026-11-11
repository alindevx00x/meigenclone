{!! '<'.'?xml version="1.0" encoding="UTF-8"?>' !!}
<sitemapindex xmlns="http://www.sitemaps.org/schemas/sitemap/0.9">
    <!-- Main Pages, Categories & Creators Sitemap -->
    <sitemap>
        <loc>{{ $baseUrl }}/sitemap-main.xml</loc>
        <lastmod>{{ now()->toAtomString() }}</lastmod>
    </sitemap>

    <!-- Complete Prompts Catalog with Google Image Search Tags -->
    <sitemap>
        <loc>{{ $baseUrl }}/sitemap-prompts.xml</loc>
        <lastmod>{{ ($latestPromptAt ?? now())->toAtomString() }}</lastmod>
    </sitemap>

    <!-- Prompts Categorized Sub-Sitemaps -->
    @foreach ($categories as $category)
        <sitemap>
            <loc>{{ $baseUrl }}/sitemap-category-{{ $category->slug }}.xml</loc>
            <lastmod>{{ ($category->prompts()->where('status', 'approved')->latest('approved_at')->value('approved_at') ? \Carbon\Carbon::parse($category->prompts()->where('status', 'approved')->latest('approved_at')->value('approved_at'))->toAtomString() : now()->toAtomString()) }}</lastmod>
        </sitemap>
    @endforeach
</sitemapindex>
