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

        return view('dashboard.index', compact('prompts', 'favorites', 'counts'));
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
