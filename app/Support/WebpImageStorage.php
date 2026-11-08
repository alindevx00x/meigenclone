<?php

namespace App\Support;

use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;
use Illuminate\Validation\ValidationException;
use Throwable;

class WebpImageStorage
{
    private const QUALITY = 82;

    private const MAX_EDGE = 2400;

    public static function storeUpload(UploadedFile $file, string $directory = 'prompt-covers'): string
    {
        $contents = file_get_contents($file->getRealPath());

        if ($contents === false) {
            throw ValidationException::withMessages(['image' => 'The uploaded image could not be read.']);
        }

        return static::storeContents($contents, $directory);
    }

    public static function convertStored(string $path): string
    {
        $disk = Storage::disk('public');
        $contents = $disk->get($path);
        $directory = dirname($path);

        return static::storeContents($contents, $directory === '.' ? 'prompt-covers' : $directory);
    }

    public static function storeFromUrl(string $url, string $directory = 'prompt-covers', int $timeout = 10): ?string
    {
        try {
            $context = stream_context_create([
                'http' => [
                    'timeout' => $timeout,
                    'user_agent' => 'Mozilla/5.0 (Macintosh; Intel Mac OS X 10_15_7)',
                ],
                'ssl' => [
                    'verify_peer' => false,
                    'verify_peer_name' => false,
                ],
            ]);

            $contents = @file_get_contents($url, false, $context);
            if ($contents === false || strlen($contents) < 100) {
                return null;
            }

            return static::storeContents($contents, $directory);
        } catch (Throwable $e) {
            return null;
        }
    }

    public static function storeContents(string $contents, string $directory): string
    {
        $webp = static::encode($contents);
        $filename = Str::random(40).'.webp';
        $path = trim($directory, '/').'/'.$filename;

        if (! Storage::disk('public')->put($path, $webp)) {
            throw ValidationException::withMessages(['image' => 'The converted image could not be saved. Please try another image.']);
        }

        // Automatically generate lightweight thumbnail for fast mobile grid rendering
        try {
            $thumbWebp = static::encode($contents, 540, 78);
            Storage::disk('public')->put(trim($directory, '/').'/thumbs/'.$filename, $thumbWebp);
        } catch (Throwable) {
            // Non-blocking thumbnail failure
        }

        return $path;
    }

    public static function generateThumbnail(string $path, int $maxWidth = 540, int $quality = 78): ?string
    {
        try {
            $disk = Storage::disk('public');
            if (! $disk->exists($path)) {
                return null;
            }
            $contents = $disk->get($path);
            $thumbWebp = static::encode($contents, $maxWidth, $quality);
            $dir = dirname($path);
            $filename = basename($path);
            $thumbPath = ($dir === '.' ? 'thumbs' : $dir.'/thumbs').'/'.$filename;
            $disk->put($thumbPath, $thumbWebp);

            return $thumbPath;
        } catch (Throwable) {
            return null;
        }
    }

    public static function encode(string $contents, int $maxEdge = self::MAX_EDGE, int $quality = self::QUALITY): string
    {
        if (class_exists(\Imagick::class)) {
            try {
                $source = new \Imagick();
                $source->readImageBlob($contents);
                $source->setIteratorIndex(0);
                $image = $source->getImage();
                $source->clear();

                if (method_exists($image, 'autoOrient')) {
                    $image->autoOrient();
                } elseif (method_exists($image, 'autoOrientImage')) {
                    $image->autoOrientImage();
                }

                $geometry = $image->getImageGeometry();
                if (max($geometry['width'], $geometry['height']) > $maxEdge) {
                    $image->thumbnailImage($maxEdge, $maxEdge, true, true);
                }

                $image->setImageFormat('webp');
                $image->setImageCompressionQuality($quality);
                $image->stripImage();
                $webp = $image->getImageBlob();
                $image->clear();

                if ($webp !== '' && str_starts_with($webp, 'RIFF')) {
                    return $webp;
                }
            } catch (Throwable) {
                // Try GD below when ImageMagick cannot decode this format.
            }
        }

        if (function_exists('imagewebp')) {
            $image = @imagecreatefromstring($contents);
            if ($image !== false) {
                if (function_exists('imagepalettetotruecolor')) {
                    imagepalettetotruecolor($image);
                }
                $width = imagesx($image);
                $height = imagesy($image);
                $largestEdge = max($width, $height);
                if ($largestEdge > $maxEdge) {
                    $scale = $maxEdge / $largestEdge;
                    $resized = imagescale($image, max(1, (int) round($width * $scale)), max(1, (int) round($height * $scale)));
                    if ($resized !== false) {
                        imagedestroy($image);
                        $image = $resized;
                    }
                }
                imagealphablending($image, false);
                imagesavealpha($image, true);
                ob_start();
                $success = imagewebp($image, null, $quality);
                $webp = ob_get_clean();
                imagedestroy($image);

                if ($success && is_string($webp) && str_starts_with($webp, 'RIFF')) {
                    return $webp;
                }
            }
        }

        throw ValidationException::withMessages([
            'image' => 'This image could not be converted to WebP. Please upload a JPG, PNG, GIF, or WebP image.',
        ]);
    }
}
