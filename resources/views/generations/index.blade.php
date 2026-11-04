@extends('layouts.app')

@section('title', 'AI Image Generator — One-Click Flux & DALL-E 3 Generation')
@section('eyebrow', 'MEIGEN STUDIO')
@section('toolbar-title', 'AI Generator')

@section('content')
<div class="workspace-page" x-data="generationStudio({{ json_encode(['prefilled' => $prefilledPrompt?->prompt_text ?? '', 'promptId' => $prefilledPrompt?->id ?? null, 'userCredits' => $user?->credits ?? 25]) }})">
    <!-- Page Header (Matching Dashboard & Workspace) -->
    <div class="page-heading">
        <div>
            <span class="section-kicker">ONE-CLICK GENERATION STUDIO</span>
            <h1>Generate AI Images Real-Time</h1>
            <p>Powered by Flux 1.1 Pro, Flux Schnell &amp; OpenAI DALL-E 3. Use platform credits or your own API key.</p>
        </div>
        <div>
            @auth
            <div class="stat-card" style="min-height: auto; padding: 10px 16px; display: flex; align-items: center; gap: 12px;">
                <span class="stat-icon stat-violet" style="width: 32px; height: 32px; font-size: 16px;">⚡</span>
                <div>
                    <div style="font-size: 10px; color: #888; font-weight: 600; text-transform: uppercase;">Available Credits</div>
                    <strong style="margin: 0; font-size: 18px; color: #222228;" x-text="credits + ' Credits'"></strong>
                </div>
            </div>
            @else
            <div class="panel-card" style="padding: 10px 14px; font-size: 11.5px; color: #555;">
                <span style="color: #7958ee; font-weight: 600;">Demo Sandbox:</span> Generates instant previews. <a href="{{ route('login') }}" class="subtle-link" style="color: #17171b; font-weight: 600;">Sign in</a> to save generations.
            </div>
            @endauth
        </div>
    </div>

    <!-- Main Studio Grid -->
    <div style="display: grid; grid-template-columns: repeat(auto-fit, minmax(340px, 1fr)); gap: 24px; align-items: start;">
        <!-- Controls Column -->
        <div class="panel-card" style="padding: 24px; display: flex; flex-direction: column; gap: 18px;">
            <!-- Prompt Textarea -->
            <div>
                <div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 8px;">
                    <label for="prompt_text" style="font-size: 12.5px; font-weight: 600; color: #222228;">Prompt Text</label>
                    <button type="button" @click="clearPrompt()" class="subtle-link" style="border: 0; background: none; cursor: pointer; font-size: 11px;">Clear</button>
                </div>
                <div style="position: relative;">
                    <textarea id="prompt_text" x-model="promptText" rows="6" placeholder="Describe the image you want to generate in detail, including lighting, style, composition, and mood..." style="width: 100%; min-height: 140px; border: 1px solid #e6e6eb; border-radius: 9px; background: #fff; padding: 12px; color: #17171b; font-family: inherit; font-size: 13.5px; line-height: 1.6; resize: vertical; box-sizing: border-box; outline: none;"></textarea>
                    <div style="position: absolute; bottom: 8px; right: 10px; font-size: 10px; color: #aaa;" x-text="promptText.length + '/2000'"></div>
                </div>
            </div>

            <!-- Model Selector -->
            <div>
                <label style="display: block; font-size: 12.5px; font-weight: 600; color: #222228; margin-bottom: 8px;">AI Model &amp; Engine</label>
                <div style="display: grid; grid-template-columns: repeat(3, 1fr); gap: 8px;">
                    <button type="button" @click="model = 'flux-schnell'; provider = 'flux'" :style="model === 'flux-schnell' ? 'background: #f0edff; border-color: #7958ee; color: #514497;' : 'background: #fff; border: 1px solid #e6e6eb; color: #555;'" style="padding: 10px 8px; border-radius: 9px; cursor: pointer; text-align: center; transition: all 0.15s; display: flex; flex-direction: column; align-items: center;">
                        <span style="font-size: 16px;">⚡</span>
                        <strong style="font-size: 11.5px; margin-top: 3px;">Flux Schnell</strong>
                        <small style="font-size: 9.5px; color: #888;">Fastest (1 credit)</small>
                    </button>
                    <button type="button" @click="model = 'flux-1.1-pro'; provider = 'flux'" :style="model === 'flux-1.1-pro' ? 'background: #f0edff; border-color: #7958ee; color: #514497;' : 'background: #fff; border: 1px solid #e6e6eb; color: #555;'" style="padding: 10px 8px; border-radius: 9px; cursor: pointer; text-align: center; transition: all 0.15s; display: flex; flex-direction: column; align-items: center;">
                        <span style="font-size: 16px;">✦</span>
                        <strong style="font-size: 11.5px; margin-top: 3px;">Flux 1.1 Pro</strong>
                        <small style="font-size: 9.5px; color: #888;">Highest fidelity</small>
                    </button>
                    <button type="button" @click="model = 'dall-e-3'; provider = 'openai'" :style="model === 'dall-e-3' ? 'background: #f0edff; border-color: #7958ee; color: #514497;' : 'background: #fff; border: 1px solid #e6e6eb; color: #555;'" style="padding: 10px 8px; border-radius: 9px; cursor: pointer; text-align: center; transition: all 0.15s; display: flex; flex-direction: column; align-items: center;">
                        <span style="font-size: 16px;">🤖</span>
                        <strong style="font-size: 11.5px; margin-top: 3px;">DALL-E 3</strong>
                        <small style="font-size: 9.5px; color: #888;">OpenAI HD</small>
                    </button>
                </div>
            </div>

            <!-- Aspect Ratio -->
            <div>
                <label style="display: block; font-size: 12.5px; font-weight: 600; color: #222228; margin-bottom: 8px;">Aspect Ratio</label>
                <div style="display: grid; grid-template-columns: repeat(3, 1fr); gap: 8px;">
                    @foreach(['1:1' => 'Square (1:1)', '16:9' => 'Landscape (16:9)', '9:16' => 'Story (9:16)'] as $ratio => $label)
                    <button type="button" @click="aspectRatio = '{{ $ratio }}'" :style="aspectRatio === '{{ $ratio }}' ? 'background: #17171b; color: #fff; border-color: #17171b;' : 'background: #fff; border: 1px solid #e6e6eb; color: #555;'" style="padding: 8px; border-radius: 8px; font-size: 11px; font-weight: 500; cursor: pointer; text-align: center; transition: all 0.15s;">
                        {{ $label }}
                    </button>
                    @endforeach
                </div>
            </div>

            <!-- Custom API Key Section (Accordion) -->
            <div x-data="{ openKey: false }" style="border-top: 1px solid #eee; padding-top: 14px;">
                <button type="button" @click="openKey = !openKey" style="border: 0; background: none; padding: 0; font-size: 11.5px; font-weight: 500; color: #765bd7; cursor: pointer; display: flex; align-items: center; gap: 5px;">
                    <span x-text="openKey ? '▾' : '▸'"></span> Bring Your Own API Key (Optional)
                </button>
                <div x-show="openKey" style="margin-top: 10px;">
                    <input type="password" x-model="apiKey" placeholder="sk-... (Replicate API token or OpenAI key)" style="width: 100%; min-height: 36px; border: 1px solid #e6e6eb; border-radius: 8px; background: #fff; padding: 8px 12px; font-size: 12px; color: #17171b; box-sizing: border-box; outline: none;">
                    <small style="color: #999; font-size: 10px; margin-top: 4px; display: block;">When specified, generations bypass platform credit limits.</small>
                </div>
            </div>

            <!-- Submit Button -->
            <button type="button" @click="generateImage()" :disabled="isLoading || !promptText.trim()" class="primary-button" style="width: 100%; min-height: 44px; justify-content: center; font-size: 13.5px; font-weight: 600;">
                <span x-show="!isLoading">⚡ Generate Artwork (1 Credit)</span>
                <span x-show="isLoading" style="display: none;">Generating preview&hellip;</span>
            </button>
        </div>

        <!-- Canvas / Result Column -->
        <div class="panel-card" style="padding: 24px; min-height: 440px; display: flex; flex-direction: column; align-items: center; justify-content: center;">
            <!-- Empty state -->
            <div x-show="!resultImage && !isLoading" class="empty-panel" style="width: 100%; border: 0; background: transparent; flex-direction: column; text-align: center; justify-content: center; padding: 40px 10px;">
                <span class="empty-icon" style="width: 44px; height: 44px; font-size: 22px; margin-bottom: 8px;">✦</span>
                <strong style="font-size: 14px;">Interactive Generation Canvas</strong>
                <p style="max-width: 340px;">Select your preferred model, adjust prompt instructions, and click Generate to see real-time outputs here.</p>
            </div>

            <!-- Loading state -->
            <div x-show="isLoading" style="display: none; text-align: center; padding: 60px 20px;">
                <div style="display: inline-block; width: 36px; height: 36px; border: 3px solid #f0edff; border-top-color: #7958ee; border-radius: 50%; animation: spin 0.8s linear infinite;"></div>
                <div style="font-weight: 600; color: #222228; font-size: 14px; margin-top: 14px;">Generating AI Masterpiece&hellip;</div>
                <p style="font-size: 11px; color: #888; margin-top: 4px;">Rendering via Flux 1.1 Pro inference engine.</p>
            </div>

            <!-- Output canvas -->
            <div x-show="resultImage && !isLoading" style="display: none; width: 100%;">
                <div style="border-radius: 12px; overflow: hidden; border: 1px solid #ececf0; background: #000; max-height: 480px; display: flex; align-items: center; justify-content: center;">
                    <img :src="resultImage" alt="Generated Output" style="max-width: 100%; max-height: 480px; object-fit: contain;">
                </div>

                <div style="display: flex; justify-content: space-between; align-items: center; margin-top: 16px; flex-wrap: wrap; gap: 10px;">
                    <div style="font-size: 11.5px; color: #777;">
                        <span style="color: #222228; font-weight: 600;" x-text="resultModel"></span> &bull; <span x-text="resultAspect"></span>
                    </div>

                    <div style="display: flex; gap: 8px; align-items: center;">
                        <a :href="resultImage" download="generated-artwork.png" class="outline-button" style="min-height: 34px; font-size: 11px; text-decoration: none;">
                            Download High-Res &darr;
                        </a>

                        @auth
                        <form :action="'/generations/' + generationId + '/publish'" method="POST" style="display: inline;">
                            @csrf
                            <button type="submit" class="primary-button" style="min-height: 34px; font-size: 11px;">
                                Publish to Gallery ✦
                            </button>
                        </form>
                        @endauth
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Recent Generations Showcase (Matching dashboard#favorites prompt grid) -->
    @if($recentGenerations->count() > 0)
    <section class="workspace-section" style="margin-top: 40px;">
        <div class="section-heading">
            <div>
                <span class="section-kicker">COMMUNITY CREATIONS</span>
                <h2>Recent generations <span class="heading-count">{{ $recentGenerations->total() }}</span></h2>
            </div>
        </div>

        <div style="display: grid; grid-template-columns: repeat(auto-fill, minmax(180px, 1fr)); gap: 14px;">
            @foreach($recentGenerations as $gen)
            <div class="panel-card" style="padding: 0; overflow: hidden; position: relative;">
                @if($gen->image_url)
                <div style="aspect-ratio: 1; overflow: hidden; background: #f0f0f2;">
                    <img src="{{ $gen->image_url }}" alt="AI Generation" style="width: 100%; height: 100%; object-fit: cover;">
                </div>
                <div style="padding: 10px;">
                    <div style="font-size: 10.5px; color: #444; font-weight: 500; overflow: hidden; text-overflow: ellipsis; white-space: nowrap;">{{ $gen->prompt_text }}</div>
                    <span style="font-size: 9.5px; color: #7958ee; font-weight: 600; text-transform: uppercase;">{{ $gen->model }}</span>
                </div>
                @else
                <div style="aspect-ratio: 1; display: flex; align-items: center; justify-content: center; color: #aaa; font-size: 11px;">Failed</div>
                @endif
            </div>
            @endforeach
        </div>

        <div class="pagination-wrap" style="margin-top: 20px;">
            {{ $recentGenerations->links() }}
        </div>
    </section>
    @endif
</div>

<style>
@keyframes spin {
    to { transform: rotate(360deg); }
}
</style>
@endsection
