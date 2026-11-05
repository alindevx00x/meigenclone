@extends('layouts.app')

@section('title', 'Profile Settings — MeiGen')
@section('eyebrow', 'YOUR CREATOR SPACE')
@section('toolbar-title', 'Profile settings')

@section('content')
<div class="workspace-page form-page" style="max-width: 980px; margin: 0 auto; padding: 24px 20px 60px;">
    <a class="back-link" href="{{ route('dashboard.index') }}"><span>&larr;</span> Back to creator dashboard</a>

    <div class="page-heading form-heading" style="margin-bottom: 24px;">
        <div>
            <span class="section-kicker">CREATOR PROFILE &middot; {{ strtoupper(auth()->user()->name) }}</span>
            <div style="display: flex; align-items: center; gap: 10px; margin-top: 4px;">
                <h1>Profile settings</h1>
                @if(auth()->user()->is_verified)
                    <span title="Verified Creator" style="display: inline-flex; align-items: center; justify-content: center; width: 22px; height: 22px; border-radius: 50%; background: linear-gradient(135deg, #8b5cf6, #6366f1); color: #fff; font-size: 11px; font-weight: 700;">✓</span>
                @endif
            </div>
            <p>Customize your public creator persona, handle, avatar, monetization, and studio keys.</p>
        </div>
        <div style="display: flex; gap: 8px; align-items: center;">
            <a href="{{ route('creators.show', auth()->user()) }}" target="_blank" class="outline-button" style="text-decoration: none; color: #6366f1; border-color: #c7d2fe; font-weight: 600;">
                View public profile ({{ auth()->user()->handle }}) <span>↗</span>
            </a>
        </div>
    </div>

    <!-- Friendly Guidance Banner -->
    <div style="display: flex; align-items: center; gap: 12px; padding: 14px 18px; border-radius: 12px; background: #f5f3ff; border: 1px solid #ddd6fe; color: #4338ca; margin-bottom: 24px;">
        <span style="font-size: 20px;">💡</span>
        <div style="font-size: 13px; line-height: 1.5;">
            <strong>Quick update:</strong> You do <em>not</em> need to enter your current password to save your profile details, handle, or bio. Simply update the fields below and click <strong>Save profile</strong>.
        </div>
    </div>

    <!-- Main Profile Edit Form -->
    <form class="prompt-form panel-card" method="POST" action="{{ route('dashboard.profile.update') }}" enctype="multipart/form-data" style="padding: 28px; border-radius: 16px; background: #ffffff; border: 1px solid #ebebf0; box-shadow: 0 4px 20px rgba(0,0,0,0.03);">
        @csrf
        @method('PUT')

        <!-- Section 1: Public Identity & Avatar -->
        <div style="margin-bottom: 24px; padding-bottom: 22px; border-bottom: 1px solid #eeeef2;">
            <h3 style="font-size: 16px; font-weight: 600; color: #1e1e24; margin: 0 0 16px; display: flex; align-items: center; gap: 8px;">
                <span>👤</span> Public Creator Identity &amp; Handle
            </h3>

            <!-- Avatar Block -->
            <div style="display: flex; align-items: center; gap: 18px; padding: 16px; background: #faf9fd; border: 1px solid #eae6f8; border-radius: 12px; margin-bottom: 18px;">
                <div style="flex-shrink: 0;">
                    @if (auth()->user()->avatar_url)
                        <img src="{{ auth()->user()->avatar_url }}" alt="{{ auth()->user()->name }}" style="width: 64px; height: 64px; border-radius: 50%; object-fit: cover; border: 3px solid #fff; box-shadow: 0 4px 12px rgba(121, 88, 238, 0.15);">
                    @else
                        <div class="avatar" style="width: 64px; height: 64px; font-size: 24px; font-weight: 700; border-radius: 50%; background: linear-gradient(135deg, #ede9ff, #f3efff); color: #654bd1; border: 3px solid #fff; box-shadow: 0 4px 12px rgba(121, 88, 238, 0.12);">
                            {{ str(auth()->user()->name)->substr(0, 1)->upper() }}
                        </div>
                    @endif
                </div>

                <div style="flex: 1; min-width: 0;">
                    <label style="display: block; font-weight: 600; font-size: 13px; color: #3f3f46; margin-bottom: 6px;">Profile Avatar</label>
                    <div style="display: grid; grid-template-columns: 1fr 1fr; gap: 10px;">
                        <div>
                            <input type="file" name="avatar" accept="image/*" class="file-input" style="font-size: 11px;">
                            <small style="color: #71717a; font-size: 11px; margin-top: 3px; display: block;">Upload PNG, JPG, or WebP (max 4MB)</small>
                        </div>
                        <div>
                            <input type="url" name="avatar_url" value="{{ old('avatar_url', auth()->user()->avatar_url) }}" placeholder="Or paste image URL (https://...)" style="font-size: 12px;">
                            <small style="color: #71717a; font-size: 11px; margin-top: 3px; display: block;">Direct URL to your avatar image</small>
                        </div>
                    </div>
                </div>
            </div>

            <div class="form-grid">
                <label>
                    Creator display name <span class="required-star">*</span>
                    <input type="text" name="name" required maxlength="80" value="{{ old('name', auth()->user()->name) }}" placeholder="e.g. Alex Rivera">
                    @error('name') <small style="color: #c62828;">{{ $message }}</small> @enderror
                </label>

                <label>
                    Creator Handle / Username <span class="required-star">*</span>
                    <div style="position: relative; display: flex; align-items: center;">
                        <span style="position: absolute; left: 12px; color: #7c3aed; font-weight: 700; font-size: 14px;">@</span>
                        <input type="text" name="username" required maxlength="30" value="{{ old('username', auth()->user()->username) }}" style="padding-left: 30px; font-weight: 600; color: #3730a3;" placeholder="username">
                    </div>
                    <small style="color: #71717a; font-size: 11.5px;">Public URL: <span style="font-family: monospace; color: #6d52d9;">{{ url('/creators/@') }}{{ old('username', auth()->user()->username) }}</span></small>
                    @error('username') <small style="color: #c62828;">{{ $message }}</small> @enderror
                </label>

                <label>
                    Email address <span class="required-star">*</span>
                    <input type="email" name="email" required maxlength="255" value="{{ old('email', auth()->user()->email) }}">
                    @error('email') <small style="color: #c62828;">{{ $message }}</small> @enderror
                </label>

                <label>
                    Website or Social Profile <span class="optional-label">Optional</span>
                    <input type="url" name="website" maxlength="255" value="{{ old('website', auth()->user()->website) }}" placeholder="https://x.com/username or your portfolio URL">
                    <small style="color: #71717a; font-size: 11.5px;">Shown on your public creator card so fans can explore your work.</small>
                    @error('website') <small style="color: #c62828;">{{ $message }}</small> @enderror
                </label>

                <label class="wide-field">
                    Creator Bio &amp; Aesthetic <span class="optional-label">Optional</span>
                    <textarea name="bio" rows="3" maxlength="500" placeholder="Tell the community about your aesthetic styles, favorite models (Flux, Midjourney, Seedance), and creative focus&hellip;">{{ old('bio', auth()->user()->bio) }}</textarea>
                    <small style="color: #71717a; font-size: 11.5px;">Displayed prominently on your public profile. Up to 500 characters.</small>
                    @error('bio') <small style="color: #c62828;">{{ $message }}</small> @enderror
                </label>
            </div>
        </div>

        <!-- Section 2: Creator Monetization & Tipping -->
        <div style="margin-bottom: 24px; padding-bottom: 22px; border-bottom: 1px solid #eeeef2;">
            <div style="display: flex; align-items: center; justify-content: space-between; margin-bottom: 14px; flex-wrap: wrap; gap: 10px;">
                <h3 style="font-size: 16px; font-weight: 600; color: #1e1e24; margin: 0; display: flex; align-items: center; gap: 8px;">
                    <span>☕</span> Creator Tipping &amp; Monetization
                </h3>
                <span style="font-size: 12px; color: #b45309; background: #fffbeb; border: 1px solid #fde68a; padding: 3px 12px; border-radius: 99px; font-weight: 700;">
                    Tips Earned: ${{ number_format((float) auth()->user()->tips_earned_total, 2) }}
                </span>
            </div>
            <p style="font-size: 12.5px; color: #64646e; margin: 0 0 16px; line-height: 1.5;">
                Receive tips from community members who love your prompts. You can accept direct platform tips, or link your external Buy Me a Coffee / Ko-fi / Stripe Payment Link.
            </p>

            <div class="form-grid">
                <label class="wide-field" style="display: flex; align-items: center; gap: 10px; cursor: pointer; font-size: 13px; font-weight: 600; color: #1e1e24;">
                    <input type="checkbox" name="monetization_enabled" value="1" @checked(old('monetization_enabled', auth()->user()->monetization_enabled)) style="width: 17px; height: 17px; accent-color: #7c3aed;">
                    <span>Enable "Tip Creator" button on your public profile and prompt cards</span>
                </label>

                <label>
                    External Tip / Payment Link <span class="optional-label">Optional</span>
                    <input type="url" name="tip_link" value="{{ old('tip_link', auth()->user()->tip_link) }}" placeholder="https://buymeacoffee.com/username or Stripe Payment Link">
                    <small style="color: #71717a; font-size: 11.5px;">Buy Me A Coffee, Ko-fi, Stripe Payment link, or PayPal.me URL.</small>
                    @error('tip_link') <small style="color: #c62828;">{{ $message }}</small> @enderror
                </label>

                <label>
                    Creator Tip Goal or Note <span class="optional-label">Optional</span>
                    <input type="text" name="tip_goal" maxlength="255" value="{{ old('tip_goal', auth()->user()->tip_goal) }}" placeholder="e.g. Funding Midjourney v7 compute & custom FLUX LoRAs">
                    <small style="color: #71717a; font-size: 11.5px;">Shown to supporters in your tipping modal to motivate contributions.</small>
                    @error('tip_goal') <small style="color: #c62828;">{{ $message }}</small> @enderror
                </label>
            </div>
        </div>

        <!-- Section 3: AI Studio Custom API Keys (Optional BYOK) -->
        <div style="margin-bottom: 24px; padding-bottom: 22px; border-bottom: 1px solid #eeeef2;">
            <div style="display: flex; align-items: center; justify-content: space-between; margin-bottom: 14px; flex-wrap: wrap; gap: 10px;">
                <h3 style="font-size: 16px; font-weight: 600; color: #1e1e24; margin: 0; display: flex; align-items: center; gap: 8px;">
                    <span>⚡</span> Remix Studio API Keys <span class="optional-label" style="font-size: 11px;">Optional</span>
                </h3>
                <span style="font-size: 12px; color: #6d52d9; background: #f2efff; padding: 2px 10px; border-radius: 99px; font-weight: 600;">
                    Credits: {{ number_format(auth()->user()->credits) }}
                </span>
            </div>
            <p style="font-size: 12.5px; color: #64646e; margin: 0 0 16px; line-height: 1.5;">
                Bring Your Own Key (BYOK) if you want unlimited generations without consuming platform studio credits.
            </p>

            <div class="form-grid">
                <label>
                    OpenAI API Key
                    <input type="password" name="openai_api_key" value="{{ old('openai_api_key', auth()->user()->openai_api_key) }}" placeholder="sk-..." autocomplete="off">
                    <small style="color: #71717a; font-size: 11px;">Used for GPT Image generations.</small>
                </label>

                <label>
                    Replicate API Token
                    <input type="password" name="replicate_api_token" value="{{ old('replicate_api_token', auth()->user()->replicate_api_token) }}" placeholder="r8_..." autocomplete="off">
                    <small style="color: #71717a; font-size: 11px;">Used for Flux Schnell &amp; Pro studio models.</small>
                </label>
            </div>
        </div>

        <!-- Section 4: Password Update (Optional) -->
        <div style="margin-bottom: 12px;">
            <h3 style="font-size: 16px; font-weight: 600; color: #1e1e24; margin: 0 0 14px; display: flex; align-items: center; gap: 8px;">
                <span>🔒</span> Security &amp; Password <span class="optional-label" style="font-size: 11px;">Leave empty to keep unchanged</span>
            </h3>

            <div class="form-grid">
                <label class="wide-field">
                    New Password
                    <input type="password" name="password" minlength="8" placeholder="Enter new password (min. 8 characters) &mdash; leave blank to keep current password" autocomplete="new-password">
                    <small style="color: #71717a; font-size: 11.5px;">No current password is required. Only fill this in if you want to set a new password.</small>
                    @error('password') <small style="color: #c62828;">{{ $message }}</small> @enderror
                </label>
            </div>
        </div>

        <div class="form-footer" style="margin-top: 24px; padding-top: 18px; border-top: 1px solid #efeff2;">
            <span style="color: #555562; font-size: 12.5px;"><i>&lowast;</i> Synchronized across your public creator page and directory prompts</span>
            <div style="display: flex; gap: 10px;">
                <a class="outline-button" href="{{ route('dashboard.index') }}">Cancel</a>
                <button class="primary-button" type="submit">Save profile <span>&rarr;</span></button>
            </div>
        </div>
    </form>
</div>
@endsection
