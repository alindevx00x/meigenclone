<?php

namespace Database\Seeders;

use App\Models\BlogCategory;
use App\Models\BlogPost;
use App\Models\User;
use Illuminate\Database\Seeder;
use Illuminate\Support\Str;

class BlogSeeder extends Seeder
{
    public function run(): void
    {
        $admin = User::where('role', 'admin')->first() ?? User::first();
        $adminId = $admin ? $admin->id : null;

        $cat1 = BlogCategory::updateOrCreate(['slug' => 'prompt-engineering'], [
            'name' => 'Prompt Engineering',
            'description' => 'Core formulas, parameter tuning, lighting cues, and prompt architecture guides.',
            'sort_order' => 1,
        ]);

        $cat2 = BlogCategory::updateOrCreate(['slug' => 'model-comparisons'], [
            'name' => 'Model Comparisons',
            'description' => 'Side-by-side benchmarks between Midjourney v6.1, Flux.1, SDXL, and modern visual engines.',
            'sort_order' => 2,
        ]);

        $cat3 = BlogCategory::updateOrCreate(['slug' => 'commercial-advertising'], [
            'name' => 'Commercial & Advertising',
            'description' => 'Practical visual prompts for e-commerce, luxury packaging, and brand marketing campaigns.',
            'sort_order' => 3,
        ]);

        $cat4 = BlogCategory::updateOrCreate(['slug' => 'styles-aesthetics'], [
            'name' => 'Styles & Aesthetics',
            'description' => 'Deep dives into 35mm film stocks, editorial portraits, architecture, and isometric 3D renders.',
            'sort_order' => 4,
        ]);

        // Post 1: Midjourney v6.1 Guide
        BlogPost::updateOrCreate(['slug' => 'midjourney-v6-prompt-architecture-guide'], [
            'user_id' => $adminId,
            'blog_category_id' => $cat1->id,
            'title' => 'The Ultimate Midjourney v6.1 Prompt Architecture Guide: From Composition to Lighting',
            'excerpt' => 'Learn the 5-layer prompt formula professional creators use to get consistent, photorealistic results in Midjourney v6.1 without buzzword bloat.',
            'reading_time_minutes' => 6,
            'status' => 'published',
            'published_at' => now()->subDays(2),
            'views_count' => 148,
            'cover_image' => 'https://images.unsplash.com/photo-1618005182384-a83a8bd57fbe?w=1200&auto=format&fit=crop&q=80',
            'meta_title' => 'Midjourney v6.1 Prompt Architecture Guide | PromptImage',
            'meta_description' => 'Master the 5-layer prompt architecture for Midjourney v6.1. Complete guide to parameters, camera angles, lighting vocabulary, and raw mode.',
            'meta_keywords' => 'midjourney prompt guide, midjourney v6.1 tutorial, prompt engineering formula, midjourney parameters',
            'content' => <<<HTML
<h2>Why Most Midjourney Prompts Fail in 2026</h2>
<p>In earlier iterations of Midjourney (v4 and v5), prompt creators got into the habit of stuffing dozens of generic descriptive adjectives at the end of every prompt: <em>"hyperrealistic, 8k resolution, trending on ArtStation, cinematic octane render, photorealistic, masterpiece"</em>.</p>
<p>In Midjourney v6 and v6.1, the natural language understanding (NLU) model was completely overhauled. Those legacy buzzwords no longer help—in fact, they dilute the prompt weight and cause the model to ignore critical composition cues. Today, achieving breathtaking fidelity requires a structured, multi-layer prompt architecture.</p>

<h2>The 5-Layer Prompt Formula</h2>
<p>Professional prompt engineers structure their instructions from general framing to specific camera settings:</p>
<pre><code>[Subject &amp; Action] + [Environment &amp; Background] + [Framing &amp; Composition] + [Lighting &amp; Atmosphere] + [Camera, Lens &amp; Film Stock] + [--parameters]</code></pre>

<h3>Layer 1: The Core Subject</h3>
<p>Be explicit about age, expressions, materials, and actions rather than relying on abstract descriptors. Instead of <em>"a handsome man"</em>, specify <em>"a 34-year-old Scandinavian architect with short silver-streaked hair, thoughtful expression, wearing a tailored charcoal wool turtleneck"</em>.</p>

<h3>Layer 2: Setting the Environment</h3>
<p>Establish background depth. Mention textures, materials, and atmospheric elements (e.g., <em>"minimalist concrete brutalist gallery, floor-to-ceiling glass windows overlooking rain-slicked Tokyo streets at twilight"</em>).</p>

<h3>Layer 3: Photographic Framing</h3>
<p>Direct the virtual camera. Choose from specific cinematography terms:</p>
<ul>
    <li><strong>Close-up portrait:</strong> Tight on facial micro-expressions and skin pores.</li>
    <li><strong>Medium shot (waist-up):</strong> Balanced contextual relation between subject and backdrop.</li>
    <li><strong>Dutch angle / low-angle shot:</strong> Imparts dynamic tension or authority.</li>
    <li><strong>Wide establishing shot:</strong> Emphasizes architectural scale and environment.</li>
</ul>

<h3>Layer 4: Lighting Vocabulary</h3>
<p>Lighting defines mood more than any other element. Avoid "good lighting" and use concrete physics:</p>
<ul>
    <li><strong>Golden hour rim light:</strong> Warm backlight separating the subject silhouette from a dark backdrop.</li>
    <li><strong>Chiaroscuro:</strong> High contrast between deep shadows and dramatic key lights.</li>
    <li><strong>Diffused softbox studio lighting:</strong> Eliminates harsh shadows for luxury beauty and product shots.</li>
</ul>

<h2>Essential Midjourney v6.1 Parameters</h2>
<p>Here are the primary parameters you should know and use:</p>
<ul>
    <li><code>--ar 16:9</code> or <code>--ar 4:5</code>: Controls aspect ratio. Social carousels love 4:5; cinematic wallpapers require 16:9 or 21:9.</li>
    <li><code>--style raw</code>: Reduces Midjourney's default aesthetic bias, delivering authentic photographic textures and natural skin tones.</li>
    <li><code>--s 50</code> to <code>--s 250</code>: Controls stylization intensity. Lower numbers adhere strictly to your text; higher numbers give the model more creative flair.</li>
</ul>

<h2>Full Working Example</h2>
<blockquote>
    "Medium portrait of a female botanist examining a luminescent orchid, soft greenhouse daylight through frosted glass panels, damp moss and terracotta pots in the blurred background, shot on 35mm Hasselblad X2D 100C, 85mm f/1.4 lens, natural muted tones --ar 16:9 --style raw --s 150 --v 6.1"
</blockquote>
<p>Notice how every word performs a distinct photographic function. Start applying this 5-layer framework to your workflow and watch your generation consistency skyrocket!</p>
HTML
        ]);

        // Post 2: Flux vs Midjourney
        BlogPost::updateOrCreate(['slug' => 'flux-vs-midjourney-comparison-2026'], [
            'user_id' => $adminId,
            'blog_category_id' => $cat2->id,
            'title' => 'Flux.1 vs Midjourney v6.1: Which AI Model Should You Choose in 2026?',
            'excerpt' => 'A comprehensive head-to-head analysis comparing Black Forest Labs Flux.1 against Midjourney v6.1 on text generation, anatomy, prompt adherence, and commercial viability.',
            'reading_time_minutes' => 7,
            'status' => 'published',
            'published_at' => now()->subDays(1),
            'views_count' => 215,
            'cover_image' => 'https://images.unsplash.com/photo-1620712943543-bcc4688e7485?w=1200&auto=format&fit=crop&q=80',
            'meta_title' => 'Flux.1 vs Midjourney v6.1: Deep Comparison | PromptImage',
            'meta_description' => 'Comprehensive benchmark of Flux.1 vs Midjourney v6.1. Text rendering, prompt obedience, human skin photorealism, and commercial suitability compared.',
            'meta_keywords' => 'flux vs midjourney, flux.1 dev comparison, midjourney 6.1 vs flux, ai image models 2026',
            'content' => <<<HTML
<h2>The Generative AI Landscape in 2026</h2>
<p>For nearly two years, Midjourney dominated the AI image generation landscape virtually uncontested in aesthetic quality. That changed dramatically with the emergence of Black Forest Labs and their revolutionary <strong>Flux.1</strong> model family (Flux Schnell, Flux Dev, and Flux Pro).</p>
<p>Today, creative professionals face a genuine choice: should you maintain your Midjourney subscription, or transition your production pipeline to Flux.1? Let's break down how both engines perform across 5 critical benchmarks.</p>

<h2>1. Text &amp; Typography Rendering</h2>
<p><strong>Winner: Flux.1 (Decisively)</strong></p>
<p>Flux.1 represents a quantum leap in legible text generation. Whether you are generating billboard advertisements, coffee shop signage, book covers, or vintage posters, Flux rendered words encased in quotes with near-100% spelling precision.</p>
<p>Midjourney v6.1 improved short text capabilities significantly compared to older versions, but still struggles with complex multi-word phrases, curved labels, and font fidelity.</p>

<h2>2. Aesthetic Cohesion &amp; "Vibe"</h2>
<p><strong>Winner: Midjourney v6.1</strong></p>
<p>Midjourney's proprietary aesthetic tuning remains its greatest superpower. Even an underspecified prompt in Midjourney produces visually striking, museum-worthy lighting, color palettes, and cinematic compositions without requiring fifty technical camera keywords.</p>
<p>Flux.1, while technically proficient, is more neutral and literal. If you don't explicitly specify lighting and color grading in your prompt, Flux will render a very flat, standard exposure.</p>

<h2>3. Prompt Adherence &amp; Complex Spatial Composition</h2>
<p><strong>Winner: Flux.1</strong></p>
<p>Flux utilizes a 12-billion-parameter rectified flow transformer architecture. This gives it unmatched comprehension of spatial relationships, such as <em>"a red cube to the left of a blue sphere with a green pyramid behind them"</em>.</p>
<p>Midjourney often blends attributes between multiple subjects (known in the industry as attribute bleeding), whereas Flux maintains strict separation between subjects, colors, and spatial positioning.</p>

<h2>4. Skin Texture &amp; Human Photorealism</h2>
<p><strong>Verdict: Tie (Depends on Preference)</strong></p>
<p>When used with <code>--style raw</code>, Midjourney v6.1 produces magnificent, filmic portraits that resemble analog medium format film (Kodak Portra or Cinestill). It renders subtle skin blemishes, micro-wrinkles, and peach fuzz with lifelike character.</p>
<p>Flux.1 achieves extreme digital sharpness resembling high-end Sony Alpha or Canon mirrorless commercial captures. For corporate headshots and clean advertising, Flux shines; for moody, artistic fashion editorial, Midjourney is unmatched.</p>

<h2>Conclusion: Which Should You Use?</h2>
<ul>
    <li><strong>Choose Flux.1 if:</strong> You create commercial graphic design with integrated typography, e-commerce product layouts, UI concept mocks, or complex multi-subject scenes.</li>
    <li><strong>Choose Midjourney if:</strong> You specialize in concept art, fantasy illustration, filmic cinematography, fashion editorial, and spontaneous creative brainstorming.</li>
</ul>
HTML
        ]);

        // Post 3: Product Photography Prompts
        BlogPost::updateOrCreate(['slug' => '10-ai-product-photography-prompts-ecommerce'], [
            'user_id' => $adminId,
            'blog_category_id' => $cat3->id,
            'title' => '10 High-Converting AI Product Photography Prompts for E-Commerce & Ads',
            'excerpt' => 'A curated collection of tested prompt templates for luxury cosmetics, beverage cans, watches, and sneakers designed to generate conversion-ready product mockups.',
            'reading_time_minutes' => 5,
            'status' => 'published',
            'published_at' => now(),
            'views_count' => 89,
            'cover_image' => 'https://images.unsplash.com/photo-1522335789203-aabd1fc54bc9?w=1200&auto=format&fit=crop&q=80',
            'meta_title' => '10 AI Product Photography Prompts for E-Commerce | PromptImage',
            'meta_description' => 'Tested AI prompts for commercial product photography in Midjourney and Flux. Cosmetic bottles, drink cans, luxury packaging, and studio backdrops.',
            'meta_keywords' => 'ai product photography, e-commerce ai prompts, midjourney product mockup, commercial visual prompts',
            'content' => <<<HTML
<h2>Transforming E-Commerce Imagery with AI</h2>
<p>High-end commercial product photography traditionally requires physical prototypes, expensive studio rentals, professional lighting equipment, and days of post-production retouching. Today, AI image generators allow brands and digital agencies to produce photorealistic marketing campaigns in minutes.</p>
<p>Below are 4 ready-to-use commercial prompt templates you can copy and customize immediately.</p>

<h2>Template 1: Luxury Serum Dropper Bottle</h2>
<blockquote>
    "Commercial studio advertising photography of an amber glass skincare serum bottle with a matte black dropper pipette. The bottle stands upright on a wet, polished black marble slab with gentle water ripples reflecting soft neon violet and gold rim lights. Subtle mist in the background, sharp studio focus, premium luxury cosmetic branding, shot on 100mm macro lens f/2.8, clean 8k --ar 4:5 --style raw"
</blockquote>
<p><strong>Why it works:</strong> The combination of reflective wet marble, amber glass transparency, and two-tone rim lighting creates an unmistakable luxury visual hierarchy.</p>

<h2>Template 2: Chilled Energy Drink Can with Condensation</h2>
<blockquote>
    "Extreme close-up commercial product shot of a sleek matte metallic crimson energy drink can standing on clean white studio podium. The can surface is densely covered with crystal-clear chilled condensation droplets. Splashes of pure water freeze in mid-air around the base, high speed shutter 1/8000s, dramatic side strobe lighting, vibrant refreshing aesthetic --ar 16:9 --style raw"
</blockquote>
<p><strong>Why it works:</strong> Specifying condensation droplets and high-speed water splash freezes captures energetic motion that conveys refreshment.</p>

<h2>Template 3: Minimalist Organic Ceramic Diffuser</h2>
<blockquote>
    "Nordic minimalist interior product photography of an off-white textured ceramic aromatherapy diffuser resting on a light oak wooden shelf. A delicate curl of white vapor rises softly from the top. Soft morning sunlight streams through sheer linen curtains creating geometric window shadows on a warm plaster wall, peaceful Zen atmosphere, Hasselblad medium format --ar 4:5"
</blockquote>

<h2>Tips for Commercial Product Prompts</h2>
<ul>
    <li><strong>Specify the podium or surface:</strong> Polished travertine, raw slate, frosted glass, or brushed steel grounds your product realistically.</li>
    <li><strong>Control reflections:</strong> Transparent and metallic materials require explicit reflection instructions (e.g. <em>"soft gradient box reflections on the curved glass"</em>).</li>
    <li><strong>Negative prompt for clean backgrounds:</strong> In Flux and SDXL, use negative prompts for text artifacts, dust, fingerprints, or distorted logos.</li>
</ul>
HTML
        ]);
    }
}
