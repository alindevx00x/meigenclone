<?php

namespace App\Http\Controllers;

use App\Models\Category;
use App\Models\Prompt;
use App\Models\User;
use Illuminate\View\View;

class AboutController extends Controller
{
    /**
     * Display the SEO-optimized About Us page.
     */
    public function index(): View
    {
        $stats = [
            'prompts_count' => Prompt::where('status', 'approved')->count(),
            'categories_count' => Category::count(),
            'creators_count' => User::whereHas('prompts', fn ($q) => $q->where('status', 'approved'))->count(),
            'views_count' => Prompt::where('status', 'approved')->sum('views_count'),
            'likes_count' => Prompt::where('status', 'approved')->sum('likes_count'),
            'copies_count' => Prompt::where('status', 'approved')->sum('copies_count'),
        ];

        $samplePrompts = Prompt::where('status', 'approved')
            ->whereNotNull('image_path')
            ->inRandomOrder()
            ->take(6)
            ->get();

        return view('pages.about', compact('stats', 'samplePrompts'));
    }
}
