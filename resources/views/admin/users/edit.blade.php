@extends('layouts.app')

@section('title', 'Edit Member: ' . $user->name)
@section('eyebrow', 'MEIGEN ADMIN')
@section('toolbar-title', 'Member management')

@section('content')
<div class="workspace-page form-page">
    <a class="back-link" href="{{ route('admin.users.index') }}"><span>&larr;</span> Back to member management</a>

    <div class="page-heading form-heading">
        <div>
            <span class="section-kicker">MEMBER ADMINISTRATION &middot; ACCOUNT #{{ $user->id }}</span>
            <h1>Edit member: {{ $user->name }}</h1>
            <p>Manage account security, studio credits, profile information, and administrator privileges.</p>
        </div>
        <div style="display: flex; gap: 8px; align-items: center; margin-bottom: 2px;">
            @if (! $user->is(auth()->user()))
                <form method="POST" action="{{ route('admin.users.status', $user) }}" style="display: inline;">
                    @csrf
                    @method('PATCH')
                    @if ($user->status === 'suspended')
                        <button type="submit" class="outline-button" style="color: #24915d; border-color: #c8e6c9;">Activate account</button>
                    @else
                        <button type="submit" class="outline-button" style="color: #c62828; border-color: #ffcdd2;" onclick="return confirm('Suspend {{ $user->name }}? They will not be able to log in until unsuspended.')">Suspend account</button>
                    @endif
                </form>
                <form method="POST" action="{{ route('admin.users.destroy', $user) }}" style="display: inline;" onsubmit="return confirm('Permanently remove {{ $user->name }}? This cannot be undone.')">
                    @csrf
                    @method('DELETE')
                    <button type="submit" class="outline-button" style="color: #b45a65;">Delete account</button>
                </form>
            @endif
        </div>
    </div>

    <!-- Quick Stats Bar (Matching Dashboard & Workspace) -->
    <div class="stats-row user-stats" style="margin-bottom: 24px;">
        <div class="stat-card">
            <span class="stat-icon stat-violet">&lowast;</span>
            <span class="stat-label">Total prompts</span>
            <strong>{{ number_format($user->prompts_count) }}</strong>
            <small>Shared with community</small>
        </div>
        <div class="stat-card">
            <span class="stat-icon stat-pink">&hearts;</span>
            <span class="stat-label">Saved favorites</span>
            <strong>{{ number_format($user->favorites_count) }}</strong>
            <small>Personal collection</small>
        </div>
        <div class="stat-card">
            <span class="stat-icon {{ $user->status === 'active' ? 'stat-green' : 'stat-amber' }}">{{ $user->status === 'active' ? '&check;' : '&#9687;' }}</span>
            <span class="stat-label">Account status</span>
            <strong>{{ ucfirst($user->status) }}</strong>
            <small>{{ $user->isAdmin() ? 'Administrator' : 'Creator' }}</small>
        </div>
        <div class="stat-card">
            <span class="stat-icon stat-amber">&#9889;</span>
            <span class="stat-label">Studio credits</span>
            <strong>{{ number_format($user->credits) }}</strong>
            <small>Flux &amp; DALL-E balance</small>
        </div>
    </div>

    <!-- Member Edit Form Card -->
    <form class="prompt-form panel-card" method="POST" action="{{ route('admin.users.update', $user) }}">
        @csrf
        @method('PUT')

        <div class="form-grid">
            <label>
                Full name <span class="required-star">*</span>
                <input type="text" name="name" required maxlength="80" value="{{ old('name', $user->name) }}">
                @error('name') <small style="color: #c62828;">{{ $message }}</small> @enderror
            </label>

            <label>
                Email address <span class="required-star">*</span>
                <input type="email" name="email" required maxlength="255" value="{{ old('email', $user->email) }}">
                @error('email') <small style="color: #c62828;">{{ $message }}</small> @enderror
            </label>

            <label>
                System Role <span class="required-star">*</span>
                <select name="role" required @disabled($user->is(auth()->user()))>
                    <option value="user" @selected(old('role', $user->role) === 'user')>Creator (Standard)</option>
                    <option value="admin" @selected(old('role', $user->role) === 'admin')>Administrator (Full Studio Access)</option>
                </select>
                @if ($user->is(auth()->user()))
                    <input type="hidden" name="role" value="admin">
                    <small>You cannot remove your own administrator status.</small>
                @else
                    <small>Administrators have full clearance to manage studio settings and members.</small>
                @endif
                @error('role') <small style="color: #c62828;">{{ $message }}</small> @enderror
            </label>

            <label>
                Account Status <span class="required-star">*</span>
                <select name="status" required @disabled($user->is(auth()->user()))>
                    <option value="active" @selected(old('status', $user->status) === 'active')>Active (Can log in)</option>
                    <option value="suspended" @selected(old('status', $user->status) === 'suspended')>Suspended (Blocked)</option>
                </select>
                @if ($user->is(auth()->user()))
                    <input type="hidden" name="status" value="active">
                    <small>You cannot suspend your own account.</small>
                @else
                    <small>Suspended members are blocked from logging in or submitting prompts.</small>
                @endif
                @error('status') <small style="color: #c62828;">{{ $message }}</small> @enderror
            </label>

            <label class="wide-field">
                AI Generation Credits <span class="required-star">*</span>
                <input type="number" min="0" max="100000" name="credits" value="{{ old('credits', $user->credits) }}" placeholder="25">
                <small>Credits balance used to generate images via Flux and DALL-E 3 studio.</small>
                @error('credits') <small style="color: #c62828;">{{ $message }}</small> @enderror
            </label>

            <label class="wide-field">
                Website or Social URL <span class="optional-label">Optional</span>
                <input type="url" name="website" maxlength="255" value="{{ old('website', $user->website) }}" placeholder="https://x.com/username">
                @error('website') <small style="color: #c62828;">{{ $message }}</small> @enderror
            </label>

            <label class="wide-field">
                Bio / Creator Headline <span class="optional-label">Optional</span>
                <textarea name="bio" rows="3" maxlength="500" placeholder="Brief creator introduction, aesthetic specialization&hellip;">{{ old('bio', $user->bio) }}</textarea>
                @error('bio') <small style="color: #c62828;">{{ $message }}</small> @enderror
            </label>

            <label class="wide-field">
                Set New Password <span class="optional-label">Leave blank to keep unchanged</span>
                <input type="password" name="password" minlength="8" placeholder="Enter new password (min. 8 characters)">
                @error('password') <small style="color: #c62828;">{{ $message }}</small> @enderror
            </label>
        </div>

        <div class="form-footer">
            <span><i>&lowast;</i> Registered {{ $user->created_at->format('M j, Y') }} ({{ $user->created_at->diffForHumans() }})</span>
            <div>
                <a class="outline-button" href="{{ route('admin.users.index') }}">Cancel</a>
                <button class="primary-button" type="submit" name="action" value="save">Save changes <span>&rarr;</span></button>
                <button class="primary-button" type="submit" name="action" value="save_and_exit" style="background: #35343d;">Save &amp; Return <span>&rarr;</span></button>
            </div>
        </div>
    </form>

    <!-- Submissions by this Creator (Matching dashboard#favorites prompt grid) -->
    <section class="workspace-section" id="favorites" style="margin-top: 36px;">
        <div class="section-heading">
            <div>
                <span class="section-kicker">SUBMISSIONS BY {{ strtoupper($user->name) }}</span>
                <h2>Contributed prompts <span class="heading-count">{{ $user->prompts_count }}</span></h2>
            </div>
            @if ($user->prompts_count > 0)
                <a class="subtle-link" href="{{ route('prompts.index', ['q' => $user->name]) }}">View in gallery <span>&rarr;</span></a>
            @endif
        </div>

        @if ($recentPrompts->isEmpty())
            <div class="empty-panel">
                <span class="empty-icon">&lowast;</span>
                <div>
                    <strong>No submissions yet.</strong>
                    <p>This community member has not contributed any prompts to the directory.</p>
                </div>
            </div>
        @else
            <div class="prompt-grid dashboard-favorites">
                @foreach ($recentPrompts as $prompt)
                    @include('partials.prompt-card', ['prompt' => $prompt])
                @endforeach
            </div>
        @endif
    </section>
</div>
@endsection
