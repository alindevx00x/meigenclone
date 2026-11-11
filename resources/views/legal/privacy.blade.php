@extends('layouts.app')

@section('title', 'Privacy Policy \u2014 ' . $directorySettings['site_name'])
@section('eyebrow', 'LEGAL & COMPLIANCE')
@section('toolbar-title', 'Privacy Policy')

@section('content')
<div class="workspace-page">
    <div class="page-heading">
        <div>
            <span class="section-kicker">DATA PROTECTION</span>
            <h1>Privacy Policy</h1>
            <p>How {{ $directorySettings['site_name'] }} collects, protects, and uses your personal information.</p>
        </div>
        <div>
            <span class="tag-pill" style="display: inline-flex; align-items: center; gap: 6px; padding: 6px 12px; border-radius: 99px; background: #f3f4f6; color: #374151; font-size: 11.5px; font-weight: 600;">
                Last Updated: November 2026
            </span>
        </div>
    </div>

    <div class="legal-layout">
        <div class="panel-card legal-content">
            <h2 style="font-size: 19px; font-weight: 700; color: #111827; margin: 0 0 12px;">1. Information We Collect</h2>
            <p>We believe in minimal data footprint collection. We collect only what is strictly necessary to deliver a world-class prompt discovery experience:</p>
            <ul style="padding-left: 20px; margin: 12px 0 20px; list-style-type: disc;">
                <li style="margin-bottom: 8px;"><strong>Account Details:</strong> When you register, we collect your name, email address, and hashed password.</li>
                <li style="margin-bottom: 8px;"><strong>User Contributions:</strong> Prompts, negative prompts, SREF codes, and model tags you choose to publish publicly.</li>
                <li style="margin-bottom: 8px;"><strong>Interaction Signals:</strong> Saved favorites and moodboard groupings stored locally to provide a customized workspace.</li>
                <li style="margin-bottom: 8px;"><strong>Technical Logs:</strong> IP address, browser user-agent, and rate-limiting security identifiers strictly for DDOS prevention and brute-force mitigation.</li>
            </ul>

            <hr style="border: 0; border-top: 1px solid #e5e7eb; margin: 24px 0;">

            <h2 style="font-size: 19px; font-weight: 700; color: #111827; margin: 0 0 12px;">2. How We Use Your Data</h2>
            <p>Your data is used solely to provide platform services:</p>
            <ul style="padding-left: 20px; margin: 12px 0 20px; list-style-type: disc;">
                <li style="margin-bottom: 8px;">Operating and maintaining the prompt catalog.</li>
                <li style="margin-bottom: 8px;">Attributing prompt authorship to verified creators.</li>
                <li style="margin-bottom: 8px;">Allowing creators to publish prompts and download prompt collection archives.</li>
                <li style="margin-bottom: 8px;">Maintaining system integrity and preventing bot abuse.</li>
            </ul>

            <hr style="border: 0; border-top: 1px solid #e5e7eb; margin: 24px 0;">

            <h2 style="font-size: 19px; font-weight: 700; color: #111827; margin: 0 0 12px;">3. No Sale of Personal Information</h2>
            <p>We <strong>never sell, lease, or monetize</strong> your personal data to data brokers, third-party advertising networks, or external AI model trainers.</p>

            <hr style="border: 0; border-top: 1px solid #e5e7eb; margin: 24px 0;">

            <h2 style="font-size: 19px; font-weight: 700; color: #111827; margin: 0 0 12px;">4. Security &amp; Data Retention</h2>
            <p>We employ TLS 1.3 encryption, secure HTTP-only session cookies, strict Content Security Policies, and Bcrypt cryptographic hashing for authentication. Account data is retained for the active lifetime of your account or until you request account deletion.</p>
        </div>

        <div class="legal-sidebar">
            <div class="panel-card" style="padding: 20px;">
                <h3 style="font-size: 13.5px; font-weight: 700; color: #111827; margin: 0 0 12px;">Legal Documents</h3>
                <nav style="display: flex; flex-direction: column; gap: 6px; font-size: 13px;">
                    <a href="{{ route('legal.privacy') }}" class="side-link is-active" style="padding: 6px 10px;">🔒 Privacy Policy</a>
                    <a href="{{ route('legal.terms') }}" class="side-link" style="padding: 6px 10px;">📜 Terms of Service</a>
                    <a href="{{ route('legal.gdpr') }}" class="side-link" style="padding: 6px 10px;">🇪🇺 GDPR Statement</a>
                    <a href="{{ route('legal.cookies') }}" class="side-link" style="padding: 6px 10px;">🍪 Cookie Policy</a>
                </nav>
            </div>
        </div>
    </div>
</div>
@endsection
