@extends('layouts.app')

@section('title', 'Cookie Policy \u2014 ' . $directorySettings['site_name'])
@section('eyebrow', 'LEGAL & COMPLIANCE')
@section('toolbar-title', 'Cookie Policy')

@section('content')
<div class="workspace-page">
    <div class="page-heading">
        <div>
            <span class="section-kicker">COOKIE USAGE</span>
            <h1>Cookie Policy</h1>
            <p>How and why {{ $directorySettings['site_name'] }} uses HTTP cookies and local storage tokens.</p>
        </div>
        <div>
            <span class="tag-pill" style="display: inline-flex; align-items: center; gap: 6px; padding: 6px 12px; border-radius: 99px; background: #f3f4f6; color: #374151; font-size: 11.5px; font-weight: 600;">
                Effective: November 2026
            </span>
        </div>
    </div>

    <div class="legal-layout">
        <div class="panel-card legal-content">
            <h2 style="font-size: 19px; font-weight: 700; color: #111827; margin: 0 0 12px;">1. What Are Cookies?</h2>
            <p>Cookies are small alphanumeric text files stored on your device by your web browser. They are essential for keeping you logged in and securing your session state against CSRF exploits.</p>

            <hr style="border: 0; border-top: 1px solid #e5e7eb; margin: 24px 0;">

            <h2 style="font-size: 19px; font-weight: 700; color: #111827; margin: 0 0 12px;">2. Cookies We Set</h2>
            <div style="display: flex; flex-direction: column; gap: 12px; margin-top: 14px;">
                <div style="padding: 14px 18px; border: 1px solid #e5e7eb; border-radius: 12px; background: #f9fafb;">
                    <div style="font-weight: 600; color: #111827; display: flex; align-items: center; justify-content: space-between;">
                        <span>prompt-directory-session</span>
                        <span style="font-size: 11px; font-weight: 600; color: #059669; background: #ecfdf5; padding: 2px 8px; border-radius: 6px;">Strictly Necessary</span>
                    </div>
                    <p style="font-size: 12.5px; color: #6b7280; margin: 6px 0 0;">Maintains your authenticated session state across requests. Encrypted with AES-256 and set with HTTP-only flags.</p>
                </div>

                <div style="padding: 14px 18px; border: 1px solid #e5e7eb; border-radius: 12px; background: #f9fafb;">
                    <div style="font-weight: 600; color: #111827; display: flex; align-items: center; justify-content: space-between;">
                        <span>XSRF-TOKEN</span>
                        <span style="font-size: 11px; font-weight: 600; color: #059669; background: #ecfdf5; padding: 2px 8px; border-radius: 6px;">Security</span>
                    </div>
                    <p style="font-size: 12.5px; color: #6b7280; margin: 6px 0 0;">Protects your account against Cross-Site Request Forgery (CSRF) attacks when favoriting prompts or copying tags.</p>
                </div>
            </div>

            <hr style="border: 0; border-top: 1px solid #e5e7eb; margin: 24px 0;">

            <h2 style="font-size: 19px; font-weight: 700; color: #111827; margin: 0 0 12px;">3. No Invasive Third-Party Trackers</h2>
            <p>We do not load third-party ad networks, tracking pixels, or intrusive commercial profiling scripts on {{ $directorySettings['site_name'] }}.</p>
        </div>

        <div class="legal-sidebar">
            <div class="panel-card" style="padding: 20px;">
                <h3 style="font-size: 13.5px; font-weight: 700; color: #111827; margin: 0 0 12px;">Legal Documents</h3>
                <nav style="display: flex; flex-direction: column; gap: 6px; font-size: 13px;">
                    <a href="{{ route('legal.privacy') }}" class="side-link" style="padding: 6px 10px;">🔒 Privacy Policy</a>
                    <a href="{{ route('legal.terms') }}" class="side-link" style="padding: 6px 10px;">📜 Terms of Service</a>
                    <a href="{{ route('legal.gdpr') }}" class="side-link" style="padding: 6px 10px;">🇪🇺 GDPR Statement</a>
                    <a href="{{ route('legal.cookies') }}" class="side-link is-active" style="padding: 6px 10px;">🍪 Cookie Policy</a>
                </nav>
            </div>
        </div>
    </div>
</div>
@endsection
