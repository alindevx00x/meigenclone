<?php

namespace App\Http\Controllers;

use App\Models\AiModel;
use App\Models\Category;
use App\Models\Prompt;
use App\Models\SiteSetting;
use App\Models\User;
use App\Support\WebpImageStorage;
use Illuminate\Support\Facades\Crypt;
use Illuminate\Support\Facades\Hash;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use Illuminate\Validation\Rule;
use Illuminate\Support\Str;

class AdminController extends Controller
{
    public function settings()
    {
        $settings = SiteSetting::current();

        return view('admin.settings', compact('settings'));
    }

    public function updateSettings(Request $request)
    {
        $mailSettingsEnabled = $request->boolean('mail_settings_enabled');
        $mailer = $request->input('mail_mailer', 'smtp');
        $data = $request->validate([
            'site_name' => ['required', 'string', 'max:60'],
            'site_tagline' => ['required', 'string', 'max:160'],
            'prompts_per_page' => ['required', 'integer', 'in:12,24,36,48'],
            'default_sort' => ['required', 'in:featured,newest,popular'],
            'mail_mailer' => ['required', 'in:smtp,log,array'],
            'mail_host' => [$mailSettingsEnabled && $mailer === 'smtp' ? 'required' : 'nullable', 'string', 'max:255'],
            'mail_port' => [$mailSettingsEnabled && $mailer === 'smtp' ? 'required' : 'nullable', 'integer', 'between:1,65535'],
            'mail_encryption' => ['required', 'in:none,tls,ssl'],
            'mail_username' => ['nullable', 'string', 'max:255'],
            'mail_password' => ['nullable', 'string', 'max:500'],
            'mail_from_address' => [$mailSettingsEnabled ? 'required' : 'nullable', 'email', 'max:255'],
            'mail_from_name' => ['nullable', 'string', 'max:120'],
            'mail_reply_to' => ['nullable', 'email', 'max:255'],
            'contact_email' => ['nullable', 'email', 'max:255'],
            'seo_title' => ['nullable', 'string', 'max:120'],
            'seo_title_template' => ['nullable', 'string', 'max:160'],
            'seo_description' => ['nullable', 'string', 'max:320'],
            'seo_keywords' => ['nullable', 'string', 'max:500'],
            'seo_canonical_base' => ['nullable', 'url', 'max:2048'],
            'seo_robots' => ['required', Rule::in(['index,follow', 'index,nofollow', 'noindex,follow', 'noindex,nofollow'])],
            'seo_og_image' => ['nullable', 'url', 'max:2048'],
        ]);

        foreach (['allow_registrations', 'allow_submissions', 'require_approval', 'mail_settings_enabled'] as $toggle) {
            $data[$toggle] = $request->boolean($toggle) ? '1' : '0';
        }
        $data['prompts_per_page'] = (string) $data['prompts_per_page'];

        $mailPassword = trim((string) ($data['mail_password'] ?? ''));
        unset($data['mail_password']);
        if ($mailPassword !== '') {
            $data['mail_password'] = Crypt::encryptString($mailPassword);
        }

        SiteSetting::updateValues($data);
        if ($request->boolean('clear_mail_password')) {
            SiteSetting::removeValue('mail_password');
        }

        return back()->with('success', 'Directory settings saved.');
    }

    public function index()
    {
        $stats = [
            'prompts' => Prompt::count(),
            'pending' => Prompt::where('status', 'pending')->count(),
            'published' => Prompt::where('status', 'approved')->count(),
            'members' => User::where('role', 'user')->count(),
            'categories' => Category::count(),
            'models' => AiModel::count(),
        ];
        $pendingPrompts = Prompt::with(['user', 'category'])->where('status', 'pending')->latest()->limit(6)->get();

        return view('admin.index', compact('stats', 'pendingPrompts'));
    }

    public function prompts(Request $request)
    {
        $status = $request->string('status')->toString();
        $query = Prompt::with(['user', 'category'])->latest();
        if (in_array($status, ['pending', 'approved', 'rejected'], true)) {
            $query->where('status', $status);
        }
        $prompts = $query->paginate(20)->withQueryString();

        return view('admin.prompts', compact('prompts', 'status'));
    }

    public function approve(Prompt $prompt)
    {
        $prompt->update(['status' => 'approved', 'approved_at' => now(), 'admin_note' => null]);

        return back()->with('success', 'Prompt approved and added to the public gallery.');
    }

    public function editPrompt(Prompt $prompt)
    {
        $categories = Category::orderBy('name')->get();
        $models = AiModel::where('is_active', true)->orderBy('sort_order')->orderBy('name')->get();

        return view('admin.prompt-form', compact('prompt', 'categories', 'models'));
    }

    public function updatePrompt(Request $request, Prompt $prompt)
    {
        $data = $request->validate([
            'title' => ['required', 'string', 'max:120'],
            'category_id' => ['required', 'exists:categories,id'],
            'model' => ['required', 'string', 'max:60'],
            'prompt_text' => ['required', 'string', 'min:20', 'max:12000'],
            'image_url' => ['nullable', 'url', 'max:2048'],
            'image' => ['nullable', 'image', 'max:5120'],
            'status' => ['required', 'in:pending,approved,rejected'],
            'is_featured' => ['nullable', 'boolean'],
            'admin_note' => ['nullable', 'string', 'max:1000'],
        ]);
        unset($data['image']);
        $data['slug'] = Str::slug($data['title']).'-'.Str::lower(Str::random(6));
        $data['is_featured'] = $request->boolean('is_featured');
        $data['approved_at'] = $data['status'] === 'approved' ? ($prompt->approved_at ?? now()) : null;

        if ($request->hasFile('image')) {
            $newImagePath = WebpImageStorage::storeUpload($request->file('image'));
            if ($prompt->image_path) {
                Storage::disk('public')->delete($prompt->image_path);
            }
            $data['image_path'] = $newImagePath;
        }

        $prompt->update($data);

        return redirect()->route('admin.prompts.index')->with('success', 'Prompt details updated.');
    }

    public function toggleFeatured(Prompt $prompt)
    {
        $prompt->update(['is_featured' => ! $prompt->is_featured]);

        return back()->with('success', $prompt->is_featured ? 'Prompt featured in the gallery.' : 'Prompt removed from featured picks.');
    }

    public function reject(Request $request, Prompt $prompt)
    {
        $data = $request->validate(['admin_note' => ['nullable', 'string', 'max:1000']]);
        $prompt->update(['status' => 'rejected', 'approved_at' => null, 'admin_note' => $data['admin_note'] ?? null]);

        return back()->with('success', 'Prompt returned to the creator with your review note.');
    }

    public function destroyPrompt(Prompt $prompt)
    {
        if ($prompt->image_path) {
            Storage::disk('public')->delete($prompt->image_path);
        }
        $prompt->delete();

        return back()->with('success', 'Prompt deleted.');
    }

    public function categories()
    {
        $categories = Category::withCount('prompts')->orderBy('name')->get();

        return view('admin.categories', compact('categories'));
    }

    public function storeCategory(Request $request)
    {
        $data = $this->categoryData($request);
        Category::create($data);

        return back()->with('success', 'Category added.');
    }

    public function updateCategory(Request $request, Category $category)
    {
        $category->update($this->categoryData($request, $category));

        return back()->with('success', 'Category updated.');
    }

    public function destroyCategory(Category $category)
    {
        if ($category->prompts()->exists()) {
            return back()->withErrors(['category' => 'Move or delete the prompts in this category before removing it.']);
        }
        $category->delete();

        return back()->with('success', 'Category removed.');
    }

    public function models()
    {
        $models = AiModel::withCount('prompts')->orderBy('sort_order')->orderBy('name')->get();

        return view('admin.models', compact('models'));
    }

    public function storeModel(Request $request)
    {
        $data = $this->modelData($request);
        AiModel::create($data);

        return back()->with('success', 'AI model added.');
    }

    public function updateModel(Request $request, AiModel $model)
    {
        $data = $this->modelData($request, $model);
        $model->update($data);

        return back()->with('success', 'AI model updated.');
    }

    public function destroyModel(AiModel $model)
    {
        if ($model->prompts()->exists()) {
            return back()->withErrors(['model' => 'Cannot remove this model because prompts are currently using it.']);
        }
        $model->delete();

        return back()->with('success', 'AI model removed.');
    }

    private function modelData(Request $request, ?AiModel $model = null): array
    {
        $uniqueName = Rule::unique('ai_models', 'name');
        $uniqueSlug = Rule::unique('ai_models', 'slug');
        if ($model) {
            $uniqueName->ignore($model->id);
            $uniqueSlug->ignore($model->id);
        }

        $data = $request->validate([
            'name' => ['required', 'string', 'max:60', $uniqueName],
            'slug' => ['nullable', 'alpha_dash', 'max:80', $uniqueSlug],
            'icon' => ['nullable', 'string', 'max:12'],
            'description' => ['nullable', 'string', 'max:255'],
            'sort_order' => ['nullable', 'integer'],
            'is_active' => ['nullable', 'boolean'],
        ]);

        $data['slug'] = $data['slug'] ?: Str::slug($data['name']);
        $data['icon'] = $data['icon'] ?: '◈';
        $data['sort_order'] = (int) ($data['sort_order'] ?? 0);
        $data['is_active'] = $request->boolean('is_active', true);

        return $data;
    }

    public function users(Request $request)
    {
        $query = User::withCount('prompts');

        if ($request->filled('q')) {
            $q = $request->string('q')->trim()->toString();
            $query->where(fn ($sub) => $sub->where('name', 'like', "%{$q}%")->orWhere('email', 'like', "%{$q}%"));
        }

        if ($request->filled('role') && in_array($request->role, ['user', 'admin'], true)) {
            $query->where('role', $request->role);
        }

        if ($request->filled('status') && in_array($request->status, ['active', 'suspended'], true)) {
            $query->where('status', $request->status);
        }

        $sort = $request->input('sort', 'newest');
        match ($sort) {
            'oldest' => $query->oldest(),
            'name' => $query->orderBy('name'),
            'prompts' => $query->orderByDesc('prompts_count'),
            default => $query->latest(),
        };

        $users = $query->paginate(20)->withQueryString();

        $stats = [
            'total' => User::count(),
            'admins' => User::where('role', 'admin')->count(),
            'creators' => User::where('role', 'user')->count(),
            'active' => User::where('status', 'active')->count(),
            'suspended' => User::where('status', 'suspended')->count(),
        ];

        return view('admin.users', compact('users', 'stats'));
    }

    public function updateUser(Request $request, User $user)
    {
        $uniqueEmail = Rule::unique('users', 'email')->ignore($user->id);

        $data = $request->validate([
            'name' => ['required', 'string', 'max:80'],
            'email' => ['required', 'email', 'max:255', $uniqueEmail],
            'role' => ['required', 'in:user,admin'],
            'status' => ['required', 'in:active,suspended'],
            'bio' => ['nullable', 'string', 'max:500'],
            'website' => ['nullable', 'url', 'max:255'],
            'password' => ['nullable', 'string', 'min:8'],
        ]);

        if ($user->is($request->user())) {
            if ($data['role'] !== 'admin') {
                return back()->withErrors(['role' => 'You cannot remove your own administrator status.']);
            }
            if ($data['status'] !== 'active') {
                return back()->withErrors(['status' => 'You cannot suspend your own account.']);
            }
        }

        $updateData = [
            'name' => $data['name'],
            'email' => $data['email'],
            'role' => $data['role'],
            'status' => $data['status'],
            'bio' => $data['bio'] ?? null,
            'website' => $data['website'] ?? null,
        ];

        if (! empty($data['password'])) {
            $updateData['password'] = Hash::make($data['password']);
        }

        $user->update($updateData);

        return back()->with('success', "Member '{$user->name}' updated successfully.");
    }

    public function toggleUserStatus(Request $request, User $user)
    {
        if ($user->is($request->user())) {
            return back()->withErrors(['status' => 'You cannot change your own account status.']);
        }

        $newStatus = $user->status === 'suspended' ? 'active' : 'suspended';
        $user->update(['status' => $newStatus]);

        $label = $newStatus === 'active' ? 'activated' : 'suspended';
        return back()->with('success', "Account for '{$user->name}' is now {$label}.");
    }

    public function destroyUser(Request $request, User $user)
    {
        if ($user->is($request->user())) {
            return back()->withErrors(['user' => 'You cannot delete your own administrator account.']);
        }

        $name = $user->name;
        $user->prompts()->update(['user_id' => null]);
        $user->favorites()->detach();
        $user->delete();

        return back()->with('success', "Member '{$name}' has been deleted.");
    }

    public function updateUserRole(Request $request, User $user)
    {
        $data = $request->validate(['role' => ['required', 'in:user,admin']]);
        if ($user->is($request->user())) {
            return back()->withErrors(['role' => 'You cannot change your own administrator access.']);
        }
        $user->update($data);

        return back()->with('success', 'Member access updated.');
    }

    private function categoryData(Request $request, ?Category $category = null): array
    {
        $uniqueName = Rule::unique('categories', 'name');
        $uniqueSlug = Rule::unique('categories', 'slug');
        if ($category) {
            $uniqueName->ignore($category->id);
            $uniqueSlug->ignore($category->id);
        }

        $data = $request->validate([
            'name' => ['required', 'string', 'max:60', $uniqueName],
            'slug' => ['nullable', 'alpha_dash', 'max:80', $uniqueSlug],
            'description' => ['nullable', 'string', 'max:160'],
            'icon' => ['nullable', 'string', 'max:12'],
        ]);
        if ($category && ! $request->exists('description')) {
            unset($data['description']);
        }
        $data['slug'] = $data['slug'] ?: str($data['name'])->slug()->toString();
        $data['icon'] = $data['icon'] ?: '✦';
        $data['color'] = $category?->color ?? 'violet';

        return $data;
    }
}
