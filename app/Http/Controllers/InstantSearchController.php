<?php

namespace App\Http\Controllers;

use App\Models\Prompt;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class InstantSearchController extends Controller
{
    public function search(Request $request): JsonResponse
    {
        $q = trim($request->query('q', ''));
        $model = $request->query('model');
        $category = $request->query('category');
        $aspectRatio = $request->query('aspect_ratio');
        $vibe = $request->query('vibe');

        $query = Prompt::where('status', 'approved');

        if ($q !== '') {
            // Split tokens for typo and partial matching
            $tokens = preg_split('/\s+/', $q, -1, PREG_SPLIT_NO_EMPTY);
            $query->where(function ($sub) use ($tokens, $q) {
                $sub->where('title', 'like', "%{$q}%")
                    ->orWhere('prompt_text', 'like', "%{$q}%");

                foreach ($tokens as $token) {
                    if (mb_strlen($token) >= 3) {
                        $sub->orWhere('title', 'like', "%{$token}%")
                            ->orWhere('prompt_text', 'like', "%{$token}%");
                    }
                }
            });
        }

        if ($model) {
            $query->where('model', $model);
        }

        if ($category) {
            $query->whereHas('category', function ($catQuery) use ($category) {
                $catQuery->where('slug', $category)->orWhere('name', $category);
            });
        }

        if ($aspectRatio) {
            $query->where(function ($ar) use ($aspectRatio) {
                $ar->where('prompt_text', 'like', "%--ar {$aspectRatio}%")
                   ->orWhere('prompt_text', 'like', "%{$aspectRatio}%");
            });
        }

        if ($vibe) {
            $keywords = match ($vibe) {
                'dark' => ['dark', 'moody', 'night', 'shadow', 'gothic', 'cyberpunk', 'black'],
                'vibrant' => ['vibrant', 'neon', 'colorful', 'bold', 'saturated', 'luminous'],
                'pastel' => ['pastel', 'soft', 'dreamy', 'gentle', 'light', 'clean'],
                'monochrome' => ['monochrome', 'black and white', 'grayscale', 'minimalist'],
                'warm' => ['golden hour', 'sunset', 'warm', 'amber', 'vintage', 'autumn'],
                default => [],
            };

            if (! empty($keywords)) {
                $query->where(function ($vibeQuery) use ($keywords) {
                    foreach ($keywords as $kw) {
                        $vibeQuery->orWhere('prompt_text', 'like', "%{$kw}%");
                    }
                });
            }
        }

        $results = $query->orderByDesc('likes_count')
            ->limit(10)
            ->get()
            ->map(function (Prompt $prompt) {
                return [
                    'id' => $prompt->id,
                    'title' => $prompt->title,
                    'slug' => $prompt->slug,
                    'url' => route('prompts.show', $prompt->slug),
                    'cover_url' => $prompt->cover_url,
                    'model' => $prompt->model,
                    'category' => $prompt->category?->name ?? 'General',
                    'likes_count' => $prompt->likes_count,
                    'snippet' => mb_strimwidth($prompt->prompt_text, 0, 90, '...', 'UTF-8'),
                ];
            });

        return response()->json([
            'results' => $results,
            'total' => $results->count(),
        ]);
    }
}
