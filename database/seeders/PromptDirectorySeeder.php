<?php

namespace Database\Seeders;

use App\Models\Category;
use App\Models\Prompt;
use App\Models\User;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;

class PromptDirectorySeeder extends Seeder
{
    public function run(): void
    {
        $admin = User::updateOrCreate(['email' => 'admin@meigen.test'], [
            'name' => 'MeiGen Admin', 'password' => Hash::make('password'), 'role' => 'admin',
        ]);
        $creator = User::updateOrCreate(['email' => 'creator@meigen.test'], [
            'name' => 'Alex Morgan', 'password' => Hash::make('password'), 'role' => 'user',
        ]);
        $mika = User::updateOrCreate(['email' => 'mika@meigen.test'], [
            'name' => 'Mika Sato', 'password' => Hash::make('password'), 'role' => 'user',
        ]);
        $sam = User::updateOrCreate(['email' => 'sam@meigen.test'], [
            'name' => 'Sam Rivera', 'password' => Hash::make('password'), 'role' => 'user',
        ]);

        $categories = [];
        foreach ([
            ['Ads & Product', 'ads-product', 'Product shots and campaign-ready ideas', '◈'],
            ['Brand & Logo', 'brand-logo', 'Identity systems with a point of view', '✳'],
            ['Videos', 'videos', 'Motion, stories, and cinematic frames', '▷'],
            ['Illustration & 3D', 'illustration-3d', 'Illustrated worlds and dimensional art', '◉'],
            ['Posters & Visuals', 'posters-visuals', 'Editorial posters and visual stories', '▧'],
            ['Portraits', 'portraits', 'Character studies and expressive portraits', '◌'],
            ['Storyboard & Characters', 'storyboard-characters', 'Characters, scenes, and storyboards', '✦'],
            ['Wallpaper', 'wallpaper', 'Atmospheric art for your favorite screens', '✧'],
        ] as [$name, $slug, $description, $icon]) {
            $categories[$slug] = Category::updateOrCreate(['slug' => $slug], [
                'name' => $name, 'description' => $description, 'icon' => $icon, 'color' => 'violet',
            ]);
        }

        $prompts = [
            ['A little window into Istanbul', 'posters-visuals', 'GPT Image', 'https://images.meigen.ai/cdn-cgi/image/format=auto,quality=85,width=640,fit=scale-down/tweets/2116744227602547147/0.jpg', 'A sun-washed Istanbul morning seen through an old apartment window. A small wooden table in the foreground, tiny ceramic cups, trailing green leaves framing the view, ferry boats crossing the Bosphorus in the distance. Warm analog film, gentle grain, quiet domestic stillness, art directed as an independent travel magazine cover. Portrait 4:5 composition, natural Mediterranean light, tactile details.'],
            ['The city, after the last train', 'posters-visuals', 'Midjourney', 'photo-1519501025264-65ba15a82390', 'A cinematic, monochrome cityscape after midnight, photographed from the edge of a quiet railway platform. Towering modern buildings fade into mist, a single warm window glows. Deep charcoal blacks, soft ivory highlights, film photography from the 1970s, subtle grain, bold typographic negative space. Editorial cover image, dramatic vertical framing, timeless and slightly melancholic.'],
            ['Sunday in the studio', 'ads-product', 'Nanobanana', 'photo-1490481651871-ab68de25d43d', 'Create a premium fashion campaign image for a thoughtful independent clothing label. A carefully composed set of four editorial frames showing fabric texture, movement, a confident portrait, and the complete silhouette. Warm cream backdrop, refined forest green and butter yellow palette, sunlight across the floor, considered art direction, sophisticated print-campaign styling. Keep every detail tactile and believable.'],
            ['Creative agency, old soul', 'brand-logo', 'GPT Image', 'photo-1494438639946-1ebd1d20bf85', 'A polished editorial identity presentation for a small creative studio. The hero image is an artfully arranged flat lay of a cream stationery set, sculptural ceramic object, folded deep teal fabric, and a tiny brushed brass detail. Soft directional light, refined typography, premium art direction, subtle film grain, tactile paper. Balanced asymmetry and generous quiet space.'],
            ['A brighter kind of tomorrow', 'illustration-3d', 'GPT Image', 'photo-1541701494587-cb58502866ab', 'Playful hand-drawn poster illustration with a joyful character at the center, a tiny crown, oversized expressive eyes, and bold ink lines. Use hot pink, cobalt, butter yellow, and tomato red. Sprinkle hand-cut stars and imperfect marker doodles around the figure. Youthful independent-comic energy, screen-print texture, deliberately imperfect edges, high contrast, portrait poster format.'],
            ['The soft side of skincare', 'ads-product', 'GPT Image', 'photo-1608248543803-ba4f8c70ae0b', 'Premium skincare hero image, glass serum bottle on a warm ivory stone plinth, tiny water droplets catching the light, delicate branches casting soft shadows. Calm sage and oat palette, bright diffused daylight, architectural composition, close crop, luxurious but human. Leave generous uncluttered space for a simple headline, editorial beauty photography, portrait 4:5.'],
            ['Somewhere the light stays', 'wallpaper', 'Midjourney', 'photo-1500530855697-b586d89ba3ee', 'A quiet alpine landscape at the last blue moment before sunrise. Layers of distant mountains, a silver lake, tiny pines in the foreground, and a faint apricot band on the horizon. Cinematic atmospheric perspective, restrained color, crisp but natural detail, soft analog grain. Panoramic wide wallpaper, no text, balanced empty space.'],
            ['A face made of daydreams', 'portraits', 'Seedance', 'photo-1534528741775-53994a69daeb', 'Cinematic close-up portrait of a young creative looking just past the lens, warm side light, a gentle breeze lifting a few strands of hair. Dreamy soft focus around the edges, tactile skin, expressive eyes, natural freckles, shallow depth of field, analog color negative. Muted rose, amber, and cream, honest and intimate, portrait 3:4.'],
            ['The tiny world of a matcha shop', 'storyboard-characters', 'Nanobanana', 'photo-1517248135467-4c7edcad34c4', 'Design a charming miniature 3D storefront for a neighborhood matcha bar, with a tiny awning, a glowing window, tiny plants, hand-lettered sign, and warm wood details. Isometric view on a soft pale lavender ground, gentle afternoon shadows, toy-like crafted materials, clean illustration lighting, vivid but considered palette, whimsical and extremely detailed.'],
            ['Neon blooms at blue hour', 'videos', 'Seedance', 'photo-1490750967868-88aa4486c946', 'A dreamy slow-motion macro shot of a small bouquet of wildflowers on a windowsill at blue hour. The camera glides gently past translucent petals while city lights bloom softly in the background. A quiet breath of wind, delicate movement, rich cobalt shadows and warm amber bokeh, poetic independent film mood, vertical 9:16 composition.'],
            ['Objects for a slower morning', 'ads-product', 'GPT Image', 'photo-1490312278390-ab64016e0aa9', 'A sculptural ceramic coffee cup on a sunlit travertine countertop, a folded linen napkin and one small olive branch nearby. Product photography for a design-led homewares brand. Natural window light, crisp shadows, warm cream tones, tactile handmade glaze, considered negative space, close editorial framing with the feeling of a slow Sunday.'],
            ['A postcard from nowhere', 'wallpaper', 'Midjourney', 'photo-1531366936337-7c912a4589a7', 'A surreal but peaceful desert night under an enormous violet and indigo sky. A tiny lone traveler stands beside a winding path; the horizon glows peach, stars feel impossibly close. Dreamlike travel poster, silkscreen grain, limited palette, simple bold shapes, beautiful negative space, wide cinematic wallpaper, no lettering.'],
            ['A tiny story in four panels', 'storyboard-characters', 'GPT Image', 'photo-1470252649378-9c29740c9fa8', 'Four-panel visual story of a small orange cat discovering a greenhouse after rain. First panel: a closed glass door. Second: curious paws on wet tile. Third: sunlight through tropical leaves. Fourth: the cat asleep in a basket. Consistent character design and color, expressive but understated, picture-book watercolor texture, soft morning light.'],
            ['Color, shape, and good energy', 'brand-logo', 'Nanobanana', 'photo-1549490349-8643362247b5', 'A modern graphic identity moodboard for a joyful independent culture festival. Confident geometric symbols, rounded type, a sunburst mark, playful color blocking in persimmon, sky blue, lilac, and warm cream. Clean vector edges mixed with subtle printed texture. Present a logo, a ticket, and a poster as one coherent family.'],
            ['Quiet architecture in the clouds', 'posters-visuals', 'GPT Image', 'photo-1511818966892-d7d671e672a2', 'Architectural editorial photograph of a sculptural pale concrete house above a hazy green valley. Strong silhouette, a low mist layer, one dark cypress tree, large soft sky, gentle light just after rainfall. Minimal composition, rich material detail, filmic contrast, elegant travel-publication cover, portrait 4:5 with room for type.'],
            ['The dreamiest little dessert', 'ads-product', 'GPT Image', 'photo-1551024506-0bccd828d307', 'A playful, premium dessert campaign for a small patisserie. A pastel glazed donut floats above a peach-colored plinth with tiny edible flowers and sugar pearls suspended around it. Studio flash meets warm natural shadows, crisp delicious texture, whimsical but elegant, restrained blush, pistachio, and butter yellow. Portrait advertising image with no text.'],
        ];

        $likes = [41, 28, 96, 17, 62, 23, 51, 36, 19, 44, 32, 27, 58, 21, 73, 29];
        foreach ($prompts as $index => [$title, $categorySlug, $model, $imageId, $text]) {
            $author = match ($index % 4) { 0 => $creator, 1 => $mika, 2 => $sam, default => null };
            Prompt::updateOrCreate(['slug' => 'community-prompt-'.str_pad((string) ($index + 1), 2, '0', STR_PAD_LEFT)], [
                'user_id' => $author?->id,
                'category_id' => $categories[$categorySlug]->id,
                'title' => $title,
                'prompt_text' => $text,
                'image_url' => str_starts_with($imageId, 'https://') ? $imageId : "https://images.unsplash.com/{$imageId}?auto=format&fit=crop&w=960&q=85",
                'model' => $model,
                'status' => 'approved',
                'is_featured' => in_array($index, [0, 2, 4, 7], true),
                'likes_count' => $likes[$index],
                'approved_at' => now()->subHours(3 + $index * 7),
                'created_at' => now()->subDays($index + 1),
                'updated_at' => now()->subDays($index + 1),
            ]);
        }

        Prompt::updateOrCreate(['slug' => 'quiet-orbit-in-review'], [
            'user_id' => $creator->id,
            'category_id' => $categories['illustration-3d']->id,
            'title' => 'A quiet orbit in a tiny glass world',
            'prompt_text' => 'A tiny astronaut tending a garden of luminous flowers inside a glass sphere, delicate details, warm light and dreamy cinematic composition.',
            'model' => 'GPT Image', 'status' => 'pending', 'is_featured' => false, 'likes_count' => 0,
        ]);

        $admin->forceFill(['role' => 'admin'])->save();
    }
}
