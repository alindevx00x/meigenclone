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

        $sort = $request->string('sort')->toString() ?: SiteSetting::value('default_sort', 'featured');

        match ($sort) {
            'newest' => $query->orderByDesc('approved_at')->orderByDesc('id'),
            'popular' => $query->orderByDesc('likes_count')->orderByDesc('approved_at'),
            default => $query->orderByDesc('is_featured')->orderByDesc('approved_at')->orderByDesc('id'),
        };

        $perPage = min(48, max(12, (int) SiteSetting::value('prompts_per_page', 24)));
        $prompts = $query->paginate($perPage)->withQueryString();
        $modelList = \App\Models\AiModel::where('is_active', true)->orderBy('sort_order')->orderBy('name')->get();
        $models = $modelList->pluck('name')->all();
        $modelIcons = $modelList->pluck('icon', 'name')->all();
        if (empty($models)) {
            $models = ['GPT Image', 'Seedance', 'Nanobanana', 'Midjourney'];
            $modelIcons = ['GPT Image' => '✦', 'Seedance' => '◉', 'Nanobanana' => '◍', 'Midjourney' => '✧'];
        }

        return view('prompts.index', compact('categories', 'prompts', 'models', 'modelIcons') + ['activeSort' => $sort]);
    }

    public function show(Prompt $prompt)
    {
        abort_unless($prompt->status === 'approved', 404);

        $prompt->load(['user', 'category']);
        $prompt->loadExists(['favoritedBy as is_favorited' => fn ($favorites) => $favorites->where('users.id', auth()->id() ?? 0)]);
        $related = Prompt::with(['user', 'category'])
            ->where('status', 'approved')->where('category_id', $prompt->category_id)
            ->where('id', '!=', $prompt->id)->latest('approved_at')->limit(3)->get();

        return view('prompts.show', compact('prompt', 'related'));
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
        }

        return back()->with('success', $exists ? 'Removed from your saved prompts.' : 'Saved to your favorites.');
    }

    public function sitemap()
    {
        $categories = Category::all();
        $prompts = Prompt::where('status', 'approved')->latest('approved_at')->limit(5000)->get();

        return response()->view('seo.sitemap', compact('categories', 'prompts'))
            ->header('Content-Type', 'text/xml; charset=utf-8');
    }
}
