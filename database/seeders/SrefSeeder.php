<?php

namespace Database\Seeders;

use App\Models\Prompt;
use App\Models\Sref;
use Illuminate\Database\Seeder;

class SrefSeeder extends Seeder
{
    public function run(): void
    {
        $sampleImages = Prompt::whereNotNull('image_path')->limit(25)->pluck('image_path')->toArray();
        $imgIdx = 0;

        $styles = [
            [
                'code' => '284910284',
                'title' => 'Moody Dark Neo-Noir Cyberpunk',
                'category' => 'Cyberpunk',
                'description' => 'Deep obsidian shadows contrasted with piercing cyan and magenta neon hues. Cinematic film grain and slick wet pavement reflections.',
                'color_palette' => ['#0F172A', '#06B6D4', '#EC4899', '#3B82F6'],
                'tags' => ['cyberpunk', 'neo-noir', 'neon', 'rain'],
                'likes_count' => 342,
                'copies_count' => 891,
                'is_featured' => true,
            ],
            [
                'code' => '391028472',
                'title' => 'Vintage 1970s Kodachrome Editorial',
                'category' => 'Vintage',
                'description' => 'Warm faded amber highlights, rich saturated reds, retro lens aberrations and natural film emulsion grain.',
                'color_palette' => ['#78350F', '#F59E0B', '#EF4444', '#FEF3C7'],
                'tags' => ['kodachrome', '70s', 'editorial', 'vintage'],
                'likes_count' => 285,
                'copies_count' => 640,
                'is_featured' => true,
            ],
            [
                'code' => '849201948',
                'title' => 'Minimalist Bauhaus Graphic Vector',
                'category' => 'Minimalist',
                'description' => 'Crisp geometric contours, primary color blocking, generous negative space and Swiss typography influence.',
                'color_palette' => ['#18181B', '#E11D48', '#2563EB', '#FACC15'],
                'tags' => ['bauhaus', 'minimalist', 'vector', 'geometry'],
                'likes_count' => 198,
                'copies_count' => 452,
                'is_featured' => true,
            ],
            [
                'code' => '174920184',
                'title' => 'Studio Ghibli Nostalgic Watercolor',
                'category' => 'Anime',
                'description' => 'Whimsical hand-painted gouache scenery, fluffy cumulus clouds, vibrant meadows and gentle pastel morning light.',
                'color_palette' => ['#14532D', '#22C55E', '#38BDF8', '#FEF08A'],
                'tags' => ['ghibli', 'anime', 'watercolor', 'nostalgia'],
                'likes_count' => 520,
                'copies_count' => 1240,
                'is_featured' => true,
            ],
            [
                'code' => '948201947',
                'title' => 'Commercial Luxury Metallic Product Mockup',
                'category' => 'Commercial',
                'description' => 'Matte frosted metal textures, photorealistic cold condensation beads, balanced diffused studio lighting on clean pedestal.',
                'color_palette' => ['#1E293B', '#94A3B8', '#F8FAFC', '#E2E8F0'],
                'tags' => ['commercial', 'beverage', 'packaging', 'studio'],
                'likes_count' => 410,
                'copies_count' => 980,
                'is_featured' => true,
            ],
            [
                'code' => '628401928',
                'title' => 'Tactile Isometric 3D Claymation',
                'category' => '3D Render',
                'description' => 'Charming isometric miniature diorama with tactile fingerprint clay textures, soft studio ambient occlusion, Blender Cycles.',
                'color_palette' => ['#831843', '#F472B6', '#FBCFE8', '#FDE047'],
                'tags' => ['claymation', 'isometric', '3d', 'cute'],
                'likes_count' => 275,
                'copies_count' => 510,
                'is_featured' => false,
            ],
            [
                'code' => '482019483',
                'title' => 'Dark Gothic Renaissance Oil Painting',
                'category' => 'Dark Fantasy',
                'description' => 'Caravaggio chiaroscuro high contrast, heavy visible oil paint strokes, cracked varnish, dramatic tenebrism.',
                'color_palette' => ['#0A0A0A', '#854D0E', '#B91C1C', '#D97706'],
                'tags' => ['renaissance', 'chiaroscuro', 'oil painting', 'gothic'],
                'likes_count' => 312,
                'copies_count' => 730,
                'is_featured' => false,
            ],
            [
                'code' => '593810294',
                'title' => 'Ethereal Bioluminescent Dreamscape',
                'category' => 'Abstract',
                'description' => 'Luminescent deep-sea organisms, glowing cyan spore trails, undulating translucent ribbons in infinite dark void.',
                'color_palette' => ['#020617', '#10B981', '#06B6D4', '#A855F7'],
                'tags' => ['bioluminescent', 'ethereal', 'glow', 'abstract'],
                'likes_count' => 380,
                'copies_count' => 890,
                'is_featured' => true,
            ],
        ];

        foreach ($styles as $style) {
            $preview = $sampleImages[$imgIdx % max(1, count($sampleImages))] ?? null;
            $imgIdx++;

            Sref::updateOrCreate(['code' => $style['code']], [
                'title' => $style['title'],
                'category' => $style['category'],
                'description' => $style['description'],
                'preview_image_path' => $preview,
                'color_palette' => $style['color_palette'],
                'tags' => $style['tags'],
                'likes_count' => $style['likes_count'],
                'copies_count' => $style['copies_count'],
                'is_featured' => $style['is_featured'],
            ]);
        }
    }
}
