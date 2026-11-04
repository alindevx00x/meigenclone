<?php

namespace Database\Seeders;

use App\Models\Collection;
use App\Models\Prompt;
use App\Models\User;
use Illuminate\Database\Seeder;
use Illuminate\Support\Str;

class CollectionSeeder extends Seeder
{
    public function run(): void
    {
        $creator = User::first();
        if (! $creator) {
            return;
        }

        $collections = [
            [
                'name' => 'Minimalist Brand Identity & Logos',
                'description' => 'A curated moodboard of vector emblems, negative space monograms, and modern luxury logo concepts.',
                'prompts' => Prompt::where('title', 'like', '%logo%')->orWhere('prompt_text', 'like', '%logo%')->limit(8)->get(),
            ],
            [
                'name' => 'Cinematic Moody Photography Moodboard',
                'description' => 'Warm 35mm film tones, golden hour street photography, and intimate chiaroscuro portraiture.',
                'prompts' => Prompt::where('title', 'like', '%portrait%')->orWhere('prompt_text', 'like', '%cinematic%')->limit(8)->get(),
            ],
            [
                'name' => 'Cyberpunk Aesthetics & Dark Sci-Fi',
                'description' => 'Neon cityscapes, chrome cyborgs, rain-soaked reflections, and high-tech futuristic environments.',
                'prompts' => Prompt::where('title', 'like', '%cyberpunk%')->orWhere('prompt_text', 'like', '%neon%')->limit(8)->get(),
            ],
        ];

        foreach ($collections as $c) {
            $prompts = $c['prompts']->isNotEmpty() ? $c['prompts'] : Prompt::limit(6)->get();
            $slug = Str::slug($c['name']);

            $col = Collection::updateOrCreate([
                'user_id' => $creator->id,
                'slug' => $slug,
            ], [
                'name' => $c['name'],
                'description' => $c['description'],
                'cover_image_path' => $prompts->first()?->image_path,
                'is_public' => true,
                'prompts_count' => $prompts->count(),
            ]);

            $col->prompts()->sync($prompts->pluck('id')->toArray());
        }
    }
}
