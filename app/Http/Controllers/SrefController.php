<?php

namespace App\Http\Controllers;

use App\Models\Prompt;
use App\Models\Sref;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class SrefController extends Controller
{
    public function index(Request $request): View
    {
        $query = Sref::query();

        if ($search = $request->query('q')) {
            $query->where(function ($q) use ($search) {
                $q->where('title', 'like', "%{$search}%")
                  ->orWhere('code', 'like', "%{$search}%")
                  ->orWhere('category', 'like', "%{$search}%")
                  ->orWhere('description', 'like', "%{$search}%");
            });
        }

        if ($category = $request->query('category')) {
            $query->where('category', $category);
        }

        $sort = $request->query('sort', 'popular');
        match ($sort) {
            'newest' => $query->latest(),
            'copies' => $query->orderByDesc('copies_count'),
            default => $query->orderByDesc('likes_count')->orderByDesc('is_featured'),
        };

        $srefs = $query->paginate(16)->withQueryString();
        $categories = Sref::distinct()->pluck('category')->filter()->values();

        return view('srefs.index', [
            'srefs' => $srefs,
            'categories' => $categories,
            'currentCategory' => $request->query('category'),
            'searchQuery' => $request->query('q'),
            'currentSort' => $sort,
        ]);
    }

    public function show(string $code): View
    {
        $sref = Sref::where('code', $code)->firstOrFail();

        // Sample prompts matching the style category
        $relatedPrompts = Prompt::where(function ($q) use ($sref) {
            $q->where('prompt_text', 'like', "%{$sref->code}%")
              ->orWhere('prompt_text', 'like', "%{$sref->category}%");
        })->limit(6)->get();

        $relatedSrefs = Sref::where('category', $sref->category)
            ->where('id', '!=', $sref->id)
            ->limit(4)
            ->get();

        return view('srefs.show', [
            'sref' => $sref,
            'relatedPrompts' => $relatedPrompts,
            'relatedSrefs' => $relatedSrefs,
        ]);
    }

    public function copy(Sref $sref): JsonResponse
    {
        $sref->increment('copies_count');

        return response()->json([
            'success' => true,
            'copies' => $sref->copies_count,
            'code' => $sref->code,
            'snippet' => "--sref {$sref->code}",
        ]);
    }

    public function like(Sref $sref): JsonResponse
    {
        $sref->increment('likes_count');

        return response()->json([
            'success' => true,
            'likes' => $sref->likes_count,
        ]);
    }
}
