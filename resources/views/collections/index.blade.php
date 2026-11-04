@extends('layouts.app')

@section('title', 'Community Moodboards & Prompt Collections')

@section('content')
<div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-10" x-data="{ createModal: false }">
    <!-- Header -->
    <div class="flex flex-col md:flex-row md:items-center justify-between gap-4 mb-8">
        <div>
            <div class="inline-flex items-center gap-2 px-3 py-1 rounded-full bg-blue-500/10 border border-blue-500/20 text-blue-400 text-xs font-semibold uppercase tracking-wider mb-2">
                <span>📌</span> Moodboards & Curation
            </div>
            <h1 class="text-3xl font-extrabold text-white tracking-tight sm:text-4xl">Moodboards & Collections</h1>
            <p class="mt-1 text-sm text-zinc-400">Organize creative inspiration, save thematic prompts into moodboards, and share with your team.</p>
        </div>

        <div class="flex items-center gap-3">
            @auth
            <button type="button" @click="createModal = true" class="px-5 py-2.5 rounded-2xl bg-gradient-to-r from-violet-600 to-indigo-600 hover:from-violet-500 text-white text-xs font-bold transition flex items-center gap-2 shadow-lg shadow-violet-600/20">
                <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"/></svg>
                New Moodboard
            </button>
            @else
            <a href="{{ route('login') }}" class="px-5 py-2.5 rounded-2xl bg-zinc-900 border border-zinc-800 hover:border-zinc-700 text-white text-xs font-semibold transition">
                Sign In to Create Moodboards
            </a>
            @endauth
        </div>
    </div>

    <!-- Navigation Tabs -->
    <div class="flex items-center gap-2 border-b border-zinc-800 pb-4 mb-8">
        <a href="{{ route('collections.index', ['tab' => 'explore']) }}" class="px-4 py-2 rounded-xl text-xs font-semibold transition {{ $currentTab === 'explore' ? 'bg-zinc-800 text-white' : 'text-zinc-400 hover:text-white' }}">
            Explore Community Boards
        </a>
        @auth
        <a href="{{ route('collections.index', ['tab' => 'mine']) }}" class="px-4 py-2 rounded-xl text-xs font-semibold transition {{ $currentTab === 'mine' ? 'bg-zinc-800 text-white' : 'text-zinc-400 hover:text-white' }}">
            My Moodboards
        </a>
        @endauth
    </div>

    <!-- Moodboard Grid -->
    <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-3 gap-6">
        @forelse($collections as $col)
        <div class="bg-zinc-900 border border-zinc-800/80 rounded-3xl overflow-hidden shadow-xl hover:border-violet-500/50 transition flex flex-col group">
            <!-- Cover image collage preview -->
            <a href="{{ route('collections.show', $col->slug) }}" class="relative aspect-video overflow-hidden bg-zinc-950 block">
                @if($col->cover_url)
                <img src="{{ $col->cover_url }}" alt="{{ $col->name }}" class="w-full h-full object-cover group-hover:scale-105 transition duration-500">
                @else
                <div class="w-full h-full bg-gradient-to-tr from-blue-900 to-violet-900 flex items-center justify-center text-zinc-500">Empty Board</div>
                @endif

                <div class="absolute bottom-3 right-3 px-2.5 py-1 rounded-xl bg-black/70 backdrop-blur-md text-[11px] font-semibold text-white">
                    {{ $col->prompts_count }} Prompts
                </div>
            </a>

            <!-- Content -->
            <div class="p-6 flex-1 flex flex-col justify-between space-y-4">
                <div>
                    <a href="{{ route('collections.show', $col->slug) }}" class="block text-lg font-bold text-white hover:text-violet-400 transition truncate">
                        {{ $col->name }}
                    </a>
                    @if($col->description)
                    <p class="mt-1 text-xs text-zinc-400 line-clamp-2 leading-relaxed">
                        {{ $col->description }}
                    </p>
                    @endif
                </div>

                <div class="pt-3 border-t border-zinc-800/80 flex items-center justify-between text-xs">
                    <div class="flex items-center gap-2 text-zinc-500">
                        <div class="w-5 h-5 rounded-full bg-zinc-800 flex items-center justify-center text-[10px] text-zinc-300 font-bold">
                            {{ substr($col->user->name, 0, 1) }}
                        </div>
                        <span class="text-zinc-400 font-medium truncate max-w-[120px]">{{ $col->user->name }}</span>
                    </div>

                    <a href="{{ route('collections.show', $col->slug) }}" class="text-violet-400 hover:text-white font-semibold transition">
                        View Board &rarr;
                    </a>
                </div>
            </div>
        </div>
        @empty
        <div class="col-span-full text-center py-16 bg-zinc-900 border border-zinc-800 rounded-3xl">
            <p class="text-zinc-400 text-sm">No moodboards found in this section.</p>
        </div>
        @endforelse
    </div>

    <!-- Create Modal -->
    <div x-show="createModal" x-cloak class="fixed inset-0 z-50 flex items-center justify-center p-4 bg-black/80 backdrop-blur-sm">
        <div @click.away="createModal = false" class="bg-zinc-900 border border-zinc-800 rounded-3xl p-6 sm:p-7 max-w-md w-full shadow-2xl space-y-5">
            <div class="flex items-center justify-between">
                <h3 class="text-lg font-bold text-white">Create New Moodboard</h3>
                <button type="button" @click="createModal = false" class="text-zinc-500 hover:text-white">&times;</button>
            </div>

            <form action="{{ route('collections.store') }}" method="POST" class="space-y-4">
                @csrf
                <div>
                    <label class="block text-xs font-semibold text-zinc-300 mb-1">Board Name</label>
                    <input type="text" name="name" placeholder="e.g. Cyberpunk Game Assets 2026" required class="w-full bg-zinc-950 border border-zinc-800 rounded-xl px-3 py-2.5 text-sm text-white placeholder-zinc-500 focus:outline-none focus:ring-2 focus:ring-violet-500">
                </div>

                <div>
                    <label class="block text-xs font-semibold text-zinc-300 mb-1">Description (Optional)</label>
                    <textarea name="description" rows="3" placeholder="Brief note on this collection's visual theme..." class="w-full bg-zinc-950 border border-zinc-800 rounded-xl px-3 py-2 text-xs text-white placeholder-zinc-500 focus:outline-none focus:ring-2 focus:ring-violet-500 resize-none"></textarea>
                </div>

                <div class="flex items-center gap-2">
                    <input type="checkbox" name="is_public" value="1" id="is_public" checked class="rounded bg-zinc-950 border-zinc-800 text-violet-600 focus:ring-violet-500">
                    <label for="is_public" class="text-xs text-zinc-400 cursor-pointer">Make this moodboard public to community</label>
                </div>

                <div class="flex justify-end gap-2 pt-2">
                    <button type="button" @click="createModal = false" class="px-4 py-2 rounded-xl bg-zinc-800 hover:bg-zinc-700 text-white text-xs font-semibold transition">Cancel</button>
                    <button type="submit" class="px-5 py-2 rounded-xl bg-violet-600 hover:bg-violet-500 text-white text-xs font-bold transition shadow-lg shadow-violet-600/20">Create Board</button>
                </div>
            </form>
        </div>
    </div>
</div>
@endsection
