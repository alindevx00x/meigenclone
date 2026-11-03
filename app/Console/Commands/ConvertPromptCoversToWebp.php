<?php

namespace App\Console\Commands;

use App\Models\Prompt;
use App\Support\WebpImageStorage;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Storage;
use Throwable;

class ConvertPromptCoversToWebp extends Command
{
    protected $signature = 'prompt-covers:convert-to-webp';

    protected $description = 'Convert existing uploaded prompt covers to optimized WebP images.';

    public function handle(): int
    {
        $disk = Storage::disk('public');
        $coverPaths = Prompt::query()
            ->whereNotNull('image_path')
            ->pluck('image_path')
            ->filter(fn ($path) => ! str_ends_with(strtolower($path), '.webp'))
            ->unique();
        $converted = 0;
        $failed = 0;

        foreach ($coverPaths as $oldPath) {
            if (! $disk->exists($oldPath)) {
                $this->warn("Skipped missing cover: {$oldPath}");
                $failed++;
                continue;
            }

            try {
                $newPath = WebpImageStorage::convertStored($oldPath);
                Prompt::query()->where('image_path', $oldPath)->update(['image_path' => $newPath]);
                $disk->delete($oldPath);
                $this->info("Converted {$oldPath} → {$newPath}");
                $converted++;
            } catch (Throwable $exception) {
                $this->error("Could not convert {$oldPath}: {$exception->getMessage()}");
                $failed++;
            }
        }

        $this->info("Finished: {$converted} converted, {$failed} skipped or failed.");

        return $failed === 0 ? self::SUCCESS : self::FAILURE;
    }
}
