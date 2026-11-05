@extends('layouts.app')

@section('title', $pack->title . ' — AI Prompt Bundle')

@section('content')
<div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-10" x-data="{ copiedAll: false }">
    <!-- Breadcrumb -->
    <div class="flex items-center gap-2 text-xs text-zinc-500 mb-6">
        <a href="{{ route('prompts.index') }}" class="hover:text-zinc-300">Home</a>
        <span>/</span>
        <a href="{{ route('packs.index') }}" class="hover:text-zinc-300">Prompt Packs</a>
        <span>/</span>
        <span class="text-white truncate max-w-xs">{{ $pack->title }}</span>
    </div>

    <!-- Header Banner Card -->
    <div class="bg-zinc-900 border border-zinc-800/80 rounded-3xl p-6 sm:p-8 shadow-2xl mb-12">
        <div class="grid grid-cols-1 lg:grid-cols-12 gap-8 items-center">
            <div class="lg:col-span-5 aspect-video rounded-2xl overflow-hidden bg-zinc-950 border border-zinc-800 shadow-inner">
                @if($pack->cover_url)
                <img src="{{ $pack->cover_url }}" alt="{{ $pack->title }}" class="w-full h-full object-cover">
                @else
                <div class="w-full h-full bg-gradient-to-tr from-amber-900 to-indigo-900 flex items-center justify-center text-zinc-500">Pack Cover</div>
                @endif
            </div>

            <div class="lg:col-span-7 space-y-5">
                <div class="flex items-center gap-3">
                    <span class="px-3 py-1 rounded-full text-xs font-bold {{ $pack->is_free ? 'bg-emerald-500/10 border border-emerald-500/20 text-emerald-400' : 'bg-amber-500/10 border border-amber-500/20 text-amber-400' }}">
                        {{ $pack->is_free ? 'FREE PACK' : '$' . number_format($pack->price, 2) . ' USD' }}
                    </span>
                    <span class="text-xs text-zinc-400">
                        <strong>{{ $pack->prompt_count }}</strong> Prompts Included
                    </span>
                    <span class="text-xs text-zinc-500">&bull;</span>
                    <span class="text-xs text-zinc-400">
                        {{ number_format($pack->downloads_count) }} Downloads
                    </span>
                </div>

                <h1 class="text-2xl sm:text-3xl font-extrabold text-white tracking-tight">{{ $pack->title }}</h1>
                <p class="text-sm text-zinc-300 leading-relaxed">{{ $pack->description }}</p>

                <!-- Creator & Actions -->
                <div class="pt-4 border-t border-zinc-800/80 flex flex-wrap items-center justify-between gap-4">
                    <a href="{{ route('creators.show', $pack->user) }}" class="flex items-center gap-3 hover:opacity-90 transition" style="text-decoration: none;">
                        <div class="w-10 h-10 rounded-full bg-zinc-800 overflow-hidden flex items-center justify-center text-white text-sm font-bold border border-zinc-700">
                            @if ($pack->user->avatar_url)
                                <img src="{{ $pack->user->avatar_url }}" alt="{{ $pack->user->name }}" class="w-full h-full object-cover">
                            @else
                                {{ substr($pack->user->name, 0, 1) }}
                            @endif
                        </div>
                        <div>
                            <div class="text-xs text-zinc-400">Created by</div>
                            <div class="text-sm font-semibold text-white flex items-center gap-1">
                                <span>{{ $pack->user->name }}</span>
                                @if($pack->user->is_verified)
                                    <span title="Verified Creator" class="text-violet-400 text-xs font-bold">✓</span>
                                @endif
                            </div>
                        </div>
                    </a>

                    <div class="flex items-center gap-3">
                        <!-- Instant Download JSON Button -->
                        <a href="{{ route('packs.download', $pack) }}" class="px-5 py-2.5 rounded-xl bg-violet-600 hover:bg-violet-500 text-white text-xs font-bold transition flex items-center gap-2 shadow-lg shadow-violet-600/25">
                            <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 16v1a3 3 0 003 3h10a3 3 0 003-3v-1m-4-4l-4 4m0 0l-4-4m4 4V4"/></svg>
                            Export Pack (JSON)
                        </a>

                        <!-- Bulk Copy All Prompts -->
                        <button type="button" @click="
                            const texts = @js($pack->prompts->pluck('prompt_text')->toArray());
                            navigator.clipboard.writeText(texts.join('\n\n---\n\n'));
                            copiedAll = true;
                            setTimeout(() => copiedAll = false, 2500);
                        " class="px-4 py-2.5 rounded-xl bg-zinc-800 hover:bg-zinc-700 text-white text-xs font-semibold transition flex items-center gap-1.5">
                            <svg class="w-4 h-4 text-zinc-400" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 16H6a2 2 0 01-2-2V6a2 2 0 012-2h8a2 2 0 012 2v2m-6 12h8a2 2 0 002-2v-8a2 2 0 00-2-2h-8a2 2 0 00-2 2v8a2 2 0 002 2z"/></svg>
                            <span x-text="copiedAll ? 'All Prompts Copied!' : 'Copy All'"></span>
                        </button>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Prompts in this Pack -->
    <div>
        <h2 class="text-xl font-bold text-white mb-6">Prompts in this Bundle ({{ $pack->prompts->count() }})</h2>

        <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-6">
            @foreach($pack->prompts as $index => $prompt)
            <div class="bg-zinc-900 border border-zinc-800/80 rounded-2xl overflow-hidden shadow-lg group hover:border-violet-500/50 transition flex flex-col justify-between">
                <div>
                    <a href="{{ route('prompts.show', $prompt->slug) }}" class="block aspect-video bg-zinc-950 overflow-hidden relative">
                        <img src="{{ $prompt->cover_url }}" alt="{{ $prompt->title }}" class="w-full h-full object-cover group-hover:scale-105 transition duration-300">
                        <span class="absolute top-2 left-2 px-2 py-0.5 rounded-md bg-black/70 backdrop-blur-md text-[10px] font-mono text-zinc-300">
                            #{{ $index + 1 }}
                        </span>
                    </a>

                    <div class="p-4 space-y-2">
                        <a href="{{ route('prompts.show', $prompt->slug) }}" class="block font-semibold text-white hover:text-violet-400 transition truncate text-sm">
                            {{ $prompt->title }}
                        </a>
                        <p class="text-xs text-zinc-400 line-clamp-3 leading-relaxed">{{ $prompt->prompt_text }}</p>
                    </div>
                </div>

                <div class="px-4 pb-4 pt-2 border-t border-zinc-800/60 flex items-center justify-between text-xs" x-data="{ copiedItem: false }">
                    <span class="text-zinc-500 font-medium">{{ $prompt->model }}</span>

                    <button type="button" @click="
                        navigator.clipboard.writeText(@js($prompt->prompt_text));
                        copiedItem = true;
                        setTimeout(() => copiedItem = false, 2000);
                    " class="text-violet-400 hover:text-white transition font-medium flex items-center gap-1">
                        <span x-text="copiedItem ? 'Copied' : 'Copy'"></span>
                    </button>
                </div>
            </div>
            @endforeach
        </div>
    </div>
</div>
@endsection
