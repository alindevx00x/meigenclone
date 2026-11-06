@extends('layouts.app')
@section('title', 'Log in')
@section('content')
<div class="auth-shell">
    <div class="auth-card">
        <a class="auth-back" href="{{ route('prompts.index') }}">← Back to gallery</a>
        <div class="auth-eyebrow"><span>✦</span> YOUR CREATIVE SPACE</div>
        <h1>Welcome back.</h1>
        <p class="auth-intro">Sign in to save inspiration and share prompts with the community.</p>
        <form method="POST" action="{{ route('login.store') }}" class="form-stack">@csrf
            <label>Email address<input type="email" name="email" value="{{ old('email') }}" required autocomplete="email" placeholder="you@example.com"></label>
            <label>Password<input type="password" name="password" required autocomplete="current-password" placeholder="Your password"></label>
            <label class="check-label"><input type="checkbox" name="remember" value="1"> <span>Keep me signed in</span></label>
            <button class="primary-button full-button" type="submit">Log in <span>→</span></button>
        </form>
        @if ($directorySettings['allow_registrations'] === '1')<p class="auth-switch">New to {{ $directorySettings['site_name'] }}? <a href="{{ route('register') }}">Create an account</a></p>@endif
        <div class="demo-note"><strong>Preview accounts</strong><span>Admin: admin@meigen.test · password</span><span>Creator: creator@meigen.test · password</span></div>
    </div>
</div>
@endsection
