<?php

use App\Http\Controllers\AdminController;
use App\Http\Controllers\AuthController;
use App\Http\Controllers\CollectionController;
use App\Http\Controllers\DashboardController;
use App\Http\Controllers\GenerationController;
use App\Http\Controllers\InstantSearchController;
use App\Http\Controllers\PromptController;
use App\Http\Controllers\PromptPackController;
use App\Http\Controllers\RemixStudioController;
use App\Http\Controllers\SrefController;
use App\Http\Controllers\TipController;
use Illuminate\Support\Facades\Route;

// Public Core Directory
Route::get('/', [PromptController::class, 'index'])->name('prompts.index');
Route::get('/sitemap.xml', [PromptController::class, 'sitemap'])->name('sitemap');
Route::get('/prompts/{prompt:slug}', [PromptController::class, 'show'])->name('prompts.show');
Route::post('/prompts/{prompt:slug}/favorite', [PromptController::class, 'toggleFavorite'])->middleware('auth')->name('prompts.favorite');

// 1. AI Generation (Disabled for now as requested)
Route::redirect('/generate', '/remix')->name('generations.index');
// Route::post('/api/generate', [GenerationController::class, 'store'])->middleware('throttle:30,1')->name('generations.store');
// Route::post('/generations/{generation}/publish', [GenerationController::class, 'publish'])->middleware('auth')->name('generations.publish');

// 2. Interactive Prompt Remixing Studio
Route::get('/remix', [RemixStudioController::class, 'index'])->name('studio.remix');

// 3. Midjourney Style Reference (SREF) & Seed Catalog
Route::get('/sref', [SrefController::class, 'index'])->name('srefs.index');
Route::get('/sref/{code}', [SrefController::class, 'show'])->name('srefs.show');
Route::post('/api/sref/{sref}/copy', [SrefController::class, 'copy'])->name('srefs.copy');
Route::post('/api/sref/{sref}/like', [SrefController::class, 'like'])->name('srefs.like');

// 4. Instant Typo-Tolerant Search API
Route::get('/api/prompts/instant-search', [InstantSearchController::class, 'search'])->name('prompts.instant_search');

// 5. Creator Monetization & Prompt Packs
Route::get('/packs', [PromptPackController::class, 'index'])->name('packs.index');
Route::get('/packs/create', [PromptPackController::class, 'create'])->middleware('auth')->name('packs.create');
Route::post('/packs', [PromptPackController::class, 'store'])->middleware('auth')->name('packs.store');
Route::get('/packs/{slug}', [PromptPackController::class, 'show'])->name('packs.show');
Route::get('/packs/{pack}/download', [PromptPackController::class, 'download'])->name('packs.download');
Route::post('/creators/{user}/tip', [TipController::class, 'store'])->name('creators.tip');

// 6. User Moodboards & Collections
Route::get('/collections', [CollectionController::class, 'index'])->name('collections.index');
Route::get('/collections/{slug}', [CollectionController::class, 'show'])->name('collections.show');
Route::post('/collections', [CollectionController::class, 'store'])->middleware('auth')->name('collections.store');
Route::post('/collections/{collection}/toggle', [CollectionController::class, 'togglePrompt'])->middleware('auth')->name('collections.toggle');
Route::get('/api/my-collections', [CollectionController::class, 'userCollections'])->name('collections.user_collections');
Route::delete('/collections/{collection}', [CollectionController::class, 'destroy'])->middleware('auth')->name('collections.destroy');

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
