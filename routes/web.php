<?php

use App\Http\Controllers\AdminController;
use App\Http\Controllers\AuthController;
use App\Http\Controllers\CollectionController;
use App\Http\Controllers\CreatorController;
use App\Http\Controllers\DashboardController;
use App\Http\Controllers\GenerationController;
use App\Http\Controllers\InstantSearchController;
use App\Http\Controllers\ProfileController;
use App\Http\Controllers\PromptController;
use App\Http\Controllers\PromptPackController;
use App\Http\Controllers\RemixStudioController;
use App\Http\Controllers\SrefController;
use App\Http\Controllers\TipController;
use Illuminate\Support\Facades\Route;

// Public Core Directory
Route::get('/', [PromptController::class, 'index'])->name('prompts.index');
Route::get('/prompts/category/{category:slug}', [PromptController::class, 'categoryLanding'])->name('prompts.category_landing');
Route::get('/prompts/style/{vibe}', [PromptController::class, 'styleLanding'])->name('prompts.style_landing');
Route::get('/sitemap.xml', [PromptController::class, 'sitemap'])->name('sitemap');
Route::get('/prompts/{prompt:slug}', [PromptController::class, 'show'])->name('prompts.show');
Route::post('/prompts/{prompt:slug}/favorite', [PromptController::class, 'toggleFavorite'])->middleware('auth')->name('prompts.favorite');

// Redirect removed legacy sections to Home
Route::redirect('/generate', '/')->name('generations.index');
Route::redirect('/remix', '/')->name('studio.remix');
Route::redirect('/sref', '/')->name('srefs.index');
Route::redirect('/packs', '/')->name('packs.index');
Route::redirect('/collections', '/')->name('collections.index');

// 1. Instant Typo-Tolerant Search API
Route::get('/api/prompts/instant-search', [InstantSearchController::class, 'search'])->name('prompts.instant_search');

// 2. Creator Profiles & Monetization
Route::get('/creators/{user}', [CreatorController::class, 'show'])->where('user', '@?[a-zA-Z0-9_\-]+')->name('creators.show');
Route::post('/creators/{user}/tip', [TipController::class, 'store'])->where('user', '@?[a-zA-Z0-9_\-]+')->name('creators.tip');
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
})->where('user', '@?[a-zA-Z0-9_\-]+')->middleware('auth')->name('creators.follow');

// 3. Real-time Prompt Analytics & Webhooks
Route::post('/api/prompts/{prompt}/track-copy', [PromptController::class, 'trackCopy'])->name('prompts.track_copy');
Route::post('/api/webhooks/tips', [TipController::class, 'webhook'])->withoutMiddleware([\Illuminate\Foundation\Http\Middleware\VerifyCsrfToken::class])->name('tips.webhook');

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
})->name('newsletter.subscribe');

// Legal & GDPR Compliance Pages
Route::view('/privacy', 'legal.privacy')->name('legal.privacy');
Route::view('/terms', 'legal.terms')->name('legal.terms');
Route::view('/gdpr', 'legal.gdpr')->name('legal.gdpr');
Route::view('/cookies', 'legal.cookies')->name('legal.cookies');

// Authentication
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
    Route::delete('/users/{user}', [AdminController::class, 'destroyUser'])->name('users.destroy');
    Route::patch('/users/{user}/role', [AdminController::class, 'updateUserRole'])->name('users.role');
    Route::get('/settings', [AdminController::class, 'settings'])->name('settings');
    Route::put('/settings', [AdminController::class, 'updateSettings'])->name('settings.update');
});
