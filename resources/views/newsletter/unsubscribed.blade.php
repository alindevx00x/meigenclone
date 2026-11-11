@extends('layouts.app')
@section('title', 'Unsubscribed from Newsletter')

@section('content')
<div class="workspace-page" style="max-width: 600px; margin: 40px auto; text-align: center; padding: 40px 20px;">
    <div class="panel-card" style="padding: 40px 24px; border-radius: 16px;">
        <div style="font-size: 48px; margin-bottom: 16px;">📬</div>
        <h1 style="font-size: 24px; font-weight: 800; margin-bottom: 10px; color: #111827;">You've been unsubscribed</h1>
        <p style="color: #6b7280; font-size: 14.5px; line-height: 1.6; margin-bottom: 24px;">
            The email address <strong>{{ $email }}</strong> will no longer receive weekly prompt digests.
        </p>
        <p style="color: #9ca3af; font-size: 13px; margin-bottom: 30px;">
            Changed your mind? You can always resubscribe from our website footer at any time.
        </p>
        <a href="{{ url('/') }}" class="primary-button" style="display: inline-block; padding: 10px 24px; font-size: 14px; text-decoration: none;">
            Return to Homepage
        </a>
    </div>
</div>
@endsection
