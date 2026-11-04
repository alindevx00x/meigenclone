<?php

namespace App\Http\Controllers;

use App\Models\Collection;
use App\Models\Prompt;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Str;
use Illuminate\View\View;

class CollectionController extends Controller
{
    public function index(Request $request): View
    {
        $tab = $request->query('tab', 'explore');

        if ($tab === 'mine' && $request->user()) {
            $collections = $request->user()->collections()
                ->withCount('prompts')
                ->latest()
                ->paginate(12)
                ->withQueryString();
        } else {
            $collections = Collection::where('is_public', true)
                ->with(['user'])
                ->withCount('prompts')
                ->orderByDesc('prompts_count')
                ->latest()
                ->paginate(12)
                ->withQueryString();
        }

        return view('collections.index', [
            'collections' => $collections,
            'currentTab' => $tab,
        ]);
    }

    public function show(string $slug): View
    {
        $collection = Collection::where('slug', $slug)
            ->with(['user', 'prompts.category'])
            ->firstOrFail();

        if (! $collection->is_public && auth()->id() !== $collection->user_id) {
            abort(403, 'This collection is private.');
        }

        $prompts = $collection->prompts()->paginate(24);

        return view('collections.show', [
            'collection' => $collection,
            'prompts' => $prompts,
        ]);
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'name' => 'required|string|max:80',
            'description' => 'nullable|string|max:400',
            'is_public' => 'nullable|boolean',
            'prompt_id' => 'nullable|exists:prompts,id',
        ]);

        $user = $request->user();
        $slug = Str::slug($validated['name']);
        if ($user->collections()->where('slug', $slug)->exists()) {
            $slug .= '-' . Str::lower(Str::random(4));
        }

        $coverImagePath = null;
        if (! empty($validated['prompt_id'])) {
            $prompt = Prompt::find($validated['prompt_id']);
            $coverImagePath = $prompt?->image_path;
        }

        $collection = $user->collections()->create([
            'name' => $validated['name'],
            'slug' => $slug,
            'description' => $validated['description'] ?? null,
            'is_public' => $request->boolean('is_public', true),
            'cover_image_path' => $coverImagePath,
            'prompts_count' => ! empty($validated['prompt_id']) ? 1 : 0,
        ]);

        if (! empty($validated['prompt_id'])) {
            $collection->prompts()->attach($validated['prompt_id']);
        }

        if ($request->wantsJson()) {
            return response()->json([
                'success' => true,
                'collection' => $collection,
                'message' => 'Collection created successfully!',
            ]);
        }

        return redirect()->route('collections.show', $collection->slug)->with('success', 'Collection created successfully!');
    }

    public function togglePrompt(Request $request, Collection $collection): JsonResponse
    {
        if (auth()->id() !== $collection->user_id) {
            return response()->json(['error' => 'Unauthorized'], 403);
        }

        $validated = $request->validate([
            'prompt_id' => 'required|exists:prompts,id',
        ]);

        $promptId = $validated['prompt_id'];
        $attached = $collection->prompts()->where('prompt_id', $promptId)->exists();

        if ($attached) {
            $collection->prompts()->detach($promptId);
            $saved = false;
        } else {
            $collection->prompts()->attach($promptId);
            $saved = true;

            // Set cover if empty
            if (! $collection->cover_image_path) {
                $prompt = Prompt::find($promptId);
                $collection->update(['cover_image_path' => $prompt?->image_path]);
            }
        }

        $collection->update(['prompts_count' => $collection->prompts()->count()]);

        return response()->json([
            'success' => true,
            'saved' => $saved,
            'prompts_count' => $collection->prompts_count,
            'message' => $saved ? "Added to {$collection->name}" : "Removed from {$collection->name}",
        ]);
    }

    public function userCollections(Request $request): JsonResponse
    {
        $user = $request->user();
        if (! $user) {
            return response()->json(['collections' => []]);
        }

        $promptId = $request->query('prompt_id');

        $collections = $user->collections()->withCount('prompts')->get()->map(function ($c) use ($promptId) {
            return [
                'id' => $c->id,
                'name' => $c->name,
                'slug' => $c->slug,
                'prompts_count' => $c->prompts_count,
                'contains_prompt' => $promptId ? $c->prompts()->where('prompt_id', $promptId)->exists() : false,
            ];
        });

        return response()->json(['collections' => $collections]);
    }

    public function destroy(Request $request, Collection $collection)
    {
        if (auth()->id() !== $collection->user_id) {
            abort(403);
        }

        $collection->delete();

        return redirect()->route('collections.index', ['tab' => 'mine'])->with('success', 'Collection deleted successfully.');
    }
}
