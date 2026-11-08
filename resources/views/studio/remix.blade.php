@extends('layouts.app')

@section('title', 'Prompt Remixing Studio — Interactive AI Prompt Builder')
@section('eyebrow', 'PROMPT STUDIO')
@section('toolbar-title', 'Remix Studio')

@section('content')
<div class="workspace-page" x-data="remixStudio({{ json_encode(['initial' => $basePrompt?->prompt_text ?? 'A high-end commercial photograph of an organic glass beverage bottle resting on wet obsidian stone, dramatic softbox studio lighting, 8k resolution']) }})">
    <!-- Page Header (Matching Dashboard & Workspace) -->
    <div class="page-heading">
        <div>
            <span class="section-kicker">INTERACTIVE TOKEN BUILDER</span>
            <h1>Prompt Remixing Studio</h1>
            <p>Combine styles, lighting, camera angles, aspect ratios, and SREF reference codes with interactive chips.</p>
        </div>
        <div style="display: flex; gap: 8px; align-items: center; flex-wrap: wrap;">
            <button type="button" @click="copyPrompt()" class="primary-button" style="min-height: 38px;">
                <span x-text="copied ? '✓ Copied' : 'Copy prompt ⧉'"></span>
            </button>
        </div>
    </div>

    <!-- Studio Layout Grid -->
    <div style="display: grid; grid-template-columns: repeat(auto-fit, minmax(340px, 1fr)); gap: 24px; align-items: start;">
        <!-- Left: Live Prompt Editor -->
        <div style="display: flex; flex-direction: column; gap: 20px;">
            <!-- Output Card -->
            <div class="panel-card" style="padding: 22px;">
                <div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 12px;">
                    <strong style="color: #222228; font-size: 13.8px; font-weight: 600;">Constructed Prompt Output</strong>
                    <div style="display: flex; gap: 10px; align-items: center; font-size: 11px;">
                        <span style="color: #999;" x-text="prompt.length + ' characters'"></span>
                        <button type="button" @click="resetPrompt()" class="subtle-link" style="background: none; border: 0; cursor: pointer;">Reset</button>
                    </div>
                </div>

                <textarea x-model="prompt" rows="7" style="width: 100%; min-height: 150px; border: 1px solid #e6e6eb; border-radius: 9px; background: #fff; padding: 12px; color: #17171b; font-family: inherit; font-size: 13.5px; line-height: 1.6; resize: vertical; box-sizing: border-box; outline: none;"></textarea>

                <!-- Active Modifier Pills -->
                <div style="margin-top: 14px; display: flex; flex-wrap: wrap; align-items: center; gap: 7px;">
                    <span style="font-size: 11.5px; color: #888; font-weight: 500;">Active Tags:</span>
                    <template x-for="tag in activeTags" :key="tag">
                        <span style="display: inline-flex; align-items: center; gap: 5px; padding: 3px 9px; border-radius: 20px; background: #f0edff; border: 1px solid #ded8fa; color: #765bd7; font-size: 11px; font-weight: 500;">
                            <span x-text="tag"></span>
                            <button type="button" @click="removeTag(tag)" style="border: 0; background: none; color: #9c83eb; cursor: pointer; font-size: 13px; line-height: 1; padding: 0 1px;">&times;</button>
                        </span>
                    </template>
                    <span x-show="activeTags.length === 0" style="font-size: 11.5px; color: #aaa; font-style: italic;">No modifiers added yet. Click chips on the right.</span>
                </div>
            </div>

            <!-- Parameters Card -->
            <div class="panel-card" style="padding: 22px;">
                <h3 style="margin: 0 0 16px; font-size: 13.8px; font-weight: 600; color: #222228; display: flex; align-items: center; gap: 6px;">
                    <span>⚙</span> Quick Aspect Ratios &amp; Midjourney Parameters
                </h3>

                <!-- Aspect Ratios -->
                <div style="margin-bottom: 16px;">
                    <div style="font-size: 11.5px; color: #777; font-weight: 500; margin-bottom: 8px;">Aspect Ratio Tag</div>
                    <div style="display: grid; grid-template-columns: repeat(4, 1fr); gap: 8px;">
                        @foreach(['--ar 1:1', '--ar 16:9', '--ar 9:16', '--ar 4:5'] as $ar)
                        <button type="button" @click="setParam('ar', '{{ $ar }}')" :style="prompt.includes('{{ $ar }}') ? 'background: #17171b; color: #fff; border-color: #17171b;' : 'background: #fff; border: 1px solid #e6e6eb; color: #444;'" style="padding: 9px 0; border-radius: 8px; font-size: 11.5px; font-weight: 500; cursor: pointer; text-align: center; transition: all 0.15s;">
                            {{ $ar }}
                        </button>
                        @endforeach
                    </div>
                </div>

                <!-- Stylize & Version -->
                <div>
                    <div style="font-size: 11.5px; color: #777; font-weight: 500; margin-bottom: 8px;">Midjourney Engine &amp; Parameters</div>
                    <div style="display: flex; flex-wrap: wrap; gap: 7px;">
                        @foreach(['--v 6.1', '--stylize 250', '--stylize 750', '--chaos 20', '--quality 2'] as $p)
                        <button type="button" @click="toggleParam('{{ $p }}')" :style="prompt.includes('{{ $p }}') ? 'background: #7958ee; color: #fff; border-color: #7958ee;' : 'background: #fff; border: 1px solid #e6e6eb; color: #444;'" style="padding: 6px 12px; border-radius: 8px; font-size: 11.5px; font-weight: 500; cursor: pointer; transition: all 0.15s;">
                            {{ $p }}
                        </button>
                        @endforeach
                    </div>
                </div>
            </div>
        </div>

        <!-- Right: Token Chips Library -->
        <div style="display: flex; flex-direction: column; gap: 20px;">
            <!-- Art Styles -->
            <div class="panel-card" style="padding: 22px;">
                <h3 style="margin: 0 0 12px; font-size: 13.8px; font-weight: 600; color: #222228; display: flex; align-items: center; gap: 6px;">
                    <span>🎨</span> Art Style Modifiers
                </h3>
                <div style="display: flex; flex-wrap: wrap; gap: 7px;">
                    @foreach($presetStyles as $name => $snippet)
                    <button type="button" @click="toggleSnippet('{{ $name }}', '{{ addslashes($snippet) }}')" :style="activeTags.includes('{{ $name }}') ? 'background: #7958ee; color: #fff; border-color: #7958ee;' : 'background: #fff; border: 1px solid #e6e6eb; color: #444;'" style="padding: 6px 12px; border-radius: 8px; font-size: 11.5px; font-weight: 500; cursor: pointer; transition: all 0.15s; display: inline-flex; align-items: center; gap: 6px;">
                        <span style="width: 6px; height: 6px; border-radius: 50%;" :style="activeTags.includes('{{ $name }}') ? 'background: #fff;' : 'background: #7958ee;'"></span>
                        {{ $name }}
                    </button>
                    @endforeach
                </div>
            </div>

            <!-- Lighting Atmosphere -->
            <div class="panel-card" style="padding: 22px;">
                <h3 style="margin: 0 0 12px; font-size: 13.8px; font-weight: 600; color: #222228; display: flex; align-items: center; gap: 6px;">
                    <span>💡</span> Lighting Atmosphere
                </h3>
                <div style="display: flex; flex-wrap: wrap; gap: 7px;">
                    @foreach($presetLighting as $name => $snippet)
                    <button type="button" @click="toggleSnippet('{{ $name }}', '{{ addslashes($snippet) }}')" :style="activeTags.includes('{{ $name }}') ? 'background: #a86400; color: #fff; border-color: #a86400;' : 'background: #fff; border: 1px solid #e6e6eb; color: #444;'" style="padding: 6px 12px; border-radius: 8px; font-size: 11.5px; font-weight: 500; cursor: pointer; transition: all 0.15s; display: inline-flex; align-items: center; gap: 6px;">
                        <span style="width: 6px; height: 6px; border-radius: 50%;" :style="activeTags.includes('{{ $name }}') ? 'background: #fff;' : 'background: #a86400;'"></span>
                        {{ $name }}
                    </button>
                    @endforeach
                </div>
            </div>

            <!-- Camera & Composition -->
            <div class="panel-card" style="padding: 22px;">
                <h3 style="margin: 0 0 12px; font-size: 13.8px; font-weight: 600; color: #222228; display: flex; align-items: center; gap: 6px;">
                    <span>📷</span> Camera &amp; Composition
                </h3>
                <div style="display: flex; flex-wrap: wrap; gap: 7px;">
                    @foreach($presetCameras as $name => $snippet)
                    <button type="button" @click="toggleSnippet('{{ $name }}', '{{ addslashes($snippet) }}')" :style="activeTags.includes('{{ $name }}') ? 'background: #16875e; color: #fff; border-color: #16875e;' : 'background: #fff; border: 1px solid #e6e6eb; color: #444;'" style="padding: 6px 12px; border-radius: 8px; font-size: 11.5px; font-weight: 500; cursor: pointer; transition: all 0.15s; display: inline-flex; align-items: center; gap: 6px;">
                        <span style="width: 6px; height: 6px; border-radius: 50%;" :style="activeTags.includes('{{ $name }}') ? 'background: #fff;' : 'background: #16875e;'"></span>
                        {{ $name }}
                    </button>
                    @endforeach
                </div>
            </div>

            <!-- Style Reference (SREF) Codes -->
            @if($featuredSrefs->count() > 0)
            <div class="panel-card" style="padding: 22px;">
                <div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 12px;">
                    <h3 style="margin: 0; font-size: 13.8px; font-weight: 600; color: #222228; display: flex; align-items: center; gap: 6px;">
                        <span>🧬</span> Midjourney SREF Codes
                    </h3>
                    <a href="{{ route('srefs.index') }}" class="subtle-link" style="font-size: 11px;">View All &rarr;</a>
                </div>
                <div style="display: grid; grid-template-columns: repeat(2, 1fr); gap: 8px;">
                    @foreach($featuredSrefs as $sref)
                    <button type="button" @click="toggleSref('{{ $sref->code }}')" :style="prompt.includes('--sref {{ $sref->code }}') ? 'border-color: #7958ee; background: #f0edff;' : 'border-color: #e6e6eb; background: #fff;'" style="padding: 8px 10px; border: 1px solid; border-radius: 9px; cursor: pointer; display: flex; align-items: center; gap: 9px; text-align: left; transition: all 0.15s;">
                        <div style="width: 28px; height: 28px; border-radius: 6px; overflow: hidden; background: #eee; flex-shrink: 0;">
                            @if($sref->preview_url)
                            <img src="{{ $sref->preview_url }}" style="width: 100%; height: 100%; object-fit: cover;">
                            @else
                            <div style="width: 100%; height: 100%; background: linear-gradient(135deg, #7958ee, #6366f1);"></div>
                            @endif
                        </div>
                        <div style="overflow: hidden; text-overflow: ellipsis; white-space: nowrap;">
                            <div style="font-weight: 600; font-size: 11.5px; color: #222228; overflow: hidden; text-overflow: ellipsis; white-space: nowrap;">{{ $sref->title }}</div>
                            <div style="font-size: 10px; color: #888; font-family: monospace;">--sref {{ $sref->code }}</div>
                        </div>
                    </button>
                    @endforeach
                </div>
            </div>
            @endif
        </div>
    </div>
</div>
@endsection
