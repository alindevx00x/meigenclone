@extends('layouts.app')

@section('title', 'GDPR Compliance Statement \u2014 ' . $directorySettings['site_name'])
@section('eyebrow', 'LEGAL & COMPLIANCE')
@section('toolbar-title', 'GDPR Statement')

@section('content')
<div class="workspace-page">
    <div class="page-heading">
        <div>
            <span class="section-kicker">DATA PROTECTION REGULATION</span>
            <h1>GDPR Compliance Statement</h1>
            <p>Our commitment to user privacy, data security, and rights under the European Union General Data Protection Regulation (EU 2016/679).</p>
        </div>
        <div>
            <span class="tag-pill" style="display: inline-flex; align-items: center; gap: 6px; padding: 6px 12px; border-radius: 99px; background: #eef2ff; color: #4f46e5; font-size: 11.5px; font-weight: 600;">
                <span style="width: 6px; height: 6px; border-radius: 50%; background: #4f46e5;"></span> Effective: November 2026
            </span>
        </div>
    </div>

    <div style="display: grid; grid-template-columns: minmax(0, 1fr) 280px; gap: 28px; align-items: start;">
        <div class="panel-card" style="padding: 32px; font-size: 14px; line-height: 1.7; color: #374151;">
            <h2 style="font-size: 19px; font-weight: 700; color: #111827; margin: 0 0 12px;">1. Overview &amp; Data Controller</h2>
            <p>{{ $directorySettings['site_name'] }} acts as the <strong>Data Controller</strong> for personal data collected through our prompt directory platform, community boards, and creator services. We design all features according to the principles of <em>Privacy by Design and by Default</em>.</p>

            <hr style="border: 0; border-top: 1px solid #e5e7eb; margin: 24px 0;">

            <h2 style="font-size: 19px; font-weight: 700; color: #111827; margin: 0 0 12px;">2. Your Rights Under GDPR</h2>
            <p>Under Chapter III of the GDPR, European citizens and international platform users enjoy the following fundamental rights regarding their personal data:</p>
            <ul style="padding-left: 20px; margin: 12px 0 20px; list-style-type: disc;">
                <li style="margin-bottom: 8px;"><strong>Right to Access (Art. 15):</strong> You have the right to request a complete machine-readable copy of any personal data we hold about you.</li>
                <li style="margin-bottom: 8px;"><strong>Right to Rectification (Art. 16):</strong> You may update or correct your profile details, display name, and prompt submissions directly through your creator dashboard.</li>
                <li style="margin-bottom: 8px;"><strong>Right to Erasure / \"Right to be Forgotten\" (Art. 17):</strong> You can request full deletion of your user account, favorites, and custom moodboards at any time.</li>
                <li style="margin-bottom: 8px;"><strong>Right to Restrict Processing (Art. 18):</strong> You can pause or restrict the processing of your data in contested circumstances.</li>
                <li style="margin-bottom: 8px;"><strong>Right to Data Portability (Art. 20):</strong> Export your saved prompts, collections, and submission records in structured JSON or CSV format.</li>
                <li style="margin-bottom: 8px;"><strong>Right to Object (Art. 21):</strong> Object to any non-essential telemetry or analytics tracking.</li>
            </ul>

            <hr style="border: 0; border-top: 1px solid #e5e7eb; margin: 24px 0;">

            <h2 style="font-size: 19px; font-weight: 700; color: #111827; margin: 0 0 12px;">3. Legal Grounds for Processing</h2>
            <p>We process your personal information only when one of the following legal grounds applies under Article 6:</p>
            <ul style="padding-left: 20px; margin: 12px 0 20px; list-style-type: disc;">
                <li style="margin-bottom: 8px;"><strong>Contractual Necessity:</strong> To provide account authentication, prompt curation, and user favorites.</li>
                <li style="margin-bottom: 8px;"><strong>Consent:</strong> Explicit opt-in for transactional email updates, newsletters, or optional public creator badges.</li>
                <li style="margin-bottom: 8px;"><strong>Legitimate Interests:</strong> Preventing fraud, rate-limiting malicious scraping, and platform security hardening.</li>
            </ul>

            <hr style="border: 0; border-top: 1px solid #e5e7eb; margin: 24px 0;">

            <h2 style="font-size: 19px; font-weight: 700; color: #111827; margin: 0 0 12px;">4. International Data Transfers</h2>
            <p>Our server infrastructure is protected by industry standard encryption protocols (TLS 1.3 in transit, AES-256 at rest). Any international transfer of personal data adheres to standard contractual clauses (SCCs) approved by the European Commission.</p>

            <hr style="border: 0; border-top: 1px solid #e5e7eb; margin: 24px 0;">

            <h2 style="font-size: 19px; font-weight: 700; color: #111827; margin: 0 0 12px;">5. Exercising Your Rights (DPO Contact)</h2>
            <p>To submit a formal Data Subject Access Request (DSAR) or request account erasure, contact our designated privacy officer:</p>
            <div style="background: #f9fafb; border: 1px solid #e5e7eb; border-radius: 12px; padding: 16px 20px; margin-top: 14px;">
                <div style="font-weight: 600; color: #111827;">Data Protection Officer (DPO)</div>
                <div style="color: #6b7280; font-size: 13px; margin-top: 4px;">Email: <a href="mailto:{{ $directorySettings['contact_email'] ?: 'privacy@promptimage.cc' }}" style="color: #4f46e5; text-decoration: underline;">{{ $directorySettings['contact_email'] ?: 'privacy@promptimage.cc' }}</a></div>
                <div style="color: #6b7280; font-size: 12px; margin-top: 4px;">Standard response SLA: Within 30 calendar days (free of charge).</div>
            </div>
        </div>

        <div style="display: flex; flex-direction: column; gap: 16px;">
            <div class="panel-card" style="padding: 20px;">
                <h3 style="font-size: 13.5px; font-weight: 700; color: #111827; margin: 0 0 12px;">Legal Documents</h3>
                <nav style="display: flex; flex-direction: column; gap: 6px; font-size: 13px;">
                    <a href="{{ route('legal.privacy') }}" class="side-link" style="padding: 6px 10px;">🔒 Privacy Policy</a>
                    <a href="{{ route('legal.terms') }}" class="side-link" style="padding: 6px 10px;">📜 Terms of Service</a>
                    <a href="{{ route('legal.gdpr') }}" class="side-link is-active" style="padding: 6px 10px;">🇪🇺 GDPR Statement</a>
                    <a href="{{ route('legal.cookies') }}" class="side-link" style="padding: 6px 10px;">🍪 Cookie Policy</a>
                </nav>
            </div>

            <div class="panel-card" style="padding: 20px; background: #faf5ff; border-color: #f3e8ff;">
                <div style="font-size: 12.5px; font-weight: 600; color: #6b21a8; margin-bottom: 6px;">Need Account Erasure?</div>
                <p style="font-size: 11.5px; color: #7e22ce; line-height: 1.5; margin: 0;">You can request full removal of your email and submissions by emailing our compliance team.</p>
            </div>
        </div>
    </div>
</div>
@endsection
