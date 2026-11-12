@extends('layouts.app')

@section('title', 'Profile Settings — ' . ($directorySettings['site_name'] ?? 'PromptImage'))
@section('eyebrow', 'YOUR CREATOR SPACE')
@section('toolbar-title', 'Profile settings')

@section('content')
<div class="workspace-page form-page profile-settings-page">
    <a class="back-link" href="{{ route('dashboard.index') }}"><span>&larr;</span> Back to creator dashboard</a>

    <div class="page-heading form-heading">
        <div>
            <span class="section-kicker">CREATOR PROFILE &middot; {{ strtoupper(auth()->user()->name) }}</span>
            <div style="display: flex; align-items: center; gap: 10px; margin-top: 4px; flex-wrap: wrap;">
                <h1>Profile settings</h1>
                @if(auth()->user()->is_verified)
                    <span title="Verified Creator" style="display: inline-flex; align-items: center; justify-content: center; width: 22px; height: 22px; border-radius: 50%; background: linear-gradient(135deg, #8b5cf6, #6366f1); color: #fff; font-size: 11px; font-weight: 700;">✓</span>
                @endif
            </div>
            <p>Customize your public creator persona, handle, avatar, and account credentials.</p>
        </div>
        <div class="page-heading-actions">
            <a href="{{ route('creators.show', auth()->user()) }}" target="_blank" class="outline-button" style="text-decoration: none; color: #6366f1; border-color: #c7d2fe; font-weight: 600;">
                View public profile ({{ auth()->user()->handle }}) <span>↗</span>
            </a>
        </div>
    </div>

    <!-- Friendly Guidance Banner -->
    <div class="profile-guidance-banner">
        <span class="guidance-icon">💡</span>
        <div class="guidance-text">
            <strong>Quick update:</strong> You do <em>not</em> need to enter your current password to save your profile details, handle, or bio. Simply update the fields below and click <strong>Save profile</strong>.
        </div>
    </div>

    <!-- Main Profile Edit Form -->
    <form class="prompt-form panel-card profile-form-card" method="POST" action="{{ route('dashboard.profile.update') }}" enctype="multipart/form-data">
        @csrf
        @method('PUT')

        <!-- Section 1: Public Identity & Avatar -->
        <div class="profile-form-section">
            <h3 class="profile-section-title">
                <span>👤</span> Public Creator Identity &amp; Handle
            </h3>

            <!-- Avatar Block -->
            <div class="profile-avatar-row">
                <div class="profile-avatar-preview">
                    @if (auth()->user()->avatar_url)
                        <img src="{{ auth()->user()->avatar_url }}" alt="{{ auth()->user()->name }}" class="profile-avatar-img">
                    @else
                        <div class="avatar profile-avatar-fallback">
                            {{ str(auth()->user()->name)->substr(0, 1)->upper() }}
                        </div>
                    @endif
                </div>

                <div class="profile-avatar-upload">
                    <label class="field-label">Profile Avatar</label>
                    <input type="file" name="avatar" accept="image/*" class="file-input">
                    <small>Upload PNG, JPG, or WebP (max 4MB). Square images look best.</small>
                    @error('avatar') <small style="color: #c62828;">{{ $message }}</small> @enderror
                </div>
            </div>

            <div class="form-grid">
                <label>
                    <span>Creator display name <span class="required-star">*</span></span>
                    <input type="text" name="name" required maxlength="80" value="{{ old('name', auth()->user()->name) }}" placeholder="e.g. Alex Rivera">
                    @error('name') <small style="color: #c62828;">{{ $message }}</small> @enderror
                </label>

                <label>
                    <span>Creator Handle / Username <span class="required-star">*</span></span>
                    <div class="username-input-group" style="display: flex; align-items: stretch; width: 100%; border: 1.5px solid #cbd5e1; border-radius: 8px; overflow: hidden; background: #fff; transition: border-color 0.15s, box-shadow 0.15s;">
                        <span class="username-prefix-addon" style="display: inline-flex; align-items: center; justify-content: center; padding: 0 14px; background: #f8fafc; color: #6366f1; font-weight: 700; font-size: 15px; border-right: 1.5px solid #e2e8f0; user-select: none;">@</span>
                        <input type="text" name="username" required maxlength="30" value="{{ old('username', auth()->user()->username) }}" placeholder="username" style="flex: 1; border: none !important; box-shadow: none !important; border-radius: 0 !important; padding: 0 12px !important; min-height: 40px; font-size: 14px; font-weight: 500; color: #1e293b; background: transparent !important;">
                    </div>
                    <small style="margin-top: 4px; display: block;">Public URL: <span class="username-preview" style="font-weight: 600;">{{ url('/creators/@') }}{{ old('username', auth()->user()->username) }}</span></small>
                    @error('username') <small style="color: #c62828;">{{ $message }}</small> @enderror
                </label>

                <label>
                    <span>Email address <span class="required-star">*</span></span>
                    <input type="email" name="email" required maxlength="255" value="{{ old('email', auth()->user()->email) }}">
                    @error('email') <small style="color: #c62828;">{{ $message }}</small> @enderror
                </label>

                <label>
                    <span>Website or Social Profile <span class="optional-label">Optional</span></span>
                    <input type="url" name="website" maxlength="255" value="{{ old('website', auth()->user()->website) }}" placeholder="https://x.com/username or portfolio URL">
                    <small>Shown on your public creator card so fans can explore your work.</small>
                    @error('website') <small style="color: #c62828;">{{ $message }}</small> @enderror
                </label>

                <label class="wide-field">
                    <span>Creator Bio &amp; Aesthetic <span class="optional-label">Optional</span></span>
                    <textarea name="bio" rows="3" maxlength="500" placeholder="Tell the community about your aesthetic styles, favorite models (Flux, Midjourney, Seedance), and creative focus…">{{ old('bio', auth()->user()->bio) }}</textarea>
                    <small>Displayed prominently on your public profile. Up to 500 characters.</small>
                    @error('bio') <small style="color: #c62828;">{{ $message }}</small> @enderror
                </label>
            </div>
        </div>

        <!-- Section 2: Password Update (Optional) -->
        <div class="profile-form-section profile-security-section">
            <h3 class="profile-section-title">
                <span>🔒</span> Security &amp; Password <span class="optional-label">Leave empty to keep unchanged</span>
            </h3>

            <div class="form-grid">
                <label class="wide-field">
                    <span>New Password</span>
                    <input type="password" name="password" minlength="8" placeholder="Enter new password (min. 8 characters) — leave blank to keep current password" autocomplete="new-password">
                    <small>No current password is required. Only fill this in if you want to set a new password.</small>
                    @error('password') <small style="color: #c62828;">{{ $message }}</small> @enderror
                </label>
            </div>
        </div>

        <div class="form-footer">
            <span class="footer-note"><i>&lowast;</i> Synchronized across your public creator page and directory prompts</span>
            <div class="footer-buttons">
                <a class="outline-button" href="{{ route('dashboard.index') }}">Cancel</a>
                <button class="primary-button" type="submit">Save profile <span>&rarr;</span></button>
            </div>
        </div>
    </form>
</div>
@endsection
