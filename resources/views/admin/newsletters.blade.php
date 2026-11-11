@extends('layouts.app')
@section('title', 'Newsletter Subscribers — Admin')
@section('eyebrow', 'ADMIN PANEL')
@section('toolbar-title', 'Newsletter Subscribers')

@section('content')
<div class="workspace-page admin-page">
    <div class="page-heading list-heading">
        <div>
            <span class="section-kicker">AUDIENCE & REACH</span>
            <h1>Newsletter Subscribers</h1>
            <p>Manage community members and visitors who opted in to receive prompt drops and platform digests.</p>
        </div>
        <div style="display: flex; gap: 8px;">
            <a href="{{ route('admin.newsletters.digest') }}" class="primary-button" style="display: inline-flex; align-items: center; gap: 6px; padding: 8px 16px; font-size: 13px; background: #4f46e5;">
                <span>⚡ Weekly Email Digest</span>
            </a>
            <a href="{{ route('admin.newsletters.export') }}" class="outline-button" style="display: inline-flex; align-items: center; gap: 6px; padding: 8px 16px; font-size: 13px;">
                <svg style="width: 15px; height: 15px;" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 16v1a3 3 0 003 3h10a3 3 0 003-3v-1m-4-4l-4 4m0 0l-4-4m4 4V4"/></svg>
                <span>Export CSV</span>
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
            <span class="stat-label">Total Subscribers</span>
            <strong>{{ number_format($stats['total']) }}</strong>
            <small>Email subscribers</small>
        </div>
        <div class="stat-card">
            <span class="stat-icon stat-green">✓</span>
            <span class="stat-label">Active Audience</span>
            <strong style="color: #059669;">{{ number_format($stats['active']) }}</strong>
            <small>Subscribed & deliverable</small>
        </div>
        <div class="stat-card">
            <span class="stat-icon stat-amber">✕</span>
            <span class="stat-label">Unsubscribed</span>
            <strong>{{ number_format($stats['inactive']) }}</strong>
            <small>Opted out</small>
        </div>
    </div>

    <!-- Filter & Search -->
    <div class="panel-card" style="margin-bottom: 20px; padding: 14px 18px; display: flex; flex-wrap: wrap; gap: 14px; align-items: center; justify-content: space-between;">
        <div class="admin-filter-tabs" style="margin: 0; border-bottom: 0;">
            <a href="{{ route('admin.newsletters.index', array_merge(request()->except('status', 'page'), [])) }}" class="{{ !request('status') ? 'is-active' : '' }}">
                All <span>({{ $stats['total'] }})</span>
            </a>
            <a href="{{ route('admin.newsletters.index', array_merge(request()->except('status', 'page'), ['status' => 'active'])) }}" class="{{ request('status') === 'active' ? 'is-active' : '' }}">
                Active <span>({{ $stats['active'] }})</span>
            </a>
            <a href="{{ route('admin.newsletters.index', array_merge(request()->except('status', 'page'), ['status' => 'inactive'])) }}" class="{{ request('status') === 'inactive' ? 'is-active' : '' }}">
                Inactive <span>({{ $stats['inactive'] }})</span>
            </a>
        </div>

        <form method="GET" action="{{ route('admin.newsletters.index') }}" style="display: flex; gap: 8px; align-items: center;">
            @if (request('status'))
                <input type="hidden" name="status" value="{{ request('status') }}">
            @endif
            <input type="search" name="q" value="{{ request('q') }}" placeholder="Search by email..." style="padding: 7px 12px; font-size: 13px; border: 1px solid #d1d5db; border-radius: 6px; min-width: 240px;">
            <button class="primary-button" type="submit" style="padding: 7px 14px; font-size: 12.5px;">Filter</button>
            @if (request('q') || request('status'))
                <a href="{{ route('admin.newsletters.index') }}" class="secondary-button" style="padding: 7px 12px; font-size: 12.5px;">Reset</a>
            @endif
        </form>
    </div>

    <!-- Table -->
    <div class="table-wrap admin-table-wrap">
        <table class="data-table">
            <thead>
                <tr>
                    <th style="width: 50px;">Status</th>
                    <th>Email Address</th>
                    <th style="width: 220px;">Linked Account</th>
                    <th style="width: 170px;">Subscribed On</th>
                    <th style="width: 150px; text-align: right;">Actions</th>
                </tr>
            </thead>
            <tbody>
                @forelse ($subscribers as $sub)
                    <tr>
                        <td>
                            @if ($sub->is_active)
                                <span class="status-pill" style="background: #ecfdf5; color: #065f46; font-weight: 700; font-size: 10.5px; padding: 2px 8px; border-radius: 99px;">
                                    ● Active
                                </span>
                            @else
                                <span class="status-pill" style="background: #f3f4f6; color: #6b7280; font-weight: 600; font-size: 10.5px; padding: 2px 8px; border-radius: 99px;">
                                    Inactive
                                </span>
                            @endif
                        </td>
                        <td>
                            <strong style="font-size: 13.5px; color: #111827;">{{ $sub->email }}</strong>
                        </td>
                        <td>
                            @if ($sub->user)
                                <a href="{{ route('admin.users.edit', $sub->user) }}" style="font-size: 12.5px; color: #6366f1; text-decoration: none; font-weight: 500;">
                                    {{ $sub->user->name }} ({{ $sub->user->handle }})
                                </a>
                            @else
                                <span style="font-size: 12px; color: #9ca3af;">Guest Visitor</span>
                            @endif
                        </td>
                        <td>
                            <span style="font-size: 12.5px; color: #374151;">{{ $sub->created_at ? $sub->created_at->format('M j, Y') : 'N/A' }}</span>
                            @if ($sub->created_at)
                                <small style="display: block; color: #9ca3af; font-size: 11px;">{{ $sub->created_at->diffForHumans() }}</small>
                            @endif
                        </td>
                        <td style="text-align: right;">
                            <div class="row-actions admin-actions" style="justify-content: flex-end;">
                                <form method="POST" action="{{ route('admin.newsletters.active', $sub) }}" style="display: inline;">
                                    @csrf
                                    @method('PATCH')
                                    <button type="submit" style="display: inline-flex; align-items: center; gap: 4px; padding: 4px 8px; border-radius: 6px; background: #f3f4f6; color: #374151; font-size: 11.5px; border: 1px solid #e5e7eb; cursor: pointer; font-weight: 600;">
                                        {{ $sub->is_active ? 'Deactivate' : 'Activate' }}
                                    </button>
                                </form>

                                <form method="POST" action="{{ route('admin.newsletters.destroy', $sub) }}" style="display: inline;" onsubmit="return confirm('Permanently remove this subscriber?')">
                                    @csrf
                                    @method('DELETE')
                                    <button type="submit" style="display: inline-flex; align-items: center; gap: 4px; padding: 4px 8px; border-radius: 6px; background: #fee2e2; color: #991b1b; font-size: 11.5px; border: 0; cursor: pointer; font-weight: 600;">
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
                            <strong style="display: block; font-size: 14px; color: #374151;">No newsletter subscribers found</strong>
                            <p style="font-size: 12.5px; color: #9ca3af; margin: 4px 0 0;">Subscribers from footer and signup modules will appear here.</p>
                        </td>
                    </tr>
                @endforelse
            </tbody>
        </table>
    </div>

    <div style="margin-top: 20px;">
        {{ $subscribers->links() }}
    </div>
</div>
@endsection
