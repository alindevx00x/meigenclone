@extends('layouts.app')

@section('title', 'Create Prompt Pack — Bundle and Monetize Your Prompts')

@section('content')
<div class="max-w-4xl mx-auto px-4 sm:px-6 lg:px-8 py-10">
    <div class="mb-8">
        <a href="{{ route('packs.index') }}" class="text-xs text-zinc-500 hover:text-zinc-300 transition">&larr; Back to Prompt Packs</a>
        <h1 class="text-3xl font-extrabold text-white mt-2">Publish a Prompt Pack</h1>
        <p class="text-sm text-zinc-400 mt-1">Package your top prompts into a thematic bundle for community download or sale.</p>
    </div>

    <form action="{{ route('packs.store') }}" method="POST" class="bg-zinc-900 border border-zinc-800/80 rounded-3xl p-6 sm:p-8 shadow-2xl space-y-6">
        @csrf

        <!-- Title -->
        <div>
            <label class="block text-sm font-semibold text-zinc-200 mb-2">Pack Title</label>
            <input type="text" name="title" value="{{ old('title') }}" placeholder="e.g. Commercial Sneaker Photography Mockups Vol. 1" required class="w-full bg-zinc-950 border border-zinc-800 rounded-2xl px-4 py-3 text-sm text-white placeholder-zinc-500 focus:outline-none focus:ring-2 focus:ring-violet-500/50">
            @error('title') <p class="text-red-400 text-xs mt-1">{{ $message }}</p> @enderror
        </div>

        <!-- Description -->
        <div>
            <label class="block text-sm font-semibold text-zinc-200 mb-2">Description & Instructions</label>
            <textarea name="description" rows="4" placeholder="Explain what creators will achieve with this pack, recommended AI models, and tips..." required class="w-full bg-zinc-950 border border-zinc-800 rounded-2xl px-4 py-3 text-sm text-white placeholder-zinc-500 focus:outline-none focus:ring-2 focus:ring-violet-500/50 resize-none">{{ old('description') }}</textarea>
            @error('description') <p class="text-red-400 text-xs mt-1">{{ $message }}</p> @enderror
        </div>

        <!-- Pricing -->
        <div class="grid grid-cols-1 sm:grid-cols-2 gap-4 items-center bg-zinc-950/60 border border-zinc-800/80 rounded-2xl p-4" x-data="{ isFree: true }">
            <div>
                <label class="flex items-center gap-2 cursor-pointer">
                    <input type="checkbox" name="is_free" value="1" x-model="isFree" class="rounded bg-zinc-900 border-zinc-700 text-violet-600 focus:ring-violet-500">
                    <span class="text-sm font-semibold text-white">Offer this pack for FREE</span>
                </label>
                <p class="text-xs text-zinc-500 mt-0.5">Free packs receive 5x more community downloads.</p>
            </div>

            <div x-show="!isFree">
                <label class="block text-xs font-semibold text-zinc-400 mb-1">Price (USD)</label>
                <div class="relative">
                    <span class="absolute left-3.5 top-2.5 text-zinc-500 text-sm">$</span>
                    <input type="number" step="0.01" min="1" max="99" name="price" value="{{ old('price', '9.99') }}" class="w-full bg-zinc-900 border border-zinc-800 rounded-xl pl-8 pr-3 py-2 text-sm text-white focus:outline-none focus:ring-2 focus:ring-violet-500/50">
                </div>
            </div>
        </div>

        <!-- Select Prompts to Include -->
        <div>
            <label class="block text-sm font-semibold text-zinc-200 mb-2">Select Prompts to Include in this Pack (minimum 2)</label>

            @if($myPrompts->count() > 0)
            <div class="grid grid-cols-1 sm:grid-cols-2 gap-3 max-h-96 overflow-y-auto p-1 pr-2">
                @foreach($myPrompts as $prompt)
                <label class="flex items-center gap-3 p-3 rounded-2xl bg-zinc-950 border border-zinc-800/80 hover:border-zinc-700 cursor-pointer transition">
                    <input type="checkbox" name="prompt_ids[]" value="{{ $prompt->id }}" class="rounded bg-zinc-900 border-zinc-700 text-violet-600 focus:ring-violet-500">
                    <div class="w-10 h-10 rounded-xl overflow-hidden bg-zinc-900 flex-shrink-0">
                        <img src="{{ $prompt->cover_url }}" class="w-full h-full object-cover">
                    </div>
                    <div class="truncate text-xs">
                        <div class="font-semibold text-white truncate">{{ $prompt->title }}</div>
                        <div class="text-zinc-500 truncate">{{ $prompt->model }}</div>
                    </div>
                </label>
                @endforeach
            </div>
            @else
            <div class="text-center py-8 bg-zinc-950 rounded-2xl border border-zinc-800 text-xs text-zinc-400">
                You haven't submitted any prompts yet. <a href="{{ route('dashboard.prompts.create') }}" class="text-violet-400 underline">Create prompts first</a> before bundling them.
            </div>
            @endif
            @error('prompt_ids') <p class="text-red-400 text-xs mt-1">{{ $message }}</p> @enderror
        </div>

        <div class="pt-4 border-t border-zinc-800 flex justify-end gap-3">
            <a href="{{ route('packs.index') }}" class="px-5 py-2.5 rounded-2xl bg-zinc-800 hover:bg-zinc-700 text-white text-xs font-semibold transition">Cancel</a>
            <button type="submit" class="px-6 py-2.5 rounded-2xl bg-violet-600 hover:bg-violet-500 text-white text-xs font-bold transition shadow-lg shadow-violet-600/20">
                Publish Pack
            </button>
        </div>
    </form>
</div>
@endsection
