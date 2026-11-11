<?php

namespace App\Services;

use App\Models\Prompt;
use App\Models\SiteSetting;
use Illuminate\Support\Facades\File;
use Illuminate\Support\Str;

class OgImageGenerator
{
    public static function generate(Prompt $prompt): string
    {
        $dir = storage_path('app/public/og-banners');
        if (! File::isDirectory($dir)) {
            File::makeDirectory($dir, 0755, true);
        }

        $destPath = $dir . '/' . $prompt->slug . '.jpg';

        if (File::exists($destPath) && filesize($destPath) > 1000) {
            return $destPath;
        }

        $width = 1200;
        $height = 630;
        $im = imagecreatetruecolor($width, $height);

        // Background color #0e0d16
        $bgColor = imagecolorallocate($im, 14, 13, 22);
        imagefilledrectangle($im, 0, 0, $width, $height, $bgColor);

        // Subtle gradient / glow effect
        $accentPurple = imagecolorallocate($im, 124, 58, 237);
        $accentIndigo = imagecolorallocate($im, 99, 102, 241);
        $white = imagecolorallocate($im, 255, 255, 255);
        $gray = imagecolorallocate($im, 156, 163, 175);
        $badgeBg = imagecolorallocate($im, 38, 35, 58);
        $badgeBorder = imagecolorallocate($im, 80, 72, 115);

        // Decorative background top banner glow
        for ($i = 0; $i < 6; $i++) {
            $glow = imagecolorallocatealpha($im, 124, 58, 237, 120 - ($i * 12));
            imagefilledellipse($im, 200, 100, 500 + ($i * 40), 300 + ($i * 30), $glow);
        }

        // Render Prompt Cover Image on Right (520x520 box)
        $coverLoaded = false;
        if ($prompt->image_path) {
            $localImagePath = storage_path('app/public/' . $prompt->image_path);
            if (File::exists($localImagePath)) {
                $ext = strtolower(pathinfo($localImagePath, PATHINFO_EXTENSION));
                $sourceImg = null;
                if ($ext === 'webp' && function_exists('imagecreatefromwebp')) {
                    $sourceImg = @imagecreatefromwebp($localImagePath);
                } elseif (($ext === 'jpg' || $ext === 'jpeg') && function_exists('imagecreatefromjpeg')) {
                    $sourceImg = @imagecreatefromjpeg($localImagePath);
                } elseif ($ext === 'png' && function_exists('imagecreatefrompng')) {
                    $sourceImg = @imagecreatefrompng($localImagePath);
                }

                if ($sourceImg) {
                    $srcW = imagesx($sourceImg);
                    $srcH = imagesy($sourceImg);
                    $targetBoxW = 510;
                    $targetBoxH = 520;
                    $targetBoxX = 630;
                    $targetBoxY = 55;

                    // Card background container
                    $cardBg = imagecolorallocate($im, 24, 23, 35);
                    imagefilledrectangle($im, $targetBoxX, $targetBoxY, $targetBoxX + $targetBoxW, $targetBoxY + $targetBoxH, $cardBg);

                    // Compute scaled dimensions to fit nicely
                    $ratio = min($targetBoxW / $srcW, $targetBoxH / $srcH);
                    $destW = (int) ($srcW * $ratio);
                    $destH = (int) ($srcH * $ratio);
                    $destX = $targetBoxX + (int) (($targetBoxW - $destW) / 2);
                    $destY = $targetBoxY + (int) (($targetBoxH - $destH) / 2);

                    imagecopyresampled($im, $sourceImg, $destX, $destY, 0, 0, $destW, $destH, $srcW, $srcH);
                    imagedestroy($sourceImg);
                    $coverLoaded = true;

                    // Subtle border around image box
                    imagerectangle($im, $targetBoxX, $targetBoxY, $targetBoxX + $targetBoxW, $targetBoxY + $targetBoxH, $badgeBorder);
                }
            }
        }

        // Left Side Content
        $leftX = 75;
        $siteName = SiteSetting::value('site_name', 'PromptImage');

        // Font discovery
        $fontPath = null;
        $possibleFonts = [
            '/usr/share/fonts/truetype/dejavu/DejaVuSans-Bold.ttf',
            '/usr/share/fonts/truetype/liberation/LiberationSans-Bold.ttf',
            '/usr/share/fonts/truetype/freefont/FreeSansBold.ttf',
            '/System/Library/Fonts/Supplemental/Arial.ttf',
            '/System/Library/Fonts/Helvetica.ttc',
        ];
        foreach ($possibleFonts as $f) {
            if (File::exists($f)) {
                $fontPath = $f;
                break;
            }
        }

        // 1. Kicker Badge: [ CATEGORY · MODEL ]
        $categoryName = strtoupper($prompt->category?->name ?? 'CREATIVE');
        $modelName = strtoupper($prompt->model ?? 'AI');
        $badgeText = "{$categoryName}  •  {$modelName}";

        if ($fontPath) {
            // Draw pill badge
            imagefilledrectangle($im, $leftX, 85, $leftX + 280, 125, $badgeBg);
            imagerectangle($im, $leftX, 85, $leftX + 280, 125, $badgeBorder);
            imagettftext($im, 11, 0, $leftX + 16, 110, $accentIndigo, $fontPath, $badgeText);

            // 2. Prompt Title (word-wrapped)
            $titleLines = static::wrapText($prompt->title, 26, 3);
            $titleY = 190;
            foreach ($titleLines as $line) {
                imagettftext($im, 24, 0, $leftX, $titleY, $white, $fontPath, $line);
                $titleY += 46;
            }

            // 3. Prompt Excerpt
            $cleanExcerpt = static::wrapText(Str::limit($prompt->prompt_text, 140), 38, 3);
            $excerptY = max($titleY + 15, 340);
            foreach ($cleanExcerpt as $line) {
                imagettftext($im, 12, 0, $leftX, $excerptY, $gray, $fontPath, $line);
                $excerptY += 26;
            }

            // 4. Footer Branding & Domain
            imagettftext($im, 15, 0, $leftX, 555, $white, $fontPath, "✦ {$siteName}");
            imagettftext($im, 11, 0, $leftX, 580, $gray, $fontPath, "Curated Prompts for Midjourney, Flux & Modern AI");
        } else {
            // Fallback GD Built-in Fonts
            imagestring($im, 4, $leftX, 95, $badgeText, $accentIndigo);
            imagestring($im, 5, $leftX, 160, Str::limit($prompt->title, 40), $white);
            imagestring($im, 3, $leftX, 220, Str::limit($prompt->prompt_text, 80), $gray);
            imagestring($im, 4, $leftX, 550, "{$siteName} - promptimage.cc", $white);
        }

        imagejpeg($im, $destPath, 90);
        imagedestroy($im);

        return $destPath;
    }

    private static function wrapText(string $text, int $maxChars = 28, int $maxLines = 3): array
    {
        $words = explode(' ', trim($text));
        $lines = [];
        $current = '';

        foreach ($words as $word) {
            if (mb_strlen($current . ' ' . $word) <= $maxChars) {
                $current = trim($current . ' ' . $word);
            } else {
                if ($current !== '') {
                    $lines[] = $current;
                }
                $current = $word;
                if (count($lines) >= $maxLines - 1) {
                    break;
                }
            }
        }

        if ($current !== '' && count($lines) < $maxLines) {
            $lines[] = $current;
        }

        if (count($lines) >= $maxLines && count($words) > 0) {
            $lastIndex = count($lines) - 1;
            $lines[$lastIndex] = Str::limit($lines[$lastIndex], $maxChars, '...');
        }

        return $lines;
    }
}
