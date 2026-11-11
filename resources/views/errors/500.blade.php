@extends('layouts.app')
@section('title', 'Server Error (500) — ' . ($directorySettings['site_name'] ?? 'PromptImage'))
@section('eyebrow', 'SYSTEM STATUS')
@section('toolbar-title', '500 Server Error')

@section('content')
<div class="workspace-page" style="text-align: center; padding: 60px 16px 80px; max-width: 680px; margin: 0 auto;">
    <div style="display: inline-flex; width: 64px; height: 64px; border-radius: 50%; background: #fee2e2; color: #dc2626; align-items: center; justify-content: center; font-size: 28px; font-weight: 700; margin-bottom: 20px;">
        ⚠️
    </div>
    <span class="section-kicker" style="color: #dc2626;">HTTP 500 &middot; TEMPORARY SERVER ISSUE</span>
    <h1 style="font-size: 32px; font-weight: 800; color: #18181b; margin: 8px 0 12px; letter-spacing: -0.02em;">
        Something went wrong on our end
    </h1>
    <p style="font-size: 15px; color: #64748b; line-height: 1.6; margin: 0 0 28px;">
        Our team has been automatically alerted. This is usually resolved in a few moments. Please try refreshing the page or head back to the prompt gallery.
    </p>

    <div style="display: flex; gap: 12px; justify-content: center; flex-wrap: wrap;">
        <button type="button" onclick="window.location.reload()" class="primary-button" style="min-height: 42px; padding: 0 20px;">
            <span>↻</span> Refresh page
        </button>
        <a href="{{ route('prompts.index') }}" class="outline-button" style="min-height: 42px; padding: 0 20px; text-decoration: none;">
            <span>⌂</span> Back to gallery
        </a>
        <a href="{{ route('contact.index') }}" class="subtle-link" style="display: inline-flex; align-items: center; padding: 0 14px; font-size: 13px; font-weight: 600; color: #6366f1;">
            Report issue <span>&rarr;</span>
        </a>
    </div>
</div>
@endsection
