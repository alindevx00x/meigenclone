@extends('layouts.app')

@section('title', $collection->name . ' — Moodboard')

@section('content')
<div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-10" x-data="{ copiedAll: false }">
    <!-- Breadcrumb -->
    <div class="flex items-center gap-2 text-xs text-zinc-500 mb-6">
        <a href="{{ route('prompts.index') }}" class="hover:text-zinc-300">Home</a>
        <span>/</span>
        <a href="{{ route('collections.index') }}" class="hover:text-zinc-300">Moodboards</a>
        <span>/</span>
        <span class="text-white truncate max-w-xs">{{ $collection->name }}</span>
    </div>

    <!-- Header Banner -->
    <div class="bg-zinc-900 border border-zinc-800/80 rounded-3xl p-6 sm:p-8 shadow-2xl mb-12 flex flex-col md:flex-row md:items-center justify-between gap-6">
        <div class="space-y-2">
            <div class="flex items-center gap-2">
                <span class="px-2.5 py-0.5 rounded-full text-[10px] font-semibold uppercase tracking-wider {{ $collection->is_public ? 'bg-emerald-500/10 text-emerald-400 border border-emerald-500/20' : 'bg-zinc-800 text-zinc-400' }}">
                    {{ $collection->is_public ? 'Public Board' : 'Private Board' }}
                </span>
                <span class="text-xs text-zinc-400">&bull; {{ $collection->prompts_count }} Saved Prompts</span>
            </div>
            <h1 class="text-3xl font-extrabold text-white tracking-tight">{{ $collection->name }}</h1>
            @if($collection->description)
            <p class="text-sm text-zinc-300 max-w-2xl leading-relaxed">{{ $collection->description }}</p>
            @endif
            <div class="text-xs text-zinc-400 pt-1">
                Curated by <a href="{{ route('creators.show', $collection->user) }}" class="text-violet-400 hover:text-violet-300 font-semibold underline decoration-zinc-700">{{ $collection->user->name }}</a>
            </div>
        </div>

        <div class="flex items-center gap-3">
            @if($collection->prompts->count() > 0)
            <button type="button" @click="
                const texts = @js($collection->prompts->pluck('prompt_text')->toArray());
                navigator.clipboard.writeText(texts.join('\n\n---\n\n'));
                copiedAll = true;
                setTimeout(() => copiedAll = false, 2500);
            " class="px-4 py-2.5 rounded-xl bg-zinc-800 hover:bg-zinc-700 text-white text-xs font-semibold transition flex items-center gap-1.5 shadow-sm">
                <svg class="w-4 h-4 text-violet-400" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 16H6a2 2 0 01-2-2V6a2 2 0 012-2h8a2 2 0 012 2v2m-6 12h8a2 2 0 002-2v-8a2 2 0 00-2-2h-8a2 2 0 00-2 2v8a2 2 0 002 2z"/></svg>
                <span x-text="copiedAll ? 'All Prompts Copied!' : 'Copy All Board Prompts'"></span>
            </button>
            @endif

            @if(auth()->id() === $collection->user_id)
            <form action="{{ route('collections.destroy', $collection) }}" method="POST" onsubmit="return confirm('Delete this moodboard?');">
                @csrf
                @method('DELETE')
                <button type="submit" class="px-4 py-2.5 rounded-xl bg-red-600/10 hover:bg-red-600/20 text-red-400 border border-red-500/20 text-xs font-semibold transition">
                    Delete Board
                </button>
            </form>
            @endif
        </div>
    </div>

    <!-- Prompts Masonry / Grid -->
    <div class="grid grid-cols-1 sm:grid-cols-2 md:grid-cols-3 lg:grid-cols-4 gap-6">
        @forelse($prompts as $prompt)
        <div class="bg-zinc-900 border border-zinc-800/80 rounded-2xl overflow-hidden shadow-lg group hover:border-violet-500/50 transition flex flex-col justify-between">
            <div>
                <a href="{{ route('prompts.show', $prompt->slug) }}" class="block aspect-video bg-zinc-950 overflow-hidden relative">
                    <img src="{{ $prompt->cover_url }}" alt="{{ $prompt->title }}" class="w-full h-full object-cover group-hover:scale-105 transition duration-300">
                </a>

                <div class="p-4 space-y-2">
                    <a href="{{ route('prompts.show', $prompt->slug) }}" class="block font-semibold text-white hover:text-violet-400 transition truncate text-sm">
                        {{ $prompt->title }}
                    </a>
                    <p class="text-xs text-zinc-400 line-clamp-3 leading-relaxed">{{ $prompt->prompt_text }}</p>
                </div>
            </div>

            <div class="px-4 pb-4 pt-2 border-t border-zinc-800/60 flex items-center justify-between text-xs" x-data="{ copiedItem: false }">
                <span class="text-zinc-500">{{ $prompt->model }}</span>

                <div class="flex items-center gap-3">
                    <button type="button" @click="
                        navigator.clipboard.writeText(@js($prompt->prompt_text));
                        copiedItem = true;
                        setTimeout(() => copiedItem = false, 2000);
                    " class="text-violet-400 hover:text-white transition font-medium">
                        <span x-text="copiedItem ? 'Copied' : 'Copy'"></span>
                    </button>

                    @if(auth()->id() === $collection->user_id)
                    <button type="button" @click="
                        fetch('/collections/{{ $collection->id }}/toggle', {
                            method: 'POST',
                            headers: { 'Content-Type': 'application/json', 'Accept': 'application/json', 'X-CSRF-TOKEN': '{{ csrf_token() }}' },
                            body: JSON.stringify({ prompt_id: {{ $prompt->id }} })
                        }).then(() => location.reload());
                    " class="text-red-400 hover:text-red-300 text-[11px]" title="Remove from board">
                        &times; Remove
                    </button>
                    @endif
                </div>
            </div>
        </div>
        @empty
        <div class="col-span-full text-center py-16 bg-zinc-900 border border-zinc-800 rounded-3xl">
            <p class="text-zinc-400 text-sm">This moodboard is currently empty. Explore the directory to save prompts here.</p>
        </div>
        @endforelse
    </div>

    <div class="mt-8">
        {{ $prompts->links() }}
    </div>
</div>
@endsection
