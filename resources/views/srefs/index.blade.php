@extends('layouts.app')

@section('title', 'Midjourney Style Reference (SREF) Catalog — Visual Aesthetic Swatches')
@section('eyebrow', 'STYLE DIRECTORY')
@section('toolbar-title', 'SREF Catalog')

@section('content')
<div class="workspace-page">
    <!-- Page Header (Matching Dashboard & Workspace) -->
    <div class="page-heading">
        <div>
            <span class="section-kicker">AESTHETIC STYLE REFERENCE CATALOG</span>
            <h1>Midjourney SREF Catalog</h1>
            <p>Discover consistent aesthetic codes, color palettes, and stylistic fingerprints for your Midjourney generations.</p>
        </div>
        <div style="display: flex; gap: 8px; align-items: center;">
            <a href="{{ route('studio.remix') }}" class="primary-button">
                <span>🪄</span> Remix in Studio
            </a>
        </div>
    </div>

    <!-- Search & Filter Controls Card -->
    <div class="panel-card" style="padding: 16px; margin-bottom: 24px;">
        <form action="{{ route('srefs.index') }}" method="GET" style="display: flex; flex-wrap: wrap; gap: 10px; align-items: center; justify-content: space-between;">
            <div style="display: flex; flex-wrap: wrap; gap: 10px; align-items: center; flex: 1 1 320px;">
                <input type="search" name="q" value="{{ $searchQuery }}" placeholder="Search style name, category, or code (e.g. 284910284)..." style="flex: 1 1 240px; min-height: 38px; border: 1px solid #e6e6eb; border-radius: 8px; padding: 0 12px; font-size: 13px; outline: none; background: #fff;" aria-label="Search SREF styles">

                <select name="category" onchange="this.form.submit()" style="min-height: 38px; width: 160px; border: 1px solid #e6e6eb; border-radius: 8px; padding: 0 10px; font-size: 12px; outline: none; background: #fff;">
                    <option value="">All Categories</option>
                    @foreach($categories as $cat)
                    <option value="{{ $cat }}" @selected($currentCategory === $cat)>{{ $cat }}</option>
                    @endforeach
                </select>

                <select name="sort" onchange="this.form.submit()" style="min-height: 38px; width: 140px; border: 1px solid #e6e6eb; border-radius: 8px; padding: 0 10px; font-size: 12px; outline: none; background: #fff;">
                    <option value="popular" @selected($currentSort === 'popular')>Most Popular</option>
                    <option value="copies" @selected($currentSort === 'copies')>Most Copied</option>
                    <option value="newest" @selected($currentSort === 'newest')>Newest Added</option>
                </select>

                <button class="primary-button" type="submit" style="min-height: 38px; padding: 0 16px;">Search <span>&rarr;</span></button>
                @if($searchQuery || $currentCategory)
                <a class="outline-button" href="{{ route('srefs.index') }}" style="min-height: 38px; display: inline-flex; align-items: center;">Clear</a>
                @endif
            </div>
        </form>
    </div>

    <!-- SREF Grid (Matching Prompt Card Aesthetic) -->
    <div style="display: grid; grid-template-columns: repeat(auto-fill, minmax(260px, 1fr)); gap: 18px;">
        @forelse($srefs as $sref)
        <div class="panel-card" style="padding: 0; overflow: hidden; display: flex; flex-direction: column; transition: transform 0.16s, box-shadow 0.16s;">
            <!-- Visual Swatch Preview -->
            <div style="aspect-ratio: 16/10; overflow: hidden; background: #eee; position: relative;">
                @if($sref->preview_url)
                <img src="{{ $sref->preview_url }}" alt="{{ $sref->title }}" style="width: 100%; height: 100%; object-fit: cover;">
                @else
                <div style="width: 100%; height: 100%; background: linear-gradient(135deg, #7958ee, #f39c12); display: flex; align-items: center; justify-content: center; color: #fff; font-size: 20px;">✦</div>
                @endif
                <span class="role-pill" style="position: absolute; top: 10px; right: 10px; background: rgba(0,0,0,0.65); color: #fff; font-size: 10px; backdrop-blur: 4px;">{{ $sref->category ?? 'Aesthetic' }}</span>
            </div>

            <!-- Card Body -->
            <div style="padding: 14px 16px; flex: 1; display: flex; flex-direction: column; justify-content: space-between;">
                <div>
                    <h3 style="margin: 0 0 4px; font-size: 14px; font-weight: 600; color: #222228;">{{ $sref->title }}</h3>
                    <p style="margin: 0 0 12px; font-size: 11.5px; color: #777; line-height: 1.5;">{{ Str::limit($sref->description, 70) }}</p>
                </div>

                <div style="display: flex; align-items: center; justify-content: space-between; border-top: 1px solid #f0f0f2; padding-top: 10px;">
                    <div style="font-family: monospace; font-size: 11px; color: #765bd7; font-weight: 600;">
                        --sref {{ $sref->code }}
                    </div>
                    <div style="display: flex; gap: 6px; align-items: center;">
                        <button type="button" data-copy="--sref {{ $sref->code }}" class="outline-button" style="padding: 4px 10px; font-size: 10.5px; min-height: 28px;">
                            Copy <span>⧉</span>
                        </button>
                        <a href="{{ route('studio.remix', ['from' => $sref->code]) }}" class="outline-button" title="Remix with this SREF" style="padding: 4px 8px; font-size: 11px; min-height: 28px; text-decoration: none;">
                            🪄
                        </a>
                    </div>
                </div>
            </div>
        </div>
        @empty
        <div class="empty-panel" style="grid-column: 1 / -1;">
            <span class="empty-icon">🧬</span>
            <div>
                <strong>No SREF style codes found.</strong>
                <p>Try searching for a different keyword or category.</p>
            </div>
        </div>
        @endforelse
    </div>

    <div class="pagination-wrap" style="margin-top: 24px;">
        {{ $srefs->links() }}
    </div>
</div>
@endsection
