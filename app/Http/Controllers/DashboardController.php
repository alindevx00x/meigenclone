<?php

namespace App\Http\Controllers;

use App\Models\Category;
use App\Models\Prompt;
use App\Models\SiteSetting;
use App\Support\WebpImageStorage;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;

class DashboardController extends Controller
{
    public function index(Request $request)
    {
        $user = $request->user();
        $prompts = $user->prompts()->with('category')->latest()->get();
        $favorites = $user->favorites()->with(['user', 'category'])->latest('favorites.created_at')->get();

        $counts = [
            'total' => $prompts->count(),
            'approved' => $prompts->where('status', 'approved')->count(),
            'pending' => $prompts->where('status', 'pending')->count(),
            'favorites' => $favorites->count(),
        ];

        // Creator Prompt Analytics
        $totalViews = (int) $prompts->sum('views_count');
        $totalCopies = (int) $prompts->sum('copies_count');
        $totalLikes = (int) $prompts->sum('likes_count');
        $copyRate = $totalViews > 0 ? round(($totalCopies / $totalViews) * 100, 1) : 0;

        // Monetization & Tipping
        $totalTips = (float) $user->receivedTips()->where('status', 'completed')->sum('amount');
        $tipsCount = (int) $user->receivedTips()->where('status', 'completed')->count();
        $recentTips = $user->receivedTips()->with('prompt')->where('status', 'completed')->latest()->take(5)->get();

        // 60-Day Activity Heatmap Data
        $startDate = \Illuminate\Support\Carbon::today()->subDays(59);
        $endDate = \Illuminate\Support\Carbon::today();

        $rawDaily = \App\Models\PromptAnalytic::where('user_id', $user->id)
            ->where('metric_date', '>=', $startDate->toDateString())
            ->selectRaw('metric_date, sum(views) as views, sum(copies) as copies, sum(favorites) as favorites')
            ->groupBy('metric_date')
            ->get()
            ->keyBy(fn ($item) => $item->metric_date->toDateString());

        $heatmapDays = [];
        for ($d = $startDate->copy(); $d->lte($endDate); $d->addDay()) {
            $dateStr = $d->toDateString();
            $record = $rawDaily->get($dateStr);
            $views = $record ? (int) $record->views : 0;
            $copies = $record ? (int) $record->copies : 0;
            $favs = $record ? (int) $record->favorites : 0;
            $score = $views + ($copies * 2) + ($favs * 3);

            // Level 0 (none), 1 (light), 2 (moderate), 3 (active), 4 (intense)
            $level = 0;
            if ($score > 40) {
                $level = 4;
            } elseif ($score > 20) {
                $level = 3;
            } elseif ($score > 8) {
                $level = 2;
            } elseif ($score > 0) {
                $level = 1;
            }

            $heatmapDays[] = [
                'date' => $dateStr,
                'day_name' => $d->format('D'),
                'day_num' => $d->format('j'),
                'label' => $d->format('M j, Y'),
                'views' => $views,
                'copies' => $copies,
                'favorites' => $favs,
                'score' => $score,
                'level' => $level,
            ];
        }

        // 14-Day Trajectory Trend Data for Chart
        $trendSlice = array_slice($heatmapDays, -14);
        $maxTrendValue = max(10, ...array_map(fn ($item) => max($item['views'], $item['copies']), $trendSlice));

        $analytics = [
            'total_views' => $totalViews,
            'total_copies' => $totalCopies,
            'total_likes' => $totalLikes,
            'copy_rate' => $copyRate,
            'total_tips' => $totalTips,
            'tips_count' => $tipsCount,
            'recent_tips' => $recentTips,
            'heatmap_days' => $heatmapDays,
            'trend_slice' => $trendSlice,
            'max_trend_value' => $maxTrendValue,
            'top_prompts' => $prompts->where('status', 'approved')->sortByDesc('views_count')->take(5),
        ];

        return view('dashboard.index', compact('prompts', 'favorites', 'counts', 'analytics'));
    }

    public function create()
    {
        abort_unless(SiteSetting::enabled('allow_submissions', true), 403, 'Prompt submissions are currently paused.');
        $categories = Category::orderBy('name')->get();
        $models = \App\Models\AiModel::where('is_active', true)->orderBy('sort_order')->orderBy('name')->get();

        return view('dashboard.prompts.form', ['prompt' => new Prompt(), 'categories' => $categories, 'models' => $models, 'editing' => false]);
    }

    public function store(Request $request)
    {
        abort_unless(SiteSetting::enabled('allow_submissions', true), 403, 'Prompt submissions are currently paused.');
        \App\Support\TurnstileVerifier::verify($request);
        $data = $this->validated($request);
        $data['user_id'] = $request->user()->id;
        $data['slug'] = Str::slug($data['title']).'-'.Str::lower(Str::random(6));
        $requiresReview = SiteSetting::enabled('require_approval', true);
        $data['status'] = $requiresReview ? 'pending' : 'approved';
        $data['approved_at'] = $requiresReview ? null : now();
        $data['image_path'] = $request->hasFile('image')
            ? WebpImageStorage::storeUpload($request->file('image'))
            : null;
        unset($data['image']);

        Prompt::create($data);

        return redirect()->route('dashboard.index')->with('success', $requiresReview
            ? 'Your prompt is in the review queue. We’ll publish it as soon as it’s approved.'
            : 'Your prompt is now live in the public gallery.');
    }

    public function edit(Request $request, Prompt $prompt)
    {
        abort_unless($prompt->user_id === $request->user()->id, 403);
        abort_unless(SiteSetting::enabled('allow_submissions', true), 403, 'Prompt submissions are currently paused.');
        $categories = Category::orderBy('name')->get();
        $models = \App\Models\AiModel::where('is_active', true)->orderBy('sort_order')->orderBy('name')->get();

        return view('dashboard.prompts.form', compact('prompt', 'categories', 'models') + ['editing' => true]);
    }

    public function update(Request $request, Prompt $prompt)
    {
        abort_unless($prompt->user_id === $request->user()->id, 403);
        abort_unless(SiteSetting::enabled('allow_submissions', true), 403, 'Prompt submissions are currently paused.');
        $data = $this->validated($request);
        $data['slug'] = Str::slug($data['title']).'-'.Str::lower(Str::random(6));
        $requiresReview = SiteSetting::enabled('require_approval', true);
        $data['status'] = $requiresReview ? 'pending' : 'approved';
        $data['approved_at'] = $requiresReview ? null : now();
        $data['admin_note'] = null;
        unset($data['image']);

        if ($request->hasFile('image')) {
            $newImagePath = WebpImageStorage::storeUpload($request->file('image'));
            if ($prompt->image_path) {
                Storage::disk('public')->delete($prompt->image_path);
            }
            $data['image_path'] = $newImagePath;
        }

        $prompt->update($data);

        return redirect()->route('dashboard.index')->with('success', $requiresReview
            ? 'Your changes were saved and sent for review.'
            : 'Your changes were saved and the prompt is live.');
    }

    public function destroy(Request $request, Prompt $prompt)
    {
        abort_unless($prompt->user_id === $request->user()->id, 403);
        if ($prompt->image_path) {
            Storage::disk('public')->delete($prompt->image_path);
        }
        $prompt->delete();

        return redirect()->route('dashboard.index')->with('success', 'Prompt removed from your studio.');
    }

    private function validated(Request $request): array
    {
        return $request->validate([
            'title' => ['required', 'string', 'max:120'],
            'category_id' => ['required', 'exists:categories,id'],
            'model' => ['required', 'string', 'max:60'],
            'prompt_text' => ['required', 'string', 'min:20', 'max:12000'],
            'image_url' => ['nullable', 'url', 'max:2048'],
            'image' => ['nullable', 'image', 'max:5120'],
        ]);
    }
}
