@extends('layouts.app')
@section('title', 'Weekly Email Digest — Admin')
@section('eyebrow', 'ADMIN PANEL')
@section('toolbar-title', 'Weekly Email Digest')

@section('content')
<div class="workspace-page admin-page">
    <div class="page-heading list-heading">
        <div>
            <span class="section-kicker">AUTOMATION & RETENTION</span>
            <h1>Weekly Email Digest</h1>
            <p>Automated newsletter sent every Monday morning featuring the Top 10 community prompts via background queues.</p>
        </div>
        <div style="display: flex; gap: 8px;">
            <a href="{{ route('admin.newsletters.index') }}" class="outline-button" style="display: inline-flex; align-items: center; gap: 6px; padding: 8px 16px; font-size: 13px;">
                <span>← View Subscribers List</span>
            </a>
        </div>
    </div>

    @if (session('success'))
        <div class="alert-box alert-success" style="margin-bottom: 20px;">
            <p>{{ session('success') }}</p>
        </div>
    @endif
    @if ($errors->any())
        <div class="alert-box alert-error" style="margin-bottom: 20px;">
            @foreach ($errors->all() as $error)
                <p>{{ $error }}</p>
            @endforeach
        </div>
    @endif

    <!-- Current Mail Delivery Status Banner -->
    @php
        $settings = \App\Models\SiteSetting::current(true);
        $isCustom = $settings['mail_settings_enabled'] === '1';
        $isBrevo = str_contains($settings['mail_host'] ?? '', 'brevo');
    @endphp
    <div style="background: #fff; border: 1.5px solid #e2e8f0; border-radius: 10px; padding: 14px 18px; margin-bottom: 22px; display: flex; justify-content: space-between; align-items: center; flex-wrap: wrap; gap: 12px;">
        <div style="display: flex; align-items: center; gap: 12px;">
            <span style="font-size: 24px;">{{ $isBrevo ? '⚡' : '✉️' }}</span>
            <div>
                <div style="font-size: 13px; font-weight: 700; color: #0f172a;">
                    Active Mail Transport: 
                    <span style="color: {{ $isBrevo ? '#009262' : '#6366f1' }}; font-weight: 800;">
                        {{ $isBrevo ? 'Brevo SMTP Relay (smtp-relay.brevo.com)' : ($isCustom ? 'Custom SMTP (' . ($settings['mail_host'] ?: 'host not set') . ')' : 'Server Default Environment') }}
                    </span>
                </div>
                <div style="font-size: 12px; color: #64748b; margin-top: 2px;">
                    From: <strong>{{ $settings['mail_from_address'] ?: config('mail.from.address') }}</strong>
                    &middot; Sender: {{ $settings['mail_from_name'] ?: config('mail.from.name') }}
                </div>
            </div>
        </div>
        <a href="{{ route('admin.settings') }}" class="outline-button" style="font-size: 12px; padding: 5px 12px; font-weight: 600;">
            ⚙️ Configure Brevo / SMTP in Settings →
        </a>
    </div>

    <!-- Status Stats -->
    <div class="stats-row admin-stats" style="margin-bottom: 24px;">
        <div class="stat-card">
            <span class="stat-icon stat-green">👥</span>
            <span class="stat-label">Active Audience</span>
            <strong style="color: #059669;">{{ number_format($activeSubscribersCount) }}</strong>
            <small>Subscribers will receive digest</small>
        </div>
        <div class="stat-card">
            <span class="stat-icon stat-violet">⏰</span>
            <span class="stat-label">Automatic Schedule</span>
            <strong style="font-size: 16px;">Mondays @ 09:00</strong>
            <small>via Laravel Scheduler &amp; Queue</small>
        </div>
        <div class="stat-card">
            <span class="stat-icon stat-amber">⚡</span>
            <span class="stat-label">Queue Status</span>
            <strong>{{ number_format($pendingJobsCount) }}</strong>
            <small>Jobs in queue backlog</small>
        </div>
        <div class="stat-card">
            <span class="stat-icon stat-blue">🎨</span>
            <span class="stat-label">Digest Size</span>
            <strong>{{ $topPrompts->count() }} Prompts</strong>
            <small>Top ranked this cycle</small>
        </div>
    </div>

    <!-- Actions Row: Test Send & Manual Trigger -->
    <div style="display: grid; grid-template-columns: repeat(auto-fit, minmax(320px, 1fr)); gap: 20px; margin-bottom: 28px;">
        <!-- Test Email Form -->
        <div class="panel-card" style="padding: 20px;">
            <div style="display: flex; align-items: center; gap: 10px; margin-bottom: 12px;">
                <span style="font-size: 20px;">🧪</span>
                <div>
                    <h3 style="font-size: 15px; font-weight: 700; margin: 0; color: #111827;">Send Test Preview</h3>
                    <p style="font-size: 12px; color: #6b7280; margin: 2px 0 0;">Deliver a live test email directly to your inbox to inspect formatting.</p>
                </div>
            </div>
            <form method="POST" action="{{ route('admin.newsletters.digest.test') }}" style="display: flex; gap: 8px; margin-top: 14px;">
                @csrf
                <input type="email" name="test_email" value="{{ auth()->user()->email }}" placeholder="Enter email address..." required style="flex: 1; padding: 8px 12px; font-size: 13px; border: 1px solid #d1d5db; border-radius: 8px;" aria-label="Recipient test email">
                <button type="submit" class="primary-button" style="padding: 8px 16px; font-size: 13px; white-space: nowrap;">
                    Send Test →
                </button>
            </form>
        </div>

        <!-- Manual Dispatch Button -->
        <div class="panel-card" style="padding: 20px;">
            <div style="display: flex; align-items: center; gap: 10px; margin-bottom: 12px;">
                <span style="font-size: 20px;">🚀</span>
                <div>
                    <h3 style="font-size: 15px; font-weight: 700; margin: 0; color: #111827;">Immediate Broadcast</h3>
                    <p style="font-size: 12px; color: #6b7280; margin: 2px 0 0;">Queue the weekly digest right now to all {{ number_format($activeSubscribersCount) }} active subscribers.</p>
                </div>
            </div>
            <div style="margin-top: 14px;">
                <form method="POST" action="{{ route('admin.newsletters.digest.send') }}" onsubmit="return confirm('Broadcast this weekly digest now to all {{ number_format($activeSubscribersCount) }} subscribers?')">
                    @csrf
                    <button type="submit" class="primary-button" style="background: #111827; color: #fff; width: 100%; padding: 9px 16px; font-size: 13px; justify-content: center;" @disabled($activeSubscribersCount === 0)>
                        Dispatch to All {{ number_format($activeSubscribersCount) }} Subscribers Now
                    </button>
                </form>
            </div>
        </div>
    </div>

    <!-- Live Preview of Top 10 Prompts -->
    <div class="panel-card" style="padding: 24px; margin-bottom: 24px;">
        <div style="display: flex; align-items: center; justify-content: space-between; margin-bottom: 18px; border-bottom: 1px solid #f3f4f6; padding-bottom: 14px;">
            <div>
                <h2 style="font-size: 18px; font-weight: 800; color: #111827; margin: 0 0 4px;">Weekly Digest Curated Content Preview</h2>
                <p style="font-size: 13px; color: #6b7280; margin: 0;">These 10 prompts are currently queued to be delivered in the upcoming email blast.</p>
            </div>
            <span style="background: #eef2ff; color: #4338ca; font-weight: 700; font-size: 12px; padding: 4px 10px; border-radius: 99px;">
                {{ $topPrompts->count() }} prompts selected
            </span>
        </div>

        <div style="display: grid; grid-template-columns: repeat(auto-fill, minmax(260px, 1fr)); gap: 16px;">
            @forelse ($topPrompts as $index => $prompt)
                <div style="border: 1px solid #e5e7eb; border-radius: 10px; overflow: hidden; background: #fafafa; display: flex; flex-direction: column;">
                    <div style="position: relative; height: 160px; background: #e5e7eb;">
                        @if ($prompt->cover_url)
                            <img src="{{ $prompt->thumbnail_url ?? $prompt->cover_url }}" alt="{{ $prompt->title }}" style="width: 100%; height: 100%; object-fit: cover;">
                        @else
                            <div style="display: flex; align-items: center; justify-content: center; height: 100%; font-size: 32px;">
                                {{ $prompt->category->icon }}
                            </div>
                        @endif
                        <span style="position: absolute; top: 8px; left: 8px; background: rgba(0,0,0,0.75); color: #fff; font-size: 11px; font-weight: 800; padding: 2px 7px; border-radius: 4px;">
                            #{{ $index + 1 }}
                        </span>
                        <span style="position: absolute; top: 8px; right: 8px; background: rgba(220,38,38,0.9); color: #fff; font-size: 11px; font-weight: 700; padding: 2px 7px; border-radius: 4px;">
                            ♥ {{ number_format($prompt->likes_count) }}
                        </span>
                    </div>
                    <div style="padding: 12px; display: flex; flex-direction: column; flex: 1;">
                        <div style="font-size: 11px; color: #4f46e5; font-weight: 700; text-transform: uppercase; margin-bottom: 4px;">
                            {{ $prompt->category->name }} · {{ $prompt->model }}
                        </div>
                        <h4 style="font-size: 13.5px; font-weight: 700; color: #111827; margin: 0 0 6px; line-height: 1.3;">
                            <a href="{{ route('prompts.show', $prompt->slug) }}" target="_blank" style="color: inherit; text-decoration: none;">
                                {{ Str::limit($prompt->title, 48) }}
                            </a>
                        </h4>
                        <p style="font-size: 11.5px; color: #6b7280; margin: 0 0 10px; flex: 1; line-height: 1.4;">
                            {{ Str::limit($prompt->prompt_text, 80) }}
                        </p>
                        <a href="{{ route('prompts.show', $prompt->slug) }}" target="_blank" style="font-size: 11.5px; color: #4f46e5; font-weight: 600; text-decoration: none;">
                            View Prompt →
                        </a>
                    </div>
                </div>
            @empty
                <p style="color: #6b7280; font-size: 14px; grid-column: 1 / -1; text-align: center; padding: 40px;">
                    No approved prompts available yet.
                </p>
            @endforelse
        </div>
    </div>
</div>
@endsection
