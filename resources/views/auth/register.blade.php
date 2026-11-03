@extends('layouts.app')
@section('title', 'Join')
@section('content')
<div class="auth-shell">
    <div class="auth-card">
        <a class="auth-back" href="{{ route('prompts.index') }}">← Back to gallery</a>
        <div class="auth-eyebrow"><span>✦</span> THE CREATOR COMMUNITY</div>
        <h1>Make room for ideas.</h1>
        <p class="auth-intro">Create an account to save prompts, build your collection, and share your own work.</p>
        <form method="POST" action="{{ route('register.store') }}" class="form-stack">@csrf
            <label>Your name<input type="text" name="name" value="{{ old('name') }}" required autocomplete="name" placeholder="Alex Morgan"></label>
            <label>Email address<input type="email" name="email" value="{{ old('email') }}" required autocomplete="email" placeholder="you@example.com"></label>
            <label>Password<input type="password" name="password" required autocomplete="new-password" placeholder="At least 8 characters"></label>
            <label>Confirm password<input type="password" name="password_confirmation" required autocomplete="new-password" placeholder="Type it again"></label>
            <button class="primary-button full-button" type="submit">Create account <span>→</span></button>
        </form>
        <p class="auth-switch">Already have an account? <a href="{{ route('login') }}">Log in</a></p>
        <p class="fine-print">By joining, you agree to keep the community thoughtful and welcoming.</p>
    </div>
</div>
@endsection
