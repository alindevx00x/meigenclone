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

            <label>Creator handle / Username
                <div style="position: relative; display: flex; align-items: center; width: 100%;">
                    <span style="position: absolute; left: 12px; color: #9ca3af; font-weight: 600; font-size: 13.5px; pointer-events: none;">@</span>
                    <input type="text" name="username" value="{{ old('username') }}" required autocomplete="username" placeholder="alexmorgan" pattern="[a-zA-Z0-9_\.]{3,30}" title="3 to 30 characters (letters, numbers, underscores, dots)" style="padding-left: 28px !important; width: 100%;">
                </div>
                <small style="display: block; color: #9ca3af; font-size: 11px; margin-top: 3px;">Your public profile will be {{ url('/creators/@your_handle') }}</small>
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

            <button class="primary-button full-button" type="submit">Create account <span>→</span></button>
        </form>
        <p class="auth-switch">Already have an account? <a href="{{ route('login') }}">Log in</a></p>
        <p class="fine-print">By joining, you agree to keep the community thoughtful and welcoming.</p>
    </div>
</div>
@endsection
