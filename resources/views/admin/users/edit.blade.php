@extends('layouts.app')

@section('title', 'Edit Member: ' . $user->name . ' — Admin Studio')
@section('eyebrow', 'MEIGEN ADMIN')
@section('toolbar-title', 'Member management')

@section('content')
<div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-8 sm:py-10">
    <!-- Breadcrumb & Back Navigation -->
    <div class="mb-6 flex flex-wrap items-center justify-between gap-4">
        <a href="{{ route('admin.users.index') }}" class="inline-flex items-center gap-2 text-xs font-semibold text-zinc-400 hover:text-white transition group">
            <span class="group-hover:-translate-x-0.5 transition-transform">&larr;</span> Back to Members Directory
        </a>
        <div class="flex items-center gap-2">
            <span class="text-xs text-zinc-500 font-mono">User ID: #{{ $user->id }}</span>
            <span class="text-zinc-700">&bull;</span>
            <span class="text-xs text-zinc-500">Joined {{ $user->created_at->format('M j, Y') }}</span>
        </div>
    </div>

    <!-- Page Header Card -->
    <div class="flex flex-col md:flex-row md:items-center justify-between gap-6 mb-8">
        <div class="flex items-start gap-4">
            <div class="w-16 h-16 rounded-2xl bg-gradient-to-tr from-violet-600 to-indigo-600 flex items-center justify-center text-white text-2xl font-bold flex-shrink-0 shadow-lg shadow-violet-600/20 border border-violet-400/20">
                {{ str($user->name)->substr(0, 1)->upper() }}
            </div>
            <div>
                <div class="flex flex-wrap items-center gap-2 mb-1.5">
                    <h1 class="text-2xl sm:text-3xl font-extrabold text-white tracking-tight">{{ $user->name }}</h1>
                    @if ($user->is(auth()->user()))
                        <span class="px-2.5 py-0.5 rounded-full bg-violet-500/10 border border-violet-500/30 text-violet-300 text-xs font-semibold">You</span>
                    @endif
                    @if ($user->isAdmin())
                        <span class="px-2.5 py-0.5 rounded-full bg-indigo-500/10 border border-indigo-500/30 text-indigo-400 text-xs font-semibold">Administrator</span>
                    @else
                        <span class="px-2.5 py-0.5 rounded-full bg-zinc-800 text-zinc-300 text-xs font-semibold">Creator</span>
                    @endif
                    @if ($user->status === 'suspended')
                        <span class="px-2.5 py-0.5 rounded-full bg-rose-500/10 border border-rose-500/30 text-rose-400 text-xs font-semibold flex items-center gap-1.5">
                            <span class="w-1.5 h-1.5 rounded-full bg-rose-500"></span> Suspended
                        </span>
                    @else
                        <span class="px-2.5 py-0.5 rounded-full bg-emerald-500/10 border border-emerald-500/30 text-emerald-400 text-xs font-semibold flex items-center gap-1.5">
                            <span class="w-1.5 h-1.5 rounded-full bg-emerald-500"></span> Active
                        </span>
                    @endif
                </div>
                <p class="text-sm text-zinc-400">{{ $user->email }} @if($user->website) &bull; <a href="{{ $user->website }}" target="_blank" rel="noopener" class="text-violet-400 hover:text-violet-300 transition">{{ $user->website }} &nearr;</a> @endif</p>
            </div>
        </div>

        <!-- Quick Header Action Buttons -->
        <div class="flex flex-wrap items-center gap-2.5">
            <a href="{{ route('prompts.index', ['q' => $user->name]) }}" class="px-4 py-2.5 rounded-2xl bg-zinc-900 border border-zinc-800 hover:border-zinc-700 text-zinc-200 hover:text-white text-xs font-semibold transition flex items-center gap-2 shadow-sm">
                <span>✦</span> View Gallery Submissions
            </a>
            @if (! $user->is(auth()->user()))
                <form method="POST" action="{{ route('admin.users.status', $user) }}" class="inline">
                    @csrf
                    @method('PATCH')
                    @if ($user->status === 'suspended')
                        <button type="submit" class="px-4 py-2.5 rounded-2xl bg-emerald-950/40 border border-emerald-800/60 hover:bg-emerald-900/50 text-emerald-300 text-xs font-semibold transition flex items-center gap-1.5">
                            <span>✓</span> Unsuspend Member
                        </button>
                    @else
                        <button type="submit" onclick="return confirm('Suspend {{ $user->name }}? They will be unable to log in until unsuspended.')" class="px-4 py-2.5 rounded-2xl bg-amber-950/40 border border-amber-800/60 hover:bg-amber-900/50 text-amber-300 text-xs font-semibold transition flex items-center gap-1.5">
                            <span>◷</span> Suspend Account
                        </button>
                    @endif
                </form>
            @endif
        </div>
    </div>

    <!-- Activity Stats Row (Matching Studio & Dashboard) -->
    <div class="grid grid-cols-2 lg:grid-cols-4 gap-4 mb-8">
        <div class="bg-zinc-900 border border-zinc-800/80 rounded-2xl p-5 shadow-lg relative overflow-hidden">
            <div class="flex items-center justify-between mb-3">
                <span class="text-xs font-semibold text-zinc-400">Prompts Submitted</span>
                <span class="w-8 h-8 rounded-xl bg-violet-500/10 text-violet-400 flex items-center justify-center text-sm font-bold">✦</span>
            </div>
            <div class="text-2xl font-extrabold text-white">{{ number_format($user->prompts_count) }}</div>
            <p class="text-[11px] text-zinc-500 mt-1">Total gallery contributions</p>
        </div>

        <div class="bg-zinc-900 border border-zinc-800/80 rounded-2xl p-5 shadow-lg relative overflow-hidden">
            <div class="flex items-center justify-between mb-3">
                <span class="text-xs font-semibold text-zinc-400">Saved Favorites</span>
                <span class="w-8 h-8 rounded-xl bg-rose-500/10 text-rose-400 flex items-center justify-center text-sm font-bold">♡</span>
            </div>
            <div class="text-2xl font-extrabold text-white">{{ number_format($user->favorites_count) }}</div>
            <p class="text-[11px] text-zinc-500 mt-1">Bookmarked inspiration</p>
        </div>

        <div class="bg-zinc-900 border border-zinc-800/80 rounded-2xl p-5 shadow-lg relative overflow-hidden">
            <div class="flex items-center justify-between mb-3">
                <span class="text-xs font-semibold text-zinc-400">Moodboards / Packs</span>
                <span class="w-8 h-8 rounded-xl bg-amber-500/10 text-amber-400 flex items-center justify-center text-sm font-bold">📦</span>
            </div>
            <div class="text-2xl font-extrabold text-white">{{ number_format($user->collections_count + $user->prompt_packs_count) }}</div>
            <p class="text-[11px] text-zinc-500 mt-1">Curated boards & bundles</p>
        </div>

        <div class="bg-zinc-900 border border-zinc-800/80 rounded-2xl p-5 shadow-lg relative overflow-hidden">
            <div class="flex items-center justify-between mb-3">
                <span class="text-xs font-semibold text-zinc-400">AI Generation Credits</span>
                <span class="w-8 h-8 rounded-xl bg-emerald-500/10 text-emerald-400 flex items-center justify-center text-sm font-bold">⚡</span>
            </div>
            <div class="text-2xl font-extrabold text-white">{{ number_format($user->credits) }}</div>
            <p class="text-[11px] text-zinc-500 mt-1">Balance for Flux & DALL-E</p>
        </div>
    </div>

    <!-- Main Workspace Studio Grid -->
    <div class="grid grid-cols-1 lg:grid-cols-12 gap-8 items-start">
        <!-- Left: Edit Member Form (8 Cols) -->
        <div class="lg:col-span-8 space-y-6">
            <form method="POST" action="{{ route('admin.users.update', $user) }}" class="bg-zinc-900 border border-zinc-800/80 rounded-3xl p-6 sm:p-8 shadow-2xl space-y-6">
                @csrf
                @method('PUT')

                <!-- Section: Account Identity -->
                <div>
                    <h2 class="text-base font-bold text-white mb-1 flex items-center gap-2">
                        <span>👤</span> Profile Identity & Credentials
                    </h2>
                    <p class="text-xs text-zinc-400 mb-4">Core contact details displayed in community creator credits.</p>

                    <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                        <div>
                            <label class="block text-xs font-semibold text-zinc-300 mb-1.5">
                                Full Name <span class="text-violet-400">*</span>
                            </label>
                            <input type="text" name="name" required maxlength="80" value="{{ old('name', $user->name) }}" class="w-full bg-zinc-950 border border-zinc-800 rounded-2xl px-4 py-2.5 text-sm text-white placeholder-zinc-500 focus:outline-none focus:ring-2 focus:ring-violet-500/50 focus:border-violet-500 transition">
                            @error('name') <p class="text-rose-400 text-xs mt-1">{{ $message }}</p> @enderror
                        </div>

                        <div>
                            <label class="block text-xs font-semibold text-zinc-300 mb-1.5">
                                Email Address <span class="text-violet-400">*</span>
                            </label>
                            <input type="email" name="email" required maxlength="255" value="{{ old('email', $user->email) }}" class="w-full bg-zinc-950 border border-zinc-800 rounded-2xl px-4 py-2.5 text-sm text-white placeholder-zinc-500 focus:outline-none focus:ring-2 focus:ring-violet-500/50 focus:border-violet-500 transition">
                            @error('email') <p class="text-rose-400 text-xs mt-1">{{ $message }}</p> @enderror
                        </div>
                    </div>
                </div>

                <!-- Section: Roles, Privileges & Credits -->
                <div class="pt-6 border-t border-zinc-800/80">
                    <h2 class="text-base font-bold text-white mb-1 flex items-center gap-2">
                        <span>🛡️</span> Roles, Permissions & AI Credits
                    </h2>
                    <p class="text-xs text-zinc-400 mb-4">Control studio dashboard capabilities, administrative clearance, and generation allowances.</p>

                    <div class="grid grid-cols-1 sm:grid-cols-3 gap-4">
                        <div>
                            <label class="block text-xs font-semibold text-zinc-300 mb-1.5">
                                System Role <span class="text-violet-400">*</span>
                            </label>
                            <select name="role" required class="w-full bg-zinc-950 border border-zinc-800 rounded-2xl px-3.5 py-2.5 text-sm text-white focus:outline-none focus:ring-2 focus:ring-violet-500/50 focus:border-violet-500 transition" @disabled($user->is(auth()->user()))>
                                <option value="user" @selected(old('role', $user->role) === 'user')>Creator (Standard)</option>
                                <option value="admin" @selected(old('role', $user->role) === 'admin')>Administrator (Full Access)</option>
                            </select>
                            @if ($user->is(auth()->user()))
                                <input type="hidden" name="role" value="admin">
                                <p class="text-[11px] text-zinc-500 mt-1">You cannot remove your own admin status.</p>
                            @else
                                <p class="text-[11px] text-zinc-500 mt-1">Admins have full access to prompts studio.</p>
                            @endif
                            @error('role') <p class="text-rose-400 text-xs mt-1">{{ $message }}</p> @enderror
                        </div>

                        <div>
                            <label class="block text-xs font-semibold text-zinc-300 mb-1.5">
                                Account Status <span class="text-violet-400">*</span>
                            </label>
                            <select name="status" required class="w-full bg-zinc-950 border border-zinc-800 rounded-2xl px-3.5 py-2.5 text-sm text-white focus:outline-none focus:ring-2 focus:ring-violet-500/50 focus:border-violet-500 transition" @disabled($user->is(auth()->user()))>
                                <option value="active" @selected(old('status', $user->status) === 'active')>Active (Can login)</option>
                                <option value="suspended" @selected(old('status', $user->status) === 'suspended')>Suspended (Blocked)</option>
                            </select>
                            @if ($user->is(auth()->user()))
                                <input type="hidden" name="status" value="active">
                                <p class="text-[11px] text-zinc-500 mt-1">You cannot suspend your own account.</p>
                            @else
                                <p class="text-[11px] text-zinc-500 mt-1">Suspended members cannot log in.</p>
                            @endif
                            @error('status') <p class="text-rose-400 text-xs mt-1">{{ $message }}</p> @enderror
                        </div>

                        <div>
                            <label class="block text-xs font-semibold text-zinc-300 mb-1.5">
                                Generation Credits <span class="text-violet-400">*</span>
                            </label>
                            <input type="number" min="0" max="100000" name="credits" value="{{ old('credits', $user->credits) }}" class="w-full bg-zinc-950 border border-zinc-800 rounded-2xl px-3.5 py-2.5 text-sm text-white focus:outline-none focus:ring-2 focus:ring-violet-500/50 focus:border-violet-500 transition">
                            <p class="text-[11px] text-zinc-500 mt-1">1 credit = 1 Flux / DALL-E generation.</p>
                            @error('credits') <p class="text-rose-400 text-xs mt-1">{{ $message }}</p> @enderror
                        </div>
                    </div>
                </div>

                <!-- Section: Creator Profile & Social Details -->
                <div class="pt-6 border-t border-zinc-800/80">
                    <h2 class="text-base font-bold text-white mb-1 flex items-center gap-2">
                        <span>🌐</span> Creator Profile &amp; Bio
                    </h2>
                    <p class="text-xs text-zinc-400 mb-4">Public showcase and author links rendered on prompt detail pages.</p>

                    <div class="space-y-4">
                        <div>
                            <label class="block text-xs font-semibold text-zinc-300 mb-1.5">
                                Portfolio or Social URL <span class="text-zinc-500 font-normal">(Optional)</span>
                            </label>
                            <input type="url" name="website" maxlength="255" value="{{ old('website', $user->website) }}" placeholder="https://x.com/creator or https://instagram.com/..." class="w-full bg-zinc-950 border border-zinc-800 rounded-2xl px-4 py-2.5 text-sm text-white placeholder-zinc-500 focus:outline-none focus:ring-2 focus:ring-violet-500/50 focus:border-violet-500 transition">
                            @error('website') <p class="text-rose-400 text-xs mt-1">{{ $message }}</p> @enderror
                        </div>

                        <div>
                            <label class="block text-xs font-semibold text-zinc-300 mb-1.5">
                                Bio / Creator Headline <span class="text-zinc-500 font-normal">(Optional)</span>
                            </label>
                            <textarea name="bio" rows="3" maxlength="500" placeholder="Brief creator introduction, specialization (e.g. Midjourney photorealism, Anime prompt engineer)..." class="w-full bg-zinc-950 border border-zinc-800 rounded-2xl px-4 py-2.5 text-sm text-white placeholder-zinc-500 focus:outline-none focus:ring-2 focus:ring-violet-500/50 focus:border-violet-500 transition resize-none">{{ old('bio', $user->bio) }}</textarea>
                            @error('bio') <p class="text-rose-400 text-xs mt-1">{{ $message }}</p> @enderror
                        </div>
                    </div>
                </div>

                <!-- Section: Security & Password Reset -->
                <div class="pt-6 border-t border-zinc-800/80">
                    <h2 class="text-base font-bold text-white mb-1 flex items-center gap-2">
                        <span>🔒</span> Password &amp; Account Security
                    </h2>
                    <p class="text-xs text-zinc-400 mb-4">Direct password override for administrative support or account recovery.</p>

                    <div>
                        <label class="block text-xs font-semibold text-zinc-300 mb-1.5">
                            Set New Password <span class="text-zinc-500 font-normal">(Leave blank to keep existing password unchanged)</span>
                        </label>
                        <input type="password" name="password" minlength="8" placeholder="Enter new password (minimum 8 characters)" class="w-full bg-zinc-950 border border-zinc-800 rounded-2xl px-4 py-2.5 text-sm text-white placeholder-zinc-500 focus:outline-none focus:ring-2 focus:ring-violet-500/50 focus:border-violet-500 transition">
                        @error('password') <p class="text-rose-400 text-xs mt-1">{{ $message }}</p> @enderror
                    </div>
                </div>

                <!-- Form Action Buttons -->
                <div class="pt-6 border-t border-zinc-800/80 flex flex-wrap items-center justify-between gap-4">
                    <a href="{{ route('admin.users.index') }}" class="px-5 py-2.5 rounded-2xl bg-zinc-950 border border-zinc-800 hover:border-zinc-700 text-zinc-300 hover:text-white text-xs font-semibold transition">
                        &larr; Cancel
                    </a>

                    <div class="flex items-center gap-3">
                        <button type="submit" name="action" value="save" class="px-6 py-2.5 rounded-2xl bg-gradient-to-r from-violet-600 to-indigo-600 hover:from-violet-500 hover:to-indigo-500 text-white text-xs font-bold transition flex items-center gap-2 shadow-lg shadow-violet-600/20">
                            Save Changes <span>&rarr;</span>
                        </button>
                        <button type="submit" name="action" value="save_and_exit" class="px-5 py-2.5 rounded-2xl bg-zinc-800 hover:bg-zinc-700 text-white text-xs font-semibold transition">
                            Save &amp; Return to List
                        </button>
                    </div>
                </div>
            </form>
        </div>

        <!-- Right: Member Activity & Insight (4 Cols) -->
        <div class="lg:col-span-4 space-y-6">
            <!-- Account Summary Card -->
            <div class="bg-zinc-900 border border-zinc-800/80 rounded-3xl p-6 shadow-xl space-y-4">
                <h3 class="text-sm font-bold text-white flex items-center gap-2">
                    <span>📋</span> Account Overview
                </h3>

                <dl class="space-y-3 text-xs">
                    <div class="flex items-center justify-between py-1.5 border-b border-zinc-800/60">
                        <dt class="text-zinc-400">Account ID</dt>
                        <dd class="text-white font-mono font-semibold">#{{ $user->id }}</dd>
                    </div>
                    <div class="flex items-center justify-between py-1.5 border-b border-zinc-800/60">
                        <dt class="text-zinc-400">Member Since</dt>
                        <dd class="text-white font-medium">{{ $user->created_at->format('M j, Y') }} <span class="text-zinc-500">({{ $user->created_at->diffForHumans() }})</span></dd>
                    </div>
                    <div class="flex items-center justify-between py-1.5 border-b border-zinc-800/60">
                        <dt class="text-zinc-400">Email Status</dt>
                        <dd>
                            @if ($user->email_verified_at)
                                <span class="text-emerald-400 font-semibold flex items-center gap-1">✓ Verified</span>
                            @else
                                <span class="text-zinc-500">Unverified</span>
                            @endif
                        </dd>
                    </div>
                    <div class="flex items-center justify-between py-1.5">
                        <dt class="text-zinc-400">Last Updated</dt>
                        <dd class="text-zinc-300 font-medium">{{ $user->updated_at->diffForHumans() }}</dd>
                    </div>
                </dl>
            </div>

            <!-- Recent Submissions by this Creator -->
            <div class="bg-zinc-900 border border-zinc-800/80 rounded-3xl p-6 shadow-xl space-y-4">
                <div class="flex items-center justify-between">
                    <h3 class="text-sm font-bold text-white flex items-center gap-2">
                        <span>✦</span> Recent Prompts ({{ $user->prompts_count }})
                    </h3>
                    @if ($user->prompts_count > 0)
                        <a href="{{ route('prompts.index', ['q' => $user->name]) }}" class="text-[11px] text-violet-400 hover:text-violet-300 font-medium">View all &rarr;</a>
                    @endif
                </div>

                @if ($recentPrompts->isEmpty())
                    <div class="py-8 text-center text-xs text-zinc-500 border border-dashed border-zinc-800 rounded-2xl">
                        This creator has not submitted any prompts yet.
                    </div>
                @else
                    <div class="space-y-2.5">
                        @foreach ($recentPrompts as $prompt)
                            <div class="flex items-center gap-3 p-2.5 rounded-2xl bg-zinc-950/80 border border-zinc-800/60 hover:border-zinc-700 transition group">
                                <div class="w-11 h-11 rounded-xl bg-zinc-900 overflow-hidden flex-shrink-0 border border-zinc-800 flex items-center justify-center">
                                    @if ($prompt->cover_url)
                                        <img src="{{ $prompt->cover_url }}" alt="{{ $prompt->title }}" class="w-full h-full object-cover">
                                    @else
                                        <span class="text-xs text-violet-400 font-bold">{{ $prompt->category?->icon ?? '✦' }}</span>
                                    @endif
                                </div>
                                <div class="flex-1 min-w-0">
                                    <div class="text-xs font-semibold text-white group-hover:text-violet-400 transition truncate">
                                        {{ $prompt->title }}
                                    </div>
                                    <div class="flex items-center gap-2 mt-0.5 text-[10px] text-zinc-500">
                                        <span>{{ $prompt->category?->name }}</span>
                                        <span>&bull;</span>
                                        <span>{{ $prompt->model }}</span>
                                    </div>
                                </div>
                                <div class="flex-shrink-0">
                                    <a href="{{ route('admin.prompts.edit', $prompt) }}" class="text-[11px] text-zinc-400 hover:text-white px-2 py-1 rounded-lg bg-zinc-800 hover:bg-zinc-700 transition font-medium">
                                        Edit
                                    </a>
                                </div>
                            </div>
                        @endforeach
                    </div>
                @endif
            </div>

            <!-- Danger Zone (If not self) -->
            @if (! $user->is(auth()->user()))
                <div class="bg-rose-950/20 border border-rose-900/40 rounded-3xl p-6 shadow-xl space-y-3">
                    <h3 class="text-sm font-bold text-rose-300 flex items-center gap-2">
                        <span>⚠</span> Danger Zone
                    </h3>
                    <p class="text-xs text-zinc-400">
                        Permanently deleting this account removes all their associated profile details. Existing prompts may be reassigned.
                    </p>
                    <form method="POST" action="{{ route('admin.users.destroy', $user) }}" onsubmit="return confirm('Permanently remove member {{ $user->name }}? This action cannot be undone.')">
                        @csrf
                        @method('DELETE')
                        <button type="submit" class="w-full py-2.5 rounded-xl bg-rose-600/20 hover:bg-rose-600 border border-rose-500/40 text-rose-300 hover:text-white text-xs font-bold transition flex items-center justify-center gap-2">
                            <span>🗑</span> Delete This Member Account
                        </button>
                    </form>
                </div>
            @endif
        </div>
    </div>
</div>
@endsection
