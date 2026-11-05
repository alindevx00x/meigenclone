@extends('layouts.app')

@section('title', 'Edit Member: ' . $user->name)
@section('eyebrow', 'MEIGEN ADMIN')
@section('toolbar-title', 'Member management')

@section('content')
<div class="workspace-page form-page">
    <a class="back-link" href="{{ route('admin.users.index') }}"><span>&larr;</span> Back to member management</a>

    <div class="page-heading form-heading">
        <div>
            <span class="section-kicker">MEMBER ADMINISTRATION &middot; ACCOUNT #{{ $user->id }}</span>
            <div style="display: flex; align-items: center; gap: 10px; margin-top: 4px;">
                <h1>Edit member: {{ $user->name }}</h1>
                @if($user->is_verified)
                    <span title="Verified Creator" style="display: inline-flex; align-items: center; justify-content: center; width: 20px; height: 20px; border-radius: 50%; background: linear-gradient(135deg, #8b5cf6, #6366f1); color: #fff; font-size: 11px; font-weight: 700;">✓</span>
                @endif
            </div>
            <p>Manage account profile, verified creator status, studio credits, security, and permissions.</p>
        </div>
        <div style="display: flex; gap: 8px; align-items: center; flex-wrap: wrap;">
            <a href="{{ route('creators.show', $user) }}" target="_blank" class="outline-button" style="color: #6366f1; border-color: #c7d2fe; text-decoration: none;">
                View public profile <span>↗</span>
            </a>
            @if (! $user->is(auth()->user()))
                <form method="POST" action="{{ route('admin.users.status', $user) }}" style="display: inline;">
                    @csrf
                    @method('PATCH')
                    @if ($user->status === 'suspended')
                        <button type="submit" class="outline-button" style="color: #24915d; border-color: #c8e6c9;">Activate account</button>
                    @else
                        <button type="submit" class="outline-button" style="color: #c62828; border-color: #ffcdd2;" onclick="return confirm('Suspend {{ $user->name }}? They will not be able to log in until unsuspended.')">Suspend account</button>
                    @endif
                </form>
                <form method="POST" action="{{ route('admin.users.destroy', $user) }}" style="display: inline;" onsubmit="return confirm('Permanently remove {{ $user->name }}? This cannot be undone.')">
                    @csrf
                    @method('DELETE')
                    <button type="submit" class="outline-button" style="color: #b45a65;">Delete account</button>
                </form>
            @endif
        </div>
    </div>

    <!-- Quick Stats Bar (Fixed checkmark icon + added Followers) -->
    <div class="stats-row user-stats" style="margin-bottom: 24px; display: grid; grid-template-columns: repeat(auto-fit, minmax(180px, 1fr)); gap: 12px;">
        <div class="stat-card">
            <span class="stat-icon stat-violet">&lowast;</span>
            <span class="stat-label">Total prompts</span>
            <strong>{{ number_format($user->prompts_count) }}</strong>
            <small>Shared with community</small>
        </div>
        <div class="stat-card">
            <span class="stat-icon stat-pink">&hearts;</span>
            <span class="stat-label">Saved favorites</span>
            <strong>{{ number_format($user->favorites_count) }}</strong>
            <small>Personal collection</small>
        </div>
        <div class="stat-card">
            <span class="stat-icon stat-violet">👥</span>
            <span class="stat-label">Followers</span>
            <strong>{{ number_format($user->followers_count ?? 0) }}</strong>
            <small>Community members</small>
        </div>
        <div class="stat-card">
            <span class="stat-icon {{ $user->status === 'active' ? 'stat-green' : 'stat-amber' }}">
                {{ $user->status === 'active' ? '✓' : '◐' }}
            </span>
            <span class="stat-label">Account status</span>
            <strong>{{ ucfirst($user->status) }}</strong>
            <small>{{ $user->isAdmin() ? 'Administrator' : ($user->is_verified ? 'Verified Creator' : 'Standard Creator') }}</small>
        </div>
        <div class="stat-card">
            <span class="stat-icon stat-amber">&#9889;</span>
            <span class="stat-label">Studio credits</span>
            <strong>{{ number_format($user->credits) }}</strong>
            <small>Flux &amp; DALL-E balance</small>
        </div>
    </div>

    <!-- Member Edit Form Card -->
    <form class="prompt-form panel-card" method="POST" action="{{ route('admin.users.update', $user) }}" enctype="multipart/form-data">
        @csrf
        @method('PUT')

        <!-- Section 1: Profile & Identity -->
        <div style="margin-bottom: 22px; padding-bottom: 18px; border-bottom: 1px solid #eeeef2;">
            <h3 style="font-size: 16px; font-weight: 600; color: #1e1e24; margin: 0 0 14px; display: flex; align-items: center; gap: 8px;">
                <span>👤</span> Profile &amp; Creator Identity
            </h3>

            <div class="form-grid">
                <label>
                    Full name <span class="required-star">*</span>
                    <input type="text" name="name" required maxlength="80" value="{{ old('name', $user->name) }}">
                    @error('name') <small style="color: #c62828;">{{ $message }}</small> @enderror
                </label>

                <label>
                    Email address <span class="required-star">*</span>
                    <input type="email" name="email" required maxlength="255" value="{{ old('email', $user->email) }}">
                    @error('email') <small style="color: #c62828;">{{ $message }}</small> @enderror
                </label>

                <div class="wide-field" style="display: flex; align-items: center; gap: 14px; padding: 12px; background: #faf9fd; border: 1px solid #eae6f8; border-radius: 10px;">
                    <div style="flex-shrink: 0;">
                        @if ($user->avatar_url)
                            <img src="{{ $user->avatar_url }}" alt="{{ $user->name }}" style="width: 52px; height: 52px; border-radius: 50%; object-fit: cover; border: 2px solid #fff; box-shadow: 0 2px 8px rgba(0,0,0,0.08);">
                        @else
                            <div class="avatar" style="width: 52px; height: 52px; font-size: 20px;">{{ str($user->name)->substr(0, 1)->upper() }}</div>
                        @endif
                    </div>
                    <div style="flex: 1; min-width: 0;">
                        <label style="margin-bottom: 4px; font-weight: 600; font-size: 13px;">Avatar Image</label>
                        <div style="display: grid; grid-template-columns: 1fr 1fr; gap: 10px;">
                            <input type="file" name="avatar" accept="image/*" class="file-input" style="font-size: 11px;">
                            <input type="url" name="avatar_url" value="{{ old('avatar_url', $user->avatar_url) }}" placeholder="Or paste image URL (https://...)" style="font-size: 12px;">
                        </div>
                    </div>
                </div>

                <div class="wide-field" style="padding: 12px 14px; background: #fdfcff; border: 1px solid #ebdfff; border-radius: 10px;">
                    <label class="check-label" style="display: flex; align-items: center; gap: 10px; cursor: pointer; font-size: 13px; font-weight: 600; color: #3730a3;">
                        <input type="checkbox" name="is_verified" value="1" @checked(old('is_verified', $user->is_verified)) style="width: 17px; height: 17px; accent-color: #7c3aed;">
                        <span>Verified Creator Badge</span>
                        <span style="display: inline-flex; align-items: center; justify-content: center; width: 16px; height: 16px; border-radius: 50%; background: linear-gradient(135deg, #8b5cf6, #6366f1); color: #fff; font-size: 10px; font-weight: 700;">✓</span>
                    </label>
                    <small style="color: #6b7280; display: block; margin-top: 4px; margin-left: 27px;">When enabled, this creator displays the verification badge next to their name across prompt cards, carousels, and their public creator profile.</small>
                </div>

                <label class="wide-field">
                    Website or Social URL <span class="optional-label">Optional</span>
                    <input type="url" name="website" maxlength="255" value="{{ old('website', $user->website) }}" placeholder="https://x.com/username or portfolio URL">
                    @error('website') <small style="color: #c62828;">{{ $message }}</small> @enderror
                </label>

                <label class="wide-field">
                    Bio / Creator Headline <span class="optional-label">Optional</span>
                    <textarea name="bio" rows="3" maxlength="500" placeholder="Brief creator introduction, visual style specialization, tools used&hellip;">{{ old('bio', $user->bio) }}</textarea>
                    @error('bio') <small style="color: #c62828;">{{ $message }}</small> @enderror
                </label>
            </div>
        </div>

        <!-- Section 2: Studio & AI Credits -->
        <div style="margin-bottom: 22px; padding-bottom: 18px; border-bottom: 1px solid #eeeef2;">
            <h3 style="font-size: 16px; font-weight: 600; color: #1e1e24; margin: 0 0 14px; display: flex; align-items: center; gap: 8px;">
                <span>⚡</span> Studio Generation &amp; Custom API Keys
            </h3>

            <div class="form-grid">
                <label>
                    AI Generation Credits <span class="required-star">*</span>
                    <input type="number" min="0" max="100000" name="credits" value="{{ old('credits', $user->credits) }}" placeholder="25">
                    <small>Balance used for Flux and DALL-E image generation in Remix Studio.</small>
                    @error('credits') <small style="color: #c62828;">{{ $message }}</small> @enderror
                </label>

                <div></div>

                <label>
                    OpenAI API Key <span class="optional-label">Optional (Custom BYOK)</span>
                    <input type="password" name="openai_api_key" value="{{ old('openai_api_key', $user->openai_api_key) }}" placeholder="sk-..." autocomplete="off">
                    <small>User's custom OpenAI key for GPT image prompts.</small>
                </label>

                <label>
                    Replicate API Token <span class="optional-label">Optional (Custom BYOK)</span>
                    <input type="password" name="replicate_api_token" value="{{ old('replicate_api_token', $user->replicate_api_token) }}" placeholder="r8_..." autocomplete="off">
                    <small>User's custom Replicate token for Flux generations.</small>
                </label>
            </div>
        </div>

        <!-- Section 3: Access & Security -->
        <div style="margin-bottom: 10px;">
            <h3 style="font-size: 16px; font-weight: 600; color: #1e1e24; margin: 0 0 14px; display: flex; align-items: center; gap: 8px;">
                <span>🔒</span> Account Security &amp; Clearance
            </h3>

            <div class="form-grid">
                <label>
                    System Role <span class="required-star">*</span>
                    <select name="role" required @disabled($user->is(auth()->user()))>
                        <option value="user" @selected(old('role', $user->role) === 'user')>Creator (Standard Member)</option>
                        <option value="admin" @selected(old('role', $user->role) === 'admin')>Administrator (Full Studio Access)</option>
                    </select>
                    @if ($user->is(auth()->user()))
                        <input type="hidden" name="role" value="admin">
                        <small>You cannot remove your own administrator status.</small>
                    @else
                        <small>Administrators have clearance to manage settings, members, and models.</small>
                    @endif
                    @error('role') <small style="color: #c62828;">{{ $message }}</small> @enderror
                </label>

                <label>
                    Account Status <span class="required-star">*</span>
                    <select name="status" required @disabled($user->is(auth()->user()))>
                        <option value="active" @selected(old('status', $user->status) === 'active')>Active (Can log in &amp; publish)</option>
                        <option value="suspended" @selected(old('status', $user->status) === 'suspended')>Suspended (Blocked)</option>
                    </select>
                    @if ($user->is(auth()->user()))
                        <input type="hidden" name="status" value="active">
                        <small>You cannot suspend your own account.</small>
                    @else
                        <small>Suspended members are blocked from logging in or submitting prompts.</small>
                    @endif
                    @error('status') <small style="color: #c62828;">{{ $message }}</small> @enderror
                </label>

                <label class="wide-field">
                    Set New Password <span class="optional-label">Leave blank to keep current password unchanged</span>
                    <input type="password" name="password" minlength="8" placeholder="Enter new password (min. 8 characters) &mdash; leave empty to keep unchanged" autocomplete="new-password">
                    <small style="color: #6b7280;">No current password is required. Only fill this if you want to reset this user's password.</small>
                    @error('password') <small style="color: #c62828;">{{ $message }}</small> @enderror
                </label>
            </div>
        </div>

        <div class="form-footer">
            <span style="color: #555562;"><i>&lowast;</i> Member registered {{ $user->created_at->format('M j, Y') }} ({{ $user->created_at->diffForHumans() }})</span>
            <div>
                <a class="outline-button" href="{{ route('admin.users.index') }}">Cancel</a>
                <button class="primary-button" type="submit" name="action" value="save">Save changes <span>&rarr;</span></button>
                <button class="primary-button" type="submit" name="action" value="save_and_exit" style="background: #35343d;">Save &amp; Return <span>&rarr;</span></button>
            </div>
        </div>
    </form>

    <!-- Submissions by this Creator (Matching dashboard#favorites prompt grid) -->
    <section class="workspace-section" id="favorites" style="margin-top: 36px;">
        <div class="section-heading">
            <div>
                <span class="section-kicker">SUBMISSIONS BY {{ strtoupper($user->name) }}</span>
                <h2>Contributed prompts <span class="heading-count">{{ $user->prompts_count }}</span></h2>
            </div>
            @if ($user->prompts_count > 0)
                <a class="subtle-link" href="{{ route('creators.show', $user) }}">View full creator profile <span>&rarr;</span></a>
            @endif
        </div>


        @if ($recentPrompts->isEmpty())
            <div class="empty-panel">
                <span class="empty-icon">&lowast;</span>
                <div>
                    <strong>No submissions yet.</strong>
                    <p>This community member has not contributed any prompts to the directory.</p>
                </div>
            </div>
        @else
            <div class="prompt-grid dashboard-favorites">
                @foreach ($recentPrompts as $prompt)
                    @include('partials.prompt-card', ['prompt' => $prompt])
                @endforeach
            </div>
        @endif
    </section>
</div>
@endsection
