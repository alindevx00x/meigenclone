@extends('layouts.app')

@section('title', 'Community Moodboards & Prompt Collections')
@section('eyebrow', 'CURATED MOODBOARDS')
@section('toolbar-title', 'Moodboards')

@section('content')
<div class="workspace-page" x-data="{ createModal: false }">
    <!-- Page Header (Matching Dashboard & Workspace) -->
    <div class="page-heading">
        <div>
            <span class="section-kicker">CURATED BOARDS &amp; COLLECTIONS</span>
            <h1>Moodboards &amp; Collections</h1>
            <p>Organize creative inspiration, save thematic prompts into moodboards, and share with your team.</p>
        </div>
        <div>
            @auth
            <button type="button" @click="createModal = true" class="primary-button">
                <span>＋</span> New Moodboard
            </button>
            @else
            <a href="{{ route('login') }}" class="outline-button">
                Sign In to Create Moodboards
            </a>
            @endauth
        </div>
    </div>

    <!-- Navigation Tabs -->
    <div class="panel-card" style="padding: 0 16px; margin-bottom: 24px;">
        <div class="admin-filter-tabs" style="margin: 0; border-bottom: 0;">
            <a href="{{ route('collections.index', ['tab' => 'explore']) }}" class="{{ $currentTab === 'explore' ? 'is-active' : '' }}">
                Explore Community Boards
            </a>
            @auth
            <a href="{{ route('collections.index', ['tab' => 'mine']) }}" class="{{ $currentTab === 'mine' ? 'is-active' : '' }}">
                My Moodboards
            </a>
            @endauth
        </div>
    </div>

    <!-- Moodboard Grid -->
    <div style="display: grid; grid-template-columns: repeat(auto-fill, minmax(300px, 1fr)); gap: 20px;">
        @forelse($collections as $col)
        <div class="panel-card" style="padding: 0; overflow: hidden; display: flex; flex-direction: column;">
            <!-- Cover preview -->
            <a href="{{ route('collections.show', $col->slug) }}" style="display: block; aspect-ratio: 16/9; overflow: hidden; background: #f0f0f2; position: relative;">
                @if($col->cover_url)
                <img src="{{ $col->cover_url }}" alt="{{ $col->name }}" style="width: 100%; height: 100%; object-fit: cover;">
                @else
                <div style="width: 100%; height: 100%; background: linear-gradient(135deg, #7958ee, #6366f1); display: flex; align-items: center; justify-content: center; color: #fff; font-size: 22px;">📌</div>
                @endif

                <span style="position: absolute; bottom: 10px; right: 10px; padding: 4px 10px; border-radius: 20px; font-size: 11px; font-weight: 600; background: rgba(0,0,0,0.7); color: #fff; backdrop-blur: 4px;">
                    {{ $col->prompts_count }} Prompts
                </span>
            </a>

            <!-- Card Body -->
            <div style="padding: 16px; flex: 1; display: flex; flex-direction: column; justify-content: space-between;">
                <div>
                    <h3 style="margin: 0 0 6px; font-size: 15px; font-weight: 600; color: #222228;">
                        <a href="{{ route('collections.show', $col->slug) }}" style="color: inherit; text-decoration: none;">{{ $col->name }}</a>
                    </h3>
                    <p style="margin: 0 0 14px; font-size: 12px; color: #777; line-height: 1.5;">{{ Str::limit($col->description, 85) ?: 'No description provided.' }}</p>
                </div>

                <div style="display: flex; align-items: center; justify-content: space-between; border-top: 1px solid #f0f0f2; padding-top: 12px;">
                    <div style="font-size: 11px; color: #888;">
                        By <a href="{{ route('creators.show', $col->user) }}" style="color: #222228; font-weight: 600; text-decoration: none;">{{ $col->user->name }}</a>
                    </div>

                    <a href="{{ route('collections.show', $col->slug) }}" class="outline-button" style="min-height: 30px; padding: 0 10px; font-size: 11px;">
                        Open Board &rarr;
                    </a>
                </div>
            </div>
        </div>
        @empty
        <div class="empty-panel" style="grid-column: 1 / -1;">
            <span class="empty-icon">📌</span>
            <div>
                <strong>No moodboards found.</strong>
                <p>Create your first collection to organize and save inspiration prompts.</p>
            </div>
        </div>
        @endforelse
    </div>

    <!-- Create Modal Dialog -->
    <div x-show="createModal" x-cloak style="display: none;" class="fixed inset-0 z-50 flex items-center justify-center p-4 bg-black/80 backdrop-blur-sm">
        <div @click.away="createModal = false" class="panel-card" style="max-width: 440px; width: 90vw; padding: 24px; box-shadow: 0 20px 50px rgba(0,0,0,0.3); border-radius: 16px;">
            <div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 16px; border-bottom: 1px solid #eee; padding-bottom: 12px;">
                <h3 style="margin: 0; font-size: 16px; font-weight: 600; color: #222228;">Create New Moodboard</h3>
                <button type="button" @click="createModal = false" style="border: 0; background: none; font-size: 20px; cursor: pointer; color: #888;">&times;</button>
            </div>

            <form action="{{ route('collections.store') }}" method="POST" style="display: flex; flex-direction: column; gap: 14px;">
                @csrf
                <div>
                    <label style="display: block; font-size: 12px; font-weight: 600; color: #222228; margin-bottom: 6px;">Board Name <span class="required-star">*</span></label>
                    <input type="text" name="name" placeholder="e.g. Cyberpunk Game Assets 2026" required style="width: 100%; min-height: 38px; border: 1px solid #e6e6eb; border-radius: 8px; padding: 0 12px; font-size: 13px; outline: none; box-sizing: border-box; background: #fff;">
                </div>

                <div>
                    <label style="display: block; font-size: 12px; font-weight: 600; color: #222228; margin-bottom: 6px;">Description (Optional)</label>
                    <textarea name="description" rows="3" placeholder="Brief note on this collection's visual theme..." style="width: 100%; min-height: 70px; border: 1px solid #e6e6eb; border-radius: 8px; padding: 8px 12px; font-size: 12px; outline: none; resize: vertical; box-sizing: border-box; background: #fff;"></textarea>
                </div>

                <div style="display: flex; align-items: center; gap: 8px;">
                    <input type="checkbox" name="is_public" value="1" id="is_public" checked style="accent-color: #7958ee;">
                    <label for="is_public" style="font-size: 12px; color: #444; cursor: pointer;">Public (visible in community exploration)</label>
                </div>

                <div style="display: flex; justify-content: flex-end; gap: 8px; margin-top: 8px; border-top: 1px solid #eee; padding-top: 14px;">
                    <button type="button" @click="createModal = false" class="outline-button">Cancel</button>
                    <button type="submit" class="primary-button">Create Moodboard &rarr;</button>
                </div>
            </form>
        </div>
    </div>
</div>
@endsection
