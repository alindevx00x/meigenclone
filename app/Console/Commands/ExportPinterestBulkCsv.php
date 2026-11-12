<?php

namespace App\Console\Commands;

use App\Models\Prompt;
use App\Models\SiteSetting;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\File;
use Illuminate\Support\Str;

class ExportPinterestBulkCsv extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'pinterest:export-bulk 
                            {--per-file=200 : Number of pins per CSV file}
                            {--domain= : Public domain to use for media URLs and links, e.g. https://promptimage.cc}
                            {--output-dir=storage/app/pinterest-exports : Destination directory for generated CSV files}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Generate partitioned Pinterest bulk upload CSV files (200 pins per file) based on the official Pinterest CSV template';

    /**
     * Execute the console command.
     */
    public function handle()
    {
        $perFile = max(1, (int) $this->option('per-file'));
        $rawDomain = $this->option('domain')
            ?: SiteSetting::value('seo_canonical_base')
            ?: config('app.url', 'https://promptimage.cc');

        $baseUrl = rtrim($rawDomain, '/');
        // Ensure scheme
        if (!Str::startsWith($baseUrl, ['http://', 'https://'])) {
            $baseUrl = 'https://' . $baseUrl;
        }

        $outDir = base_path($this->option('output-dir'));
        if (!File::isDirectory($outDir)) {
            File::makeDirectory($outDir, 0755, true);
        } else {
            // Clean up old CSV files in export dir
            foreach (File::glob($outDir . '/*.csv') as $oldFile) {
                @unlink($oldFile);
            }
        }

        $this->info("Exporting Pinterest Bulk Pins with base URL: {$baseUrl}");
        $this->info("Target directory: {$outDir}");

        $query = Prompt::with('category')
            ->where('status', 'approved')
            ->where(function ($q) {
                $q->whereNotNull('image_path')->orWhereNotNull('image_url');
            })
            ->orderBy('id', 'asc');

        $total = $query->count();
        if ($total === 0) {
            $this->warn('No approved prompts found to export.');
            return 0;
        }

        $this->info("Found {$total} approved prompts. Generating batches of {$perFile} pins...");

        $batchIndex = 1;
        $totalFiles = (int) ceil($total / $perFile);
        $exportedCount = 0;

        $csvHeader = [
            'Title',
            'Media URL',
            'Pinterest board',
            'Thumbnail',
            'Description',
            'Link',
            'Publish date',
            'Keywords',
        ];

        $query->chunk($perFile, function ($prompts) use (&$batchIndex, $totalFiles, $outDir, $baseUrl, $csvHeader, &$exportedCount) {
            $fileName = sprintf('pinterest-bulk-part-%02d-of-%02d.csv', $batchIndex, $totalFiles);
            $filePath = $outDir . DIRECTORY_SEPARATOR . $fileName;

            $fp = fopen($filePath, 'w');
            if (!$fp) {
                $this->error("Failed to open file for writing: {$filePath}");
                return false;
            }

            // Write CSV Header
            fputcsv($fp, $csvHeader);

            foreach ($prompts as $prompt) {
                // 1. Title (up to 100 chars on Pinterest)
                $cleanTitle = Str::limit(trim(preg_replace('/\s+/', ' ', strip_tags($prompt->title))), 95);

                // 2. Media URL (Must be a publicly accessible image URL)
                $mediaUrl = '';
                if (!empty($prompt->image_path)) {
                    $mediaUrl = $baseUrl . '/storage/' . ltrim($prompt->image_path, '/');
                } elseif (!empty($prompt->image_url)) {
                    $mediaUrl = $prompt->image_url;
                }

                // 3. Pinterest Board (Category name or fallback)
                $board = $prompt->category?->name ?: 'AI Art Prompts';

                // 4. Thumbnail (Blank for static images, only used for video pins)
                $thumbnail = '';

                // 5. Description (Pinterest limit is 500 characters)
                $promptSnippet = trim(preg_replace('/\s+/', ' ', strip_tags($prompt->prompt_text)));
                $modelTag = $prompt->model ? " | Model: {$prompt->model}" : '';
                $descPrefix = "AI Art Prompt: \"{$promptSnippet}\"";
                $descSuffix = "{$modelTag} | Free AI prompt & visual copy on PromptImage.";
                $description = Str::limit($descPrefix . ' ' . $descSuffix, 490);

                // 6. Link (Prompt detail page on live website)
                $link = $baseUrl . '/prompts/' . $prompt->slug;

                // 7. Publish date (Leave empty for instant draft/scheduling in Pinterest)
                $publishDate = '';

                // 8. Keywords (Comma-separated tags for Pinterest search discovery)
                $keywordsList = [
                    'ai art',
                    'midjourney prompt',
                    'flux prompt',
                    'ai prompts',
                    strtolower($prompt->category?->name ?? 'ai art'),
                ];
                if ($prompt->model) {
                    $keywordsList[] = strtolower($prompt->model);
                }
                // Extract 2 key words from title
                $titleWords = array_slice(array_filter(explode(' ', strtolower(preg_replace('/[^a-zA-Z0-9\s]/', '', $prompt->title))), fn($w) => strlen($w) > 3), 0, 3);
                foreach ($titleWords as $tw) {
                    $keywordsList[] = $tw;
                }
                $keywords = implode(', ', array_unique($keywordsList));

                fputcsv($fp, [
                    $cleanTitle,
                    $mediaUrl,
                    $board,
                    $thumbnail,
                    $description,
                    $link,
                    $publishDate,
                    $keywords,
                ]);

                $exportedCount++;
            }

            fclose($fp);
            $this->line("  ✓ Generated: {$fileName} (" . count($prompts) . " pins)");
            $batchIndex++;
        });

        $this->info("Successfully exported {$exportedCount} pins across {$totalFiles} CSV files!");
        $this->info("Files are located at: {$outDir}");

        return 0;
    }
}
