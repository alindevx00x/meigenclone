@extends('layouts.app')

@section('title', 'Contact Us — ' . ($directorySettings['site_name'] ?? 'PromptImage'))
@section('eyebrow', 'SUPPORT & INQUIRIES')
@section('toolbar-title', 'Contact Us')

@section('content')
<div class="workspace-page">
    <div class="page-heading">
        <div>
            <span class="section-kicker">WE ARE HERE TO HELP</span>
            <h1>Get in Touch</h1>
            <p>Have questions, partnership inquiries, or need help with your account? Send us a message.</p>
        </div>
        <div>
            <span class="tag-pill" style="display: inline-flex; align-items: center; gap: 6px; padding: 6px 14px; border-radius: 99px; background: #e0f2fe; color: #0369a1; font-size: 12px; font-weight: 600;">
                <span style="display: inline-block; width: 8px; height: 8px; border-radius: 50%; background: #0284c7;"></span> Typical reply: within 24 hours
            </span>
        </div>
    </div>

    @if (session('success'))
        <div style="background: #ecfdf5; border: 1px solid #a7f3d0; color: #065f46; border-radius: 10px; padding: 16px 20px; margin-bottom: 24px; display: flex; align-items: center; gap: 12px;">
            <svg style="width: 22px; height: 22px; flex-shrink: 0;" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>
            <div>
                <strong style="display: block; font-size: 14px;">Message received!</strong>
                <span style="font-size: 13px;">{{ session('success') }}</span>
            </div>
        </div>
    @endif

    @if ($errors->any())
        <div style="background: #fef2f2; border: 1px solid #fecaca; color: #991b1b; border-radius: 10px; padding: 14px 18px; margin-bottom: 24px;">
            <strong style="display: block; margin-bottom: 4px; font-size: 13.5px;">Please correct the errors below:</strong>
            <ul style="margin: 0; padding-left: 18px; font-size: 12.5px;">
                @foreach ($errors->all() as $error)
                    <li>{{ $error }}</li>
                @endforeach
            </ul>
        </div>
    @endif

    <div class="contact-layout">
        <div class="panel-card contact-form-card">
            <h2 style="font-size: 18px; font-weight: 700; color: #111827; margin: 0 0 6px;">Send us a message</h2>
            <p style="font-size: 13px; color: #6b7280; margin: 0 0 24px;">Fill out the form below and our team will get back to your email address promptly.</p>

            <form method="POST" action="{{ route('contact.store') }}" class="form-stack">
                @csrf
                <div class="contact-names-row">
                    <label style="display: block; font-size: 13px; font-weight: 600; color: #374151;">
                        Your Name <span style="color: #ef4444;">*</span>
                        <input type="text" name="name" value="{{ old('name', auth()->user()?->name) }}" required placeholder="e.g. Alex Morgan" style="margin-top: 6px; width: 100%; border: 1px solid #d1d5db; border-radius: 8px; padding: 10px 14px; font-size: 13.5px; box-sizing: border-box;">
                    </label>

                    <label style="display: block; font-size: 13px; font-weight: 600; color: #374151;">
                        Email Address <span style="color: #ef4444;">*</span>
                        <input type="email" name="email" value="{{ old('email', auth()->user()?->email) }}" required placeholder="you@domain.com" style="margin-top: 6px; width: 100%; border: 1px solid #d1d5db; border-radius: 8px; padding: 10px 14px; font-size: 13.5px; box-sizing: border-box;">
                    </label>
                </div>

                <label style="display: block; font-size: 13px; font-weight: 600; color: #374151; margin-top: 14px;">
                    Subject
                    <input type="text" name="subject" value="{{ old('subject') }}" placeholder="e.g. Creator account question, general feedback, bug report" style="margin-top: 6px; width: 100%; border: 1px solid #d1d5db; border-radius: 8px; padding: 10px 14px; font-size: 13.5px; box-sizing: border-box;">
                </label>

                <label style="display: block; font-size: 13px; font-weight: 600; color: #374151; margin-top: 14px;">
                    Message <span style="color: #ef4444;">*</span>
                    <textarea name="message" rows="6" required placeholder="Describe your inquiry in detail..." style="margin-top: 6px; width: 100%; border: 1px solid #d1d5db; border-radius: 8px; padding: 12px 14px; font-size: 13.5px; font-family: inherit; line-height: 1.5; resize: vertical; box-sizing: border-box;">{{ old('message') }}</textarea>
                </label>

                @if (($settings['cloudflare_turnstile_enabled'] ?? '0') === '1' && !empty($settings['cloudflare_turnstile_site_key']))
                    <div style="margin-top: 16px; margin-bottom: 8px;">
                        <script src="https://challenges.cloudflare.com/turnstile/v0/api.js" async defer></script>
                        <div class="cf-turnstile" data-sitekey="{{ $settings['cloudflare_turnstile_site_key'] }}" data-theme="light"></div>
                        @error('turnstile')
                            <span style="color: #ef4444; font-size: 12px; display: block; margin-top: 4px; font-weight: 500;">{{ $message }}</span>
                        @enderror
                    </div>
                @endif

                <div class="contact-submit-row">
                    <span style="font-size: 12px; color: #9ca3af;">Protected by rate-limiting & spam filters.</span>
                    <button class="primary-button" type="submit" style="display: inline-flex; align-items: center; justify-content: center; gap: 8px; padding: 10px 22px; font-size: 13.5px; font-weight: 600; border-radius: 8px; background: #6366f1; color: #fff; border: 0; cursor: pointer;">
                        <span>Send Message</span>
                        <svg style="width: 15px; height: 15px;" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M14 5l7 7m0 0l-7 7m7-7H3"/></svg>
                    </button>
                </div>
            </form>
        </div>

        <div class="contact-sidebar">
            <div class="panel-card" style="padding: 24px;">
                <h3 style="font-size: 15px; font-weight: 700; color: #111827; margin: 0 0 12px; display: flex; align-items: center; gap: 8px;">
                    <svg style="width: 18px; height: 18px; color: #6366f1;" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 8l7.89 5.26a2 2 0 002.22 0L21 8M5 19h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v10a2 2 0 002 2z"/></svg>
                    Direct Email
                </h3>
                <p style="font-size: 13px; color: #4b5563; margin: 0 0 10px; line-height: 1.5;">
                    Prefer emailing directly? Reach out to our support and team inbox:
                </p>
                <a href="mailto:{{ $directorySettings['contact_email'] ?: 'support@promptimage.cc' }}" style="font-size: 13px; font-weight: 600; color: #6366f1; word-break: break-all; text-decoration: none;">
                    {{ $directorySettings['contact_email'] ?: 'support@promptimage.cc' }}
                </a>
            </div>

            <div class="panel-card" style="padding: 24px;">
                <h3 style="font-size: 15px; font-weight: 700; color: #111827; margin: 0 0 12px; display: flex; align-items: center; gap: 8px;">
                    <svg style="width: 18px; height: 18px; color: #059669;" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 16h-1v-4h-1m1-4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>
                    Creator & Prompt Help
                </h3>
                <p style="font-size: 13px; color: #4b5563; margin: 0 0 14px; line-height: 1.5;">
                    Looking to submit prompts, customize your creator profile, or browse models?
                </p>
                <div style="display: flex; flex-direction: column; gap: 8px; font-size: 12.5px;">
                    <a href="{{ auth()->check() ? route('dashboard.prompts.create') : route('register') }}" style="color: #4b5563; text-decoration: none; font-weight: 500; display: flex; align-items: center; justify-content: space-between;">
                        <span>Submit a Prompt</span>
                        <span>→</span>
                    </a>
                    <a href="{{ route('dashboard.index') }}" style="color: #4b5563; text-decoration: none; font-weight: 500; display: flex; align-items: center; justify-content: space-between;">
                        <span>Creator Dashboard</span>
                        <span>→</span>
                    </a>
                    <a href="{{ route('legal.privacy') }}" style="color: #4b5563; text-decoration: none; font-weight: 500; display: flex; align-items: center; justify-content: space-between;">
                        <span>Privacy Policy</span>
                        <span>→</span>
                    </a>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection
