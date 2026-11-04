<?php

namespace App\Http\Controllers;

use App\Models\Prompt;
use App\Models\PromptPack;
use Illuminate\Http\Request;
use Illuminate\Support\Str;
use Illuminate\View\View;
use Symfony\Component\HttpFoundation\StreamedResponse;

class PromptPackController extends Controller
{
    public function index(Request $request): View
    {
        $query = PromptPack::where('status', 'active');

        if ($request->query('free') === '1') {
            $query->where('is_free', true);
        }

        if ($search = $request->query('q')) {
            $query->where(function ($q) use ($search) {
                $q->where('title', 'like', "%{$search}%")
                  ->orWhere('description', 'like', "%{$search}%");
            });
        }

        $packs = $query->with('user')
            ->orderByDesc('is_featured')
            ->orderByDesc('downloads_count')
            ->paginate(12)
            ->withQueryString();

        return view('packs.index', [
            'packs' => $packs,
            'isFree' => $request->query('free') === '1',
            'searchQuery' => $request->query('q'),
        ]);
    }

    public function show(string $slug): View
    {
        $pack = PromptPack::where('slug', $slug)
            ->with(['user', 'prompts.category'])
            ->firstOrFail();

        return view('packs.show', [
            'pack' => $pack,
        ]);
    }

    public function create(): View
    {
        $myPrompts = auth()->user()->prompts()->latest()->get();

        return view('packs.create', [
            'myPrompts' => $myPrompts,
        ]);
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'title' => 'required|string|max:120',
            'description' => 'required|string|max:1000',
            'price' => 'nullable|numeric|min:0|max:999',
            'is_free' => 'nullable|boolean',
            'prompt_ids' => 'required|array|min:2',
            'prompt_ids.*' => 'exists:prompts,id',
        ]);

        $isFree = $request->boolean('is_free') || empty($validated['price']) || (float) $validated['price'] == 0;
        $price = $isFree ? 0.00 : (float) $validated['price'];

        $slug = Str::slug($validated['title']);
        $uniqueSlug = $slug . '-' . Str::lower(Str::random(6));

        // Get cover image from first prompt in bundle
        $firstPrompt = Prompt::find($validated['prompt_ids'][0]);

        $pack = PromptPack::create([
            'user_id' => $request->user()->id,
            'title' => $validated['title'],
            'slug' => $uniqueSlug,
            'description' => $validated['description'],
            'price' => $price,
            'is_free' => $isFree,
            'cover_image_path' => $firstPrompt?->image_path,
            'prompt_count' => count($validated['prompt_ids']),
            'status' => 'active',
        ]);

        foreach ($validated['prompt_ids'] as $index => $promptId) {
            $pack->prompts()->attach($promptId, ['sort_order' => $index]);
        }

        return redirect()->route('packs.show', $pack->slug)->with('success', 'Your prompt pack has been published successfully!');
    }

    public function download(PromptPack $pack): StreamedResponse
    {
        $pack->increment('downloads_count');

        $prompts = $pack->prompts()->get()->map(function (Prompt $p) {
            return [
                'title' => $p->title,
                'model' => $p->model,
                'category' => $p->category?->name,
                'prompt' => $p->prompt_text,
                'likes' => $p->likes_count,
            ];
        });

        $exportData = [
            'pack_title' => $pack->title,
            'creator' => $pack->user->name,
            'description' => $pack->description,
            'total_prompts' => $pack->prompt_count,
            'generated_at' => now()->toIso8601String(),
            'prompts' => $prompts,
        ];

        $json = json_encode($exportData, JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE);
        $fileName = Str::slug($pack->title) . '-prompts.json';

        return response()->streamDownload(function () use ($json) {
            echo $json;
        }, $fileName, ['Content-Type' => 'application/json']);
    }
}
