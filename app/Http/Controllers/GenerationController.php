<?php

namespace App\Http\Controllers;

use App\Models\Category;
use App\Models\Generation;
use App\Models\Prompt;
use App\Services\ImageGenerationService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Str;
use Illuminate\View\View;

class GenerationController extends Controller
{
    public function __construct(
        protected ImageGenerationService $generationService
    ) {}

    public function index(Request $request): View
    {
        $promptId = $request->query('prompt_id');
        $prefilledPrompt = null;
        if ($promptId) {
            $prefilledPrompt = Prompt::find($promptId);
        }

        $user = $request->user();
        $recentGenerations = $user
            ? $user->generations()->latest()->paginate(12)
            : Generation::where('ip_address', $request->ip())->latest()->paginate(12);

        return view('generations.index', [
            'prefilledPrompt' => $prefilledPrompt,
            'recentGenerations' => $recentGenerations,
            'user' => $user,
        ]);
    }

    public function store(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'prompt_text' => 'required|string|min:5|max:2000',
            'provider' => 'nullable|string|in:flux,replicate,openai,mock',
            'model' => 'nullable|string|in:flux-schnell,flux-1.1-pro,dall-e-3',
            'aspect_ratio' => 'nullable|string|in:1:1,16:9,9:16,4:5',
            'api_key' => 'nullable|string|max:255',
            'prompt_id' => 'nullable|exists:prompts,id',
        ]);

        $user = $request->user();
        $provider = $validated['provider'] ?? 'flux';
        $model = $validated['model'] ?? 'flux-schnell';
        $aspectRatio = $validated['aspect_ratio'] ?? '1:1';
        $apiKey = $validated['api_key'] ?? ($user?->{$provider === 'openai' ? 'openai_api_key' : 'replicate_api_token'});

        try {
            $generation = $this->generationService->generate(
                promptText: $validated['prompt_text'],
                provider: $provider,
                model: $model,
                aspectRatio: $aspectRatio,
                apiKey: $apiKey,
                user: $user,
                promptId: $validated['prompt_id'] ?? null,
                ipAddress: $request->ip()
            );

            return response()->json([
                'success' => true,
                'generation' => [
                    'id' => $generation->id,
                    'image_url' => $generation->image_url,
                    'prompt_text' => $generation->prompt_text,
                    'model' => $generation->model,
                    'aspect_ratio' => $generation->aspect_ratio,
                    'status' => $generation->status,
                ],
                'credits_remaining' => $user?->fresh()->credits,
                'message' => 'Image generated successfully!',
            ]);
        } catch (\Throwable $e) {
            return response()->json([
                'success' => false,
                'message' => $e->getMessage(),
            ], 422);
        }
    }

    public function publish(Request $request, Generation $generation)
    {
        $this->authorize('view', $generation);

        if (! $generation->output_image_path) {
            return back()->with('error', 'Generation does not have a valid output image.');
        }

        $category = Category::firstOrCreate(['name' => 'AI Generations'], [
            'slug' => 'ai-generations',
            'icon' => '✦',
            'color' => 'violet',
        ]);

        $title = Str::limit($generation->prompt_text, 60, '');
        $slug = Str::slug($title) ?: 'generation';
        $uniqueSlug = $slug . '-' . Str::lower(Str::random(6));

        $prompt = Prompt::create([
            'user_id' => $request->user()->id,
            'category_id' => $category->id,
            'title' => $title,
            'slug' => $uniqueSlug,
            'prompt_text' => $generation->prompt_text,
            'image_url' => null,
            'image_path' => $generation->output_image_path,
            'model' => match ($generation->provider) {
                'openai' => 'GPT Image',
                'flux', 'replicate' => 'Flux',
                default => 'Flux',
            },
            'status' => 'approved',
            'is_featured' => false,
            'likes_count' => 1,
            'approved_at' => now(),
        ]);

        $generation->update(['prompt_id' => $prompt->id]);

        return redirect()->route('prompts.show', $prompt->slug)->with('success', 'Your generated image has been published as a prompt to the community!');
    }
}
