<?php

use App\Http\Controllers\AdminController;
use App\Http\Controllers\AuthController;
use App\Http\Controllers\DashboardController;
use App\Http\Controllers\PromptController;
use Illuminate\Support\Facades\Route;

Route::get('/', [PromptController::class, 'index'])->name('prompts.index');
Route::get('/sitemap.xml', [PromptController::class, 'sitemap'])->name('sitemap');
Route::get('/prompts/{prompt:slug}', [PromptController::class, 'show'])->name('prompts.show');
Route::post('/prompts/{prompt:slug}/favorite', [PromptController::class, 'toggleFavorite'])->middleware('auth')->name('prompts.favorite');

Route::middleware('guest')->group(function () {
    Route::get('/login', [AuthController::class, 'loginForm'])->name('login');
    Route::post('/login', [AuthController::class, 'login'])->middleware('throttle:10,1')->name('login.store');
    Route::get('/register', [AuthController::class, 'registerForm'])->name('register');
    Route::post('/register', [AuthController::class, 'register'])->middleware('throttle:10,1')->name('register.store');
});
Route::post('/logout', [AuthController::class, 'logout'])->middleware('auth')->name('logout');

Route::middleware('auth')->prefix('dashboard')->name('dashboard.')->group(function () {
    Route::get('/', [DashboardController::class, 'index'])->name('index');
    Route::get('/prompts/create', [DashboardController::class, 'create'])->name('prompts.create');
    Route::post('/prompts', [DashboardController::class, 'store'])->middleware('throttle:15,1')->name('prompts.store');
    Route::get('/prompts/{prompt}/edit', [DashboardController::class, 'edit'])->name('prompts.edit');
    Route::put('/prompts/{prompt}', [DashboardController::class, 'update'])->name('prompts.update');
    Route::delete('/prompts/{prompt}', [DashboardController::class, 'destroy'])->name('prompts.destroy');
});

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
    Route::put('/users/{user}', [AdminController::class, 'updateUser'])->name('users.update');
    Route::patch('/users/{user}/status', [AdminController::class, 'toggleUserStatus'])->name('users.status');
    Route::delete('/users/{user}', [AdminController::class, 'destroyUser'])->name('users.destroy');
    Route::patch('/users/{user}/role', [AdminController::class, 'updateUserRole'])->name('users.role');
    Route::get('/settings', [AdminController::class, 'settings'])->name('settings');
    Route::put('/settings', [AdminController::class, 'updateSettings'])->name('settings.update');
});
