@extends('layouts.app')
@section('title', 'Verify your email')
@section('content')
<div class="auth-shell">
    <div class="auth-card">
        <a class="auth-back" href="{{ route('prompts.index') }}">← Back to gallery</a>
        <div class="auth-eyebrow"><span>✦</span> ACCOUNT VERIFICATION</div>
        <h1>Verify your email</h1>
        <p class="auth-intro">Thanks for signing up! Before getting started, could you verify your email address by clicking on the link we just emailed to you?</p>

        @if (session('status') == 'verification-link-sent')
            <div style="background: #ecfdf5; border: 1px solid #a7f3d0; color: #065f46; padding: 12px 16px; border-radius: 10px; font-size: 13.5px; margin-bottom: 20px;">
                A new verification link has been sent to the email address you provided during registration.
            </div>
        @endif

        <div style="display: flex; flex-direction: column; gap: 12px;">
            <form method="POST" action="{{ route('verification.send') }}">
                @csrf
                <button class="primary-button full-button" type="submit">Resend verification email <span>→</span></button>
            </form>

            <form method="POST" action="{{ route('logout') }}" style="text-align: center; margin-top: 8px;">
                @csrf
                <button type="submit" style="background: none; border: none; color: #64748b; font-size: 13px; text-decoration: underline; cursor: pointer;">
                    Log out of account
                </button>
            </form>
        </div>
    </div>
</div>
@endsection
