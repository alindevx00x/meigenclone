<?php

namespace App\Http\Controllers;

use App\Models\Category;
use App\Models\Prompt;
use App\Models\Sref;
use Illuminate\Http\Request;
use Illuminate\View\View;

class RemixStudioController extends Controller
{
    public function index(Request $request): View
    {
        $promptSlug = $request->query('from');
        $basePrompt = null;
        if ($promptSlug) {
            $basePrompt = Prompt::where('slug', $promptSlug)->first();
        }

        $presetStyles = [
            'Cyberpunk Neon' => 'neon-lit reflections, chromatic aberration, futuristic cyberpunk aesthetic, octane render, 8k',
            'Cinematic 35mm Film' => 'shot on 35mm film, Kodak Portra 400, natural grain, cinematic shallow depth of field, award-winning cinematography',
            'Studio Product Photography' => 'hyper-detailed commercial studio photography, crisp condensation, softbox diffused lighting, award-winning advertising mockup',
            '80s Retro Anime' => 'vintage 1980s cel-shaded anime aesthetic, retro grain, hand-drawn keyframe, vibrant pastel palette',
            'Isometric 3D Claymation' => 'isometric 3D render, tactile clay texture, soft studio lighting, cute miniature diorama, Blender Cycles',
            'Dark Fantasy Oil Painting' => 'dark fantasy gothic oil painting, expressive impasto brushstrokes, dramatic chiaroscuro Caravaggio lighting',
            'Minimalist Vector Art' => 'clean minimalist vector illustration, flat vibrant color blocking, bold geometry, Bauhaus design aesthetic',
        ];

        $presetLighting = [
            'Golden Hour' => 'warm golden hour sun flare, soft atmospheric haze',
            'Volumetric Rays' => 'volumetric light beams filtering through fog, moody dramatic god rays',
            'Cyber Neon Rim' => 'vibrant cyan and magenta dual rim lighting, dark studio contrast',
            'Soft Studio Diffused' => 'clean soft diffused studio light, balanced fill, seamless backdrop',
            'Bioluminescent Glow' => 'bioluminescent organic glow, luminous deep-sea flora reflections',
        ];

        $presetCameras = [
            'Extreme Macro' => 'ultra-sharp macro lens, extreme microscopic texture detail, f/1.8 shallow focus',
            'Wide Cinematic' => 'wide-angle anamorphic lens, 2.39:1 letterbox, epic sense of scale',
            'Drone Aerial' => 'top-down drone bird-eye aerial shot, pristine geometric composition',
            'Portra Close-up' => 'intimate 85mm portrait lens, creamy bokeh, catchlights in the eyes',
        ];

        $featuredSrefs = Sref::where('is_featured', true)->limit(6)->get();

        return view('studio.remix', [
            'basePrompt' => $basePrompt,
            'presetStyles' => $presetStyles,
            'presetLighting' => $presetLighting,
            'presetCameras' => $presetCameras,
            'featuredSrefs' => $featuredSrefs,
            'categories' => Category::all(),
        ]);
    }
}
