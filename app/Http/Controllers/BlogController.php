<?php

namespace App\Http\Controllers;

use App\Models\BlogCategory;
use App\Models\BlogPost;
use App\Models\Prompt;
use Illuminate\Http\Request;
use Illuminate\View\View;

class BlogController extends Controller
{
    /**
     * Display public blog archive.
     */
    public function index(Request $request): View
    {
        $query = BlogPost::published()
            ->with(['category', 'author'])
            ->latest('published_at');

        $activeCategory = null;
        if ($request->filled('category')) {
            $activeCategory = BlogCategory::where('slug', $request->input('category'))->first();
            if ($activeCategory) {
                $query->where('blog_category_id', $activeCategory->id);
            }
        }

        if ($request->filled('q')) {
            $search = trim($request->input('q'));
            $query->where(function ($q) use ($search) {
                $q->where('title', 'like', "%{$search}%")
                    ->orWhere('excerpt', 'like', "%{$search}%")
                    ->orWhere('content', 'like', "%{$search}%");
            });
        }

        $posts = $query->paginate(9)->withQueryString();

        $categories = BlogCategory::withCount(['posts' => function ($q) {
            $q->where('status', 'published')
                ->where(function ($sub) {
                    $sub->whereNull('published_at')->orWhere('published_at', '<=', now());
                });
        }])->having('posts_count', '>', 0)->orderBy('sort_order')->orderBy('name')->get();

        // Featured post (latest or first on page 1 without search filter)
        $featuredPost = null;
        if (!$request->filled('q') && !$request->filled('category') && $posts->currentPage() === 1 && $posts->count() > 0) {
            $featuredPost = $posts->first();
        }

        return view('blog.index', compact('posts', 'categories', 'activeCategory', 'featuredPost'));
    }

    /**
     * Display single blog post.
     */
    public function show(string $slug): View
    {
        $post = BlogPost::published()
            ->with(['category', 'author'])
            ->where('slug', $slug)
            ->firstOrFail();

        // Increment view count quietly
        $post->increment('views_count');

        // Related posts in same category or latest
        $relatedPosts = BlogPost::published()
            ->where('id', '!=', $post->id)
            ->when($post->blog_category_id, function ($q) use ($post) {
                $q->where('blog_category_id', $post->blog_category_id);
            })
            ->latest('published_at')
            ->take(3)
            ->get();

        // Curated inspirational prompts to showcase in article sidebar or bottom
        $relatedPrompts = Prompt::where('status', 'approved')
            ->inRandomOrder()
            ->take(4)
            ->get();

        // Automatically build Table of Contents from h2 and h3
        $toc = [];
        if (preg_match_all('/<h([23])(?:[^>]*?)>(.*?)<\/h\1>/is', $post->content, $matches, PREG_SET_ORDER)) {
            foreach ($matches as $match) {
                $level = (int) $match[1];
                $title = strip_tags($match[2]);
                $anchor = \Illuminate\Support\Str::slug($title);
                $toc[] = [
                    'level' => $level,
                    'title' => $title,
                    'anchor' => $anchor,
                ];
            }
        }

        // Inject IDs into headers if missing so anchors scroll smoothly
        $contentWithAnchors = preg_replace_callback('/<h([23])((?:[^>]*?))>(.*?)<\/h\1>/is', function ($matches) {
            $level = $matches[1];
            $attrs = $matches[2];
            $inner = $matches[3];
            $anchor = \Illuminate\Support\Str::slug(strip_tags($inner));
            if (!str_contains($attrs, 'id=')) {
                return "<h{$level}{$attrs} id=\"{$anchor}\">{$inner}</h{$level}>";
            }
            return $matches[0];
        }, $post->content);

        return view('blog.show', compact('post', 'relatedPosts', 'relatedPrompts', 'toc', 'contentWithAnchors'));
    }

    /**
     * Dedicated category landing.
     */
    public function category(string $slug): View
    {
        $category = BlogCategory::where('slug', $slug)->firstOrFail();
        request()->merge(['category' => $category->slug]);

        return $this->index(request());
    }
}
