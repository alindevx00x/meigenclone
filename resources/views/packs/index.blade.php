@extends('layouts.app')

@section('title', 'Curated Prompt Packs — Pre-Packaged Bundles for Creators')
@section('eyebrow', 'MEIGEN MARKETPLACE')
@section('toolbar-title', 'Prompt Packs')

@section('content')
<div class="workspace-page">
    <!-- Page Header (Matching Dashboard & Workspace) -->
    <div class="page-heading">
        <div>
            <span class="section-kicker">CURATED BUNDLES &amp; MARKET</span>
            <h1>Curated Prompt Packs</h1>
            <p>Download high-converting bundles of thematic prompts with instant 1-click JSON exports.</p>
        </div>
        <div style="display: flex; gap: 8px; align-items: center;">
            @auth
            <a href="{{ route('packs.create') }}" class="primary-button">
                <span>＋</span> Create Prompt Pack
            </a>
            @else
            <a href="{{ route('login') }}" class="outline-button">
                Sign In to Publish Packs
            </a>
            @endauth
        </div>
    </div>

    <!-- Filter & Search Bar -->
    <div class="panel-card" style="padding: 14px 18px; margin-bottom: 24px; display: flex; flex-wrap: wrap; justify-content: space-between; align-items: center; gap: 14px;">
        <div class="admin-filter-tabs" style="margin: 0; border-bottom: 0;">
            <a href="{{ route('packs.index') }}" class="{{ !$isFree ? 'is-active' : '' }}">
                All Bundles
            </a>
            <a href="{{ route('packs.index', ['free' => 1]) }}" class="{{ $isFree ? 'is-active' : '' }}">
                Free Downloads
            </a>
        </div>

        <form action="{{ route('packs.index') }}" method="GET" style="display: flex; gap: 8px; align-items: center;">
            <input type="search" name="q" value="{{ $searchQuery }}" placeholder="Search bundles..." style="min-height: 36px; border: 1px solid #e6e6eb; border-radius: 8px; padding: 0 12px; font-size: 12.5px; outline: none; background: #fff;" aria-label="Search prompt packs">
            <button class="primary-button" type="submit" style="min-height: 36px; padding: 0 14px; font-size: 11px;">Search</button>
        </form>
    </div>

    <!-- Packs Grid -->
    <div style="display: grid; grid-template-columns: repeat(auto-fill, minmax(300px, 1fr)); gap: 20px;">
        @forelse($packs as $pack)
        <div class="panel-card" style="padding: 0; overflow: hidden; display: flex; flex-direction: column;">
            <!-- Cover preview -->
            <a href="{{ route('packs.show', $pack->slug) }}" style="display: block; aspect-ratio: 16/9; overflow: hidden; background: #f0f0f2; position: relative;">
                @if($pack->cover_url)
                <img src="{{ $pack->cover_url }}" alt="{{ $pack->title }}" style="width: 100%; height: 100%; object-fit: cover;">
                @else
                <div style="width: 100%; height: 100%; background: linear-gradient(135deg, #a86400, #7958ee); display: flex; align-items: center; justify-content: center; color: #fff; font-size: 22px;">📦</div>
                @endif

                <span style="position: absolute; top: 12px; right: 12px; padding: 4px 10px; border-radius: 20px; font-size: 11px; font-weight: 700; background: rgba(0,0,0,0.7); color: #fff; backdrop-blur: 4px;">
                    {{ $pack->is_free ? 'FREE' : '$' . number_format($pack->price, 2) }}
                </span>
            </a>

            <!-- Card Body -->
            <div style="padding: 16px; flex: 1; display: flex; flex-direction: column; justify-content: space-between;">
                <div>
                    <h3 style="margin: 0 0 6px; font-size: 15px; font-weight: 600; color: #222228;">
                        <a href="{{ route('packs.show', $pack->slug) }}" style="color: inherit; text-decoration: none;">{{ $pack->title }}</a>
                    </h3>
                    <p style="margin: 0 0 14px; font-size: 12px; color: #777; line-height: 1.5;">{{ Str::limit($pack->description, 95) }}</p>
                </div>

                <div style="display: flex; align-items: center; justify-content: space-between; border-top: 1px solid #f0f0f2; padding-top: 12px;">
                    <div style="font-size: 11.5px; color: #888;">
                        <strong style="color: #222228;">{{ $pack->prompts_count }}</strong> prompts &bull; {{ $pack->downloads_count }} downloads
                    </div>

                    <a href="{{ route('packs.show', $pack->slug) }}" class="primary-button" style="min-height: 32px; padding: 0 12px; font-size: 11px;">
                        View Pack &rarr;
                    </a>
                </div>
            </div>
        </div>
        @empty
        <div class="empty-panel" style="grid-column: 1 / -1;">
            <span class="empty-icon">📦</span>
            <div>
                <strong>No prompt packs found.</strong>
                <p>Be the first to create and publish a curated bundle of prompts.</p>
            </div>
            @auth
            <a class="outline-button" href="{{ route('packs.create') }}">Create Pack &rarr;</a>
            @endauth
        </div>
        @endforelse
    </div>

    <div class="pagination-wrap" style="margin-top: 24px;">
        {{ $packs->links() }}
    </div>
</div>
@endsection
