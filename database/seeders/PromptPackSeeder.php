<?php

namespace Database\Seeders;

use App\Models\Prompt;
use App\Models\PromptPack;
use App\Models\User;
use Illuminate\Database\Seeder;
use Illuminate\Support\Str;

class PromptPackSeeder extends Seeder
{
    public function run(): void
    {
        $creator = User::where('role', 'user')->first() ?? User::first();
        if (! $creator) {
            return;
        }

        $packs = [
            [
                'title' => 'Hyper-Realistic Commercial Product Mockups',
                'description' => 'A curated bundle of 20 high-converting advertising mockups for luxury perfumes, chilled beverage cans, cosmetics, and packaging design with photorealistic studio lighting.',
                'price' => 0.00,
                'is_free' => true,
                'is_featured' => true,
                'downloads' => 420,
                'category_kw' => 'Ads & Product',
            ],
            [
                'title' => 'Cinematic 35mm Film Editorial Portraits',
                'description' => 'Shot on Kodak Portra 400 with creamy bokeh, warm highlights, and authentic film grain for high-fashion magazines and editorial photography.',
                'price' => 9.99,
                'is_free' => false,
                'is_featured' => true,
                'downloads' => 310,
                'category_kw' => 'Portraits',
            ],
            [
                'title' => 'Futuristic Cyberpunk & Sci-Fi Worlds',
                'description' => 'Atmospheric neo-tokyo skylines, rain-slicked asphalt, holographic billboards, and neon-rimmed cyborg characters.',
                'price' => 0.00,
                'is_free' => true,
                'is_featured' => true,
                'downloads' => 580,
                'category_kw' => 'Wallpaper',
            ],
            [
                'title' => 'Tactile 3D Clay & Isometric Game Assets',
                'description' => 'Whimsical 3D miniature assets, cute claymation characters, and isometric gaming environments rendered in Blender Cycles.',
                'price' => 14.99,
                'is_free' => false,
                'is_featured' => false,
                'downloads' => 195,
                'category_kw' => 'Illustration & 3D',
            ],
        ];

        foreach ($packs as $pData) {
            $slug = Str::slug($pData['title']);
            $prompts = Prompt::whereHas('category', function ($q) use ($pData) {
                $q->where('name', 'like', "%{$pData['category_kw']}%");
            })->limit(10)->get();

            if ($prompts->isEmpty()) {
                $prompts = Prompt::limit(10)->get();
            }

            $firstPrompt = $prompts->first();

            $pack = PromptPack::updateOrCreate(['slug' => $slug], [
                'user_id' => $creator->id,
                'title' => $pData['title'],
                'description' => $pData['description'],
                'cover_image_path' => $firstPrompt?->image_path,
                'price' => $pData['price'],
                'is_free' => $pData['is_free'],
                'prompt_count' => $prompts->count(),
                'downloads_count' => $pData['downloads'],
                'is_featured' => $pData['is_featured'],
                'status' => 'active',
            ]);

            $pack->prompts()->sync($prompts->pluck('id')->toArray());
        }
    }
}
