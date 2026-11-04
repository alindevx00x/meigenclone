@extends('layouts.app')

@section('title', $sref->title . ' — Midjourney SREF --sref ' . $sref->code)

@section('content')
<div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-10" x-data="{ copied: false, liked: false, likes: {{ $sref->likes_count }} }">
    <!-- Breadcrumb -->
    <div class="flex items-center gap-2 text-xs text-zinc-500 mb-6">
        <a href="{{ route('prompts.index') }}" class="hover:text-zinc-300">Home</a>
        <span>/</span>
        <a href="{{ route('srefs.index') }}" class="hover:text-zinc-300">SREF Catalog</a>
        <span>/</span>
        <span class="text-white">{{ $sref->code }}</span>
    </div>

    <!-- Hero Card -->
    <div class="bg-zinc-900 border border-zinc-800/80 rounded-3xl p-6 sm:p-8 shadow-2xl mb-12">
        <div class="grid grid-cols-1 lg:grid-cols-12 gap-8 items-center">
            <!-- Left Preview -->
            <div class="lg:col-span-5 aspect-square rounded-2xl overflow-hidden bg-zinc-950 border border-zinc-800 shadow-inner">
                @if($sref->preview_url)
                <img src="{{ $sref->preview_url }}" alt="{{ $sref->title }}" class="w-full h-full object-cover">
                @else
                <div class="w-full h-full bg-gradient-to-tr from-violet-900 to-indigo-900 flex items-center justify-center text-zinc-500">Aesthetic Preview</div>
                @endif
            </div>

            <!-- Right Details -->
            <div class="lg:col-span-7 space-y-6">
                <div>
                    <div class="inline-flex items-center gap-2 px-3 py-1 rounded-full bg-violet-500/10 border border-violet-500/20 text-violet-400 text-xs font-semibold uppercase tracking-wider mb-3">
                        {{ $sref->category }} Aesthetic
                    </div>
                    <h1 class="text-3xl font-extrabold text-white tracking-tight sm:text-4xl">{{ $sref->title }}</h1>
                    <p class="mt-2 text-sm text-zinc-300 leading-relaxed">{{ $sref->description }}</p>
                </div>

                <!-- Color Palette -->
                @if(!empty($sref->color_palette))
                <div>
                    <div class="text-xs text-zinc-400 font-semibold uppercase tracking-wider mb-2">Dominant Color Fingerprint</div>
                    <div class="flex items-center gap-3">
                        @foreach($sref->color_palette as $hex)
                        <div class="flex items-center gap-2 bg-zinc-950 border border-zinc-800 rounded-xl px-3 py-1.5 shadow-sm">
                            <span class="w-4 h-4 rounded-md shadow-inner" style="background-color: {{ $hex }}"></span>
                            <span class="text-xs font-mono text-zinc-300">{{ $hex }}</span>
                        </div>
                        @endforeach
                    </div>
                </div>
                @endif

                <!-- SREF Tag Box & One-Click Actions -->
                <div class="p-4 rounded-2xl bg-zinc-950 border border-zinc-800 flex flex-col sm:flex-row items-center justify-between gap-4">
                    <div>
                        <div class="text-[11px] text-zinc-500 font-medium">Midjourney Parameter Syntax</div>
                        <div class="text-lg font-mono font-bold text-violet-400">--sref {{ $sref->code }}</div>
                    </div>

                    <div class="flex items-center gap-2 w-full sm:w-auto">
                        <button type="button" @click="
                            navigator.clipboard.writeText('--sref {{ $sref->code }}');
                            copied = true;
                            fetch('/api/sref/{{ $sref->id }}/copy', { method: 'POST', headers: { 'X-CSRF-TOKEN': '{{ csrf_token() }}' } });
                            setTimeout(() => copied = false, 2500);
                        " class="flex-1 sm:flex-none px-5 py-2.5 rounded-xl bg-violet-600 hover:bg-violet-500 text-white text-xs font-bold transition flex items-center justify-center gap-2">
                            <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 16H6a2 2 0 01-2-2V6a2 2 0 012-2h8a2 2 0 012 2v2m-6 12h8a2 2 0 002-2v-8a2 2 0 00-2-2h-8a2 2 0 00-2 2v8a2 2 0 002 2z"/></svg>
                            <span x-text="copied ? 'Copied to Clipboard!' : 'Copy --sref Code'"></span>
                        </button>

                        <a href="{{ route('studio.remix') }}?from={{ $sref->code }}" class="px-4 py-2.5 rounded-xl bg-zinc-800 hover:bg-zinc-700 text-white text-xs font-semibold transition flex items-center gap-1.5">
                            <span>🪄</span> Remix in Studio
                        </a>

                        <button type="button" @click="
                            if (!liked) {
                                likes++;
                                liked = true;
                                fetch('/api/sref/{{ $sref->id }}/like', { method: 'POST', headers: { 'X-CSRF-TOKEN': '{{ csrf_token() }}' } });
                            }
                        " :class="liked ? 'bg-pink-600/20 text-pink-400 border-pink-500/30' : 'bg-zinc-900 border-zinc-800 text-zinc-400 hover:text-white'" class="p-2.5 rounded-xl border text-xs transition flex items-center gap-1.5">
                            <span>❤️</span>
                            <span x-text="likes"></span>
                        </button>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Related Prompts Using This Style -->
    @if($relatedPrompts->count() > 0)
    <div class="mb-14">
        <h2 class="text-xl font-bold text-white mb-6">Prompts in this Aesthetic</h2>
        <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-3 gap-6">
            @foreach($relatedPrompts as $prompt)
            <div class="bg-zinc-900 border border-zinc-800 rounded-2xl overflow-hidden shadow-lg group hover:border-violet-500/50 transition">
                <a href="{{ route('prompts.show', $prompt->slug) }}" class="block aspect-video bg-zinc-950 overflow-hidden">
                    <img src="{{ $prompt->cover_url }}" alt="{{ $prompt->title }}" class="w-full h-full object-cover group-hover:scale-105 transition duration-300">
                </a>
                <div class="p-4 space-y-2">
                    <a href="{{ route('prompts.show', $prompt->slug) }}" class="block font-semibold text-white hover:text-violet-400 transition truncate text-sm">
                        {{ $prompt->title }}
                    </a>
                    <p class="text-xs text-zinc-400 line-clamp-2">{{ $prompt->prompt_text }}</p>
                </div>
            </div>
            @endforeach
        </div>
    </div>
    @endif
</div>
@endsection
