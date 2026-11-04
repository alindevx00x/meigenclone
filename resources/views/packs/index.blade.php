@extends('layouts.app')

@section('title', 'Curated Prompt Packs — Pre-Packaged Bundles for Creators & Marketers')

@section('content')
<div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-10">
    <!-- Header -->
    <div class="flex flex-col md:flex-row md:items-center justify-between gap-4 mb-8">
        <div>
            <div class="inline-flex items-center gap-2 px-3 py-1 rounded-full bg-amber-500/10 border border-amber-500/20 text-amber-400 text-xs font-semibold uppercase tracking-wider mb-2">
                <span>📦</span> Curated Bundles & Market
            </div>
            <h1 class="text-3xl font-extrabold text-white tracking-tight sm:text-4xl">Curated Prompt Packs</h1>
            <p class="mt-1 text-sm text-zinc-400">Download high-converting bundles of thematic prompts with instant 1-click JSON exports.</p>
        </div>

        <div class="flex items-center gap-3">
            @auth
            <a href="{{ route('packs.create') }}" class="px-5 py-2.5 rounded-2xl bg-gradient-to-r from-violet-600 to-indigo-600 hover:from-violet-500 text-white text-xs font-bold transition flex items-center gap-2 shadow-lg shadow-violet-600/20">
                <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"/></svg>
                Create Prompt Pack
            </a>
            @else
            <a href="{{ route('login') }}" class="px-5 py-2.5 rounded-2xl bg-zinc-900 border border-zinc-800 hover:border-zinc-700 text-white text-xs font-semibold transition">
                Sign In to Publish Packs
            </a>
            @endauth
        </div>
    </div>

    <!-- Filter Bar -->
    <div class="flex flex-col sm:flex-row items-center justify-between gap-4 bg-zinc-900 border border-zinc-800/80 rounded-3xl p-4 mb-8 shadow-xl">
        <div class="flex items-center gap-2 w-full sm:w-auto">
            <a href="{{ route('packs.index') }}" class="px-4 py-2 rounded-xl text-xs font-semibold transition {{ !$isFree ? 'bg-violet-600 text-white' : 'bg-zinc-950 text-zinc-400 hover:text-white' }}">
                All Packs
            </a>
            <a href="{{ route('packs.index', ['free' => 1]) }}" class="px-4 py-2 rounded-xl text-xs font-semibold transition {{ $isFree ? 'bg-violet-600 text-white' : 'bg-zinc-950 text-zinc-400 hover:text-white' }}">
                Free Downloads
            </a>
        </div>

        <form action="{{ route('packs.index') }}" method="GET" class="w-full sm:w-80">
            <div class="relative">
                <svg class="absolute left-3.5 top-3 w-4 h-4 text-zinc-500" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z"/></svg>
                <input type="text" name="q" value="{{ $searchQuery }}" placeholder="Search bundles..." class="w-full bg-zinc-950/80 border border-zinc-800 rounded-xl pl-10 pr-4 py-2 text-xs text-white placeholder-zinc-500 focus:outline-none focus:ring-2 focus:ring-violet-500/50">
            </div>
        </form>
    </div>

    <!-- Packs Grid -->
    <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-6">
        @forelse($packs as $pack)
        <div class="bg-zinc-900 border border-zinc-800/80 rounded-3xl overflow-hidden shadow-xl hover:border-violet-500/50 transition flex flex-col group">
            <!-- Cover -->
            <a href="{{ route('packs.show', $pack->slug) }}" class="relative aspect-video overflow-hidden bg-zinc-950 block">
                @if($pack->cover_url)
                <img src="{{ $pack->cover_url }}" alt="{{ $pack->title }}" class="w-full h-full object-cover group-hover:scale-105 transition duration-500">
                @else
                <div class="w-full h-full bg-gradient-to-tr from-amber-900 to-violet-900 flex items-center justify-center text-zinc-500">Bundle Preview</div>
                @endif

                <!-- Badges -->
                <div class="absolute top-3 left-3 px-3 py-1 rounded-full bg-black/70 backdrop-blur-md border border-white/10 text-xs font-bold {{ $pack->is_free ? 'text-emerald-400' : 'text-amber-400' }}">
                    {{ $pack->is_free ? 'FREE' : '$' . number_format($pack->price, 2) }}
                </div>

                <div class="absolute top-3 right-3 px-2.5 py-1 rounded-full bg-zinc-900/80 backdrop-blur-md text-[11px] font-semibold text-white">
                    {{ $pack->prompt_count }} Prompts
                </div>
            </a>

            <!-- Content -->
            <div class="p-6 flex-1 flex flex-col justify-between space-y-4">
                <div>
                    <a href="{{ route('packs.show', $pack->slug) }}" class="block text-lg font-bold text-white hover:text-violet-400 transition">
                        {{ $pack->title }}
                    </a>
                    <p class="mt-2 text-xs text-zinc-400 line-clamp-2 leading-relaxed">
                        {{ $pack->description }}
                    </p>
                </div>

                <div class="pt-4 border-t border-zinc-800/80 flex items-center justify-between text-xs">
                    <div class="text-zinc-500">
                        <span>By <strong class="text-zinc-300">{{ $pack->user->name }}</strong></span>
                        <span class="mx-1.5">&bull;</span>
                        <span>{{ number_format($pack->downloads_count) }} downloads</span>
                    </div>

                    <a href="{{ route('packs.show', $pack->slug) }}" class="px-3.5 py-1.5 rounded-xl bg-violet-600/10 hover:bg-violet-600 border border-violet-500/20 text-violet-300 hover:text-white font-semibold transition">
                        View Pack &rarr;
                    </a>
                </div>
            </div>
        </div>
        @empty
        <div class="col-span-full text-center py-16 bg-zinc-900 border border-zinc-800 rounded-3xl">
            <p class="text-zinc-400 text-sm">No prompt packs found.</p>
        </div>
        @endforelse
    </div>

    <!-- Pagination -->
    <div class="mt-10">
        {{ $packs->links() }}
    </div>
</div>
@endsection
