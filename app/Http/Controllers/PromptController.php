<?php

namespace App\Http\Controllers;

use App\Models\Category;
use App\Models\Prompt;
use App\Models\SiteSetting;
use Illuminate\Http\Request;

class PromptController extends Controller
{
    public function index(Request $request)
    {
        $categories = Category::withCount(['prompts as prompts_count' => fn ($query) => $query->where('status', 'approved')])
            ->orderBy('name')->get();

        $query = Prompt::query()
            ->with(['user', 'category'])
            ->withExists(['favoritedBy as is_favorited' => fn ($favorites) => $favorites->where('users.id', auth()->id() ?? 0)])
            ->where('status', 'approved');

        if ($request->filled('category')) {
            $query->whereHas('category', fn ($category) => $category->where('slug', $request->string('category')->toString()));
        }

        if ($request->filled('model') && $request->string('model') !== 'All') {
            $query->where('model', $request->string('model')->toString());
        }

        if ($request->filled('q')) {
            $search = $request->string('q')->trim()->toString();
            $query->where(fn ($match) => $match
                ->where('title', 'like', "%{$search}%")
                ->orWhere('prompt_text', 'like', "%{$search}%")
                ->orWhereHas('user', fn ($user) => $user->where('name', 'like', "%{$search}%"))
                ->orWhereHas('category', fn ($category) => $category->where('name', 'like', "%{$search}%")));
        }

        // Generate fresh random seed on each visit/reload (page 1), preserving seed when paginating
        if (! $request->filled('page') || (int) $request->input('page') <= 1) {
            $seed = mt_rand(1, 99999999);
            session()->put('prompt_gallery_seed', $seed);
        } else {
            $seed = (int) session('prompt_gallery_seed', mt_rand(1, 99999999));
        }

        $sort = $request->string('sort')->toString() ?: SiteSetting::value('default_sort', 'random');

        match ($sort) {
            'newest' => $query->orderByDesc('approved_at')->orderByDesc('id'),
            'popular' => $query->orderByDesc('likes_count')->orderByDesc('approved_at')->orderByDesc('id'),
            'featured' => $query->orderByDesc('is_featured')->orderByDesc('approved_at')->orderByDesc('id'),
            'random' => $query->inRandomOrder($seed),
            default => $query->inRandomOrder($seed),
        };

        $perPage = min(250, max(6, (int) SiteSetting::value('prompts_per_page', 24)));
        $prompts = $query->paginate($perPage)->withQueryString();
        $modelList = \App\Models\AiModel::where('is_active', true)->orderBy('sort_order')->orderBy('name')->get();
        $models = $modelList->pluck('name')->all();
        $modelIcons = $modelList->pluck('icon', 'name')->all();
        if (empty($models)) {
            $models = ['GPT Image', 'Seedance', 'Nanobanana', 'Midjourney'];
            $modelIcons = ['GPT Image' => '✦', 'Seedance' => '◉', 'Nanobanana' => '◍', 'Midjourney' => '✧'];
        }

        $selectedFeaturedId = (int) SiteSetting::value('featured_prompt_id', 0);
        $featuredPrompt = null;
        if ($selectedFeaturedId > 0) {
            $featuredPrompt = Prompt::with(['user', 'category'])
                ->where('status', 'approved')
                ->where('id', $selectedFeaturedId)
                ->where(fn($q) => $q->whereNotNull('image_url')->orWhereNotNull('image_path'))
                ->first();
        }

        $featuredQuery = Prompt::with(['user', 'category'])
            ->where('status', 'approved')
            ->where(fn($q) => $q->whereNotNull('image_url')->orWhereNotNull('image_path'));

        if ($featuredPrompt) {
            $featuredQuery->where('id', '!=', $featuredPrompt->id);
        }

        $otherFeatured = $featuredQuery->where(function ($q) {
                $q->where('is_featured', true)->orWhere('likes_count', '>=', 5);
            })
            ->inRandomOrder($seed)
            ->take($featuredPrompt ? 4 : 5)
            ->get();

        if ($otherFeatured->count() < 4) {
            $otherFeatured = $featuredQuery->inRandomOrder($seed)->take($featuredPrompt ? 4 : 5)->get();
        }

        $featuredPrompts = $featuredPrompt ? collect([$featuredPrompt])->concat($otherFeatured) : $otherFeatured;

        return view('prompts.index', compact('categories', 'prompts', 'models', 'modelIcons', 'featuredPrompts') + ['activeSort' => $sort]);
    }

    public function show(Prompt $prompt)
    {
        abort_unless($prompt->status === 'approved', 404);

        // Deduplicated view tracking per session
        $sessionKey = 'viewed_prompt_' . $prompt->id;
        if (! session()->has($sessionKey)) {
            session()->put($sessionKey, true);
            $prompt->increment('views_count');

            if ($prompt->user_id) {
                $today = now()->toDateString();
                \Illuminate\Support\Facades\DB::table('prompt_analytics_daily')->upsert(
                    [
                        'prompt_id' => $prompt->id,
                        'user_id' => $prompt->user_id,
                        'metric_date' => $today,
                        'views' => 1,
                        'copies' => 0,
                        'favorites' => 0,
                        'created_at' => now(),
                        'updated_at' => now(),
                    ],
                    ['prompt_id', 'metric_date'],
                    [
                        'views' => \Illuminate\Support\Facades\DB::raw('prompt_analytics_daily.views + 1'),
                        'updated_at' => now(),
                    ]
                );
            }
        }

        $prompt->load(['user', 'category']);
        $prompt->loadExists(['favoritedBy as is_favorited' => fn ($favorites) => $favorites->where('users.id', auth()->id() ?? 0)]);
        $related = Prompt::with(['user', 'category'])
            ->where('status', 'approved')->where('category_id', $prompt->category_id)
            ->where('id', '!=', $prompt->id)->latest('approved_at')->limit(3)->get();

        return view('prompts.show', compact('prompt', 'related'));
    }

    public function ogImage(Prompt $prompt)
    {
        abort_unless($prompt->status === 'approved', 404);
        $path = \App\Services\OgImageGenerator::generate($prompt);

        return response()->file($path, [
            'Content-Type' => 'image/jpeg',
            'Cache-Control' => 'public, max-age=604800, immutable',
        ]);
    }

    public function trackCopy(Prompt $prompt): \Illuminate\Http\JsonResponse
    {
        $prompt->increment('copies_count');

        if ($prompt->user_id) {
            $today = now()->toDateString();
            \Illuminate\Support\Facades\DB::table('prompt_analytics_daily')->upsert(
                [
                    'prompt_id' => $prompt->id,
                    'user_id' => $prompt->user_id,
                    'metric_date' => $today,
                    'views' => 0,
                    'copies' => 1,
                    'favorites' => 0,
                    'created_at' => now(),
                    'updated_at' => now(),
                ],
                ['prompt_id', 'metric_date'],
                [
                    'copies' => \Illuminate\Support\Facades\DB::raw('prompt_analytics_daily.copies + 1'),
                    'updated_at' => now(),
                ]
            );
        }

        return response()->json([
            'success' => true,
            'copies_count' => (int) $prompt->fresh()->copies_count,
        ]);
    }

    public function toggleFavorite(Prompt $prompt)
    {
        abort_unless($prompt->status === 'approved', 404);
        $user = request()->user();
        $exists = $user->favorites()->whereKey($prompt->id)->exists();

        if ($exists) {
            $user->favorites()->detach($prompt->id);
            $prompt->decrement('likes_count');
        } else {
            $user->favorites()->attach($prompt->id);
            $prompt->increment('likes_count');

            if ($prompt->user_id) {
                $today = now()->toDateString();
                \Illuminate\Support\Facades\DB::table('prompt_analytics_daily')->upsert(
                    [
                        'prompt_id' => $prompt->id,
                        'user_id' => $prompt->user_id,
                        'metric_date' => $today,
                        'views' => 0,
                        'copies' => 0,
                        'favorites' => 1,
                        'created_at' => now(),
                        'updated_at' => now(),
                    ],
                    ['prompt_id', 'metric_date'],
                    [
                        'favorites' => \Illuminate\Support\Facades\DB::raw('prompt_analytics_daily.favorites + 1'),
                        'updated_at' => now(),
                    ]
                );
            }
        }

        $freshLikes = (int) $prompt->fresh()->likes_count;

        if (request()->expectsJson() || request()->ajax()) {
            return response()->json([
                'success' => true,
                'is_favorited' => ! $exists,
                'likes_count' => $freshLikes,
                'formatted_likes' => number_format($freshLikes),
                'message' => $exists ? 'Removed from your saved prompts.' : 'Saved to your favorites.',
            ]);
        }

        return back()->with('success', $exists ? 'Removed from your saved prompts.' : 'Saved to your favorites.');
    }

    public function categoryLanding(Category $category)
    {
        $categories = Category::all();
        $prompts = $category->prompts()
            ->where('status', 'approved')
            ->orderByDesc('likes_count')
            ->orderByDesc('approved_at')
            ->paginate(24);

        $topModels = Prompt::where('category_id', $category->id)
            ->where('status', 'approved')
            ->select('model', \Illuminate\Support\Facades\DB::raw('count(*) as count'))
            ->groupBy('model')
            ->orderByDesc('count')
            ->take(4)
            ->get();

        $faqs = [
            [
                'question' => "What are the best {$category->name} prompts for Midjourney v6 and Flux?",
                'answer' => "Top {$category->name} prompts focus on specific lighting conditions, materials, camera lenses, and rendering engines. Use high-detail parameter tokens like --ar 16:9, --stylize 250, and 8k commercial photography tags."
            ],
            [
                'question' => "How do I remix and customize these {$category->name} prompts?",
                'answer' => "Click the Remix (🪄) icon on any card to open the interactive builder. You can swap aspect ratios, adjust color temperatures, and append custom SREF style reference codes."
            ],
            [
                'question' => "Can I use these {$category->name} images commercially?",
                'answer' => "Prompts are free and open for public creative use. Commercial generation rights depend on the terms of your AI generation tool subscription (Midjourney Pro, Flux, etc.)."
            ]
        ];

        return view('prompts.landing', [
            'type' => 'category',
            'title' => "Best {$category->name} AI Prompts (2026 Library)",
            'metaTitle' => "Top {$category->name} Prompts for Midjourney, Flux & DALL-E",
            'metaDescription' => "Explore curated {$category->name} AI prompts. One-click copy, Remix token builder, and Midjourney Discord command shortcuts.",
            'category' => $category,
            'prompts' => $prompts,
            'categories' => $categories,
            'topModels' => $topModels,
            'faqs' => $faqs,
        ]);
    }

    public function styleLanding(string $vibe)
    {
        $vibeTitles = [
            'cyberpunk' => ['title' => 'Cyberpunk & Neon Sci-Fi Prompts', 'kw' => 'cyberpunk, neon glow, dystopian sci-fi, futuristic city, blade runner aesthetic'],
            'minimalist' => ['title' => 'Minimalist & Clean Editorial Prompts', 'kw' => 'minimalist, negative space, clean studio, Scandinavian design, beige aesthetic'],
            'cinematic' => ['title' => '35mm Cinematic Film Photography Prompts', 'kw' => '35mm, Kodak Portra, anamorphic lens, movie still, volumetric lighting'],
            'anime' => ['title' => 'Anime, Manga & Makoto Shinkai Prompts', 'kw' => 'anime aesthetic, Studio Ghibli, Makoto Shinkai sky, vibrant cel shading'],
            'isometric' => ['title' => '3D Isometric & Low-Poly Render Prompts', 'kw' => 'isometric 3D, Blender 3D render, cute low poly, miniature diorama, claymation'],
        ];

        $info = $vibeTitles[$vibe] ?? [
            'title' => ucwords(str_replace('-', ' ', $vibe)) . ' Style AI Prompts',
            'kw' => $vibe . ', aesthetic style, midjourney prompt, flux render'
        ];

        $categories = Category::all();
        $prompts = Prompt::where('status', 'approved')
            ->where(function ($q) use ($vibe, $info) {
                $q->where('title', 'like', "%{$vibe}%")
                  ->orWhere('prompt_text', 'like', "%{$vibe}%");
                foreach (explode(',', $info['kw']) as $word) {
                    $w = trim($word);
                    if ($w) $q->orWhere('prompt_text', 'like', "%{$w}%");
                }
            })
            ->orderByDesc('likes_count')
            ->paginate(24);

        $faqs = [
            [
                'question' => "How do I get consistent {$info['title']} results?",
                'answer' => "Include specific rendering parameters such as volumetric dust, specific camera sensor types, and accurate lighting keywords into your prompt."
            ],
            [
                'question' => "Which models perform best for {$vibe} style prompts?",
                'answer' => "Midjourney v6.1 and Flux.1 Schnell deliver industry-leading fidelity for textured lighting and artistic style fidelity."
            ]
        ];

        return view('prompts.landing', [
            'type' => 'style',
            'title' => $info['title'],
            'metaTitle' => "{$info['title']} \u2014 Curated AI Prompt Index",
            'metaDescription' => "Browse high-engagement {$vibe} prompts for Midjourney, Flux, and SDXL. 1-click Discord copy, SREF codes, and instant remixing.",
            'vibe' => $vibe,
            'prompts' => $prompts,
            'categories' => $categories,
            'topModels' => collect([]),
            'faqs' => $faqs,
        ]);
    }

    public function sitemap()
    {
        $categories = Category::all();
        $latestPromptAt = Prompt::where('status', 'approved')->latest('approved_at')->value('approved_at');
        if ($latestPromptAt) {
            $latestPromptAt = \Carbon\Carbon::parse($latestPromptAt);
        }

        $canonicalBase = trim(\App\Models\SiteSetting::value('seo_canonical_base', ''));
        $baseUrl = $canonicalBase !== '' ? rtrim($canonicalBase, '/') : url('/');

        return response()->view('seo.sitemap-index', compact('categories', 'latestPromptAt', 'baseUrl'))
            ->header('Content-Type', 'text/xml; charset=utf-8')
            ->header('Cache-Control', 'public, max-age=3600, s-maxage=86400');
    }

    public function sitemapMain()
    {
        $categories = Category::all();
        $creators = \App\Models\User::whereHas('prompts', fn ($q) => $q->where('status', 'approved'))->limit(250)->get();
        $blogCategories = \App\Models\BlogCategory::all();
        $blogPosts = \App\Models\BlogPost::published()->latest('updated_at')->limit(500)->get();

        $canonicalBase = trim(\App\Models\SiteSetting::value('seo_canonical_base', ''));
        $baseUrl = $canonicalBase !== '' ? rtrim($canonicalBase, '/') : url('/');

        return response()->view('seo.sitemap-main', compact('categories', 'creators', 'baseUrl', 'blogCategories', 'blogPosts'))
            ->header('Content-Type', 'text/xml; charset=utf-8')
            ->header('Cache-Control', 'public, max-age=3600, s-maxage=86400');
    }

    public function sitemapPrompts()
    {
        $prompts = Prompt::with('category')
            ->where('status', 'approved')
            ->latest('approved_at')
            ->limit(10000)
            ->get();

        $canonicalBase = trim(\App\Models\SiteSetting::value('seo_canonical_base', ''));
        $baseUrl = $canonicalBase !== '' ? rtrim($canonicalBase, '/') : url('/');

        return response()->view('seo.sitemap-prompts', compact('prompts', 'baseUrl'))
            ->header('Content-Type', 'text/xml; charset=utf-8')
            ->header('Cache-Control', 'public, max-age=3600, s-maxage=86400');
    }

    public function sitemapCategoryPrompts(string $slug)
    {
        $category = Category::where('slug', $slug)->firstOrFail();
        $prompts = $category->prompts()
            ->where('status', 'approved')
            ->latest('approved_at')
            ->limit(5000)
            ->get();

        $canonicalBase = trim(\App\Models\SiteSetting::value('seo_canonical_base', ''));
        $baseUrl = $canonicalBase !== '' ? rtrim($canonicalBase, '/') : url('/');

        return response()->view('seo.sitemap-prompts', compact('prompts', 'baseUrl'))
            ->header('Content-Type', 'text/xml; charset=utf-8')
            ->header('Cache-Control', 'public, max-age=3600, s-maxage=86400');
    }

    public function robots(\Illuminate\Http\Request $request)
    {
        $canonicalBase = trim(\App\Models\SiteSetting::value('seo_canonical_base', ''));
        if ($canonicalBase !== '') {
            $sitemapUrl = rtrim($canonicalBase, '/') . '/sitemap.xml';
        } else {
            $sitemapUrl = $request->getSchemeAndHttpHost() . '/sitemap.xml';
        }

        $content = "User-agent: *\n"
            . "Allow: /\n"
            . "Disallow: /admin/\n"
            . "Disallow: /dashboard/\n"
            . "Disallow: /login\n"
            . "Disallow: /register\n\n"
            . "Sitemap: {$sitemapUrl}\n";

        return response($content, 200)
            ->header('Content-Type', 'text/plain; charset=UTF-8')
            ->header('Cache-Control', 'public, max-age=300, must-revalidate');
    }
}
