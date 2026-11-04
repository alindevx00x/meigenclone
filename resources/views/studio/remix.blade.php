@extends('layouts.app')

@section('title', 'Prompt Remixing Studio — Interactive AI Prompt Builder')

@section('content')
<div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-10" x-data="remixStudio({{ json_encode(['initial' => $basePrompt?->prompt_text ?? 'A high-end commercial photograph of an organic glass beverage bottle resting on wet obsidian stone, dramatic softbox studio lighting, 8k resolution']) }})">
    <!-- Header -->
    <div class="flex flex-col md:flex-row md:items-center justify-between gap-4 mb-8">
        <div>
            <div class="inline-flex items-center gap-2 px-3 py-1 rounded-full bg-indigo-500/10 border border-indigo-500/20 text-indigo-400 text-xs font-semibold uppercase tracking-wider mb-2">
                <span>🪄</span> Interactive Token Builder
            </div>
            <h1 class="text-3xl font-extrabold text-white tracking-tight sm:text-4xl">Prompt Remixing Studio</h1>
            <p class="mt-1 text-sm text-zinc-400">Combine styles, lighting, camera angles, aspect ratios, and SREF reference codes with interactive chips.</p>
        </div>

        <div class="flex items-center gap-3">
            <button type="button" @click="copyPrompt()" class="px-5 py-2.5 rounded-2xl bg-zinc-900 border border-zinc-800 hover:border-zinc-700 text-white text-xs font-semibold transition flex items-center gap-2 shadow-sm">
                <svg class="w-4 h-4 text-violet-400" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 16H6a2 2 0 01-2-2V6a2 2 0 012-2h8a2 2 0 012 2v2m-6 12h8a2 2 0 002-2v-8a2 2 0 00-2-2h-8a2 2 0 00-2 2v8a2 2 0 002 2z"/></svg>
                <span x-text="copied ? 'Copied to Clipboard!' : 'Copy Remixed Prompt'"></span>
            </button>

            <button type="button" @click="sendToGenerator()" class="px-5 py-2.5 rounded-2xl bg-gradient-to-r from-violet-600 to-indigo-600 hover:from-violet-500 hover:to-indigo-500 text-white text-xs font-bold transition flex items-center gap-2 shadow-lg shadow-violet-600/20">
                <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 10V3L4 14h7v7l9-11h-7z"/></svg>
                Generate This Remix
            </button>
        </div>
    </div>

    <!-- Studio Layout -->
    <div class="grid grid-cols-1 lg:grid-cols-12 gap-8 items-start">
        <!-- Live Editor Column -->
        <div class="lg:col-span-7 space-y-6">
            <!-- Active Prompt Card -->
            <div class="bg-zinc-900 border border-zinc-800/80 rounded-3xl p-6 sm:p-7 shadow-xl">
                <div class="flex items-center justify-between mb-3">
                    <h2 class="text-sm font-semibold text-zinc-200">Constructed Prompt Output</h2>
                    <div class="flex items-center gap-2 text-xs">
                        <span class="text-zinc-500" x-text="prompt.length + ' chars'"></span>
                        <button type="button" @click="resetPrompt()" class="text-zinc-400 hover:text-white transition">Reset</button>
                    </div>
                </div>

                <textarea x-model="prompt" rows="7" class="w-full bg-zinc-950/90 border border-zinc-800 rounded-2xl p-4 text-sm text-white placeholder-zinc-500 focus:outline-none focus:ring-2 focus:ring-violet-500/50 focus:border-violet-500 transition leading-relaxed"></textarea>

                <!-- Active Modifiers Pills -->
                <div class="mt-4 flex flex-wrap items-center gap-2">
                    <span class="text-xs text-zinc-500">Active Tags:</span>
                    <template x-for="tag in activeTags" :key="tag">
                        <span class="inline-flex items-center gap-1.5 px-3 py-1 rounded-full bg-violet-500/10 border border-violet-500/30 text-violet-300 text-xs font-medium">
                            <span x-text="tag"></span>
                            <button type="button" @click="removeTag(tag)" class="text-violet-400 hover:text-white">&times;</button>
                        </span>
                    </template>
                    <span x-show="activeTags.length === 0" class="text-xs text-zinc-600 italic">No modifiers added yet. Click chips below.</span>
                </div>
            </div>

            <!-- Parameters Bar -->
            <div class="bg-zinc-900 border border-zinc-800/80 rounded-3xl p-6 shadow-xl space-y-5">
                <h3 class="text-sm font-semibold text-zinc-200 flex items-center gap-2">
                    <span>⚙️</span> Quick Aspect Ratios & Midjourney Parameters
                </h3>

                <!-- Aspect Ratios -->
                <div>
                    <div class="text-xs text-zinc-400 mb-2 font-medium">Aspect Ratio Tag</div>
                    <div class="grid grid-cols-4 gap-2">
                        @foreach(['--ar 1:1', '--ar 16:9', '--ar 9:16', '--ar 4:5'] as $ar)
                        <button type="button" @click="setParam('ar', '{{ $ar }}')" :class="prompt.includes('{{ $ar }}') ? 'bg-violet-600/20 border-violet-500 text-white font-semibold' : 'bg-zinc-950/60 border-zinc-800 text-zinc-400 hover:border-zinc-700'" class="py-2.5 rounded-xl border text-xs text-center transition">
                            {{ $ar }}
                        </button>
                        @endforeach
                    </div>
                </div>

                <!-- Stylize & Version -->
                <div>
                    <div class="text-xs text-zinc-400 mb-2 font-medium">Midjourney Engine & Stylize</div>
                    <div class="flex flex-wrap gap-2">
                        @foreach(['--v 6.1', '--stylize 250', '--stylize 750', '--chaos 20', '--quality 2'] as $p)
                        <button type="button" @click="toggleParam('{{ $p }}')" :class="prompt.includes('{{ $p }}') ? 'bg-indigo-600/20 border-indigo-500 text-white font-semibold' : 'bg-zinc-950/60 border-zinc-800 text-zinc-400 hover:border-zinc-700'" class="px-3 py-1.5 rounded-xl border text-xs transition">
                            {{ $p }}
                        </button>
                        @endforeach
                    </div>
                </div>
            </div>
        </div>

        <!-- Token Chips Library Column -->
        <div class="lg:col-span-5 space-y-6">
            <!-- Styles Chips -->
            <div class="bg-zinc-900 border border-zinc-800/80 rounded-3xl p-6 shadow-xl">
                <h3 class="text-sm font-semibold text-zinc-200 mb-3 flex items-center gap-2">
                    <span>🎨</span> Art Style Modifiers
                </h3>
                <div class="flex flex-wrap gap-2">
                    @foreach($presetStyles as $name => $snippet)
                    <button type="button" @click="toggleSnippet('{{ $name }}', '{{ addslashes($snippet) }}')" :class="activeTags.includes('{{ $name }}') ? 'bg-violet-600 border-violet-500 text-white font-semibold shadow-md' : 'bg-zinc-950/80 border-zinc-800 text-zinc-300 hover:border-zinc-700'" class="px-3 py-1.5 rounded-xl border text-xs transition text-left flex items-center gap-1.5">
                        <span class="w-1.5 h-1.5 rounded-full" :class="activeTags.includes('{{ $name }}') ? 'bg-white' : 'bg-violet-400'"></span>
                        {{ $name }}
                    </button>
                    @endforeach
                </div>
            </div>

            <!-- Lighting Chips -->
            <div class="bg-zinc-900 border border-zinc-800/80 rounded-3xl p-6 shadow-xl">
                <h3 class="text-sm font-semibold text-zinc-200 mb-3 flex items-center gap-2">
                    <span>💡</span> Lighting Atmosphere
                </h3>
                <div class="flex flex-wrap gap-2">
                    @foreach($presetLighting as $name => $snippet)
                    <button type="button" @click="toggleSnippet('{{ $name }}', '{{ addslashes($snippet) }}')" :class="activeTags.includes('{{ $name }}') ? 'bg-amber-600 border-amber-500 text-white font-semibold shadow-md' : 'bg-zinc-950/80 border-zinc-800 text-zinc-300 hover:border-zinc-700'" class="px-3 py-1.5 rounded-xl border text-xs transition text-left flex items-center gap-1.5">
                        <span class="w-1.5 h-1.5 rounded-full" :class="activeTags.includes('{{ $name }}') ? 'bg-white' : 'bg-amber-400'"></span>
                        {{ $name }}
                    </button>
                    @endforeach
                </div>
            </div>

            <!-- Camera & Lenses -->
            <div class="bg-zinc-900 border border-zinc-800/80 rounded-3xl p-6 shadow-xl">
                <h3 class="text-sm font-semibold text-zinc-200 mb-3 flex items-center gap-2">
                    <span>📷</span> Camera & Composition
                </h3>
                <div class="flex flex-wrap gap-2">
                    @foreach($presetCameras as $name => $snippet)
                    <button type="button" @click="toggleSnippet('{{ $name }}', '{{ addslashes($snippet) }}')" :class="activeTags.includes('{{ $name }}') ? 'bg-emerald-600 border-emerald-500 text-white font-semibold shadow-md' : 'bg-zinc-950/80 border-zinc-800 text-zinc-300 hover:border-zinc-700'" class="px-3 py-1.5 rounded-xl border text-xs transition text-left flex items-center gap-1.5">
                        <span class="w-1.5 h-1.5 rounded-full" :class="activeTags.includes('{{ $name }}') ? 'bg-white' : 'bg-emerald-400'"></span>
                        {{ $name }}
                    </button>
                    @endforeach
                </div>
            </div>

            <!-- Style Reference (SREF) Codes -->
            @if($featuredSrefs->count() > 0)
            <div class="bg-zinc-900 border border-zinc-800/80 rounded-3xl p-6 shadow-xl">
                <div class="flex items-center justify-between mb-3">
                    <h3 class="text-sm font-semibold text-zinc-200 flex items-center gap-2">
                        <span>🧬</span> Midjourney SREF Codes
                    </h3>
                    <a href="{{ route('srefs.index') }}" class="text-xs text-violet-400 hover:underline">View All</a>
                </div>
                <div class="grid grid-cols-2 gap-2">
                    @foreach($featuredSrefs as $sref)
                    <button type="button" @click="toggleSref('{{ $sref->code }}')" :class="prompt.includes('--sref {{ $sref->code }}') ? 'bg-indigo-600/20 border-indigo-500 text-white' : 'bg-zinc-950/80 border-zinc-800 text-zinc-300 hover:border-zinc-700'" class="p-2.5 rounded-2xl border text-xs text-left transition flex items-center gap-2.5">
                        <div class="w-7 h-7 rounded-lg overflow-hidden bg-zinc-800 flex-shrink-0">
                            @if($sref->preview_url)
                            <img src="{{ $sref->preview_url }}" class="w-full h-full object-cover">
                            @else
                            <div class="w-full h-full bg-gradient-to-br from-violet-600 to-indigo-600"></div>
                            @endif
                        </div>
                        <div class="truncate">
                            <div class="font-semibold text-white truncate">{{ $sref->title }}</div>
                            <div class="text-[10px] text-zinc-500 font-mono">--sref {{ $sref->code }}</div>
                        </div>
                    </button>
                    @endforeach
                </div>
            </div>
            @endif
        </div>
    </div>
</div>

<script>
document.addEventListener('alpine:init', () => {
    Alpine.data('remixStudio', (config) => ({
        initialPrompt: config.initial,
        prompt: config.initial,
        activeTags: [],
        snippetsMap: {},
        copied: false,

        resetPrompt() {
            this.prompt = this.initialPrompt;
            this.activeTags = [];
            this.snippetsMap = {};
        },

        toggleSnippet(name, snippet) {
            const idx = this.activeTags.indexOf(name);
            if (idx > -1) {
                // Remove
                this.activeTags.splice(idx, 1);
                this.prompt = this.prompt.replace(', ' + snippet, '').replace(snippet, '').trim();
                delete this.snippetsMap[name];
            } else {
                // Add
                this.activeTags.push(name);
                this.snippetsMap[name] = snippet;
                this.prompt = this.prompt.trim() + (this.prompt.trim() ? ', ' : '') + snippet;
            }
        },

        removeTag(name) {
            const snippet = this.snippetsMap[name];
            if (snippet) {
                this.prompt = this.prompt.replace(', ' + snippet, '').replace(snippet, '').trim();
            }
            this.activeTags = this.activeTags.filter(t => t !== name);
            delete this.snippetsMap[name];
        },

        setParam(type, val) {
            // Replace existing aspect ratio or append
            this.prompt = this.prompt.replace(/--ar\s+\d+:\d+/g, '').trim();
            this.prompt += ' ' + val;
        },

        toggleParam(p) {
            if (this.prompt.includes(p)) {
                this.prompt = this.prompt.replace(p, '').replace(/\s+/g, ' ').trim();
            } else {
                this.prompt += ' ' + p;
            }
        },

        toggleSref(code) {
            const tag = '--sref ' + code;
            if (this.prompt.includes(tag)) {
                this.prompt = this.prompt.replace(tag, '').replace(/\s+/g, ' ').trim();
            } else {
                // Remove any other sref first
                this.prompt = this.prompt.replace(/--sref\s+\d+/g, '').trim();
                this.prompt += ' ' + tag;
            }
        },

        async copyPrompt() {
            try {
                await navigator.clipboard.writeText(this.prompt);
                this.copied = true;
                setTimeout(() => this.copied = false, 2500);
            } catch (e) {
                alert('Copied prompt!');
            }
        },

        sendToGenerator() {
            const url = '{{ route('generations.index') }}?prefilled=' + encodeURIComponent(this.prompt);
            window.location.href = url;
        }
    }));
});
</script>
@endsection
