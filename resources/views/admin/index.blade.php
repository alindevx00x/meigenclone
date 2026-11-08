@extends('layouts.app')
@section('title', 'Admin studio')
@section('eyebrow', 'ADMIN STUDIO')
@section('toolbar-title', 'Admin studio')
@section('content')
<div class="workspace-page admin-page">
    <div class="page-heading dashboard-heading">
        <div>
            <span class="section-kicker">COMMUNITY OPERATIONS</span>
            <h1>Welcome to the studio.</h1>
            <p>Keep the prompt library thoughtful, useful, and inspiring.</p>
        </div>
        <div style="display: flex; gap: 10px;">
            <a class="secondary-button" href="{{ route('admin.contacts.index') }}" style="display: inline-flex; align-items: center; gap: 6px; padding: 8px 14px; font-size: 13px;">
                <span>✉ Messages</span>
                @if ($stats['contacts_unread'] > 0)
                    <span style="background: #b45309; color: #fff; font-size: 11px; font-weight: 700; padding: 1px 6px; border-radius: 99px;">{{ $stats['contacts_unread'] }}</span>
                @endif
            </a>
            <a class="primary-button" href="{{ route('admin.prompts.index', ['status' => 'pending']) }}">
                Review queue <span>→</span>
            </a>
        </div>
    </div>

    <div class="stats-row admin-stats">
        <a class="stat-card" href="{{ route('admin.prompts.index') }}">
            <span class="stat-icon stat-violet">✦</span>
            <span class="stat-label">All prompts</span>
            <strong>{{ number_format($stats['prompts']) }}</strong>
            <small>Across the whole library</small>
        </a>
        <a class="stat-card" href="{{ route('admin.prompts.index', ['status' => 'pending']) }}">
            <span class="stat-icon stat-amber">◷</span>
            <span class="stat-label">Awaiting review</span>
            <strong>{{ number_format($stats['pending']) }}</strong>
            <small>Needs your attention</small>
        </a>
        <a class="stat-card" href="{{ route('admin.contacts.index') }}">
            <span class="stat-icon stat-pink">✉</span>
            <span class="stat-label">Inquiries</span>
            <strong style="{{ $stats['contacts_unread'] > 0 ? 'color: #b45309;' : '' }}">{{ number_format($stats['contacts_total']) }}</strong>
            <small>{{ $stats['contacts_unread'] }} unread submission{{ $stats['contacts_unread'] === 1 ? '' : 's' }}</small>
        </a>
        <a class="stat-card" href="{{ route('admin.newsletters.index') }}">
            <span class="stat-icon stat-green">📬</span>
            <span class="stat-label">Subscribers</span>
            <strong>{{ number_format($stats['newsletters_count']) }}</strong>
            <small>Newsletter audience</small>
        </a>
    </div>

    <div class="admin-shortcuts">
        <a href="{{ route('admin.prompts.index') }}">
            <span class="shortcut-icon">▤</span>
            <span><strong>Manage prompts</strong><small>Approve, feature, or remove submissions</small></span>
            <b>→</b>
        </a>
        <a href="{{ route('admin.categories.index') }}">
            <span class="shortcut-icon">▦</span>
            <span><strong>Manage categories</strong><small>{{ $stats['categories'] }} collections in your library</small></span>
            <b>→</b>
        </a>
        <a href="{{ route('admin.models.index') }}">
            <span class="shortcut-icon">◈</span>
            <span><strong>Manage AI models</strong><small>{{ $stats['models'] ?? 0 }} generation engines</small></span>
            <b>→</b>
        </a>
        <a href="{{ route('admin.users.index') }}">
            <span class="shortcut-icon">◎</span>
            <span><strong>Manage members</strong><small>Review access and creator accounts</small></span>
            <b>→</b>
        </a>
        <a href="{{ route('admin.contacts.index') }}">
            <span class="shortcut-icon">✉</span>
            <span><strong>Contact Messages</strong><small>{{ $stats['contacts_unread'] }} unread messages</small></span>
            <b>→</b>
        </a>
        <a href="{{ route('admin.newsletters.index') }}">
            <span class="shortcut-icon">📬</span>
            <span><strong>Newsletter Audience</strong><small>{{ number_format($stats['newsletters_count']) }} email subscribers</small></span>
            <b>→</b>
        </a>
        <a href="{{ route('admin.settings') }}">
            <span class="shortcut-icon">⚙</span>
            <span><strong>Directory settings</strong><small>Control publishing and gallery options</small></span>
            <b>→</b>
        </a>
    </div>

    <section class="workspace-section admin-queue">
        <div class="section-heading">
            <div>
                <span class="section-kicker">INCOMING FROM THE COMMUNITY</span>
                <h2>Ready for review <span class="heading-count">{{ $stats['pending'] }}</span></h2>
            </div>
            <a class="subtle-link" href="{{ route('admin.prompts.index', ['status' => 'pending']) }}">Open review queue <span>→</span></a>
        </div>
        @if ($pendingPrompts->isEmpty())
            <div class="empty-panel"><span class="empty-icon">✓</span><div><strong>You’re all caught up.</strong><p>New creator submissions will appear here.</p></div></div>
        @else
            <div class="table-wrap">
                <table class="data-table">
                    <thead><tr><th>Submission</th><th>Creator</th><th>Category</th><th>Submitted</th><th></th></tr></thead>
                    <tbody>
                        @foreach ($pendingPrompts as $prompt)
                            <tr>
                                <td>
                                    <div class="table-prompt">
                                        <span class="table-thumb">{{ $prompt->category->icon }}</span>
                                        <div><strong>{{ $prompt->title }}</strong><small>{{ $prompt->model }}</small></div>
                                    </div>
                                </td>
                                <td>{{ $prompt->user?->name ?? 'Deleted account' }}</td>
                                <td>{{ $prompt->category->name }}</td>
                                <td>{{ $prompt->created_at->diffForHumans() }}</td>
                                <td><a class="mini-action" href="{{ route('admin.prompts.index', ['status' => 'pending']) }}">Review →</a></td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
        @endif
    </section>

    <!-- Recent Contact Messages Section -->
    <section class="workspace-section" style="margin-top: 36px;">
        <div class="section-heading">
            <div>
                <span class="section-kicker">USER COMMUNICATIONS</span>
                <h2>Recent Contact Messages <span class="heading-count">{{ $recentContacts->count() }}</span></h2>
            </div>
            <a class="subtle-link" href="{{ route('admin.contacts.index') }}">View all messages <span>→</span></a>
        </div>
        @if ($recentContacts->isEmpty())
            <div class="empty-panel"><span class="empty-icon">✓</span><div><strong>No messages yet.</strong><p>Messages sent via the Contact page will appear here.</p></div></div>
        @else
            <div class="table-wrap">
                <table class="data-table">
                    <thead><tr><th>Status</th><th>Sender</th><th>Subject & Message</th><th>Received</th><th></th></tr></thead>
                    <tbody>
                        @foreach ($recentContacts as $msg)
                            <tr>
                                <td>
                                    @if (!$msg->is_read)
                                        <span class="status-pill" style="background: #fef3c7; color: #b45309; font-weight: 700; font-size: 10.5px; padding: 2px 7px; border-radius: 99px;">● Unread</span>
                                    @else
                                        <span class="status-pill" style="background: #f3f4f6; color: #6b7280; font-weight: 600; font-size: 10.5px; padding: 2px 7px; border-radius: 99px;">Read</span>
                                    @endif
                                </td>
                                <td>
                                    <strong style="display: block; font-size: 13px;">{{ $msg->name }}</strong>
                                    <small style="color: #6366f1;">{{ $msg->email }}</small>
                                </td>
                                <td>
                                    <strong style="display: block; font-size: 12.5px; color: #1f2937;">{{ $msg->subject }}</strong>
                                    <small style="color: #6b7280;">{{ Str::limit($msg->message, 80) }}</small>
                                </td>
                                <td>{{ $msg->created_at->diffForHumans() }}</td>
                                <td>
                                    <a class="mini-action" href="{{ route('admin.contacts.index') }}">View Details →</a>
                                </td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
        @endif
    </section>
</div>
@endsection
