@extends('layouts.app')
@section('title', 'Contact Submissions — Admin')
@section('eyebrow', 'ADMIN PANEL')
@section('toolbar-title', 'Contact Submissions')

@section('content')
<div class="workspace-page admin-page">
    <div class="page-heading list-heading">
        <div>
            <span class="section-kicker">INQUIRIES & SUPPORT</span>
            <h1>Contact Submissions</h1>
            <p>Review and respond to messages submitted by users and visitors from the public contact form.</p>
        </div>
        <div style="display: flex; gap: 8px;">
            <a href="{{ route('contact.index') }}" target="_blank" class="secondary-button" style="display: inline-flex; align-items: center; gap: 6px; padding: 7px 14px; font-size: 12.5px;">
                <span>View Public Form ↗</span>
            </a>
        </div>
    </div>

    @if (session('success'))
        <div class="alert-box alert-success" style="margin-bottom: 20px;">
            <p>{{ session('success') }}</p>
        </div>
    @endif

    <!-- Quick Stats Bar -->
    <div class="stats-row admin-stats" style="margin-bottom: 24px;">
        <div class="stat-card">
            <span class="stat-icon stat-violet">✉</span>
            <span class="stat-label">Total Submissions</span>
            <strong>{{ number_format($stats['total']) }}</strong>
            <small>All time inquiries</small>
        </div>
        <div class="stat-card">
            <span class="stat-icon stat-amber">●</span>
            <span class="stat-label">Unread Messages</span>
            <strong style="color: #b45309;">{{ number_format($stats['unread']) }}</strong>
            <small>Requires attention</small>
        </div>
        <div class="stat-card">
            <span class="stat-icon stat-green">✓</span>
            <span class="stat-label">Read / Resolved</span>
            <strong>{{ number_format($stats['read']) }}</strong>
            <small>Reviewed submissions</small>
        </div>
    </div>

    <!-- Filter Tabs & Search -->
    <div class="panel-card" style="margin-bottom: 20px; padding: 14px 18px; display: flex; flex-wrap: wrap; gap: 14px; align-items: center; justify-content: space-between;">
        <div class="admin-filter-tabs" style="margin: 0; border-bottom: 0;">
            <a href="{{ route('admin.contacts.index', array_merge(request()->except('status', 'page'), [])) }}" class="{{ !request('status') ? 'is-active' : '' }}">
                All <span>({{ $stats['total'] }})</span>
            </a>
            <a href="{{ route('admin.contacts.index', array_merge(request()->except('status', 'page'), ['status' => 'unread'])) }}" class="{{ request('status') === 'unread' ? 'is-active' : '' }}">
                Unread <span>({{ $stats['unread'] }})</span>
            </a>
            <a href="{{ route('admin.contacts.index', array_merge(request()->except('status', 'page'), ['status' => 'read'])) }}" class="{{ request('status') === 'read' ? 'is-active' : '' }}">
                Read <span>({{ $stats['read'] }})</span>
            </a>
        </div>

        <form method="GET" action="{{ route('admin.contacts.index') }}" style="display: flex; gap: 8px; align-items: center;">
            @if (request('status'))
                <input type="hidden" name="status" value="{{ request('status') }}">
            @endif
            <input type="search" name="q" value="{{ request('q') }}" placeholder="Search name, email, subject..." class="table-filter-input" style="padding: 7px 12px; font-size: 13px; min-width: 240px;">
            <button class="primary-button" type="submit" style="padding: 7px 14px; font-size: 12.5px;">Filter</button>
            @if (request('q') || request('status'))
                <a href="{{ route('admin.contacts.index') }}" class="secondary-button" style="padding: 7px 12px; font-size: 12.5px;">Reset</a>
            @endif
        </form>
    </div>

    <!-- Submissions Table -->
    <div class="table-wrap admin-table-wrap">
        <table class="data-table">
            <thead>
                <tr>
                    <th style="width: 50px;">Status</th>
                    <th style="width: 220px;">Sender</th>
                    <th>Subject & Message</th>
                    <th style="width: 150px;">Submitted</th>
                    <th style="width: 160px; text-align: right;">Actions</th>
                </tr>
            </thead>
            <tbody>
                @forelse ($contacts as $contact)
                    <tr style="{{ !$contact->is_read ? 'background: #fdfaf6;' : '' }}">
                        <td>
                            @if (!$contact->is_read)
                                <span class="status-pill" style="background: #fef3c7; color: #b45309; font-weight: 700; font-size: 10.5px; padding: 2px 8px; border-radius: 99px;">
                                    ● Unread
                                </span>
                            @else
                                <span class="status-pill" style="background: #f3f4f6; color: #6b7280; font-weight: 600; font-size: 10.5px; padding: 2px 8px; border-radius: 99px;">
                                    Read
                                </span>
                            @endif
                        </td>
                        <td>
                            <strong style="display: block; font-size: 13.5px; color: #111827;">{{ $contact->name }}</strong>
                            <a href="mailto:{{ $contact->email }}" style="font-size: 12px; color: #6366f1; text-decoration: none;">{{ $contact->email }}</a>
                            @if ($contact->ip_address)
                                <small style="display: block; color: #9ca3af; font-size: 10.5px; margin-top: 2px;">IP: {{ $contact->ip_address }}</small>
                            @endif
                        </td>
                        <td>
                            <div style="max-width: 650px;">
                                <strong style="display: block; font-size: 13px; color: #1f2937; margin-bottom: 3px;">
                                    {{ $contact->subject }}
                                </strong>
                                <p style="margin: 0; font-size: 12.5px; color: #4b5563; line-height: 1.45; white-space: pre-wrap; word-break: break-word;">{{ Str::limit($contact->message, 180) }}</p>
                                @if (strlen($contact->message) > 180)
                                    <details style="margin-top: 6px; font-size: 12px; color: #6366f1; cursor: pointer;">
                                        <summary>Show full message</summary>
                                        <div style="background: #fff; border: 1px solid #e5e7eb; border-radius: 6px; padding: 12px; margin-top: 6px; white-space: pre-wrap; font-size: 12.5px; color: #1f2937; line-height: 1.5;">{{ $contact->message }}</div>
                                    </details>
                                @endif
                            </div>
                        </td>
                        <td>
                            <span style="font-size: 12.5px; color: #374151; font-weight: 500;">{{ $contact->created_at->format('M j, Y') }}</span>
                            <small style="display: block; color: #9ca3af; font-size: 11px;">{{ $contact->created_at->format('H:i') }} ({{ $contact->created_at->diffForHumans() }})</small>
                        </td>
                        <td style="text-align: right;">
                            <div class="row-actions admin-actions" style="justify-content: flex-end;">
                                <a href="mailto:{{ $contact->email }}?subject={{ urlencode('Re: ' . $contact->subject) }}" class="btn-action-view" title="Reply via email" style="display: inline-flex; align-items: center; gap: 4px; padding: 4px 8px; border-radius: 6px; background: #ede9fe; color: #5b21b6; font-size: 11.5px; text-decoration: none; font-weight: 600;">
                                    <span>Reply</span>
                                </a>

                                <form method="POST" action="{{ route('admin.contacts.read', $contact) }}" style="display: inline;">
                                    @csrf
                                    @method('PATCH')
                                    <button type="submit" class="btn-action-read" style="display: inline-flex; align-items: center; gap: 4px; padding: 4px 8px; border-radius: 6px; background: #f3f4f6; color: #374151; font-size: 11.5px; border: 1px solid #e5e7eb; cursor: pointer; font-weight: 600;">
                                        {{ $contact->is_read ? 'Mark Unread' : 'Mark Read' }}
                                    </button>
                                </form>

                                <form method="POST" action="{{ route('admin.contacts.destroy', $contact) }}" style="display: inline;" onsubmit="return confirm('Permanently delete this submission?')">
                                    @csrf
                                    @method('DELETE')
                                    <button type="submit" class="btn-action-delete" style="display: inline-flex; align-items: center; gap: 4px; padding: 4px 8px; border-radius: 6px; background: #fee2e2; color: #991b1b; font-size: 11.5px; border: 0; cursor: pointer; font-weight: 600;">
                                        Delete
                                    </button>
                                </form>
                            </div>
                        </td>
                    </tr>
                @empty
                    <tr>
                        <td colspan="5" class="table-empty" style="text-align: center; padding: 40px 20px;">
                            <div style="font-size: 32px; margin-bottom: 8px;">📬</div>
                            <strong style="display: block; font-size: 14px; color: #374151;">No contact submissions found</strong>
                            <p style="font-size: 12.5px; color: #9ca3af; margin: 4px 0 0;">Submissions received from the public Contact Us form will appear here.</p>
                        </td>
                    </tr>
                @endforelse
            </tbody>
        </table>
    </div>

    <div style="margin-top: 20px;">
        {{ $contacts->links() }}
    </div>
</div>
@endsection
