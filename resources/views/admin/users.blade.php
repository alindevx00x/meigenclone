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
                                <span class="avatar">{{ str($member->name)->substr(0, 1)->upper() }}</span>
                                <div>
                                    <strong>{{ $member->name }} @if ($member->is(auth()->user())) <span style="font-size: 10px; color: #795fda; font-weight: 500;">(You)</span> @endif</strong>
                                    <small>{{ $member->email }}</small>
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
                                {{ $member->isAdmin() ? 'Administrator' : 'Creator' }}
                            </span>
                        </td>
                        <td>
                            <strong>{{ number_format($member->prompts_count) }}</strong>
                            <small style="color: #999;">prompts</small>
                        </td>
                        <td>
                            <span style="font-size: 12.5px; color: #666;">{{ $member->created_at->format('M j, Y') }}</span>
                            <small style="display: block; color: #aaa; font-size: 10px;">{{ $member->created_at->diffForHumans() }}</small>
                        </td>
                        <td>
                            <div style="display: flex; gap: 6px; justify-content: flex-end; align-items: center;">
                                <!-- Edit User Button (Opens Dialog) -->
                                <button type="button" class="outline-button" onclick="document.getElementById('edit-user-modal-{{ $member->id }}').showModal()" style="min-height: 32px; padding: 0 10px; font-size: 11px;">
                                    Edit settings ⚙
                                </button>

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

                            <!-- Edit Modal for Member -->
                            <dialog id="edit-user-modal-{{ $member->id }}" class="user-modal panel-card" style="border: 1px solid #ddd; border-radius: 14px; max-width: 540px; width: 90vw; padding: 24px; box-shadow: 0 10px 40px rgba(0,0,0,0.15);">
                                <div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 18px; border-bottom: 1px solid #eee; padding-bottom: 12px;">
                                    <div>
                                        <h3 style="margin: 0; font-size: 18px; color: #1e1e24;">Edit Member: {{ $member->name }}</h3>
                                        <small style="color: #888;">Account ID #{{ $member->id }} · Registered {{ $member->created_at->format('M j, Y') }}</small>
                                    </div>
                                    <button type="button" onclick="document.getElementById('edit-user-modal-{{ $member->id }}').close()" style="border: 0; background: none; font-size: 22px; cursor: pointer; color: #888;">×</button>
                                </div>

                                <form method="POST" action="{{ route('admin.users.update', $member) }}">
                                    @csrf
                                    @method('PUT')

                                    <div style="display: grid; grid-template-columns: 1fr 1fr; gap: 14px; margin-bottom: 14px;">
                                        <label style="display: flex; flex-direction: column; gap: 5px; font-size: 12px; font-weight: 500;">
                                            Full name <span class="required-star">*</span>
                                            <input type="text" name="name" required maxlength="80" value="{{ old('name', $member->name) }}" style="min-height: 36px;">
                                        </label>

                                        <label style="display: flex; flex-direction: column; gap: 5px; font-size: 12px; font-weight: 500;">
                                            Email address <span class="required-star">*</span>
                                            <input type="email" name="email" required maxlength="255" value="{{ old('email', $member->email) }}" style="min-height: 36px;">
                                        </label>

                                        <label style="display: flex; flex-direction: column; gap: 5px; font-size: 12px; font-weight: 500;">
                                            System Role <span class="required-star">*</span>
                                            <select name="role" required style="min-height: 36px;" @disabled($member->is(auth()->user()))>
                                                <option value="user" @selected(old('role', $member->role) === 'user')>Creator (Standard)</option>
                                                <option value="admin" @selected(old('role', $member->role) === 'admin')>Administrator (Full Access)</option>
                                            </select>
                                            @if ($member->is(auth()->user()))
                                                <input type="hidden" name="role" value="admin">
                                            @endif
                                        </label>

                                        <label style="display: flex; flex-direction: column; gap: 5px; font-size: 12px; font-weight: 500;">
                                            Account Status <span class="required-star">*</span>
                                            <select name="status" required style="min-height: 36px;" @disabled($member->is(auth()->user()))>
                                                <option value="active" @selected(old('status', $member->status) === 'active')>Active (Can log in)</option>
                                                <option value="suspended" @selected(old('status', $member->status) === 'suspended')>Suspended (Blocked)</option>
                                            </select>
                                            @if ($member->is(auth()->user()))
                                                <input type="hidden" name="status" value="active">
                                            @endif
                                        </label>
                                    </div>

                                    <div style="display: flex; flex-direction: column; gap: 14px; margin-bottom: 14px;">
                                        <label style="display: flex; flex-direction: column; gap: 5px; font-size: 12px; font-weight: 500;">
                                            Website or Social URL <span class="optional-label">Optional</span>
                                            <input type="url" name="website" maxlength="255" value="{{ old('website', $member->website) }}" placeholder="https://x.com/username" style="min-height: 36px;">
                                        </label>

                                        <label style="display: flex; flex-direction: column; gap: 5px; font-size: 12px; font-weight: 500;">
                                            Bio / Creator Headline <span class="optional-label">Optional</span>
                                            <textarea name="bio" rows="2" maxlength="500" placeholder="Brief creator introduction…" style="min-height: 60px; resize: vertical;">{{ old('bio', $member->bio) }}</textarea>
                                        </label>

                                        <label style="display: flex; flex-direction: column; gap: 5px; font-size: 12px; font-weight: 500;">
                                            Set New Password <span class="optional-label">Leave blank to keep unchanged</span>
                                            <input type="password" name="password" minlength="8" placeholder="Enter new password (min. 8 characters)" style="min-height: 36px;">
                                        </label>
                                    </div>

                                    <div style="display: flex; justify-content: flex-end; gap: 10px; margin-top: 20px; border-top: 1px solid #eee; padding-top: 14px;">
                                        <button type="button" class="outline-button" onclick="document.getElementById('edit-user-modal-{{ $member->id }}').close()">Cancel</button>
                                        <button type="submit" class="primary-button">Save Member Settings <span>→</span></button>
                                    </div>
                                </form>
                            </dialog>
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
