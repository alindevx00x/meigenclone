<?php

namespace App\Http\Controllers;

use App\Models\AiModel;
use App\Models\Category;
use App\Models\ContactMessage;
use App\Models\NewsletterSubscriber;
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
        $settings = SiteSetting::current(forAdmin: true);
        $approvedPrompts = Prompt::where('status', 'approved')
            ->latest()
            ->take(120)
            ->get(['id', 'title', 'model', 'views_count', 'likes_count']);

        return view('admin.settings', compact('settings', 'approvedPrompts'));
    }

    public function updateSettings(Request $request)
    {
        $mailSettingsEnabled = $request->boolean('mail_settings_enabled');
        $mailer = $request->input('mail_mailer', 'smtp');
        $data = $request->validate([
            'site_name' => ['required', 'string', 'max:60'],
            'site_tagline' => ['required', 'string', 'max:160'],
            'prompts_per_page' => ['required', 'integer', 'min:6', 'max:250'],
            'default_sort' => ['required', 'in:random,featured,newest,popular'],
            'featured_prompt_id' => ['nullable', 'string', 'max:30'],
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
            'require_email_verification' => ['nullable'],
            'storage_driver' => ['nullable', 'in:local,s3,r2'],
            'storage_aws_access_key_id' => ['nullable', 'string', 'max:255'],
            'storage_aws_secret_access_key' => ['nullable', 'string', 'max:255'],
            'storage_aws_default_region' => ['nullable', 'string', 'max:100'],
            'storage_aws_bucket' => ['nullable', 'string', 'max:255'],
            'storage_aws_url' => ['nullable', 'string', 'max:255'],
            'storage_aws_endpoint' => ['nullable', 'string', 'max:255'],
            'stripe_enabled' => ['nullable'],
            'stripe_publishable_key' => ['nullable', 'string', 'max:255'],
            'stripe_secret_key' => ['nullable', 'string', 'max:255'],
            'stripe_webhook_secret' => ['nullable', 'string', 'max:255'],
            'paypal_enabled' => ['nullable'],
            'paypal_client_id' => ['nullable', 'string', 'max:255'],
            'paypal_secret' => ['nullable', 'string', 'max:255'],
            'paypal_mode' => ['nullable', 'in:sandbox,live'],
            'custom_head_code' => ['nullable', 'string', 'max:65000'],
            'custom_body_code' => ['nullable', 'string', 'max:65000'],
            'favicon' => ['nullable', 'file', 'max:2048', function ($attribute, $value, $fail) {
                if (!$value instanceof \Illuminate\Http\UploadedFile) return;
                $ext = strtolower($value->getClientOriginalExtension());
                if (!in_array($ext, ['ico', 'png', 'svg', 'webp', 'jpg', 'jpeg'], true)) {
                    $fail('The favicon must be an ICO, PNG, SVG, or WebP file.');
                }
            }],
            'logo' => ['nullable', 'file', 'max:4096', function ($attribute, $value, $fail) {
                if (!$value instanceof \Illuminate\Http\UploadedFile) return;
                $ext = strtolower($value->getClientOriginalExtension());
                if (!in_array($ext, ['svg', 'png', 'webp', 'jpg', 'jpeg', 'ico'], true)) {
                    $fail('The logo must be an SVG, PNG, WebP, JPG, or ICO image.');
                }
            }],
        ]);

        if ($request->hasFile('favicon')) {
            $path = $request->file('favicon')->store('favicons', 'public');
            $data['favicon_url'] = '/storage/' . $path;
        } elseif ($request->boolean('remove_favicon')) {
            $data['favicon_url'] = '';
        }

        if ($request->hasFile('logo')) {
            $path = $request->file('logo')->store('logos', 'public');
            $data['logo_url'] = '/storage/' . $path;
        } elseif ($request->boolean('remove_logo')) {
            $data['logo_url'] = '';
        }

        $data['featured_prompt_id'] = $request->input('featured_prompt_id') ?: '';

        foreach (['allow_registrations', 'allow_submissions', 'require_approval', 'mail_settings_enabled', 'require_email_verification', 'stripe_enabled', 'paypal_enabled'] as $toggle) {
            $data[$toggle] = $request->boolean($toggle) ? '1' : '0';
        }

        foreach ([
            'storage_driver', 'storage_aws_access_key_id', 'storage_aws_secret_access_key',
            'storage_aws_default_region', 'storage_aws_bucket', 'storage_aws_url', 'storage_aws_endpoint',
            'stripe_publishable_key', 'stripe_secret_key', 'stripe_webhook_secret',
            'paypal_client_id', 'paypal_secret', 'paypal_mode'
        ] as $settingKey) {
            if ($request->has($settingKey)) {
                $data[$settingKey] = (string) $request->input($settingKey, '');
            }
        }
        $data['prompts_per_page'] = (string) $data['prompts_per_page'];

        $mailPassword = trim((string) ($data['mail_password'] ?? ''));
        unset($data['mail_password']);
        if ($mailPassword !== '') {
            $data['mail_password'] = Crypt::encryptString($mailPassword);
        }

        $data['custom_head_code'] = (string) $request->input('custom_head_code', '');
        $data['custom_body_code'] = (string) $request->input('custom_body_code', '');

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
            'contacts_unread' => ContactMessage::where('is_read', false)->count(),
            'contacts_total' => ContactMessage::count(),
            'newsletters_count' => NewsletterSubscriber::count(),
        ];
        $pendingPrompts = Prompt::with(['user', 'category'])->where('status', 'pending')->latest()->limit(6)->get();
        $recentContacts = ContactMessage::latest()->limit(5)->get();

        return view('admin.index', compact('stats', 'pendingPrompts', 'recentContacts'));
    }

    public function prompts(Request $request)
    {
        $status = $request->string('status')->toString();
        $query = Prompt::with(['user', 'category']);

        if (in_array($status, ['pending', 'approved', 'rejected'], true)) {
            $query->where('status', $status);
        }

        if ($request->filled('q')) {
            $q = $request->string('q')->trim()->toString();
            $query->where(fn ($sub) => $sub
                ->where('title', 'like', "%{$q}%")
                ->orWhere('prompt_text', 'like', "%{$q}%")
                ->orWhereHas('user', fn ($u) => $u->where('name', 'like', "%{$q}%"))
            );
        }

        if ($request->filled('category_id')) {
            $query->where('category_id', $request->input('category_id'));
        }

        if ($request->filled('model')) {
            $query->where('model', $request->input('model'));
        }

        if ($request->filled('featured')) {
            if ($request->input('featured') === '1') {
                $query->where('is_featured', true);
            } elseif ($request->input('featured') === '0') {
                $query->where('is_featured', false);
            }
        }

        $sort = $request->input('sort', 'newest');
        match ($sort) {
            'oldest' => $query->oldest(),
            'likes' => $query->orderByDesc('likes_count'),
            'title' => $query->orderBy('title'),
            default => $query->latest(),
        };

        $prompts = $query->paginate(24)->withQueryString();
        $categories = Category::orderBy('name')->get();
        $models = AiModel::where('is_active', true)->orderBy('sort_order')->orderBy('name')->get();

        $counts = [
            'all' => Prompt::count(),
            'pending' => Prompt::where('status', 'pending')->count(),
            'approved' => Prompt::where('status', 'approved')->count(),
            'rejected' => Prompt::where('status', 'rejected')->count(),
        ];

        return view('admin.prompts', compact('prompts', 'status', 'categories', 'models', 'counts'));
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

    public function editUser(User $user)
    {
        $user->loadCount(['prompts', 'favorites', 'collections', 'promptPacks', 'generations', 'followers', 'following']);
        $recentPrompts = $user->prompts()->with('category')->latest()->take(8)->get();

        return view('admin.users.edit', compact('user', 'recentPrompts'));
    }

    public function updateUser(Request $request, User $user)
    {
        $uniqueEmail = Rule::unique('users', 'email')->ignore($user->id);
        $uniqueUsername = Rule::unique('users', 'username')->ignore($user->id);

        $cleanUsername = ltrim(trim($request->input('username', '')), '@');
        if ($cleanUsername !== '') {
            $request->merge(['username' => $cleanUsername]);
        }

        $data = $request->validate([
            'name' => ['required', 'string', 'max:80'],
            'username' => ['nullable', 'string', 'min:3', 'max:30', 'regex:/^[a-zA-Z0-9_]+$/', $uniqueUsername],
            'email' => ['required', 'email', 'max:255', $uniqueEmail],
            'role' => ['required', 'in:user,admin'],
            'status' => ['required', 'in:active,suspended'],
            'is_verified' => ['nullable', 'boolean'],
            'email_verified' => ['nullable', 'boolean'],
            'avatar_url' => ['nullable', 'string', 'max:500'],
            'avatar' => ['nullable', 'image', 'mimes:jpg,jpeg,png,webp', 'max:4096'],
            'bio' => ['nullable', 'string', 'max:500'],
            'website' => ['nullable', 'url', 'max:255'],
            'tip_link' => ['nullable', 'url', 'max:500'],
            'tip_goal' => ['nullable', 'string', 'max:255'],
            'stripe_account_id' => ['nullable', 'string', 'max:120'],
            'paypal_email' => ['nullable', 'email', 'max:190'],
            'monetization_enabled' => ['nullable', 'boolean'],
            'credits' => ['nullable', 'integer', 'min:0', 'max:100000'],
            'openai_api_key' => ['nullable', 'string', 'max:255'],
            'replicate_api_token' => ['nullable', 'string', 'max:255'],
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
            'is_verified' => $request->boolean('is_verified'),
            'bio' => $data['bio'] ?? null,
            'website' => $data['website'] ?? null,
            'tip_link' => $data['tip_link'] ?? null,
            'tip_goal' => $data['tip_goal'] ?? null,
            'stripe_account_id' => $data['stripe_account_id'] ?? null,
            'paypal_email' => $data['paypal_email'] ?? null,
            'monetization_enabled' => $request->boolean('monetization_enabled', true),
            'avatar_url' => $data['avatar_url'] ?? null,
        ];

        if ($request->has('email_verified')) {
            $isEmailVerified = $request->boolean('email_verified');
            $updateData['email_verified_at'] = $isEmailVerified ? ($user->email_verified_at ?: now()) : null;
            if ($isEmailVerified) {
                $updateData['is_verified'] = true;
            }
        }

        if (! empty($data['username'])) {
            $updateData['username'] = strtolower($data['username']);
        }

        if ($request->hasFile('avatar')) {
            $avatarPath = WebpImageStorage::storeUpload($request->file('avatar'), 'avatars');
            $updateData['avatar_url'] = '/storage/' . $avatarPath;
        }

        if (array_key_exists('credits', $data)) {
            $updateData['credits'] = (int) $data['credits'];
        }

        if ($request->has('openai_api_key')) {
            $updateData['openai_api_key'] = $data['openai_api_key'] ?: null;
        }

        if ($request->has('replicate_api_token')) {
            $updateData['replicate_api_token'] = $data['replicate_api_token'] ?: null;
        }

        if (! empty($data['password'])) {
            $updateData['password'] = Hash::make($data['password']);
        }

        $user->update($updateData);

        if ($request->input('action') === 'save_and_exit') {
            return redirect()->route('admin.users.index')->with('success', "Member '{$user->name}' updated successfully.");
        }

        return redirect()->route('admin.users.edit', $user)->with('success', "Member '{$user->name}' settings saved successfully.");
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

    public function toggleEmailVerification(Request $request, User $user)
    {
        $wasVerified = (bool) $user->email_verified_at;
        $user->update([
            'email_verified_at' => $wasVerified ? null : now(),
            'is_verified' => ! $wasVerified,
        ]);

        $label = ! $wasVerified ? 'verified' : 'marked unverified';
        return back()->with('success', "Member '{$user->name}' has been {$label}.");
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

    public function contacts(Request $request)
    {
        $query = ContactMessage::query();

        if ($request->filled('q')) {
            $s = trim($request->string('q')->toString());
            $query->where(function ($q) use ($s) {
                $q->where('name', 'like', "%{$s}%")
                    ->orWhere('email', 'like', "%{$s}%")
                    ->orWhere('subject', 'like', "%{$s}%")
                    ->orWhere('message', 'like', "%{$s}%");
            });
        }

        if ($request->filled('status')) {
            if ($request->status === 'unread') {
                $query->where('is_read', false);
            } elseif ($request->status === 'read') {
                $query->where('is_read', true);
            }
        }

        $contacts = $query->latest()->paginate(20)->withQueryString();

        $stats = [
            'total' => ContactMessage::count(),
            'unread' => ContactMessage::where('is_read', false)->count(),
            'read' => ContactMessage::where('is_read', true)->count(),
        ];

        return view('admin.contacts', compact('contacts', 'stats'));
    }

    public function toggleContactRead(ContactMessage $contact)
    {
        $contact->update(['is_read' => ! $contact->is_read]);
        $status = $contact->is_read ? 'marked as read' : 'marked as unread';
        return back()->with('success', "Message from '{$contact->name}' {$status}.");
    }

    public function destroyContact(ContactMessage $contact)
    {
        $contact->delete();
        return back()->with('success', 'Contact submission removed successfully.');
    }

    public function newsletters(Request $request)
    {
        $query = NewsletterSubscriber::with('user');

        if ($request->filled('q')) {
            $s = trim($request->string('q')->toString());
            $query->where('email', 'like', "%{$s}%");
        }

        if ($request->filled('status')) {
            if ($request->status === 'active') {
                $query->where('is_active', true);
            } elseif ($request->status === 'inactive') {
                $query->where('is_active', false);
            }
        }

        $subscribers = $query->latest()->paginate(25)->withQueryString();

        $stats = [
            'total' => NewsletterSubscriber::count(),
            'active' => NewsletterSubscriber::where('is_active', true)->count(),
            'inactive' => NewsletterSubscriber::where('is_active', false)->count(),
        ];

        return view('admin.newsletters', compact('subscribers', 'stats'));
    }

    public function toggleNewsletterActive(NewsletterSubscriber $subscriber)
    {
        $subscriber->update(['is_active' => ! $subscriber->is_active]);
        $status = $subscriber->is_active ? 'activated' : 'deactivated';
        return back()->with('success', "Subscriber '{$subscriber->email}' {$status}.");
    }

    public function destroyNewsletter(NewsletterSubscriber $subscriber)
    {
        $subscriber->delete();
        return back()->with('success', 'Newsletter subscriber removed.');
    }

    public function exportNewsletters()
    {
        $subscribers = NewsletterSubscriber::where('is_active', true)->orderBy('created_at', 'desc')->get();
        $filename = 'subscribers_' . now()->format('Y-m-d') . '.csv';

        $headers = [
            'Content-Type' => 'text/csv',
            'Content-Disposition' => "attachment; filename=\"{$filename}\"",
        ];

        $callback = function () use ($subscribers) {
            $handle = fopen('php://output', 'w');
            fputcsv($handle, ['Email', 'Subscribed At', 'Is Active']);
            foreach ($subscribers as $row) {
                fputcsv($handle, [$row->email, $row->created_at->toIso8601String(), $row->is_active ? 'yes' : 'no']);
            }
            fclose($handle);
        };

        return response()->stream($callback, 200, $headers);
    }
}
