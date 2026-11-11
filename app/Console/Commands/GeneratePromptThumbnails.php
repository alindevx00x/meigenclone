<?php

namespace App\Console\Commands;

use App\Models\Prompt;
use App\Support\WebpImageStorage;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Storage;
use Throwable;

class GeneratePromptThumbnails extends Command
{
    protected $signature = 'prompt-covers:generate-thumbnails 
                            {--limit=0 : Maximum number of thumbnails to generate in this run (0 for all)} 
                            {--force : Regenerate even if thumbnail already exists}
                            {--reverse : Process oldest prompts first instead of newest}';

    protected $description = 'Generate WebP thumbnails (max 540px) for existing prompt cover images.';

    public function handle(): int
    {
        $disk = Storage::disk('public');
        $force = (bool) $this->option('force');
        $limit = (int) $this->option('limit');
        $reverse = (bool) $this->option('reverse');

        $query = Prompt::query()
            ->whereNotNull('image_path')
            ->where('image_path', '!=', '')
            ->orderBy($reverse ? 'id' : 'id', $reverse ? 'asc' : 'desc');

        $totalFound = $query->count();
        $this->info("Found {$totalFound} prompt covers in total.");

        $prompts = $query->get(['id', 'image_path']);

        $generated = 0;
        $skipped = 0;
        $failed = 0;

        $bar = $this->output->createProgressBar($limit > 0 ? min($limit, $totalFound) : $totalFound);
        $bar->start();

        foreach ($prompts as $prompt) {
            if ($limit > 0 && $generated >= $limit) {
                break;
            }
            $path = $prompt->image_path;

            if (! $disk->exists($path)) {
                $failed++;
                $bar->advance();
                continue;
            }

            $dir = dirname($path);
            $baseName = pathinfo($path, PATHINFO_FILENAME);
            $thumbRelPath = ($dir === '.' ? 'thumbs' : $dir.'/thumbs').'/'.$baseName.'.webp';

            if (! $force && $disk->exists($thumbRelPath)) {
                $skipped++;
                $bar->advance();
                continue;
            }

            try {
                $result = WebpImageStorage::generateThumbnail($path, 540, 78);
                if ($result) {
                    $generated++;
                } else {
                    $failed++;
                }
            } catch (Throwable) {
                $failed++;
            }

            $bar->advance();
        }

        $bar->finish();
        $this->newLine(2);

        $this->info("Completed thumbnail generation:");
        $this->line("- Newly generated: <info>{$generated}</info>");
        $this->line("- Already existed: <comment>{$skipped}</comment>");
        $this->line("- Failed/Missing:  <error>{$failed}</error>");

        return self::SUCCESS;
    }
}
