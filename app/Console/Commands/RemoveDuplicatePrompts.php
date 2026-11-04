<?php

namespace App\Console\Commands;

use App\Models\Prompt;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\File;

class RemoveDuplicatePrompts extends Command
{
    protected $signature = 'prompts:remove-duplicates {--dry-run : Output duplicates without deleting}';

    protected $description = 'Removes duplicate prompts based on prompt_text, preserving the best record (likes, featured, earliest ID) and cleaning orphaned files';

    public function handle(): int
    {
        $isDryRun = (bool) $this->option('dry-run');

        $this->info("Scanning for duplicate prompts by prompt_text...");

        $duplicateGroups = DB::table('prompts')
            ->select('prompt_text', DB::raw('COUNT(*) as total_count'))
            ->groupBy('prompt_text')
            ->having('total_count', '>', 1)
            ->get();

        $groupCount = $duplicateGroups->count();
        if ($groupCount === 0) {
            $this->info("No duplicate prompts found. Database is completely clean!");
            return Command::SUCCESS;
        }

        $totalExtra = $duplicateGroups->sum('total_count') - $groupCount;
        $this->info("Found {$groupCount} duplicate groups containing {$totalExtra} redundant records.");

        if ($isDryRun) {
            $this->warn("Dry-run mode active. No records or files deleted.");
            return Command::SUCCESS;
        }

        $bar = $this->output->createProgressBar($groupCount);
        $bar->start();

        $deletedCount = 0;
        $filesDeleted = 0;

        foreach ($duplicateGroups as $group) {
            // Find all prompt records in this group
            $prompts = Prompt::where('prompt_text', $group->prompt_text)
                ->orderByDesc('is_featured')
                ->orderByDesc('likes_count')
                ->orderBy('id')
                ->get();

            // First one is the winner to keep
            $keep = $prompts->first();
            $duplicates = $prompts->slice(1);

            foreach ($duplicates as $duplicate) {
                // 1. Reassign any user favorites to the kept record if not already favorited
                $favoriteUserIds = DB::table('favorites')
                    ->where('prompt_id', $duplicate->id)
                    ->pluck('user_id');

                foreach ($favoriteUserIds as $userId) {
                    $alreadyFavorited = DB::table('favorites')
                        ->where('user_id', $userId)
                        ->where('prompt_id', $keep->id)
                        ->exists();

                    if (! $alreadyFavorited) {
                        DB::table('favorites')->insert([
                            'user_id' => $userId,
                            'prompt_id' => $keep->id,
                            'created_at' => now(),
                            'updated_at' => now(),
                        ]);
                    }
                }

                // 2. Delete local image file if not used by the kept record or any other record
                if ($duplicate->image_path && $duplicate->image_path !== $keep->image_path) {
                    $otherUses = Prompt::where('image_path', $duplicate->image_path)
                        ->where('id', '!=', $duplicate->id)
                        ->exists();

                    if (! $otherUses) {
                        $fullPath = storage_path('app/public/' . $duplicate->image_path);
                        if (File::exists($fullPath)) {
                            File::delete($fullPath);
                            $filesDeleted++;
                        }
                    }
                }

                // 3. Delete the duplicate record
                $duplicate->delete();
                $deletedCount++;
            }

            $bar->advance();
        }

        $bar->finish();
        $this->newLine(2);

        $this->info("Successfully removed {$deletedCount} duplicate prompts!");
        $this->info("Deleted {$filesDeleted} orphaned local image files from disk.");
        $this->info("Total prompts remaining in database: " . Prompt::count());

        $remainingDuplicates = DB::table('prompts')
            ->select('prompt_text', DB::raw('COUNT(*) as cnt'))
            ->groupBy('prompt_text')
            ->having('cnt', '>', 1)
            ->count();

        $this->info("Remaining duplicate groups: {$remainingDuplicates}");

        return Command::SUCCESS;
    }
}
