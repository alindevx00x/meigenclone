@extends('layouts.app')
@section('title', 'Manage members')
@section('eyebrow', 'MEIGEN ADMIN')
@section('toolbar-title', 'Member management')
@section('content')
<div class="workspace-page admin-page">
    <div class="page-heading list-heading">
        <div>
            <span class="section-kicker">THE PEOPLE BEHIND THE PROMPTS</span>
            <h1>Community members</h1>
            <p>Manage creator accounts, account security, profile information, and administrator privileges.</p>
        </div>
    </div>

    @if ($errors->any())
        <div class="alert-box alert-error" style="margin-bottom: 20px;">
            @foreach ($errors->all() as $error)
                <p>{{ $error }}</p>
            @endforeach
        </div>
    @endif
    @if (session('success'))
        <div class="alert-box alert-success" style="margin-bottom: 20px;">
            <p>{{ session('success') }}</p>
        </div>
    @endif

    <!-- Quick Stats Bar -->
    <div class="stats-row admin-stats" style="margin-bottom: 24px;">
        <div class="stat-card">
            <span class="stat-icon stat-violet">◎</span>
            <span class="stat-label">Total members</span>
            <strong>{{ number_format($stats['total']) }}</strong>
            <small>Registered accounts</small>
        </div>
        <div class="stat-card">
            <span class="stat-icon stat-pink">✦</span>
            <span class="stat-label">Administrators</span>
            <strong>{{ number_format($stats['admins']) }}</strong>
            <small>Full studio access</small>
        </div>
        <div class="stat-card">
            <span class="stat-icon stat-green">✓</span>
            <span class="stat-label">Active creators</span>
            <strong>{{ number_format($stats['active']) }}</strong>
            <small>In good standing</small>
        </div>
        <div class="stat-card">
            <span class="stat-icon stat-amber">◷</span>
            <span class="stat-label">Suspended</span>
            <strong>{{ number_format($stats['suspended']) }}</strong>
            <small>Restricted accounts</small>
        </div>
    </div>

    <!-- Search & Filter Controls -->
    <form class="panel-card" method="GET" action="{{ route('admin.users.index') }}" style="margin-bottom: 20px; display: flex; flex-wrap: wrap; gap: 10px; align-items: center; justify-content: space-between;">
        <div style="display: flex; flex-wrap: wrap; gap: 10px; align-items: center; flex: 1 1 300px;">
            <input type="search" name="q" value="{{ request('q') }}" placeholder="Search by name or email…" style="flex: 1 1 200px; min-height: 38px;" aria-label="Search members">
            
            <select name="role" style="min-height: 38px; width: 140px;" aria-label="Filter by role">
                <option value="">All Roles</option>
                <option value="admin" @selected(request('role') === 'admin')>Administrator</option>
                <option value="user" @selected(request('role') === 'user')>Creator</option>
            </select>

            <select name="status" style="min-height: 38px; width: 140px;" aria-label="Filter by status">
                <option value="">All Statuses</option>
                <option value="active" @selected(request('status') === 'active')>Active</option>
                <option value="suspended" @selected(request('status') === 'suspended')>Suspended</option>
            </select>

            <select name="sort" style="min-height: 38px; width: 150px;" aria-label="Sort members">
                <option value="newest" @selected(request('sort', 'newest') === 'newest')>Newest first</option>
                <option value="oldest" @selected(request('sort') === 'oldest')>Oldest first</option>
                <option value="prompts" @selected(request('sort') === 'prompts')>Most submissions</option>
                <option value="name" @selected(request('sort') === 'name')>Name (A–Z)</option>
            </select>

            <button class="primary-button" type="submit" style="min-height: 38px; padding: 0 16px;">Filter <span>→</span></button>
            @if (request()->hasAny(['q', 'role', 'status', 'sort']))
                <a class="outline-button" href="{{ route('admin.users.index') }}" style="min-height: 38px; display: inline-flex; align-items: center;">Clear</a>
            @endif
        </div>
    </form>

    <!-- Members Table -->
    <div class="table-wrap admin-table-wrap">
        <table class="data-table">
            <thead>
                <tr>
                    <th>Member</th>
                    <th>Status</th>
                    <th>Access</th>
                    <th>Submissions</th>
                    <th>Joined</th>
                    <th style="text-align: right;">Settings &amp; Actions</th>
                </tr>
            </thead>
            <tbody>
                @forelse ($users as $member)
                    <tr>
                        <td>
                            <div class="member-table-cell">
                                <a href="{{ route('creators.show', $member) }}" style="text-decoration: none; flex-shrink: 0;">
                                    @if ($member->avatar_url)
                                        <img src="{{ $member->avatar_url }}" alt="{{ $member->name }}" class="avatar" style="object-fit: cover;">
                                    @else
                                        <span class="avatar">{{ str($member->name)->substr(0, 1)->upper() }}</span>
                                    @endif
                                </a>
                                <div>
                                    <div style="display: flex; align-items: center; gap: 5px;">
                                        <a href="{{ route('creators.show', $member) }}" style="font-weight: 600; color: #17171b; text-decoration: none;">
                                            {{ $member->name }}
                                        </a>
                                        @if($member->is_verified)
                                            <span title="Verified Creator" style="display: inline-flex; align-items: center; justify-content: center; width: 14px; height: 14px; border-radius: 50%; background: linear-gradient(135deg, #8b5cf6, #6366f1); color: #fff; font-size: 8.5px; font-weight: 700;">✓</span>
                                        @endif
                                        @if ($member->is(auth()->user()))
                                            <span style="font-size: 10px; color: #795fda; font-weight: 500;">(You)</span>
                                        @endif
                                    </div>
                                    <small style="color: #64646e;">{{ $member->email }}</small>
                                    @if ($member->website)
                                        <small><a href="{{ $member->website }}" target="_blank" rel="noopener" style="color: #795fda;">{{ Str::limit($member->website, 30) }} ↗</a></small>
                                    @endif
                                </div>
                            </div>
                        </td>
                        <td>
                            @if ($member->status === 'suspended')
                                <span class="role-pill" style="background: #ffebee; color: #c62828; font-weight: 600;">Suspended</span>
                            @else
                                <span class="role-pill" style="background: #e8f5e9; color: #2e7d32; font-weight: 600;">Active</span>
                            @endif
                        </td>
                        <td>
                            <span class="role-pill {{ $member->isAdmin() ? 'role-admin' : '' }}">
                                {{ $member->isAdmin() ? 'Administrator' : ($member->is_verified ? 'Verified Creator' : 'Creator') }}
                            </span>
                        </td>
                        <td>
                            <strong style="color: #18181b;">{{ number_format($member->prompts_count) }}</strong>
                            <small style="color: #71717a;">prompts</small>
                        </td>
                        <td>
                            <span style="font-size: 12.5px; color: #4b5563;">{{ $member->created_at->format('M j, Y') }}</span>
                            <small style="display: block; color: #71717a; font-size: 11px;">{{ $member->created_at->diffForHumans() }}</small>
                        </td>
                        <td>
                            <div style="display: flex; gap: 6px; justify-content: flex-end; align-items: center;">
                                <a href="{{ route('creators.show', $member) }}" target="_blank" class="outline-button" title="View Public Profile" style="min-height: 32px; padding: 0 8px; font-size: 11px; color: #6366f1; border-color: #c7d2fe; text-decoration: none;">
                                    Profile ↗
                                </a>
                                <!-- Edit User Button (Opens in dedicated page) -->
                                <a href="{{ route('admin.users.edit', $member) }}" class="outline-button" style="min-height: 32px; padding: 0 10px; font-size: 11px; display: inline-flex; align-items: center; text-decoration: none;">
                                    Edit settings ⚙
                                </a>

                                @if (! $member->is(auth()->user()))
                                    <!-- Toggle Status Button -->
                                    <form method="POST" action="{{ route('admin.users.status', $member) }}" style="display: inline;">
                                        @csrf
                                        @method('PATCH')
                                        @if ($member->status === 'suspended')
                                            <button type="submit" class="outline-button" title="Unsuspend member" style="min-height: 32px; padding: 0 8px; font-size: 11px; color: #2e7d32; border-color: #a5d6a7;">
                                                Activate
                                            </button>
                                        @else
                                            <button type="submit" class="outline-button" title="Suspend account" onclick="return confirm('Suspend {{ $member->name }}? They will not be able to log in until unsuspended.')" style="min-height: 32px; padding: 0 8px; font-size: 11px; color: #c62828; border-color: #ffcdd2;">
                                                Suspend
                                            </button>
                                        @endif
                                    </form>

                                    <!-- Delete User Button -->
                                    <form method="POST" action="{{ route('admin.users.destroy', $member) }}" onsubmit="return confirm('Permanently remove member {{ $member->name }}? This cannot be undone.')" style="display: inline;">
                                        @csrf
                                        @method('DELETE')
                                        <button class="icon-delete" type="submit" title="Delete member account">×</button>
                                    </form>
                                @endif
                            </div>
                        </td>
                    </tr>
                @empty
                    <tr>
                        <td colspan="6" class="table-empty">
                            No community members match your filter criteria.
                        </td>
                    </tr>
                @endforelse
            </tbody>
        </table>
    </div>

    <div class="pagination-wrap" style="margin-top: 20px;">
        {{ $users->links() }}
    </div>
</div>
@endsection
