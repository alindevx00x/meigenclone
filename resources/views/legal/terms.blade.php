@extends('layouts.app')

@section('title', 'Terms of Service \u2014 ' . $directorySettings['site_name'])
@section('eyebrow', 'LEGAL & COMPLIANCE')
@section('toolbar-title', 'Terms of Service')

@section('content')
<div class="workspace-page">
    <div class="page-heading">
        <div>
            <span class="section-kicker">TERMS &amp; CONDITIONS</span>
            <h1>Terms of Service</h1>
            <p>Rules and guidelines governing your access and usage of {{ $directorySettings['site_name'] }}.</p>
        </div>
        <div>
            <span class="tag-pill" style="display: inline-flex; align-items: center; gap: 6px; padding: 6px 12px; border-radius: 99px; background: #f3f4f6; color: #374151; font-size: 11.5px; font-weight: 600;">
                Effective: November 2026
            </span>
        </div>
    </div>

    <div class="legal-layout">
        <div class="panel-card legal-content">
            <h2 style="font-size: 19px; font-weight: 700; color: #111827; margin: 0 0 12px;">1. Acceptance of Terms</h2>
            <p>By creating an account, browsing the prompt gallery, or utilizing our creator studios, you agree to comply with and be bound by these Terms of Service. If you do not agree to these terms, please do not use the service.</p>

            <hr style="border: 0; border-top: 1px solid #e5e7eb; margin: 24px 0;">

            <h2 style="font-size: 19px; font-weight: 700; color: #111827; margin: 0 0 12px;">2. User-Submitted Content &amp; AI Prompts</h2>
            <p>You retain full ownership of any original creative prompts and metadata you author. By publishing a prompt on {{ $directorySettings['site_name'] }}, you grant the platform a non-exclusive, worldwide, royalty-free license to index, display, format, and share the prompt with community members.</p>
            <p style="margin-top: 10px;">You agree not to submit prompts designed to generate illegal material, non-consensual depictions, hate speech, malware, or content violating third-party intellectual property rights.</p>

            <hr style="border: 0; border-top: 1px solid #e5e7eb; margin: 24px 0;">

            <h2 style="font-size: 19px; font-weight: 700; color: #111827; margin: 0 0 12px;">3. Service Availability &amp; Disclaimer</h2>
            <p>The platform is provided on an \"AS IS\" and \"AS AVAILABLE\" basis. While we strive for 99.9% uptime and curate high-quality generation tokens, we make no guarantees regarding output reproducibility across external AI model providers (Midjourney, Flux, Stable Diffusion, DALL-E).</p>
        </div>

        <div class="legal-sidebar">
            <div class="panel-card" style="padding: 20px;">
                <h3 style="font-size: 13.5px; font-weight: 700; color: #111827; margin: 0 0 12px;">Legal Documents</h3>
                <nav style="display: flex; flex-direction: column; gap: 6px; font-size: 13px;">
                    <a href="{{ route('legal.privacy') }}" class="side-link" style="padding: 6px 10px;">🔒 Privacy Policy</a>
                    <a href="{{ route('legal.terms') }}" class="side-link is-active" style="padding: 6px 10px;">📜 Terms of Service</a>
                    <a href="{{ route('legal.gdpr') }}" class="side-link" style="padding: 6px 10px;">🇪🇺 GDPR Statement</a>
                    <a href="{{ route('legal.cookies') }}" class="side-link" style="padding: 6px 10px;">🍪 Cookie Policy</a>
                </nav>
            </div>
        </div>
    </div>
</div>
@endsection
