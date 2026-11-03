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

    private static function storeContents(string $contents, string $directory): string
    {
        $webp = static::encode($contents);
        $path = trim($directory, '/').'/'.Str::random(40).'.webp';

        if (! Storage::disk('public')->put($path, $webp)) {
            throw ValidationException::withMessages(['image' => 'The converted image could not be saved. Please try another image.']);
        }

        return $path;
    }

    private static function encode(string $contents): string
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
                if (max($geometry['width'], $geometry['height']) > self::MAX_EDGE) {
                    $image->thumbnailImage(self::MAX_EDGE, self::MAX_EDGE, true, true);
                }

                $image->setImageFormat('webp');
                $image->setImageCompressionQuality(self::QUALITY);
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
                if ($largestEdge > self::MAX_EDGE) {
                    $scale = self::MAX_EDGE / $largestEdge;
                    $resized = imagescale($image, max(1, (int) round($width * $scale)), max(1, (int) round($height * $scale)));
                    if ($resized !== false) {
                        imagedestroy($image);
                        $image = $resized;
                    }
                }
                imagealphablending($image, false);
                imagesavealpha($image, true);
                ob_start();
                $success = imagewebp($image, null, self::QUALITY);
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
