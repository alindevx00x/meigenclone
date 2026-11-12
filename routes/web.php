<?php

use App\Http\Controllers\AboutController;
use App\Http\Controllers\Admin\BlogController as AdminBlogController;
use App\Http\Controllers\AdminController;
use App\Http\Controllers\AuthController;
use App\Http\Controllers\BlogController;
use App\Http\Controllers\CollectionController;
use App\Http\Controllers\ContactController;
use App\Http\Controllers\CreatorController;
use App\Http\Controllers\DashboardController;
use App\Http\Controllers\InstantSearchController;
use App\Http\Controllers\ProfileController;
use App\Http\Controllers\PromptController;
use App\Http\Controllers\PromptPackController;
use App\Http\Controllers\SrefController;
use App\Http\Controllers\TipController;
use Illuminate\Support\Facades\Route;

// Public Core Directory
Route::get('/', [PromptController::class, 'index'])->name('prompts.index');
Route::get('/prompts/category/{category:slug}', [PromptController::class, 'categoryLanding'])->name('prompts.category_landing');
Route::get('/prompts/style/{vibe}', [PromptController::class, 'styleLanding'])->name('prompts.style_landing');
Route::get('/sitemap.xml', [PromptController::class, 'sitemap'])->name('sitemap');
Route::get('/sitemap-main.xml', [PromptController::class, 'sitemapMain'])->name('sitemap.main');
Route::get('/sitemap-prompts.xml', [PromptController::class, 'sitemapPrompts'])->name('sitemap.prompts');
Route::get('/sitemap-category-{slug}.xml', [PromptController::class, 'sitemapCategoryPrompts'])->name('sitemap.category');
Route::get('/robots.txt', [PromptController::class, 'robots'])->name('robots');
Route::get('/prompts/{prompt:slug}', [PromptController::class, 'show'])->name('prompts.show');
Route::get('/prompts/{prompt:slug}/og-image.jpg', [PromptController::class, 'ogImage'])->name('prompts.og_image');
Route::post('/prompts/{prompt:slug}/favorite', [PromptController::class, 'toggleFavorite'])->middleware(['auth', 'throttle:30,1'])->name('prompts.favorite');

// On-demand fast WebP thumbnail delivery & auto-generation
Route::get('/storage/prompt-covers/thumbs/{filename}', function (string $filename) {
    $baseName = pathinfo($filename, PATHINFO_FILENAME);
    $disk = \Illuminate\Support\Facades\Storage::disk('public');
    $webpRelPath = 'prompt-covers/thumbs/' . $baseName . '.webp';

    // 1. Serve optimized .webp thumbnail if already on disk
    if ($disk->exists($webpRelPath)) {
        return response()->file(storage_path('app/public/' . $webpRelPath), [
            'Content-Type' => 'image/webp',
            'Cache-Control' => 'public, max-age=31536000, immutable',
        ]);
    }

    // 2. Discover original image across supported extensions (.webp, .jpg, .jpeg, .png)
    $candidates = [
        'prompt-covers/' . $baseName . '.webp',
        'prompt-covers/' . $baseName . '.jpg',
        'prompt-covers/' . $baseName . '.jpeg',
        'prompt-covers/' . $baseName . '.png',
    ];

    foreach ($candidates as $candidate) {
        if ($disk->exists($candidate)) {
            try {
                $generated = \App\Support\WebpImageStorage::generateThumbnail($candidate, 540, 78);
                if ($generated && $disk->exists($generated)) {
                    return response()->file(storage_path('app/public/' . $generated), [
                        'Content-Type' => 'image/webp',
                        'Cache-Control' => 'public, max-age=31536000, immutable',
                    ]);
                }
            } catch (\Throwable) {
                // Continue to next candidate
            }

            return response()->file(storage_path('app/public/' . $candidate), [
                'Cache-Control' => 'public, max-age=86400',
            ]);
        }
    }

    abort(404);
})->where('filename', '[a-zA-Z0-9_\-\.]+')->name('prompt.thumbnail.serve');

// Redirect removed legacy sections to Home
Route::redirect('/generate', '/')->name('generations.index');
Route::redirect('/remix', '/')->name('studio.remix');
Route::redirect('/sref', '/')->name('srefs.index');
Route::redirect('/packs', '/')->name('packs.index');
Route::redirect('/collections', '/')->name('collections.index');

// 1. Instant Typo-Tolerant Search API (Rate-limited)
Route::get('/api/prompts/instant-search', [InstantSearchController::class, 'search'])->middleware('throttle:60,1')->name('prompts.instant_search');

// 2. Creator Profiles & Monetization
Route::get('/creators/{user}', [CreatorController::class, 'show'])->where('user', '@?[a-zA-Z0-9_\-]+')->name('creators.show');
Route::post('/creators/{user}/tip', [TipController::class, 'store'])->where('user', '@?[a-zA-Z0-9_\-]+')->middleware('throttle:20,1')->name('creators.tip');
Route::get('/tips/stripe/success', [TipController::class, 'stripeSuccess'])->name('tips.stripe.success');
Route::get('/tips/paypal/capture', [TipController::class, 'paypalCapture'])->name('tips.paypal.capture');
Route::post('/creators/{user}/follow', function (\App\Models\User $user) {
    $follower = request()->user();
    if ($follower->id === $user->id) {
        return back()->with('error', 'You cannot follow yourself.');
    }
    $isFollowing = $follower->isFollowing($user);
    if ($isFollowing) {
        $follower->following()->detach($user->id);
        $msg = 'Unfollowed ' . $user->name;
    } else {
        $follower->following()->attach($user->id);
        $msg = 'You are now following ' . $user->name;
    }
    return back()->with('success', $msg);
})->where('user', '@?[a-zA-Z0-9_\-]+')->middleware(['auth', 'throttle:30,1'])->name('creators.follow');

// 3. Real-time Prompt Analytics & Webhooks
Route::post('/api/prompts/{prompt}/track-copy', [PromptController::class, 'trackCopy'])->middleware('throttle:60,1')->name('prompts.track_copy');
Route::post('/api/webhooks/tips', [TipController::class, 'webhook'])->withoutMiddleware([\Illuminate\Foundation\Http\Middleware\VerifyCsrfToken::class])->middleware('throttle:120,1')->name('tips.webhook');

// Email Verification
Route::middleware('auth')->group(function () {
    Route::get('/email/verify', [\App\Http\Controllers\Auth\VerificationController::class, 'notice'])->name('verification.notice');
    Route::get('/email/verify/{id}/{hash}', [\App\Http\Controllers\Auth\VerificationController::class, 'verify'])->middleware(['signed', 'throttle:6,1'])->name('verification.verify');
    Route::post('/email/verification-notification', [\App\Http\Controllers\Auth\VerificationController::class, 'resend'])->middleware('throttle:6,1')->name('verification.send');
});

// Newsletter subscription
Route::post('/newsletter/subscribe', function (\Illuminate\Http\Request $request) {
    $data = $request->validate([
        'email' => ['required', 'email', 'max:255'],
    ]);
    \Illuminate\Support\Facades\DB::table('newsletter_subscribers')->updateOrInsert(
        ['email' => strtolower(trim($data['email']))],
        ['user_id' => auth()->id(), 'is_active' => true, 'updated_at' => now(), 'created_at' => now()]
    );
    return back()->with('success', 'Thanks for subscribing! You will receive the weekly Top 10 Prompts digest.');
})->middleware('throttle:6,1')->name('newsletter.subscribe');

Route::get('/newsletter/unsubscribe/{email}', [AdminController::class, 'unsubscribeNewsletter'])->name('newsletter.unsubscribe');

// About Us Page (SEO-Optimized)
Route::get('/about', [AboutController::class, 'index'])->name('about');

// Blog & Knowledge Base (Guides & Tutorials)
Route::get('/blog', [BlogController::class, 'index'])->name('blog.index');
Route::get('/blog/category/{slug}', [BlogController::class, 'category'])->name('blog.category');
Route::get('/blog/{slug}', [BlogController::class, 'show'])->name('blog.show');

// Contact Page
Route::get('/contact', [ContactController::class, 'index'])->name('contact.index');
Route::post('/contact', [ContactController::class, 'store'])->middleware('throttle:5,1')->name('contact.store');

// Legal & GDPR Compliance Pages
Route::view('/privacy', 'legal.privacy')->name('legal.privacy');
Route::view('/terms', 'legal.terms')->name('legal.terms');
Route::view('/gdpr', 'legal.gdpr')->name('legal.gdpr');
Route::view('/cookies', 'legal.cookies')->name('legal.cookies');

// Authentication
Route::get('/api/check-username', [AuthController::class, 'checkUsername'])->middleware('throttle:60,1')->name('check.username');
Route::middleware('guest')->group(function () {
    Route::get('/login', [AuthController::class, 'loginForm'])->name('login');
    Route::post('/login', [AuthController::class, 'login'])->middleware('throttle:10,1')->name('login.store');
    Route::get('/register', [AuthController::class, 'registerForm'])->name('register');
    Route::post('/register', [AuthController::class, 'register'])->middleware('throttle:10,1')->name('register.store');
});
Route::post('/logout', [AuthController::class, 'logout'])->middleware('auth')->name('logout');

// User Dashboard
Route::middleware('auth')->prefix('dashboard')->name('dashboard.')->group(function () {
    Route::get('/', [DashboardController::class, 'index'])->name('index');
    Route::get('/profile', [ProfileController::class, 'edit'])->name('profile.edit');
    Route::put('/profile', [ProfileController::class, 'update'])->name('profile.update');
    Route::get('/prompts/create', [DashboardController::class, 'create'])->name('prompts.create');
    Route::post('/prompts', [DashboardController::class, 'store'])->middleware('throttle:15,1')->name('prompts.store');
    Route::get('/prompts/{prompt}/edit', [DashboardController::class, 'edit'])->name('prompts.edit');
    Route::put('/prompts/{prompt}', [DashboardController::class, 'update'])->name('prompts.update');
    Route::delete('/prompts/{prompt}', [DashboardController::class, 'destroy'])->name('prompts.destroy');
});

// Admin Panel
Route::middleware(['auth', 'can:admin'])->prefix('admin')->name('admin.')->group(function () {
    Route::get('/', [AdminController::class, 'index'])->name('dashboard');
    Route::get('/prompts', [AdminController::class, 'prompts'])->name('prompts.index');
    Route::post('/prompts/bulk', [AdminController::class, 'bulkPrompts'])->name('prompts.bulk');
    Route::get('/prompts/{prompt}/edit', [AdminController::class, 'editPrompt'])->name('prompts.edit');
    Route::put('/prompts/{prompt}', [AdminController::class, 'updatePrompt'])->name('prompts.update');
    Route::patch('/prompts/{prompt}/approve', [AdminController::class, 'approve'])->name('prompts.approve');
    Route::patch('/prompts/{prompt}/reject', [AdminController::class, 'reject'])->name('prompts.reject');
    Route::patch('/prompts/{prompt}/feature', [AdminController::class, 'toggleFeatured'])->name('prompts.feature');
    Route::delete('/prompts/{prompt}', [AdminController::class, 'destroyPrompt'])->name('prompts.destroy');
    Route::get('/categories', [AdminController::class, 'categories'])->name('categories.index');
    Route::post('/categories', [AdminController::class, 'storeCategory'])->name('categories.store');
    Route::put('/categories/{category}', [AdminController::class, 'updateCategory'])->name('categories.update');
    Route::delete('/categories/{category}', [AdminController::class, 'destroyCategory'])->name('categories.destroy');
    Route::get('/models', [AdminController::class, 'models'])->name('models.index');
    Route::post('/models', [AdminController::class, 'storeModel'])->name('models.store');
    Route::put('/models/{model}', [AdminController::class, 'updateModel'])->name('models.update');
    Route::delete('/models/{model}', [AdminController::class, 'destroyModel'])->name('models.destroy');
    Route::get('/users', [AdminController::class, 'users'])->name('users.index');
    Route::get('/users/{user}/edit', [AdminController::class, 'editUser'])->name('users.edit');
    Route::put('/users/{user}', [AdminController::class, 'updateUser'])->name('users.update');
    Route::patch('/users/{user}/status', [AdminController::class, 'toggleUserStatus'])->name('users.status');
    Route::patch('/users/{user}/verify', [AdminController::class, 'toggleEmailVerification'])->name('users.verify');
    Route::delete('/users/{user}', [AdminController::class, 'destroyUser'])->name('users.destroy');
    Route::patch('/users/{user}/role', [AdminController::class, 'updateUserRole'])->name('users.role');
    Route::get('/contacts', [AdminController::class, 'contacts'])->name('contacts.index');
    Route::patch('/contacts/{contact}/read', [AdminController::class, 'toggleContactRead'])->name('contacts.read');
    Route::delete('/contacts/{contact}', [AdminController::class, 'destroyContact'])->name('contacts.destroy');
    Route::get('/newsletters', [AdminController::class, 'newsletters'])->name('newsletters.index');
    Route::get('/newsletters/digest', [AdminController::class, 'newsletterDigest'])->name('newsletters.digest');
    Route::post('/newsletters/digest/test', [AdminController::class, 'sendDigestTest'])->name('newsletters.digest.test');
    Route::post('/newsletters/digest/send', [AdminController::class, 'sendDigestNow'])->name('newsletters.digest.send');
    Route::patch('/newsletters/{subscriber}/active', [AdminController::class, 'toggleNewsletterActive'])->name('newsletters.active');
    Route::delete('/newsletters/{subscriber}', [AdminController::class, 'destroyNewsletter'])->name('newsletters.destroy');
    Route::get('/newsletters/export', [AdminController::class, 'exportNewsletters'])->name('newsletters.export');
    // Pinterest Bulk Export Download
    Route::get('/prompts/pinterest-export-zip', function () {
        $zipPath = storage_path('app/pinterest-bulk-csv-all.zip');
        if (!file_exists($zipPath)) {
            abort(404, 'Pinterest export archive not found. Run php artisan prompts:export-pinterest-bulk first.');
        }
        return response()->download($zipPath, 'promptimage-pinterest-bulk-all-25-parts.zip');
    })->name('prompts.pinterest_export_zip');

    // Blog Management
    Route::get('/blog/posts', [AdminBlogController::class, 'index'])->name('blog.index');
    Route::get('/blog/posts/create', [AdminBlogController::class, 'create'])->name('blog.create');
    Route::post('/blog/posts', [AdminBlogController::class, 'store'])->name('blog.store');
    Route::get('/blog/posts/{post}/edit', [AdminBlogController::class, 'edit'])->name('blog.edit');
    Route::put('/blog/posts/{post}', [AdminBlogController::class, 'update'])->name('blog.update');
    Route::delete('/blog/posts/{post}', [AdminBlogController::class, 'destroy'])->name('blog.destroy');
    Route::post('/blog/upload-image', [AdminBlogController::class, 'uploadImage'])->name('blog.upload_image');
    Route::get('/blog/categories', [AdminBlogController::class, 'categories'])->name('blog.categories.index');
    Route::post('/blog/categories', [AdminBlogController::class, 'storeCategory'])->name('blog.categories.store');
    Route::put('/blog/categories/{category}', [AdminBlogController::class, 'updateCategory'])->name('blog.categories.update');
    Route::delete('/blog/categories/{category}', [AdminBlogController::class, 'destroyCategory'])->name('blog.categories.destroy');

    Route::get('/settings', [AdminController::class, 'settings'])->name('settings');
    Route::put('/settings', [AdminController::class, 'updateSettings'])->name('settings.update');
});
