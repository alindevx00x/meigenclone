<?php

namespace App\Console\Commands;

use App\Models\AiModel;
use App\Models\Category;
use App\Models\Prompt;
use App\Models\User;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\File;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;

class ScrapeMeigenPrompts extends Command
{
    protected $signature = 'prompts:scrape-meigen
        {--count=5000 : Target number of prompts to import}
        {--batch=100 : API batch limit}
        {--concurrency=25 : Parallel image download concurrency}
        {--localize-existing : Also download and localize images for existing prompts}';

    protected $description = 'Scrapes prompts from meigen.ai, downloads images locally to storage, and imports categories, creators, and AI models';

    public function handle(): int
    {
        // 1. If requested or needed, localize existing prompts first
        if ($this->option('localize-existing')) {
            $this->localizeExistingPrompts();
        }

        $targetCount = (int) $this->option('count');
        $batchSize = min(100, max(20, (int) $this->option('batch')));
        $concurrency = min(40, max(5, (int) $this->option('concurrency')));

        $this->info("Fetching up to {$targetCount} new prompts with local image storage...");

        $imported = 0;
        $skipped = 0;
        $offset = 0;
        $seenIds = [];

        // Preload existing image filenames, remote URLs, and prompt text hashes to prevent duplicates
        $existingImageUrls = Prompt::whereNotNull('image_url')->pluck('image_url', 'id')->toArray();
        $existingImagePaths = Prompt::whereNotNull('image_path')->pluck('image_path', 'id')->toArray();
        $existingTextHashes = Prompt::select('prompt_text')->get()->mapWithKeys(fn ($p) => [md5(trim($p->prompt_text)) => true])->toArray();

        // 40+ diverse search queries to ensure 5,000+ distinct prompts
        $searchQueries = [
            '', 'design', 'portrait', 'illustration', 'product', 'cinematic', 'wallpaper',
            'logo', '3d', 'character', 'anime', 'art', 'photography', 'fashion',
            'architecture', 'cyberpunk', 'fantasy', 'nature', 'vintage', 'futuristic',
            'minimalist', 'watercolor', 'digital art', 'isometric', 'neon', 'retro',
            'typography', 'food', 'space', 'landscape', 'animals', 'sci-fi', 'interior',
            'macro', 'textures', 'comic', 'render', 'hyperrealistic', 'concept art', 'poster',
            'editorial', 'urban', 'surreal', 'street style', 'automotive', 'wildlife'
        ];
        $queryIndex = 0;

        $bar = $this->output->createProgressBar($targetCount);
        $bar->start();

        // Ensure storage directory exists
        $storageDir = storage_path('app/public/prompt-covers');
        if (! File::isDirectory($storageDir)) {
            File::makeDirectory($storageDir, 0755, true);
        }

        while ($imported < $targetCount && $queryIndex < count($searchQueries)) {
            $currentQuery = $searchQueries[$queryIndex];
            $url = 'https://www.meigen.ai/api/search';
            $params = [
                'q' => $currentQuery,
                'type' => 'posts',
                'limit' => $batchSize,
                'offset' => $offset,
                'media' => 'matched-v1',
            ];

            try {
                $response = Http::withHeaders([
                    'User-Agent' => 'Mozilla/5.0 (Macintosh; Intel Mac OS X 10_15_7) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/120.0.0.0 Safari/537.36',
                    'Accept' => 'application/json',
                ])->timeout(15)->get($url, $params);

                if (! $response->successful()) {
                    $this->warn("\nAPI returned status {$response->status()} for query '{$currentQuery}' offset {$offset}. Next query.");
                    $queryIndex++;
                    $offset = 0;
                    continue;
                }

                $data = $response->json();
                $items = $data['data'] ?? [];

                if (empty($items)) {
                    $queryIndex++;
                    $offset = 0;
                    continue;
                }

                // Filter candidates that need downloading
                $pendingItems = [];
                foreach ($items as $item) {
                    if ($imported + count($pendingItems) >= $targetCount) {
                        break;
                    }

                    $id = $item['id'] ?? null;
                    if (! $id || isset($seenIds[$id])) {
                        continue;
                    }
                    $seenIds[$id] = true;

                    $text = trim($item['text'] ?? '');
                    if (mb_strlen($text) < 15) {
                        $skipped++;
                        continue;
                    }

                    $imageUrl = $item['thumbnail_url'] ?? ($item['media_urls'][0] ?? null);
                    if (! $imageUrl) {
                        $skipped++;
                        continue;
                    }

                    // Check if prompt text already exists
                    $textHash = md5($text);
                    if (isset($existingTextHashes[$textHash])) {
                        $skipped++;
                        continue;
                    }
                    $existingTextHashes[$textHash] = true;

                    // Check if already in database by image URL
                    if (in_array($imageUrl, $existingImageUrls, true)) {
                        $skipped++;
                        continue;
                    }

                    $pendingItems[] = [
                        'item' => $item,
                        'text' => $text,
                        'imageUrl' => $imageUrl,
                    ];
                }

                // Download images in concurrent chunks
                $downloadChunks = array_chunk($pendingItems, $concurrency);
                foreach ($downloadChunks as $chunk) {
                    $urlsToDownload = array_column($chunk, 'imageUrl');
                    $downloadedPaths = $this->downloadImagesConcurrently($urlsToDownload, $storageDir);

                    foreach ($chunk as $candidate) {
                        $rawUrl = $candidate['imageUrl'];
                        $localPath = $downloadedPaths[$rawUrl] ?? null;

                        if (! $localPath) {
                            $skipped++;
                            continue;
                        }

                        $item = $candidate['item'];
                        $text = $candidate['text'];

                        // 1. Author / Creator User
                        $user = $this->resolveUser($item);

                        // 2. AI Model
                        $modelName = $this->resolveModel($item['model'] ?? null);

                        // 3. Category Detection / Creation
                        $category = $this->resolveCategory($text, $modelName);

                        // 4. Clean Title Extraction
                        $title = $this->extractTitle($text, $category->name);

                        // 5. Unique Slug
                        $slug = Str::slug($title);
                        if (empty($slug)) {
                            $slug = 'prompt';
                        }
                        $uniqueSlug = $slug . '-' . Str::lower(Str::random(6));

                        // 6. Stats & Dates
                        $likes = (int) ($item['likes'] ?? 0);
                        $favorites = (int) ($item['favorites_count'] ?? 0);
                        $totalLikes = max($likes, $favorites, rand(5, 120));
                        $isFeatured = $totalLikes > 85 || ($item['rank'] ?? 0) > 0.02;

                        $createdAt = ! empty($item['created_at'])
                            ? rescue(fn () => \Carbon\Carbon::parse($item['created_at']), now(), false)
                            : now()->subDays(rand(0, 45));

                        Prompt::create([
                            'user_id' => $user->id,
                            'category_id' => $category->id,
                            'title' => mb_strimwidth($title, 0, 120, '', 'UTF-8'),
                            'slug' => $uniqueSlug,
                            'prompt_text' => $text,
                            'image_url' => null, // Explicitly null to enforce local storage
                            'image_path' => $localPath, // Relative to public disk, e.g. prompt-covers/xyz.png
                            'model' => $modelName,
                            'status' => 'approved',
                            'is_featured' => $isFeatured,
                            'likes_count' => $totalLikes,
                            'approved_at' => $createdAt,
                            'created_at' => $createdAt,
                            'updated_at' => $createdAt,
                        ]);

                        $existingImageUrls[] = $rawUrl;
                        $imported++;
                        $bar->advance();

                        if ($imported >= $targetCount) {
                            break 2;
                        }
                    }
                }

                $offset += $batchSize;
                if ($offset >= 1000) {
                    $queryIndex++;
                    $offset = 0;
                }

                usleep(50000); // 50ms pause
            } catch (\Throwable $e) {
                $this->warn("\nError fetching batch: {$e->getMessage()}. Advancing query.");
                $queryIndex++;
                $offset = 0;
            }
        }

        $bar->finish();
        $this->newLine(2);
        $this->info("Successfully imported {$imported} prompts with local images! ({$skipped} skipped)");
        $this->info("Total prompts in database: " . Prompt::count());
        $this->info("Prompts with local image: " . Prompt::whereNotNull('image_path')->count());
        $this->info("Prompts with external URL: " . Prompt::whereNotNull('image_url')->count());

        return Command::SUCCESS;
    }

    /**
     * Download multiple image URLs in parallel using curl_multi and save them to disk.
     * Returns an associative map: [ originalUrl => 'prompt-covers/filename.ext' ]
     */
    private function downloadImagesConcurrently(array $urls, string $storageDir): array
    {
        $urls = array_unique(array_filter($urls));
        if (empty($urls)) {
            return [];
        }

        $results = [];
        $mh = curl_multi_init();
        $handles = [];

        foreach ($urls as $url) {
            $ch = curl_init($url);
            curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
            curl_setopt($ch, CURLOPT_TIMEOUT, 18);
            curl_setopt($ch, CURLOPT_CONNECTTIMEOUT, 6);
            curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
            curl_setopt($ch, CURLOPT_FOLLOWLOCATION, true);
            curl_setopt($ch, CURLOPT_USERAGENT, 'Mozilla/5.0 (Macintosh; Intel Mac OS X 10_15_7)');
            curl_multi_add_handle($mh, $ch);
            $handles[$url] = $ch;
        }

        $running = null;
        do {
            curl_multi_exec($mh, $running);
            curl_multi_select($mh, 0.05);
        } while ($running > 0);

        foreach ($handles as $url => $ch) {
            $contents = curl_multi_getcontent($ch);
            $code = curl_getinfo($ch, CURLINFO_HTTP_CODE);
            curl_multi_remove_handle($mh, $ch);
            curl_close($ch);

            if ($code === 200 && is_string($contents) && strlen($contents) > 200) {
                // Determine extension based on magic bytes
                $ext = 'png';
                if (str_starts_with($contents, "\x89PNG")) {
                    $ext = 'png';
                } elseif (str_starts_with($contents, "\xFF\xD8\xFF")) {
                    $ext = 'jpg';
                } elseif (str_starts_with($contents, "RIFF") && str_contains(substr($contents, 8, 8), 'WEBP')) {
                    $ext = 'webp';
                }

                $filename = Str::random(40) . '.' . $ext;
                $fullPath = $storageDir . '/' . $filename;

                if (file_put_contents($fullPath, $contents) !== false) {
                    $results[$url] = 'prompt-covers/' . $filename;
                }
            }
        }

        curl_multi_close($mh);

        return $results;
    }

    /**
     * Localize existing prompts in database that have external image URLs
     */
    public function localizeExistingPrompts(): void
    {
        $unlocalized = Prompt::whereNotNull('image_url')->whereNull('image_path')->get();
        $count = $unlocalized->count();

        if ($count === 0) {
            $this->info("No existing prompts require local image download.");
            return;
        }

        $this->info("Localizing {$count} existing prompts to project storage...");
        $bar = $this->output->createProgressBar($count);
        $bar->start();

        $storageDir = storage_path('app/public/prompt-covers');
        if (! File::isDirectory($storageDir)) {
            File::makeDirectory($storageDir, 0755, true);
        }

        $chunks = $unlocalized->chunk(25);
        foreach ($chunks as $chunk) {
            $urls = $chunk->pluck('image_url')->toArray();
            $downloaded = $this->downloadImagesConcurrently($urls, $storageDir);

            foreach ($chunk as $prompt) {
                if (isset($downloaded[$prompt->image_url])) {
                    $prompt->image_path = $downloaded[$prompt->image_url];
                    $prompt->image_url = null; // Clear remote link
                    $prompt->save();
                }
                $bar->advance();
            }
        }

        $bar->finish();
        $this->newLine();
        $this->info("Existing prompts localized successfully.");
    }

    private function resolveUser(array $item): User
    {
        $displayName = trim($item['author_display_name'] ?? '');
        $username = trim($item['author_username'] ?? '');

        if (empty($displayName) && empty($username)) {
            $displayName = 'MeiGen Creator';
            $username = 'creator_' . Str::lower(Str::random(6));
        } elseif (empty($displayName)) {
            $displayName = $username;
        } elseif (empty($username)) {
            $username = Str::slug($displayName, '_');
        }

        $emailHandle = Str::slug($username, '') ?: Str::lower(Str::random(8));
        $email = $emailHandle . '@community.meigen.ai';

        $user = User::firstWhere('email', $email);
        if ($user) {
            return $user;
        }

        return User::create([
            'name' => Str::limit($displayName, 80, ''),
            'email' => $email,
            'role' => 'user',
            'status' => 'active',
            'avatar_url' => $item['author_avatar_url'] ?? null,
            'website' => $item['author_social_url'] ?? null,
            'password' => Hash::make(Str::random(24)),
        ]);
    }

    private function resolveModel(?string $rawModel): string
    {
        $normalized = Str::lower(trim($rawModel ?? ''));

        $mapped = match (true) {
            str_contains($normalized, 'gpt') => 'GPT Image',
            str_contains($normalized, 'seedance') => 'Seedance',
            str_contains($normalized, 'nano') || str_contains($normalized, 'imagen') => 'Nanobanana',
            str_contains($normalized, 'midjourney') || str_contains($normalized, 'mj') => 'Midjourney',
            str_contains($normalized, 'flux') => 'Flux',
            default => 'GPT Image',
        };

        AiModel::firstOrCreate(['name' => $mapped], [
            'slug' => Str::slug($mapped),
            'icon' => match ($mapped) {
                'GPT Image' => '✦',
                'Seedance' => '◉',
                'Nanobanana' => '◍',
                'Midjourney' => '✧',
                'Flux' => '◈',
                default => '◇',
            },
            'sort_order' => 10,
            'is_active' => true,
        ]);

        return $mapped;
    }

    private function resolveCategory(string $text, string $model): Category
    {
        $lower = Str::lower($text);

        $rules = [
            'Ads & Product' => ['product', 'drink', 'beverage', 'bottle', 'can', 'perfume', 'cosmetic', 'packaging', 'sneaker', 'commercial', 'brand packaging'],
            'Brand & Logo' => ['logo', 'identity', 'lettermark', 'vector icon', 'monogram', 'minimalist logo', 'emblem'],
            'Portraits' => ['portrait', 'woman', 'man', 'girl', 'boy', 'facial', 'skin texture', 'fashion model', 'close-up shot', 'gaze'],
            'Posters & Visuals' => ['poster', 'editorial', 'magazine cover', 'typography', 'graphic design', 'layout', 'condensed type'],
            'Illustration & 3D' => ['illustration', '3d render', 'claymation', 'isometric', 'vector art', 'digital painting', 'stylized 3d', 'cartoon'],
            'Storyboard & Characters' => ['character', 'storyboard', 'comic', 'mascot', 'action pose', 'superhero', 'fantasy warrior'],
            'Wallpaper' => ['wallpaper', 'landscape', 'mountain', 'scenery', 'sunset', 'night sky', 'cyberpunk city', 'architecture', 'interior design'],
            'Videos' => ['video', 'motion', 'cinematic shot', 'camera pan', 'drone view', 'film sequence'],
        ];

        foreach ($rules as $catName => $keywords) {
            foreach ($keywords as $kw) {
                if (str_contains($lower, $kw)) {
                    return Category::firstOrCreate(['name' => $catName], [
                        'slug' => Str::slug($catName),
                        'icon' => match ($catName) {
                            'Ads & Product' => '✦',
                            'Brand & Logo' => '▦',
                            'Portraits' => '◉',
                            'Posters & Visuals' => '▤',
                            'Illustration & 3D' => '◐',
                            'Storyboard & Characters' => '◈',
                            'Wallpaper' => '▥',
                            'Videos' => '▷',
                            default => '✦',
                        },
                        'color' => 'violet',
                    ]);
                }
            }
        }

        if (str_contains($lower, 'anime') || str_contains($lower, 'manga')) {
            return Category::firstOrCreate(['name' => 'Anime & Manga'], [
                'slug' => 'anime-manga',
                'icon' => '✿',
                'color' => 'pink',
            ]);
        }

        if (str_contains($lower, 'fashion') || str_contains($lower, 'clothing') || str_contains($lower, 'outfit')) {
            return Category::firstOrCreate(['name' => 'Fashion & Editorial'], [
                'slug' => 'fashion-editorial',
                'icon' => '✧',
                'color' => 'amber',
            ]);
        }

        if (str_contains($lower, 'architecture') || str_contains($lower, 'interior') || str_contains($lower, 'room')) {
            return Category::firstOrCreate(['name' => 'Architecture & Spaces'], [
                'slug' => 'architecture-spaces',
                'icon' => '🏛',
                'color' => 'emerald',
            ]);
        }

        return Category::firstOrCreate(['name' => 'Creative & Abstract'], [
            'slug' => 'creative-abstract',
            'icon' => '✹',
            'color' => 'indigo',
        ]);
    }

    private function extractTitle(string $text, string $categoryName): string
    {
        $text = trim($text);

        if (str_starts_with($text, '{') && str_ends_with($text, '}')) {
            $json = json_decode($text, true);
            if ($json) {
                $type = $json['image_generation']['photo_style']['type']
                    ?? $json['image_generation']['subject']['poses'][0]
                    ?? $json['image_generation']['subject']['expression']
                    ?? null;
                if ($type) {
                    return Str::title(trim($type));
                }
            }
        }

        $clean = preg_replace('/^(\d+:\d+|--ar\s+\d+:\d+)\s*/i', '', $text);
        $clean = preg_replace('/^(portrait|landscape|vertical|horizontal)\s*:\s*/i', '', $clean);

        $firstLine = explode("\n", $clean)[0];
        $firstLine = trim($firstLine, "\"'\u{201c}\u{201d}#* ");

        $parts = preg_split('/([.!?])\s+/', $firstLine, 2);
        $candidate = $parts[0] ?? $firstLine;

        if (mb_strlen($candidate, 'UTF-8') > 65) {
            $candidate = mb_strimwidth($candidate, 0, 60, '', 'UTF-8');
            $lastSpace = mb_strrpos($candidate, ' ', 0, 'UTF-8');
            if ($lastSpace !== false && $lastSpace > 15) {
                $candidate = mb_substr($candidate, 0, $lastSpace, 'UTF-8');
            }
        }

        $candidate = ucfirst(trim($candidate, " ,;:-\u{2013}"));

        if (mb_strlen($candidate) < 6) {
            $candidate = $categoryName . ' ' . Str::random(4);
        }

        return $candidate;
    }
}
