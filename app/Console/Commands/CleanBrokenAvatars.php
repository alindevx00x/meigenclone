<?php

namespace App\Console\Commands;

use App\Models\User;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Http;

class CleanBrokenAvatars extends Command
{
    protected $signature = 'avatars:clean-broken {--all : Check all external avatar URLs, not just twimg}';

    protected $description = 'Checks external avatar URLs (such as pbs.twimg.com) and clears any that return 404 or fail to load';

    public function handle(): int
    {
        $query = User::whereNotNull('avatar_url')
            ->where('avatar_url', '!=', '');

        if (! $this->option('all')) {
            $query->where('avatar_url', 'like', '%pbs.twimg.com%');
        } else {
            $query->where('avatar_url', 'like', 'http%')
                ->where('avatar_url', 'not like', '%/storage/%');
        }

        $users = $query->get();
        $total = $users->count();

        if ($total === 0) {
            $this->info('No external avatar URLs found to check.');
            return self::SUCCESS;
        }

        $this->info("Scanning {$total} avatar URLs for broken/404 links...");

        $cleaned = 0;
        $bar = $this->output->createProgressBar($total);
        $bar->start();

        foreach ($users as $user) {
            $url = $user->avatar_url;
            $isBroken = false;

            try {
                // Quick HEAD request with 3s timeout
                $response = Http::timeout(3)
                    ->withHeaders(['User-Agent' => 'Mozilla/5.0 (compatible; PromptImageBot/1.0)'])
                    ->head($url);

                if ($response->status() === 404 || $response->status() === 410 || $response->failed()) {
                    $isBroken = true;
                }
            } catch (\Throwable $e) {
                $isBroken = true;
            }

            if ($isBroken) {
                $user->update(['avatar_url' => null]);
                $cleaned++;
            }

            $bar->advance();
        }

        $bar->finish();
        $this->newLine(2);

        $this->info("Completed! Cleaned {$cleaned} broken avatar URLs out of {$total} checked.");
        $this->info('Affected creators will now cleanly display their initials avatar with zero 404 console errors.');

        return self::SUCCESS;
    }
}
