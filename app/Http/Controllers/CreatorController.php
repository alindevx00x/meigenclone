<?php

namespace App\Http\Controllers;

use App\Models\User;
use Illuminate\Http\Request;

class CreatorController extends Controller
{
    public function show(Request $request, User $user)
    {
        $user->loadCount([
            'prompts as approved_prompts_count' => fn ($q) => $q->where('status', 'approved'),
            'favorites',
            'followers',
            'following',
            'collections',
            'promptPacks' => fn ($q) => $q->where('status', 'active'),
        ]);

        $totalLikes = (int) $user->prompts()->where('status', 'approved')->sum('likes_count');

        $query = $user->prompts()
            ->with(['category', 'user'])
            ->withExists(['favoritedBy as is_favorited' => fn ($fav) => $fav->where('users.id', auth()->id() ?? 0)])
            ->where('status', 'approved');

        if ($request->filled('category')) {
            $query->whereHas('category', fn ($c) => $c->where('slug', $request->string('category')->toString()));
        }

        $sort = $request->string('sort', 'newest')->toString();
        match ($sort) {
            'popular' => $query->orderByDesc('likes_count')->orderByDesc('approved_at'),
            'oldest' => $query->orderBy('approved_at')->orderBy('id'),
            default => $query->orderByDesc('approved_at')->orderByDesc('id'),
        };

        $prompts = $query->paginate(24)->withQueryString();

        $categories = $user->prompts()
            ->where('status', 'approved')
            ->join('categories', 'prompts.category_id', '=', 'categories.id')
            ->select('categories.id', 'categories.name', 'categories.slug', 'categories.icon')
            ->selectRaw('count(prompts.id) as count')
            ->groupBy('categories.id', 'categories.name', 'categories.slug', 'categories.icon')
            ->orderByDesc('count')
            ->get();

        $collections = $user->collections()
            ->withCount('prompts')
            ->latest()
            ->take(8)
            ->get();

        $promptPacks = $user->promptPacks()
            ->where('status', 'active')
            ->latest()
            ->take(6)
            ->get();

        $isFollowing = auth()->check() ? auth()->user()->isFollowing($user) : false;

        return view('creators.show', compact('user', 'prompts', 'totalLikes', 'categories', 'collections', 'promptPacks', 'isFollowing'));
    }
}
