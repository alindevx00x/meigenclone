@extends('layouts.app')

@section('title', 'Page Not Found (404) — ' . ($directorySettings['site_name'] ?? 'PromptImage'))
@section('eyebrow', 'ERROR 404')
@section('toolbar-title', 'Page Not Found')

@section('content')
<div class="workspace-page" style="min-height: 70vh; display: flex; flex-direction: column; align-items: center; justify-content: center; text-align: center; padding: 60px 20px;">
    <div style="max-width: 600px; margin: 0 auto;">
        <div style="display: inline-flex; align-items: center; justify-content: center; width: 84px; height: 84px; border-radius: 24px; background: linear-gradient(135deg, #ede9fe, #fae8ff); color: #7c3aed; font-size: 38px; margin-bottom: 24px; box-shadow: 0 10px 25px -5px rgba(124, 58, 237, 0.15);">
            ✦
        </div>

        <span class="section-kicker" style="display: block; margin-bottom: 8px; font-weight: 700; letter-spacing: 1px; color: #8b5cf6;">ERROR 404</span>
        <h1 style="font-size: 32px; font-weight: 800; color: #111827; margin: 0 0 14px; letter-spacing: -0.5px; line-height: 1.2;">
            Lost in the Latent Space
        </h1>
        <p style="font-size: 15px; color: #6b7280; line-height: 1.6; margin: 0 auto 28px; max-width: 480px;">
            The page, prompt, or creator profile you are looking for may have been moved, deleted, or never existed in the library.
        </p>

        <!-- Search Bar -->
        <form method="GET" action="{{ route('prompts.index') }}" style="display: flex; max-width: 440px; margin: 0 auto 32px; border: 1px solid #d1d5db; border-radius: 10px; overflow: hidden; background: #fff; box-shadow: 0 4px 12px rgba(0,0,0,0.04);">
            <input type="search" name="q" placeholder="Search thousands of prompts, styles, models..." style="flex: 1; border: 0; padding: 12px 16px; font-size: 13.5px; outline: none;">
            <button type="submit" class="primary-button" style="border-radius: 0; padding: 0 18px; font-size: 13px; font-weight: 600;">Search</button>
        </form>

        <!-- Navigation Buttons -->
        <div style="display: flex; flex-wrap: wrap; gap: 12px; justify-content: center; align-items: center;">
            <a href="{{ route('prompts.index') }}" class="primary-button" style="display: inline-flex; align-items: center; gap: 6px; padding: 10px 22px; font-size: 13.5px; border-radius: 8px;">
                <span>← Browse Prompts</span>
            </a>
            <a href="{{ route('contact.index') }}" class="secondary-button" style="display: inline-flex; align-items: center; gap: 6px; padding: 10px 20px; font-size: 13.5px; border-radius: 8px;">
                <span>Contact Support</span>
            </a>
        </div>
    </div>
</div>
@endsection
