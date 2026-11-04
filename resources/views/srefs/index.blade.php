@extends('layouts.app')

@section('title', 'Midjourney Style Reference (SREF) Catalog — Visual Aesthetic Swatches')

@section('content')
<div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-10">
    <!-- Header Banner -->
    <div class="flex flex-col md:flex-row md:items-center justify-between gap-4 mb-8">
        <div>
            <div class="inline-flex items-center gap-2 px-3 py-1 rounded-full bg-pink-500/10 border border-pink-500/20 text-pink-400 text-xs font-semibold uppercase tracking-wider mb-2">
                <span>🧬</span> Aesthetic Style Reference Catalog
            </div>
            <h1 class="text-3xl font-extrabold text-white tracking-tight sm:text-4xl">Midjourney SREF Catalog</h1>
            <p class="mt-1 text-sm text-zinc-400">Discover consistent aesthetic codes, color palettes, and stylistic fingerprints for your Midjourney generations.</p>
        </div>

        <div class="flex items-center gap-3">
            <a href="{{ route('studio.remix') }}" class="px-5 py-2.5 rounded-2xl bg-zinc-900 border border-zinc-800 hover:border-zinc-700 text-white text-xs font-semibold transition flex items-center gap-2 shadow-sm">
                <span>🪄</span> Remix in Studio
            </a>
            <a href="{{ route('generations.index') }}" class="px-5 py-2.5 rounded-2xl bg-gradient-to-r from-violet-600 to-indigo-600 hover:from-violet-500 text-white text-xs font-bold transition flex items-center gap-2 shadow-lg shadow-violet-600/20">
                <span>✦</span> AI Generator
            </a>
        </div>
    </div>

    <!-- Search & Filters -->
    <div class="bg-zinc-900 border border-zinc-800/80 rounded-3xl p-5 mb-8 shadow-xl">
        <form action="{{ route('srefs.index') }}" method="GET" class="flex flex-col md:flex-row items-center gap-4">
            <div class="relative flex-1 w-full">
                <svg class="absolute left-4 top-3.5 w-4 h-4 text-zinc-500" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z"/></svg>
                <input type="text" name="q" value="{{ $searchQuery }}" placeholder="Search style by name, category, or code (e.g. 284910284)..." class="w-full bg-zinc-950/80 border border-zinc-800 rounded-2xl pl-11 pr-4 py-3 text-sm text-white placeholder-zinc-500 focus:outline-none focus:ring-2 focus:ring-violet-500/50">
            </div>

            <!-- Categories Dropdown -->
            <select name="category" onchange="this.form.submit()" class="w-full md:w-48 bg-zinc-950/80 border border-zinc-800 rounded-2xl px-4 py-3 text-sm text-zinc-300 focus:outline-none focus:ring-2 focus:ring-violet-500/50">
                <option value="">All Categories</option>
                @foreach($categories as $cat)
                <option value="{{ $cat }}" @selected($currentCategory === $cat)>{{ $cat }}</option>
                @endforeach
            </select>

            <!-- Sort -->
            <select name="sort" onchange="this.form.submit()" class="w-full md:w-44 bg-zinc-950/80 border border-zinc-800 rounded-2xl px-4 py-3 text-sm text-zinc-300 focus:outline-none focus:ring-2 focus:ring-violet-500/50">
                <option value="popular" @selected($currentSort === 'popular')>Most Popular</option>
                <option value="copies" @selected($currentSort === 'copies')>Most Copied</option>
                <option value="newest" @selected($currentSort === 'newest')>Newest Added</option>
            </select>

            <button type="submit" class="w-full md:w-auto px-6 py-3 rounded-2xl bg-violet-600 hover:bg-violet-500 text-white text-sm font-semibold transition">
                Search
            </button>
        </form>
    </div>

    <!-- SREF Grid -->
    <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-6">
        @forelse($srefs as $sref)
        <div class="bg-zinc-900 border border-zinc-800/80 rounded-3xl overflow-hidden shadow-xl hover:border-violet-500/50 transition flex flex-col group">
            <!-- Preview Image -->
            <a href="{{ route('srefs.show', $sref->code) }}" class="relative aspect-video overflow-hidden bg-zinc-950 block">
                @if($sref->preview_url)
                <img src="{{ $sref->preview_url }}" alt="{{ $sref->title }}" class="w-full h-full object-cover group-hover:scale-105 transition duration-500">
                @else
                <div class="w-full h-full bg-gradient-to-tr from-violet-900 to-indigo-900 flex items-center justify-center text-zinc-500 text-xs">Aesthetic Preview</div>
                @endif

                <div class="absolute top-3 left-3 px-2.5 py-1 rounded-xl bg-black/60 backdrop-blur-md border border-white/10 text-[11px] font-mono font-semibold text-white">
                    --sref {{ $sref->code }}
                </div>

                <div class="absolute top-3 right-3 px-2.5 py-1 rounded-xl bg-violet-600/80 backdrop-blur-md text-[10px] font-semibold text-white uppercase tracking-wider">
                    {{ $sref->category }}
                </div>
            </a>

            <!-- Card Content -->
            <div class="p-5 flex-1 flex flex-col justify-between space-y-4">
                <div>
                    <a href="{{ route('srefs.show', $sref->code) }}" class="block text-base font-bold text-white hover:text-violet-400 transition truncate">
                        {{ $sref->title }}
                    </a>
                    <p class="mt-1 text-xs text-zinc-400 line-clamp-2 leading-relaxed">
                        {{ $sref->description }}
                    </p>
                </div>

                <!-- Color Palette Swatches -->
                @if(!empty($sref->color_palette))
                <div>
                    <div class="text-[10px] text-zinc-500 font-semibold uppercase tracking-wider mb-1.5">Color Palette</div>
                    <div class="flex items-center gap-1.5">
                        @foreach($sref->color_palette as $hex)
                        <div class="w-6 h-6 rounded-lg border border-white/10 shadow-sm cursor-pointer transition hover:scale-110" style="background-color: {{ $hex }}" title="{{ $hex }}"></div>
                        @endforeach
                    </div>
                </div>
                @endif

                <!-- Footer & Action -->
                <div class="pt-2 border-t border-zinc-800/80 flex items-center justify-between" x-data="{ copied: false }">
                    <div class="flex items-center gap-3 text-xs text-zinc-500 font-mono">
                        <span title="Copies count">📋 {{ number_format($sref->copies_count) }}</span>
                        <span title="Likes">❤️ {{ number_format($sref->likes_count) }}</span>
                    </div>

                    <button type="button" @click="
                        navigator.clipboard.writeText('--sref {{ $sref->code }}');
                        copied = true;
                        fetch('/api/sref/{{ $sref->id }}/copy', { method: 'POST', headers: { 'X-CSRF-TOKEN': '{{ csrf_token() }}' } });
                        setTimeout(() => copied = false, 2000);
                    " class="px-3 py-1.5 rounded-xl bg-zinc-800 hover:bg-violet-600 text-white text-xs font-semibold transition flex items-center gap-1">
                        <span x-text="copied ? 'Copied!' : 'Copy Code'"></span>
                    </button>
                </div>
            </div>
        </div>
        @empty
        <div class="col-span-full text-center py-16 bg-zinc-900 border border-zinc-800 rounded-3xl">
            <p class="text-zinc-400 text-sm">No style reference codes found matching your criteria.</p>
        </div>
        @endforelse
    </div>

    <!-- Pagination -->
    <div class="mt-10">
        {{ $srefs->links() }}
    </div>
</div>
@endsection
