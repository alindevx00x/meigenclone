@extends('layouts.app')

@section('title', 'AI Image Generator — One-Click Flux & DALL-E 3 Generation')

@section('content')
<div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-10" x-data="generationStudio({{ json_encode(['prefilled' => $prefilledPrompt?->prompt_text ?? '', 'promptId' => $prefilledPrompt?->id ?? null, 'userCredits' => $user?->credits ?? 25]) }})">
    <!-- Header -->
    <div class="flex flex-col md:flex-row md:items-center justify-between gap-4 mb-8">
        <div>
            <div class="inline-flex items-center gap-2 px-3 py-1 rounded-full bg-violet-500/10 border border-violet-500/20 text-violet-400 text-xs font-semibold uppercase tracking-wider mb-2">
                <span class="w-2 h-2 rounded-full bg-violet-400 animate-pulse"></span> One-Click Generation Studio
            </div>
            <h1 class="text-3xl font-extrabold text-white tracking-tight sm:text-4xl">Generate AI Images Real-Time</h1>
            <p class="mt-1 text-sm text-zinc-400">Powered by Flux 1.1 Pro, Flux Schnell & OpenAI DALL-E 3. Use platform credits or your own API key.</p>
        </div>

        @auth
        <div class="flex items-center gap-3 bg-zinc-900 border border-zinc-800 rounded-2xl px-5 py-3 shadow-sm">
            <div class="w-10 h-10 rounded-xl bg-gradient-to-br from-violet-600 to-indigo-600 flex items-center justify-center text-white text-lg font-bold shadow-inner">
                ✦
            </div>
            <div>
                <div class="text-xs text-zinc-400 uppercase font-semibold">Platform Credits</div>
                <div class="text-xl font-extrabold text-white" x-text="credits + ' Credits'"></div>
            </div>
        </div>
        @else
        <div class="bg-zinc-900/80 border border-zinc-800 rounded-2xl p-4 text-xs text-zinc-300">
            <span class="text-violet-400 font-semibold">Demo Sandbox:</span> Generates instant high-fidelity previews. <a href="{{ route('login') }}" class="underline text-white font-medium hover:text-violet-400">Sign in</a> to save to your creator profile.
        </div>
        @endauth
    </div>

    <!-- Main Studio Grid -->
    <div class="grid grid-cols-1 lg:grid-cols-12 gap-8 items-start">
        <!-- Controls Column -->
        <div class="lg:col-span-5 bg-zinc-900 border border-zinc-800/80 rounded-3xl p-6 sm:p-7 shadow-xl space-y-6">
            <!-- Prompt Textarea -->
            <div>
                <label for="prompt_text" class="block text-sm font-semibold text-zinc-200 mb-2 flex items-center justify-between">
                    <span>Prompt Text</span>
                    <button type="button" @click="clearPrompt()" class="text-xs text-zinc-500 hover:text-zinc-300 transition">Clear</button>
                </label>
                <div class="relative">
                    <textarea id="prompt_text" x-model="promptText" rows="6" placeholder="Describe the image you want to generate in detail, including lighting, style, composition, and mood..." class="w-full bg-zinc-950/80 border border-zinc-800 rounded-2xl px-4 py-3 text-sm text-white placeholder-zinc-500 focus:outline-none focus:ring-2 focus:ring-violet-500/50 focus:border-violet-500 transition resize-none"></textarea>
                    <div class="absolute bottom-3 right-3 text-xs text-zinc-500" x-text="promptText.length + '/2000'"></div>
                </div>
            </div>

            <!-- Model Selector -->
            <div>
                <label class="block text-sm font-semibold text-zinc-200 mb-2">AI Model & Engine</label>
                <div class="grid grid-cols-3 gap-2">
                    <button type="button" @click="model = 'flux-schnell'; provider = 'flux'" :class="model === 'flux-schnell' ? 'bg-violet-600/20 border-violet-500 text-white font-semibold' : 'bg-zinc-950/60 border-zinc-800 text-zinc-400 hover:border-zinc-700'" class="p-3 rounded-2xl border text-center transition flex flex-col items-center">
                        <span class="text-base">⚡</span>
                        <span class="text-xs mt-1 font-medium">Flux Schnell</span>
                        <span class="text-[10px] text-zinc-500">Fastest</span>
                    </button>
                    <button type="button" @click="model = 'flux-1.1-pro'; provider = 'flux'" :class="model === 'flux-1.1-pro' ? 'bg-violet-600/20 border-violet-500 text-white font-semibold' : 'bg-zinc-950/60 border-zinc-800 text-zinc-400 hover:border-zinc-700'" class="p-3 rounded-2xl border text-center transition flex flex-col items-center">
                        <span class="text-base">✦</span>
                        <span class="text-xs mt-1 font-medium">Flux 1.1 Pro</span>
                        <span class="text-[10px] text-zinc-500">High Quality</span>
                    </button>
                    <button type="button" @click="model = 'dall-e-3'; provider = 'openai'" :class="model === 'dall-e-3' ? 'bg-violet-600/20 border-violet-500 text-white font-semibold' : 'bg-zinc-950/60 border-zinc-800 text-zinc-400 hover:border-zinc-700'" class="p-3 rounded-2xl border text-center transition flex flex-col items-center">
                        <span class="text-base">◈</span>
                        <span class="text-xs mt-1 font-medium">DALL-E 3</span>
                        <span class="text-[10px] text-zinc-500">OpenAI</span>
                    </button>
                </div>
            </div>

            <!-- Aspect Ratio -->
            <div>
                <label class="block text-sm font-semibold text-zinc-200 mb-2">Aspect Ratio</label>
                <div class="grid grid-cols-4 gap-2">
                    <button type="button" @click="aspectRatio = '1:1'" :class="aspectRatio === '1:1' ? 'bg-violet-600/20 border-violet-500 text-white font-semibold' : 'bg-zinc-950/60 border-zinc-800 text-zinc-400 hover:border-zinc-700'" class="py-2.5 rounded-xl border text-xs text-center transition">
                        1:1 Square
                    </button>
                    <button type="button" @click="aspectRatio = '16:9'" :class="aspectRatio === '16:9' ? 'bg-violet-600/20 border-violet-500 text-white font-semibold' : 'bg-zinc-950/60 border-zinc-800 text-zinc-400 hover:border-zinc-700'" class="py-2.5 rounded-xl border text-xs text-center transition">
                        16:9 Cinema
                    </button>
                    <button type="button" @click="aspectRatio = '9:16'" :class="aspectRatio === '9:16' ? 'bg-violet-600/20 border-violet-500 text-white font-semibold' : 'bg-zinc-950/60 border-zinc-800 text-zinc-400 hover:border-zinc-700'" class="py-2.5 rounded-xl border text-xs text-center transition">
                        9:16 Story
                    </button>
                    <button type="button" @click="aspectRatio = '4:5'" :class="aspectRatio === '4:5' ? 'bg-violet-600/20 border-violet-500 text-white font-semibold' : 'bg-zinc-950/60 border-zinc-800 text-zinc-400 hover:border-zinc-700'" class="py-2.5 rounded-xl border text-xs text-center transition">
                        4:5 Social
                    </button>
                </div>
            </div>

            <!-- API Key Accordion (Optional BYOK) -->
            <div x-data="{ openKey: false }" class="border-t border-zinc-800/80 pt-4">
                <button type="button" @click="openKey = !openKey" class="flex items-center justify-between w-full text-xs font-medium text-zinc-400 hover:text-zinc-200 transition">
                    <span class="flex items-center gap-1.5">
                        <svg class="w-4 h-4 text-zinc-500" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 7a2 2 0 012 2m4 0a6 6 0 01-7.743 5.743L11 17H9v2H7v2H4a1 1 0 01-1-1v-2.586a1 1 0 01.293-.707l5.964-5.964A6 6 0 1121 9z"/></svg>
                        Bring Your Own API Key (Optional)
                    </span>
                    <span x-text="openKey ? '−' : '+'" class="text-sm font-bold"></span>
                </button>
                <div x-show="openKey" x-collapse class="mt-3 space-y-2">
                    <input type="password" x-model="apiKey" placeholder="Enter your Replicate or OpenAI API key..." class="w-full bg-zinc-950 border border-zinc-800 rounded-xl px-3 py-2 text-xs text-white placeholder-zinc-500 focus:outline-none focus:ring-1 focus:ring-violet-500">
                    <p class="text-[11px] text-zinc-500">Using your personal API key does not consume platform credits. Keys are encrypted and never shared.</p>
                </div>
            </div>

            <!-- Submit Button -->
            <button type="button" @click="generateImage()" :disabled="isLoading || !promptText.trim()" class="w-full bg-gradient-to-r from-violet-600 via-indigo-600 to-purple-600 hover:from-violet-500 hover:to-indigo-500 text-white font-bold py-3.5 px-6 rounded-2xl shadow-lg shadow-violet-600/25 transition disabled:opacity-50 disabled:cursor-not-allowed flex items-center justify-center gap-2 text-sm">
                <template x-if="!isLoading">
                    <span class="flex items-center gap-2">
                        <svg class="w-5 h-5" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 10V3L4 14h7v7l9-11h-7z"/></svg>
                        Generate Image Now
                    </span>
                </template>
                <template x-if="isLoading">
                    <span class="flex items-center gap-2">
                        <svg class="animate-spin -ml-1 mr-2 h-5 w-5 text-white" fill="none" viewBox="0 0 24 24"><circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle><path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8v8H4z"></path></svg>
                        Rendering Image...
                    </span>
                </template>
            </button>
        </div>

        <!-- Output Canvas Column -->
        <div class="lg:col-span-7 bg-zinc-900 border border-zinc-800/80 rounded-3xl p-6 sm:p-7 shadow-xl flex flex-col items-center justify-center min-h-[520px] relative overflow-hidden">
            <!-- Empty State -->
            <div x-show="!resultImage && !isLoading" class="text-center py-16 px-4">
                <div class="w-20 h-20 mx-auto rounded-3xl bg-zinc-950 border border-zinc-800/80 flex items-center justify-center text-zinc-600 mb-4 shadow-inner">
                    <svg class="w-10 h-10" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M4 16l4.586-4.586a2 2 0 012.828 0L16 16m-2-2l1.586-1.586a2 2 0 012.828 0L20 14m-6-6h.01M6 20h12a2 2 0 002-2V6a2 2 0 00-2-2H6a2 2 0 00-2 2v12a2 2 0 002 2z"/></svg>
                </div>
                <h3 class="text-lg font-bold text-white">Your generated artwork will appear here</h3>
                <p class="mt-1 text-sm text-zinc-400 max-w-md mx-auto">Select a model, tweak your prompt or remix an existing community prompt, then click Generate.</p>
            </div>

            <!-- Loading Spinner State -->
            <div x-show="isLoading" class="text-center py-20">
                <div class="relative w-24 h-24 mx-auto mb-6">
                    <div class="absolute inset-0 rounded-full border-4 border-violet-500/20"></div>
                    <div class="absolute inset-0 rounded-full border-4 border-violet-500 border-t-transparent animate-spin"></div>
                    <div class="absolute inset-0 flex items-center justify-center text-violet-400 text-xl font-bold">✦</div>
                </div>
                <h3 class="text-lg font-bold text-white">Synthesizing Imagery...</h3>
                <p class="mt-1 text-xs text-zinc-400">Diffusion steps in progress. High-resolution canvas rendering.</p>
            </div>

            <!-- Result State -->
            <div x-show="resultImage && !isLoading" class="w-full space-y-4">
                <div class="relative rounded-2xl overflow-hidden border border-zinc-800 bg-zinc-950 shadow-2xl flex items-center justify-center">
                    <img :src="resultImage" alt="Generated Artwork" class="w-full max-h-[580px] object-contain">
                </div>

                <div class="flex flex-wrap items-center justify-between gap-3 pt-2">
                    <div class="text-xs text-zinc-400">
                        <span class="text-white font-medium" x-text="resultModel"></span> &bull; <span x-text="resultAspect"></span>
                    </div>

                    <div class="flex items-center gap-2">
                        <a :href="resultImage" download="generated-artwork.png" class="px-4 py-2 rounded-xl bg-zinc-800 hover:bg-zinc-700 text-white text-xs font-semibold transition flex items-center gap-1.5">
                            <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 16v1a3 3 0 003 3h10a3 3 0 003-3v-1m-4-4l-4 4m0 0l-4-4m4 4V4"/></svg>
                            Download High-Res
                        </a>

                        @auth
                        <form :action="'/generations/' + generationId + '/publish'" method="POST" class="inline">
                            @csrf
                            <button type="submit" class="px-4 py-2 rounded-xl bg-violet-600 hover:bg-violet-500 text-white text-xs font-semibold transition flex items-center gap-1.5">
                                <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"/></svg>
                                Publish to Community
                            </button>
                        </form>
                        @endauth
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Recent Generations Gallery -->
    @if($recentGenerations->count() > 0)
    <div class="mt-16">
        <h2 class="text-xl font-bold text-white mb-6 flex items-center gap-2">
            <span>Recent Generations</span>
            <span class="text-xs font-normal text-zinc-500">({{ $recentGenerations->total() }})</span>
        </h2>

        <div class="grid grid-cols-2 sm:grid-cols-3 md:grid-cols-4 lg:grid-cols-6 gap-4">
            @foreach($recentGenerations as $gen)
            <div class="group relative rounded-2xl overflow-hidden border border-zinc-800/80 bg-zinc-900 aspect-square hover:border-violet-500/50 transition">
                @if($gen->image_url)
                <img src="{{ $gen->image_url }}" alt="Generation" class="w-full h-full object-cover group-hover:scale-105 transition duration-300">
                <div class="absolute inset-0 bg-gradient-to-t from-black/80 via-transparent to-transparent opacity-0 group-hover:opacity-100 transition p-3 flex flex-col justify-end">
                    <p class="text-[11px] text-white line-clamp-2">{{ $gen->prompt_text }}</p>
                    <span class="text-[9px] text-violet-400 mt-1 font-semibold uppercase">{{ $gen->model }}</span>
                </div>
                @else
                <div class="w-full h-full flex items-center justify-center text-zinc-600 text-xs">Failed</div>
                @endif
            </div>
            @endforeach
        </div>

        <div class="mt-6">
            {{ $recentGenerations->links() }}
        </div>
    </div>
    @endif
</div>

<script>
document.addEventListener('alpine:init', () => {
    Alpine.data('generationStudio', (config) => ({
        promptText: config.prefilled || '',
        promptId: config.promptId || null,
        credits: config.userCredits || 25,
        model: 'flux-schnell',
        provider: 'flux',
        aspectRatio: '1:1',
        apiKey: '',
        isLoading: false,
        resultImage: null,
        resultModel: '',
        resultAspect: '',
        generationId: null,

        clearPrompt() {
            this.promptText = '';
        },

        async generateImage() {
            if (!this.promptText.trim()) return;

            this.isLoading = true;
            this.resultImage = null;

            try {
                const response = await fetch('{{ route('generations.store') }}', {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/json',
                        'Accept': 'application/json',
                        'X-CSRF-TOKEN': '{{ csrf_token() }}'
                    },
                    body: JSON.stringify({
                        prompt_text: this.promptText,
                        provider: this.provider,
                        model: this.model,
                        aspect_ratio: this.aspectRatio,
                        api_key: this.apiKey,
                        prompt_id: this.promptId
                    })
                });

                const data = await response.json();

                if (response.ok && data.success) {
                    this.resultImage = data.generation.image_url;
                    this.resultModel = data.generation.model;
                    this.resultAspect = data.generation.aspect_ratio;
                    this.generationId = data.generation.id;
                    if (data.credits_remaining !== null && data.credits_remaining !== undefined) {
                        this.credits = data.credits_remaining;
                    }
                } else {
                    alert(data.message || 'Generation failed. Please try again.');
                }
            } catch (err) {
                alert('A network error occurred during generation: ' + err.message);
            } finally {
                this.isLoading = false;
            }
        }
    }));
});
</script>
@endsection
