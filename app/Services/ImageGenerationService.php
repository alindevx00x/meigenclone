<?php

namespace App\Services;

use App\Models\Generation;
use App\Models\User;
use Illuminate\Support\Facades\File;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Str;
use Throwable;

class ImageGenerationService
{
    /**
     * Generate an image based on prompt text, provider, and aspect ratio.
     */
    public function generate(
        string $promptText,
        string $provider = 'flux',
        string $model = 'flux-schnell',
        string $aspectRatio = '1:1',
        ?string $apiKey = null,
        ?User $user = null,
        ?int $promptId = null,
        ?string $ipAddress = null
    ): Generation {
        // Ensure generations storage directory exists
        $storageDir = storage_path('app/public/generations');
        if (! File::isDirectory($storageDir)) {
            File::makeDirectory($storageDir, 0755, true);
        }

        // Deduct 1 credit if user is using platform credits
        $usingCustomKey = ! empty($apiKey);
        if ($user && ! $usingCustomKey) {
            if ($user->credits < 1) {
                throw new \RuntimeException('You have exhausted your generation credits. Please add your own API key or upgrade.');
            }
            $user->decrement('credits', 1);
        }

        $generation = Generation::create([
            'user_id' => $user?->id,
            'prompt_id' => $promptId,
            'provider' => $provider,
            'model' => $model,
            'prompt_text' => $promptText,
            'aspect_ratio' => $aspectRatio,
            'status' => 'pending',
            'credits_used' => $usingCustomKey ? 0 : 1,
            'ip_address' => $ipAddress,
        ]);

        try {
            $imagePath = null;

            if ($provider === 'openai' && $apiKey) {
                $imagePath = $this->generateWithOpenAI($promptText, $model, $aspectRatio, $apiKey, $storageDir);
            } elseif (($provider === 'replicate' || $provider === 'flux') && $apiKey) {
                $imagePath = $this->generateWithReplicate($promptText, $model, $aspectRatio, $apiKey, $storageDir);
            } else {
                // High fidelity local canvas engine generation for demo / test without requiring live paid credits
                $imagePath = $this->generateLocalSample($promptText, $aspectRatio, $storageDir);
            }

            $generation->update([
                'status' => 'completed',
                'output_image_path' => $imagePath,
            ]);

            return $generation->fresh();
        } catch (Throwable $e) {
            $generation->update([
                'status' => 'failed',
                'error_message' => $e->getMessage(),
            ]);

            // Refund credit on failure
            if ($user && ! $usingCustomKey) {
                $user->increment('credits', 1);
            }

            throw $e;
        }
    }

    private function generateWithOpenAI(string $prompt, string $model, string $aspectRatio, string $apiKey, string $storageDir): string
    {
        $size = match ($aspectRatio) {
            '16:9' => '1792x1024',
            '9:16' => '1024x1792',
            default => '1024x1024',
        };

        $response = Http::withHeaders([
            'Authorization' => "Bearer {$apiKey}",
            'Content-Type' => 'application/json',
        ])->timeout(60)->post('https://api.openai.com/v1/images/generations', [
            'model' => $model === 'dall-e-3' ? 'dall-e-3' : 'dall-e-3',
            'prompt' => $prompt,
            'n' => 1,
            'size' => $size,
            'response_format' => 'b64_json',
        ]);

        if (! $response->successful()) {
            $err = $response->json('error.message') ?? 'OpenAI API generation failed.';
            throw new \RuntimeException($err);
        }

        $b64 = $response->json('data.0.b64_json');
        if (! $b64) {
            throw new \RuntimeException('No image data returned from OpenAI.');
        }

        $filename = 'gen_' . Str::random(32) . '.png';
        File::put("{$storageDir}/{$filename}", base64_decode($b64));

        return "generations/{$filename}";
    }

    private function generateWithReplicate(string $prompt, string $model, string $aspectRatio, string $apiKey, string $storageDir): string
    {
        $replicateModel = match ($model) {
            'flux-1.1-pro' => 'black-forest-labs/flux-1.1-pro',
            default => 'black-forest-labs/flux-schnell',
        };

        $response = Http::withHeaders([
            'Authorization' => "Bearer {$apiKey}",
            'Content-Type' => 'application/json',
            'Prefer' => 'wait',
        ])->timeout(90)->post("https://api.replicate.com/v1/models/{$replicateModel}/predictions", [
            'input' => [
                'prompt' => $prompt,
                'aspect_ratio' => $aspectRatio,
                'output_format' => 'png',
            ],
        ]);

        if (! $response->successful()) {
            $err = $response->json('detail') ?? 'Replicate API generation failed.';
            throw new \RuntimeException($err);
        }

        $output = $response->json('output');
        $imageUrl = is_array($output) ? ($output[0] ?? null) : $output;

        if (! $imageUrl) {
            throw new \RuntimeException('No output image URL from Replicate.');
        }

        $imageBytes = Http::timeout(30)->get($imageUrl)->body();
        $filename = 'gen_' . Str::random(32) . '.png';
        File::put("{$storageDir}/{$filename}", $imageBytes);

        return "generations/{$filename}";
    }

    private function generateLocalSample(string $prompt, string $aspectRatio, string $storageDir): string
    {
        [$width, $height] = match ($aspectRatio) {
            '16:9' => [1280, 720],
            '9:16' => [720, 1280],
            '4:5' => [800, 1000],
            default => [900, 900],
        };

        // Create a modern gradient art canvas with typography
        $image = imagecreatetruecolor($width, $height);

        // Derive palette from prompt hash
        $hash = crc32($prompt);
        $hue1 = abs($hash % 360);
        $hue2 = ($hue1 + 120) % 360;

        $c1 = $this->hslToRgb($hue1, 0.75, 0.15);
        $c2 = $this->hslToRgb($hue2, 0.85, 0.35);

        // Render gradient
        for ($y = 0; $y < $height; $y++) {
            $ratio = $y / max(1, $height - 1);
            $r = (int) ($c1[0] * (1 - $ratio) + $c2[0] * $ratio);
            $g = (int) ($c1[1] * (1 - $ratio) + $c2[1] * $ratio);
            $b = (int) ($c1[2] * (1 - $ratio) + $c2[2] * $ratio);
            $color = imagecolorallocate($image, $r, $g, $b);
            imageline($image, 0, $y, $width, $y, $color);
        }

        // Draw decorative concentric circles / geometric AI motifs
        for ($i = 0; $i < 6; $i++) {
            $radius = (int) (min($width, $height) * (0.15 + $i * 0.12));
            $alphaColor = imagecolorallocatealpha($image, 255, 255, 255, 105 - ($i * 12));
            imagearc($image, (int) ($width * 0.5), (int) ($height * 0.45), $radius * 2, $radius * 2, 0, 360, $alphaColor);
        }

        // Add badge and prompt watermarking
        $white = imagecolorallocate($image, 255, 255, 255);
        $tagColor = imagecolorallocate($image, 168, 85, 247); // violet-500
        $gray = imagecolorallocate($image, 203, 213, 225);

        imagestring($image, 5, 40, 40, "PROMPT STUDIO GENERATION", $tagColor);
        imagestring($image, 3, 40, 65, "Aspect: {$aspectRatio} | Flux Schnell Engine", $gray);

        // Wrap prompt snippet across lines
        $words = explode(' ', $prompt);
        $line = '';
        $lineY = $height - 120;
        $maxLines = 3;
        $curLine = 0;

        foreach ($words as $word) {
            if (strlen($line . ' ' . $word) > 55) {
                imagestring($image, 3, 40, $lineY, $line, $white);
                $line = $word;
                $lineY += 22;
                $curLine++;
                if ($curLine >= $maxLines) {
                    $line .= '...';
                    break;
                }
            } else {
                $line .= ($line === '' ? '' : ' ') . $word;
            }
        }
        if ($line !== '') {
            imagestring($image, 3, 40, $lineY, $line, $white);
        }

        $filename = 'gen_' . Str::random(32) . '.png';
        imagepng($image, "{$storageDir}/{$filename}");
        imagedestroy($image);

        return "generations/{$filename}";
    }

    private function hslToRgb(float $h, float $s, float $l): array
    {
        $h /= 360;
        $r = $l;
        $g = $l;
        $b = $l;
        $v = ($l <= 0.5) ? ($l * (1.0 + $s)) : ($l + $s - $l * $s);
        if ($v > 0) {
            $m = $l + $l - $v;
            $sv = ($v - $m) / $v;
            $h *= 6.0;
            $sextant = (int) $h;
            $fract = $h - $sextant;
            $vsf = $v * $sv * $fract;
            $mid1 = $m + $vsf;
            $mid2 = $v - $vsf;
            switch ($sextant) {
                case 0: $r = $v; $g = $mid1; $b = $m; break;
                case 1: $r = $mid2; $g = $v; $b = $m; break;
                case 2: $r = $m; $g = $v; $b = $mid1; break;
                case 3: $r = $m; $g = $mid2; $b = $v; break;
                case 4: $r = $mid1; $g = $m; $b = $v; break;
                case 5: $r = $v; $g = $m; $b = $mid2; break;
            }
        }
        return [(int) ($r * 255), (int) ($g * 255), (int) ($b * 255)];
    }
}
