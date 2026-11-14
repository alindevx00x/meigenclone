@extends('layouts.app')

@php
    $siteName = $directorySettings['site_name'] ?? 'PromptImage';
    $siteUrl = url('/');
    $logoUrl = !empty($directorySettings['logo_url']) ? $directorySettings['logo_url'] : $siteUrl . '/favicon.ico';
@endphp

@section('title', 'About Us — The Open AI Prompt Library for Creators')
@section('meta_title', 'About Us — The Open AI Prompt Library | ' . $siteName)
@section('meta_description', 'Discover ' . $siteName . ', the premier curated AI prompt directory for Midjourney, Flux.1, and generative art. Learn about our mission, curation standards, and creator ecosystem.')
@section('eyebrow', strtoupper($siteName) . ' COMMUNITY')
@section('toolbar-title', 'About Us')

@section('structured_data')
@php
    $aboutSchema = [
        '@context' => 'https://schema.org',
        '@graph' => [
            [
                '@type' => 'AboutPage',
                '@id' => route('about'),
                'url' => route('about'),
                'name' => 'About ' . $siteName,
                'description' => 'Discover ' . $siteName . ', the premier open directory for verified AI art prompts across Midjourney, Flux.1, and modern diffusion models.',
                'isPartOf' => [
                    '@type' => 'WebSite',
                    '@id' => $siteUrl . '#website',
                    'name' => $siteName,
                    'url' => $siteUrl,
                ],
            ],
            [
                '@type' => 'Organization',
                '@id' => $siteUrl . '#organization',
                'name' => $siteName,
                'url' => $siteUrl,
                'logo' => $logoUrl,
                'description' => 'The open creative prompt library for Midjourney, Flux.1, Seedance, and modern AI visual models.',
                'knowsAbout' => [
                    'Artificial Intelligence',
                    'Prompt Engineering',
                    'Midjourney Prompts',
                    'Flux.1 Prompts',
                    'Generative AI Visuals',
                    'Commercial AI Photography',
                    'Text-to-Image Synthesis',
                ],
                'contactPoint' => [
                    '@type' => 'ContactPoint',
                    'contactType' => 'Customer Support',
                    'url' => route('contact.index'),
                ],
            ],
            [
                '@type' => 'FAQPage',
                'mainEntity' => [
                    [
                        '@type' => 'Question',
                        'name' => 'What is ' . $siteName . '?',
                        'acceptedAnswer' => [
                            '@type' => 'Answer',
                            'text' => $siteName . ' is an open, curated repository of high-fidelity AI visual prompts engineered for Midjourney v6.1, Flux.1, Stable Diffusion, and modern generative models. Every prompt is verified to generate reproducible, high-quality visuals with 1-click copy.',
                        ],
                    ],
                    [
                        '@type' => 'Question',
                        'name' => 'Can I use these prompts for commercial projects?',
                        'acceptedAnswer' => [
                            '@type' => 'Answer',
                            'text' => 'Yes! All prompts in the public directory can be copied and used freely to generate imagery for commercial client work, advertising campaigns, e-commerce stores, social media, and digital artwork.',
                        ],
                    ],
                    [
                        '@type' => 'Question',
                        'name' => 'How are prompts curated and tested?',
                        'acceptedAnswer' => [
                            '@type' => 'Answer',
                            'text' => 'Our editorial board and community reviewers benchmark submissions against model syntax, lighting vocabulary, negative prompts, and camera parameters to ensure maximum prompt efficacy.',
                        ],
                    ],
                    [
                        '@type' => 'Question',
                        'name' => 'How can creators earn money on ' . $siteName . '?',
                        'acceptedAnswer' => [
                            '@type' => 'Answer',
                            'text' => 'Verified prompt engineers can connect Stripe or PayPal to receive direct, zero-platform-fee tips from users who find their prompts valuable.',
                        ],
                    ],
                ],
            ],
        ],
    ];
@endphp
<script type="application/ld+json">
{!! json_encode($aboutSchema, JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE | JSON_PRETTY_PRINT) !!}
</script>
@endsection

@section('content')
<div class="workspace-page about-page" style="max-width: 1100px; margin: 0 auto; padding: 24px 20px 80px;">

    <!-- Hero Header -->
    <header style="text-align: center; max-width: 820px; margin: 0 auto 48px;">
        <span style="display: inline-block; padding: 4px 14px; background: #eef2ff; color: #4f46e5; border-radius: 9999px; font-size: 12px; font-weight: 700; text-transform: uppercase; letter-spacing: 0.06em; margin-bottom: 14px;">
            ✦ MISSION &amp; PHILOSOPHY
        </span>
        <h1 style="font-size: clamp(30px, 4.5vw, 46px); font-weight: 800; color: #0f172a; line-height: 1.15; letter-spacing: -0.025em; margin-bottom: 18px;">
            Empowering Visual Storytellers with Precision AI Prompts
        </h1>
        <p style="font-size: clamp(16px, 2.2vw, 19px); color: #475569; line-height: 1.6; margin: 0 auto;">
            We bridge the gap between creative imagination and algorithmic execution. {{ $siteName }} is an open, community-curated library designed to help creators generate magazine-quality AI art with zero wasted GPU hours.
        </p>
    </header>

    <!-- Real-time Stats Grid -->
    <div style="display: grid; grid-template-columns: repeat(auto-fit, minmax(200px, 1fr)); gap: 16px; margin-bottom: 56px;">
        <div class="panel-card" style="padding: 24px 20px; text-align: center; border: 1.5px solid #e2e8f0; border-radius: 12px; background: #fff;">
            <div style="font-size: 36px; font-weight: 800; color: #6366f1; letter-spacing: -0.02em;">{{ number_format($stats['prompts_count']) }}+</div>
            <div style="font-size: 13.5px; font-weight: 600; color: #0f172a; margin-top: 4px;">Tested AI Prompts</div>
            <div style="font-size: 12px; color: #64748b; margin-top: 2px;">Midjourney &amp; Flux.1 verified</div>
        </div>
        <div class="panel-card" style="padding: 24px 20px; text-align: center; border: 1.5px solid #e2e8f0; border-radius: 12px; background: #fff;">
            <div style="font-size: 36px; font-weight: 800; color: #0f172a; letter-spacing: -0.02em;">{{ number_format($stats['categories_count']) }}</div>
            <div style="font-size: 13.5px; font-weight: 600; color: #0f172a; margin-top: 4px;">Curated Categories</div>
            <div style="font-size: 12px; color: #64748b; margin-top: 2px;">From Editorial to 3D &amp; Ads</div>
        </div>
        <div class="panel-card" style="padding: 24px 20px; text-align: center; border: 1.5px solid #e2e8f0; border-radius: 12px; background: #fff;">
            <div style="font-size: 36px; font-weight: 800; color: #16a34a; letter-spacing: -0.02em;">{{ number_format($stats['creators_count']) }}+</div>
            <div style="font-size: 13.5px; font-weight: 600; color: #0f172a; margin-top: 4px;">Active Creators</div>
            <div style="font-size: 12px; color: #64748b; margin-top: 2px;">Contributing prompt artists</div>
        </div>
        <div class="panel-card" style="padding: 24px 20px; text-align: center; border: 1.5px solid #e2e8f0; border-radius: 12px; background: #fff;">
            <div style="font-size: 36px; font-weight: 800; color: #ec4899; letter-spacing: -0.02em;">1-Click</div>
            <div style="font-size: 13.5px; font-weight: 600; color: #0f172a; margin-top: 4px;">Fast Workflow</div>
            <div style="font-size: 12px; color: #64748b; margin-top: 2px;">Copy parameters instantly</div>
        </div>
    </div>

    <!-- The Problem & Our Story -->
    <section class="panel-card" style="padding: clamp(24px, 4vw, 44px); border: 1.5px solid #e2e8f0; border-radius: 14px; background: #fff; margin-bottom: 48px;">
        <div style="max-width: 820px;">
            <span style="font-size: 11.5px; font-weight: 700; text-transform: uppercase; color: #6366f1; letter-spacing: 0.05em;">THE PROMPT REVOLUTION</span>
            <h2 style="font-size: clamp(22px, 3vw, 28px); font-weight: 800; color: #0f172a; margin: 8px 0 16px;">
                Why Prompt Engineering Demands Structure
            </h2>
            <p style="font-size: 15.5px; color: #334155; line-height: 1.75; margin-bottom: 16px;">
                The introduction of diffusion and flow-matching visual models transformed graphic design, advertising, concept art, and product visualization forever. However, the secret sauce behind breathtaking AI imagery—<strong>the prompt formula</strong>—remained stubbornly fragmented.
            </p>
            <p style="font-size: 15.5px; color: #334155; line-height: 1.75; margin-bottom: 16px;">
                Creators were forced to dig through chaotic Discord chat logs, lose bookmarks in fleeting social threads, or spend countless hours and GPU credits brute-forcing prompt variations without predictable results.
            </p>
            <p style="font-size: 15.5px; color: #334155; line-height: 1.75; margin-bottom: 0;">
                <strong>{{ $siteName }} was founded to change that.</strong> We built a dedicated, permanent home for prompt engineering: every entry includes the exact seed prompt, AI model version, style references, aspect ratio formulas, and real output previews.
            </p>
        </div>
    </section>

    <!-- Curation Standard: 4 Pillars Grid -->
    <section style="margin-bottom: 56px;">
        <div style="text-align: center; max-width: 600px; margin: 0 auto 32px;">
            <span style="font-size: 11.5px; font-weight: 700; text-transform: uppercase; color: #6366f1; letter-spacing: 0.05em;">QUALITY GUARANTEE</span>
            <h2 style="font-size: clamp(22px, 3vw, 30px); font-weight: 800; color: #0f172a; margin-top: 6px;">Our 4-Pillar Curation Standard</h2>
            <p style="font-size: 14.5px; color: #64748b; margin-top: 6px;">Every approved submission in our index satisfies rigorous visual quality checks.</p>
        </div>

        <div style="display: grid; grid-template-columns: repeat(auto-fit, minmax(240px, 1fr)); gap: 20px;">
            <div class="panel-card" style="padding: 24px; border: 1.5px solid #e2e8f0; border-radius: 12px; background: #fff;">
                <div style="font-size: 24px; margin-bottom: 10px;">📐</div>
                <h3 style="font-size: 16px; font-weight: 700; color: #0f172a; margin-bottom: 8px;">1. Parameter Precision</h3>
                <p style="font-size: 13.5px; color: #475569; line-height: 1.6; margin: 0;">
                    Correct syntax for aspect ratios (<code>--ar 16:9</code>), stylize thresholds (<code>--s 250</code>), model versions (<code>--v 6.1</code>), and raw photo modes (<code>--style raw</code>).
                </p>
            </div>

            <div class="panel-card" style="padding: 24px; border: 1.5px solid #e2e8f0; border-radius: 12px; background: #fff;">
                <div style="font-size: 24px; margin-bottom: 10px;">💡</div>
                <h3 style="font-size: 16px; font-weight: 700; color: #0f172a; margin-bottom: 8px;">2. Cinematic Lighting</h3>
                <p style="font-size: 13.5px; color: #475569; line-height: 1.6; margin: 0;">
                    Vocabulary borrowed from professional cinematography: volumetric god rays, rim lighting, soft diffused bounce, Kodak Portra 400 tones, and octane render subtleties.
                </p>
            </div>

            <div class="panel-card" style="padding: 24px; border: 1.5px solid #e2e8f0; border-radius: 12px; background: #fff;">
                <div style="font-size: 24px; margin-bottom: 10px;">🎯</div>
                <h3 style="font-size: 16px; font-weight: 700; color: #0f172a; margin-bottom: 8px;">3. Reproducible Fidelity</h3>
                <p style="font-size: 13.5px; color: #475569; line-height: 1.6; margin: 0;">
                    Prompts avoid meaningless buzzword stuffing ("hyper-detailed, 8k, unreal engine 5"). Instead, they emphasize concrete spatial composition, materials, and focal lengths.
                </p>
            </div>

            <div class="panel-card" style="padding: 24px; border: 1.5px solid #e2e8f0; border-radius: 12px; background: #fff;">
                <div style="font-size: 24px; margin-bottom: 10px;">💼</div>
                <h3 style="font-size: 16px; font-weight: 700; color: #0f172a; margin-bottom: 8px;">4. Commercial Utility</h3>
                <p style="font-size: 13.5px; color: #475569; line-height: 1.6; margin: 0;">
                    Curated across high-demand practical industries: advertising mockups, e-commerce product bottles, fashion lookbooks, UI concept art, and architectural renders.
                </p>
            </div>
        </div>
    </section>

    <!-- Supported AI Models Ecosystem -->
    <section class="panel-card" style="padding: clamp(24px, 4vw, 40px); border: 1.5px solid #e2e8f0; border-radius: 14px; background: #f8fafc; margin-bottom: 56px;">
        <div style="text-align: center; max-width: 640px; margin: 0 auto 30px;">
            <span style="font-size: 11.5px; font-weight: 700; text-transform: uppercase; color: #6366f1; letter-spacing: 0.05em;">COMPATIBILITY</span>
            <h2 style="font-size: clamp(22px, 3vw, 28px); font-weight: 800; color: #0f172a; margin-top: 6px;">Supported Generative Engines</h2>
            <p style="font-size: 14px; color: #64748b; margin-top: 6px;">Our prompt library is optimized across all leading foundation image models.</p>
        </div>

        <div style="display: grid; grid-template-columns: repeat(auto-fit, minmax(200px, 1fr)); gap: 14px;">
            <div style="padding: 16px; background: #fff; border-radius: 8px; border: 1px solid #e2e8f0;">
                <div style="font-weight: 700; color: #0f172a; font-size: 15px; margin-bottom: 4px;">Midjourney v6.1 &amp; Niji</div>
                <div style="font-size: 12.5px; color: #64748b;">Full parameter support including style references (--sref) and character sheets.</div>
            </div>
            <div style="padding: 16px; background: #fff; border-radius: 8px; border: 1px solid #e2e8f0;">
                <div style="font-weight: 700; color: #0f172a; font-size: 15px; margin-bottom: 4px;">Flux.1 (Dev &amp; Schnell)</div>
                <div style="font-size: 12.5px; color: #64748b;">Flawless typography rendering, complex hand anatomy, and natural prose prompts.</div>
            </div>
            <div style="padding: 16px; background: #fff; border-radius: 8px; border: 1px solid #e2e8f0;">
                <div style="font-weight: 700; color: #0f172a; font-size: 15px; margin-bottom: 4px;">Stable Diffusion XL</div>
                <div style="font-size: 12.5px; color: #64748b;">Optimized token weights, negative prompt brackets, and LoRA compatibility.</div>
            </div>
            <div style="padding: 16px; background: #fff; border-radius: 8px; border: 1px solid #e2e8f0;">
                <div style="font-weight: 700; color: #0f172a; font-size: 15px; margin-bottom: 4px;">DALL-E 3 &amp; GPT Image</div>
                <div style="font-size: 12.5px; color: #64748b;">Semantic prompt restructuring for OpenAI image synthesis.</div>
            </div>
        </div>
    </section>

    <!-- Creator Community & Direct Monetization -->
    <section class="panel-card" style="padding: clamp(24px, 4vw, 44px); border: 1.5px solid #e2e8f0; border-radius: 14px; background: #fff; margin-bottom: 56px;">
        <div style="display: grid; grid-template-columns: repeat(auto-fit, minmax(300px, 1fr)); gap: 32px; align-items: center;">
            <div>
                <span style="font-size: 11.5px; font-weight: 700; text-transform: uppercase; color: #16a34a; letter-spacing: 0.05em;">CREATOR ECONOMY</span>
                <h2 style="font-size: clamp(22px, 3vw, 28px); font-weight: 800; color: #0f172a; margin: 8px 0 14px;">
                    Built by Prompt Artists, Funded by Appreciation
                </h2>
                <p style="font-size: 14.5px; color: #334155; line-height: 1.7; margin-bottom: 14px;">
                    We believe creators should be rewarded for the hours they spend mastering AI models. That's why {{ $siteName }} includes built-in creator monetization:
                </p>
                <ul style="padding-left: 20px; font-size: 14px; color: #475569; line-height: 1.8; margin-bottom: 20px;">
                    <li><strong>Direct Creator Tipping</strong>: Users can send $2, $5, $10, or custom tips directly to creators via Stripe and PayPal.</li>
                    <li><strong>0% Platform Commission</strong>: 100% of the net tip goes directly to the creator's payout account.</li>
                    <li><strong>Verified Creator Profiles</strong>: Build your personal portfolio, display your follower count, and link your Twitter/X, Discord, and portfolio.</li>
                </ul>
                <a href="{{ auth()->check() ? route('dashboard.prompts.create') : route('register') }}" class="primary-button" style="display: inline-flex; align-items: center; gap: 6px;">
                    <span>＋</span> Publish a Prompt Today
                </a>
            </div>

            <div style="background: linear-gradient(135deg, #f8fafc, #ede9fe); padding: 28px; border-radius: 12px; border: 1.5px solid #e0e7ff; text-align: center;">
                <div style="width: 64px; height: 64px; border-radius: 50%; background: #6366f1; color: #fff; font-size: 28px; display: grid; place-items: center; margin: 0 auto 16px; box-shadow: 0 4px 12px rgba(99,102,241,0.3);">
                    💝
                </div>
                <h3 style="font-size: 17px; font-weight: 700; color: #1e1b4b; margin-bottom: 6px;">Support the AI Creative Commons</h3>
                <p style="font-size: 13.5px; color: #475569; line-height: 1.5; margin-bottom: 16px;">
                    When a prompt saves you hours of concept iteration, tip the creator with a single click.
                </p>
                <div style="display: inline-flex; gap: 8px; justify-content: center; font-weight: 600; font-size: 13px;">
                    <span style="background: #fff; padding: 6px 14px; border-radius: 9999px; border: 1px solid #cbd5e1; color: #0f172a;">$2 Coffee</span>
                    <span style="background: #fff; padding: 6px 14px; border-radius: 9999px; border: 1px solid #cbd5e1; color: #0f172a;">$5 Spark</span>
                    <span style="background: #fff; padding: 6px 14px; border-radius: 9999px; border: 1px solid #cbd5e1; color: #0f172a;">$10 Hero</span>
                </div>
            </div>
        </div>
    </section>

    <!-- FAQ Section -->
    <section style="margin-bottom: 56px;">
        <div style="text-align: center; max-width: 600px; margin: 0 auto 36px;">
            <span style="font-size: 11.5px; font-weight: 700; text-transform: uppercase; color: #6366f1; letter-spacing: 0.05em;">FREQUENTLY ASKED QUESTIONS</span>
            <h2 style="font-size: clamp(22px, 3vw, 30px); font-weight: 800; color: #0f172a; margin-top: 6px;">Everything You Need to Know</h2>
        </div>

        <div style="display: flex; flex-direction: column; gap: 14px; max-width: 820px; margin: 0 auto;">
            <details class="panel-card" style="padding: 18px 22px; border: 1.5px solid #e2e8f0; border-radius: 10px; background: #fff; cursor: pointer;">
                <summary style="font-weight: 700; color: #0f172a; font-size: 15.5px; list-style: none; display: flex; justify-content: space-between; align-items: center;">
                    <span>Is {{ $siteName }} free to use?</span>
                    <span style="color: #6366f1; font-size: 18px;">＋</span>
                </summary>
                <div style="padding-top: 14px; font-size: 14px; color: #475569; line-height: 1.65; border-top: 1px solid #f1f5f9; margin-top: 12px;">
                    Yes! You can browse the entire repository of 4,900+ prompts, search by category or model, and copy prompt text with one click without paying a cent.
                </div>
            </details>

            <details class="panel-card" style="padding: 18px 22px; border: 1.5px solid #e2e8f0; border-radius: 10px; background: #fff; cursor: pointer;">
                <summary style="font-weight: 700; color: #0f172a; font-size: 15.5px; list-style: none; display: flex; justify-content: space-between; align-items: center;">
                    <span>Can I use these prompts for client and commercial work?</span>
                    <span style="color: #6366f1; font-size: 18px;">＋</span>
                </summary>
                <div style="padding-top: 14px; font-size: 14px; color: #475569; line-height: 1.65; border-top: 1px solid #f1f5f9; margin-top: 12px;">
                    Absolutely. The prompts in our catalog are intended to serve as inspiration and starting foundations for your personal or commercial generative workflows in Midjourney, Flux, Stable Diffusion, or whatever tools you employ.
                </div>
            </details>

            <details class="panel-card" style="padding: 18px 22px; border: 1.5px solid #e2e8f0; border-radius: 10px; background: #fff; cursor: pointer;">
                <summary style="font-weight: 700; color: #0f172a; font-size: 15.5px; list-style: none; display: flex; justify-content: space-between; align-items: center;">
                    <span>How can I submit my own prompts to the gallery?</span>
                    <span style="color: #6366f1; font-size: 18px;">＋</span>
                </summary>
                <div style="padding-top: 14px; font-size: 14px; color: #475569; line-height: 1.65; border-top: 1px solid #f1f5f9; margin-top: 12px;">
                    Sign up for a free creator account, navigate to the Creator Dashboard, and click "Publish a prompt". Upload your generated visual image, paste the exact prompt parameters used, select the AI model and category, and submit. Our editorial board reviews submissions quickly.
                </div>
            </details>

            <details class="panel-card" style="padding: 18px 22px; border: 1.5px solid #e2e8f0; border-radius: 10px; background: #fff; cursor: pointer;">
                <summary style="font-weight: 700; color: #0f172a; font-size: 15.5px; list-style: none; display: flex; justify-content: space-between; align-items: center;">
                    <span>Where can I read in-depth tutorials and prompt breakdowns?</span>
                    <span style="color: #6366f1; font-size: 18px;">＋</span>
                </summary>
                <div style="padding-top: 14px; font-size: 14px; color: #475569; line-height: 1.65; border-top: 1px solid #f1f5f9; margin-top: 12px;">
                    Check out our <a href="{{ route('blog.index') }}" style="color: #6366f1; font-weight: 600;">Blog &amp; Knowledge Base</a> for comprehensive tutorials on Midjourney composition techniques, lighting formulas, negative prompt optimization, and model benchmarks.
                </div>
            </details>
        </div>
    </section>

    <!-- Final Call to Action -->
    <section class="panel-card" style="padding: 44px 28px; background: linear-gradient(135deg, #0f172a, #312e81); border: none; border-radius: 16px; color: #fff; text-align: center;">
        <h2 style="font-size: clamp(24px, 3.5vw, 34px); font-weight: 800; color: #fff; margin-bottom: 12px;">
            Start Exploring 4,900+ Tested Prompts
        </h2>
        <p style="max-width: 540px; margin: 0 auto 24px; color: #cbd5e1; font-size: 15px; line-height: 1.6;">
            Find your next visual spark today. Copy proven formulas for ads, portraits, characters, anime, and wallpaper in seconds.
        </p>
        <div style="display: flex; gap: 12px; justify-content: center; flex-wrap: wrap;">
            <a href="{{ route('prompts.index') }}" class="primary-button" style="height: 44px; padding: 0 24px; font-size: 14px; background: #6366f1; border-color: #6366f1;">
                Explore Gallery <span>→</span>
            </a>
            <a href="{{ route('blog.index') }}" class="outline-button" style="height: 44px; padding: 0 22px; font-size: 14px; color: #fff; border-color: rgba(255,255,255,0.3); background: rgba(255,255,255,0.06);">
                Read Blog Guides
            </a>
        </div>
    </section>

</div>
@endsection
