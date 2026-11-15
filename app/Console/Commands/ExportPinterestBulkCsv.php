<?php

namespace App\Console\Commands;

use App\Models\Prompt;
use App\Models\SiteSetting;
use Carbon\Carbon;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\File;
use Illuminate\Support\Str;
use ZipArchive;

class ExportPinterestBulkCsv extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'pinterest:export-bulk 
                            {--per-file=200 : Number of pins per CSV file}
                            {--start-date=2026-11-15 : Start date for scheduled publishing (YYYY-MM-DD)}
                            {--start-time=17:00 : Start time on initial day (HH:MM)}
                            {--pins-per-day=6 : Number of pins to schedule per day}
                            {--domain= : Public domain to use for media URLs and links, e.g. https://promptimage.cc}
                            {--output-dir=storage/app/pinterest-exports : Destination directory for generated CSV files}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Generate partitioned Pinterest bulk upload CSV files (200 pins per file) with continuous scheduled publish dates starting Nov 15, 2026 from 17:00 (6 pins/day)';

    /**
     * Pinterest publish times for day 0 starting at 17:00.
     */
    protected array $day0Slots = [
        ['hour' => 17, 'minute' => 0],  // 05:00 PM
        ['hour' => 18, 'minute' => 15], // 06:15 PM
        ['hour' => 19, 'minute' => 30], // 07:30 PM
        ['hour' => 20, 'minute' => 45], // 08:45 PM
        ['hour' => 21, 'minute' => 45], // 09:45 PM
        ['hour' => 22, 'minute' => 45], // 10:45 PM
    ];

    /**
     * Pinterest publish times for 6 pins per day (distributed throughout high-engagement hours).
     */
    protected array $dailySlots = [
        ['hour' => 9, 'minute' => 0],   // 09:00 AM
        ['hour' => 12, 'minute' => 0],  // 12:00 PM
        ['hour' => 15, 'minute' => 0],  // 03:00 PM
        ['hour' => 18, 'minute' => 0],  // 06:00 PM
        ['hour' => 20, 'minute' => 30], // 08:30 PM
        ['hour' => 22, 'minute' => 0],  // 10:00 PM
    ];

    /**
     * Execute the console command.
     */
    public function handle()
    {
        $perFile = max(1, (int) $this->option('per-file'));
        $pinsPerDay = max(1, (int) $this->option('pins-per-day'));
        $startDateStr = (string) $this->option('start-date');

        try {
            $startDate = Carbon::parse($startDateStr)->startOfDay();
        } catch (\Throwable) {
            $startDate = Carbon::parse('2026-11-15')->startOfDay();
        }

        $rawDomain = $this->option('domain')
            ?: SiteSetting::value('seo_canonical_base')
            ?: config('app.url', 'https://promptimage.cc');

        $baseUrl = rtrim($rawDomain, '/');
        if (!Str::startsWith($baseUrl, ['http://', 'https://'])) {
            $baseUrl = 'https://' . $baseUrl;
        }

        $outDir = base_path($this->option('output-dir'));
        if (!File::isDirectory($outDir)) {
            File::makeDirectory($outDir, 0755, true);
        } else {
            foreach (File::glob($outDir . '/*.csv') as $oldFile) {
                @unlink($oldFile);
            }
        }

        $this->info("Exporting Pinterest Bulk Pins with base URL: {$baseUrl}");
        $this->info("Start date: {$startDate->format('Y-m-d')} | {$pinsPerDay} pins per day");
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

        $this->info("Found {$total} approved prompts. Generating batches of {$perFile} pins with continuous scheduled dates...");

        $batchIndex = 1;
        $totalFiles = (int) ceil($total / $perFile);
        $globalPinIndex = 0;
        $generatedFiles = [];

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

        $slotsCount = count($this->dailySlots);

        $query->chunk($perFile, function ($prompts) use (
            &$batchIndex, $totalFiles, $outDir, $baseUrl, $csvHeader, 
            &$globalPinIndex, &$generatedFiles, $startDate, $pinsPerDay, $slotsCount
        ) {
            $fileName = sprintf('pinterest-bulk-part-%02d-of-%02d.csv', $batchIndex, $totalFiles);
            $filePath = $outDir . DIRECTORY_SEPARATOR . $fileName;

            $fp = fopen($filePath, 'w');
            if (!$fp) {
                $this->error("Failed to open file for writing: {$filePath}");
                return false;
            }

            fputcsv($fp, $csvHeader);

            foreach ($prompts as $prompt) {
                // 1. Title (up to 100 chars on Pinterest)
                $cleanTitle = Str::limit(trim(preg_replace('/\s+/', ' ', strip_tags($prompt->title))), 95);

                // 2. Media URL (Must be publicly accessible on live site)
                $mediaUrl = '';
                if (!empty($prompt->image_path)) {
                    $mediaUrl = $baseUrl . '/storage/' . ltrim($prompt->image_path, '/');
                } elseif (!empty($prompt->image_url)) {
                    $mediaUrl = $prompt->image_url;
                }

                // 3. Pinterest Board (SEO optimized board name mapped from category)
                $categoryName = $prompt->category?->name ?? 'AI Art Prompts';
                $boardMap = [
                    'Ads & Product'           => 'AI Product Photography Prompts',
                    'Portraits'               => 'Cinematic AI Portraits & People',
                    'Brand & Logo'            => 'AI Logo Design & Brand Identity',
                    'Creative & Abstract'     => 'Abstract AI Art & Surrealism',
                    'Posters & Visuals'       => 'Graphic Design & Poster Prompts',
                    'Illustration & 3D'       => '3D Claymation & Illustration',
                    'Storyboard & Characters' => 'AI Character Concept Art',
                    'Wallpaper'               => 'Aesthetic AI Wallpapers 4K',
                    'Fashion & Editorial'     => 'AI Fashion & Streetwear Style',
                    'Architecture & Spaces'   => 'Architecture & Interior AI Prompts',
                    'Anime & Manga'           => 'Retro Anime & Niji Prompts',
                    'Videos'                  => 'AI Video Prompts & Motion',
                ];
                $board = $boardMap[$categoryName] ?? $categoryName;

                // 4. Thumbnail (Only used for video pins)
                $thumbnail = '';

                // 5. Description (Pinterest limit is 500 characters)
                $promptSnippet = trim(preg_replace('/\s+/', ' ', strip_tags($prompt->prompt_text)));
                $modelTag = $prompt->model ? " | Model: {$prompt->model}" : '';
                $descPrefix = "AI Art Prompt: \"{$promptSnippet}\"";
                $descSuffix = "{$modelTag} | Free AI prompt & visual copy on PromptImage.";
                $description = Str::limit($descPrefix . ' ' . $descSuffix, 490);

                // 6. Link (Prompt detail page on live website)
                $link = $baseUrl . '/prompts/' . $prompt->slug;

                // 7. Publish date: 6 pins per day starting Nov 15, 2026 from 17:00
                // Calculate which day and which daily slot this pin falls into
                $dayOffset = (int) floor($globalPinIndex / $pinsPerDay);
                $slotIndex = $globalPinIndex % $pinsPerDay;

                // Pick hour/min: Day 0 uses evening peak slots starting from 17:00, future days use high-engagement daily slots
                if ($dayOffset === 0 && !empty($this->day0Slots)) {
                    $slotConfig = $this->day0Slots[$slotIndex % count($this->day0Slots)];
                } else {
                    $slotConfig = $this->dailySlots[$slotIndex % $slotsCount];
                }
                
                $pinDate = (clone $startDate)
                    ->addDays($dayOffset)
                    ->setHour($slotConfig['hour'])
                    ->setMinute($slotConfig['minute'])
                    ->setSecond(0);

                // Standard Pinterest bulk publish date format: YYYY-MM-DDTHH:MM:SS (ISO 8601 UTC)
                $publishDate = $pinDate->format('Y-m-d\TH:i:s');

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

                $globalPinIndex++;
            }

            fclose($fp);
            $generatedFiles[] = $filePath;
            $this->line("  ✓ Generated {$fileName} (" . count($prompts) . " pins)");
            $batchIndex++;
        });

        // Create or update zip archive
        $zipPath = storage_path('app/pinterest-bulk-csv-all.zip');
        $zip = new ZipArchive();
        if ($zip->open($zipPath, ZipArchive::CREATE | ZipArchive::OVERWRITE) === true) {
            foreach ($generatedFiles as $f) {
                $zip->addFile($f, basename($f));
            }
            $zip->close();
            $this->info("✓ Compressed all CSV partition files into: {$zipPath}");
        }

        $totalDays = (int) ceil($globalPinIndex / $pinsPerDay);
        $finalDate = (clone $startDate)->addDays($totalDays - 1)->format('Y-m-d');

        $this->newLine();
        $this->info("🎉 Completed Pinterest Bulk Export!");
        $this->info("Total Pins: {$globalPinIndex}");
        $this->info("Total Files: " . count($generatedFiles));
        $this->info("Publish Schedule: {$startDate->format('Y-m-d')} to {$finalDate} ({$totalDays} days, {$pinsPerDay} pins/day)");
        $this->info("Zip Download: {$zipPath}");

        return 0;
    }
}
