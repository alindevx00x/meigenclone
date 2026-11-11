@extends('layouts.app')
@section('title', 'Join')
@section('content')
<div class="auth-shell">
    <div class="auth-card">
        <a class="auth-back" href="{{ route('prompts.index') }}">← Back to gallery</a>
        <div class="auth-eyebrow"><span>✦</span> THE CREATOR COMMUNITY</div>
        <h1>Make room for ideas.</h1>
        <p class="auth-intro">Create an account to save prompts, build your collection, and share your own work.</p>

        @if ($errors->any())
            <div style="background: #fef2f2; border: 1px solid #fecaca; color: #991b1b; border-radius: 8px; padding: 12px 16px; margin-bottom: 18px; font-size: 13px;">
                <ul style="margin: 0; padding-left: 16px;">
                    @foreach ($errors->all() as $error)
                        <li>{{ $error }}</li>
                    @endforeach
                </ul>
            </div>
        @endif

        <form method="POST" action="{{ route('register.store') }}" class="form-stack">@csrf
            <label>Your name
                <input type="text" name="name" value="{{ old('name') }}" required autocomplete="name" placeholder="Alex Morgan">
            </label>

            <label x-data="{
                username: '{{ old('username', '') }}',
                status: 'idle',
                message: '',
                checkUsername() {
                    const clean = this.username.replace(/^@/, '').trim();
                    if (!clean) {
                        this.status = 'idle';
                        this.message = '';
                        return;
                    }
                    if (clean.length < 3) {
                        this.status = 'error';
                        this.message = 'Username must be at least 3 characters.';
                        return;
                    }
                    this.status = 'checking';
                    fetch('{{ route('check.username') }}?username=' + encodeURIComponent(clean))
                        .then(r => r.json())
                        .then(data => {
                            if (data.available) {
                                this.status = 'available';
                                this.message = data.message || 'Username is available!';
                            } else {
                                this.status = data.status === 'taken' ? 'taken' : 'error';
                                this.message = data.message || 'Username is not available.';
                            }
                        })
                        .catch(() => {
                            this.status = 'idle';
                        });
                }
            }" x-init="if (username) checkUsername()">
                Creator handle / Username
                <div style="position: relative; display: flex; align-items: center; width: 100%;">
                    <span style="position: absolute; left: 12px; color: #9ca3af; font-weight: 600; font-size: 13.5px; pointer-events: none;">@</span>
                    <input type="text"
                           name="username"
                           x-model="username"
                           @input.debounce.300ms="checkUsername()"
                           required
                           autocomplete="username"
                           placeholder="alexmorgan"
                           pattern="[a-zA-Z0-9_\.]{3,30}"
                           title="3 to 30 characters (letters, numbers, underscores, dots)"
                           :style="status === 'taken' || status === 'error' ? 'padding-left: 28px !important; width: 100%; border-color: #ef4444; background: #fff5f5;' : (status === 'available' ? 'padding-left: 28px !important; width: 100%; border-color: #10b981; background: #f0fdf4;' : 'padding-left: 28px !important; width: 100%;')"
                           style="padding-left: 28px !important; width: 100%;">
                    <span x-show="status === 'checking'" style="position: absolute; right: 12px; font-size: 11px; color: #9ca3af;" x-cloak>
                        Checking...
                    </span>
                    <span x-show="status === 'taken' || status === 'error'" style="position: absolute; right: 12px; display: flex; align-items: center;" x-cloak>
                        <svg width="18" height="18" viewBox="0 0 24 24" fill="#ef4444" aria-label="Not available"><path d="M12 2C6.48 2 2 6.48 2 12s4.48 10 10 10 10-4.48 10-10S17.52 2 12 2zm4.3 14.3a1 1 0 01-1.4 0L12 13.4l-2.9 2.9a1 1 0 01-1.4-1.4l2.9-2.9-2.9-2.9a1 1 0 011.4-1.4l2.9 2.9 2.9-2.9a1 1 0 011.4 1.4L13.4 12l2.9 2.9a1 1 0 010 1.4z" fill="#ef4444"/></svg>
                    </span>
                    <span x-show="status === 'available'" style="position: absolute; right: 12px; display: flex; align-items: center;" x-cloak>
                        <svg width="18" height="18" viewBox="0 0 24 24" fill="#10b981" aria-label="Available"><path d="M12 2C6.48 2 2 6.48 2 12s4.48 10 10 10 10-4.48 10-10S17.52 2 12 2zm-1.2 14.6l-4.2-4.2a1 1 0 011.4-1.4l2.8 2.8 6.4-6.4a1 1 0 111.4 1.4l-7.8 7.8z" fill="#10b981"/></svg>
                    </span>
                </div>

                {{-- Red error image/badge & message when taken --}}
                <div x-show="status === 'taken' || status === 'error'" x-cloak style="display: flex; align-items: center; gap: 7px; margin-top: 6px; padding: 6px 10px; border-radius: 6px; background: #fef2f2; border: 1px solid #fee2e2; color: #b91c1c; font-size: 12px; font-weight: 500;">
                    <svg width="16" height="16" viewBox="0 0 20 20" fill="currentColor" style="flex-shrink: 0;"><path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zM8.707 7.293a1 1 0 00-1.414 1.414L8.586 10l-1.293 1.293a1 1 0 101.414 1.414L10 11.414l1.293 1.293a1 1 0 001.414-1.414L11.414 10l1.293-1.293a1 1 0 00-1.414-1.414L10 8.586 8.707 7.293z" clip-rule="evenodd"/></svg>
                    <span x-text="message"></span>
                </div>

                {{-- Green message & checkmark when available --}}
                <div x-show="status === 'available'" x-cloak style="display: flex; align-items: center; gap: 7px; margin-top: 6px; padding: 6px 10px; border-radius: 6px; background: #f0fdf4; border: 1px solid #dcfce7; color: #15803d; font-size: 12px; font-weight: 500;">
                    <svg width="16" height="16" viewBox="0 0 20 20" fill="currentColor" style="flex-shrink: 0;"><path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zm3.707-9.293a1 1 0 00-1.414-1.414L9 10.586 7.707 9.293a1 1 0 00-1.414 1.414l2 2a1 1 0 001.414 0l4-4z" clip-rule="evenodd"/></svg>
                    <span x-text="message"></span>
                </div>

                <small x-show="status === 'idle'" style="display: block; color: #9ca3af; font-size: 11px; margin-top: 3px;">Your public profile will be {{ url('/creators/@your_handle') }}</small>
            </label>

            <label>Email address
                <input type="email" name="email" value="{{ old('email') }}" required autocomplete="email" placeholder="you@example.com">
            </label>

            <label>Password
                <input type="password" name="password" required autocomplete="new-password" placeholder="At least 8 characters">
            </label>

            <label>Confirm password
                <input type="password" name="password_confirmation" required autocomplete="new-password" placeholder="Type it again">
            </label>

            @if (($directorySettings['cloudflare_turnstile_enabled'] ?? '0') === '1' && !empty($directorySettings['cloudflare_turnstile_site_key']))
                <div style="margin-top: 12px; margin-bottom: 6px;">
                    <script src="https://challenges.cloudflare.com/turnstile/v0/api.js" async defer></script>
                    <div class="cf-turnstile" data-sitekey="{{ $directorySettings['cloudflare_turnstile_site_key'] }}" data-theme="light"></div>
                    @error('turnstile')
                        <span style="color: #ef4444; font-size: 12px; display: block; margin-top: 4px; font-weight: 500;">{{ $message }}</span>
                    @enderror
                </div>
            @endif

            <button class="primary-button full-button" type="submit">Create account <span>→</span></button>
        </form>
        <p class="auth-switch">Already have an account? <a href="{{ route('login') }}">Log in</a></p>
        <p class="fine-print">By joining, you agree to keep the community thoughtful and welcoming.</p>
    </div>
</div>
@endsection
