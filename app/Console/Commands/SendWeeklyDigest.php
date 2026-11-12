<?php

namespace App\Console\Commands;

use App\Mail\WeeklyDigestMail;
use App\Models\NewsletterSubscriber;
use App\Models\Prompt;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Mail;

class SendWeeklyDigest extends Command
{
    protected $signature = 'newsletter:send-weekly-digest
                            {--test= : Send preview digest to a specific test email}
                            {--limit=10 : Number of top prompts to include}';

    protected $description = 'Dispatches the weekly Top 10 Prompts email digest to newsletter subscribers';

    public function handle(): int
    {
        $limit = (int) $this->option('limit') ?: 10;
        $testEmail = $this->option('test');

        $this->info("Fetching Top {$limit} approved prompts...");

        $prompts = Prompt::with('category')
            ->where('status', 'approved')
            ->where(function ($q) {
                $q->whereNotNull('image_path')->orWhereNotNull('image_url');
            })
            ->orderByDesc('is_featured')
            ->orderByDesc('likes_count')
            ->orderByDesc('views_count')
            ->latest('approved_at')
            ->take($limit)
            ->get();

        if ($prompts->isEmpty()) {
            $this->warn('No approved prompts found to include in the weekly digest.');
            return self::FAILURE;
        }

        if ($testEmail) {
            $this->info("Sending direct preview digest to {$testEmail}...");
            try {
                Mail::to($testEmail)->sendNow(new WeeklyDigestMail($prompts, $testEmail));
                $this->info("Successfully sent preview digest to {$testEmail}.");
                return self::SUCCESS;
            } catch (\Throwable $e) {
                $this->error("Failed to send test digest: {$e->getMessage()}");
                Log::error("Weekly digest test send failed: {$e->getMessage()}", ['exception' => $e]);
                return self::FAILURE;
            }
        }

        $subscribers = NewsletterSubscriber::where('is_active', true)->get();

        if ($subscribers->isEmpty()) {
            $this->warn('No active subscribers found.');
            return self::SUCCESS;
        }

        $this->info("Queuing weekly digest to {$subscribers->count()} active subscribers...");

        $dispatched = 0;
        foreach ($subscribers as $subscriber) {
            try {
                Mail::to($subscriber->email)->queue(new WeeklyDigestMail($prompts, $subscriber->email));
                $dispatched++;
            } catch (\Throwable $e) {
                Log::warning("Failed queuing digest for subscriber {$subscriber->email}: {$e->getMessage()}");
            }
        }

        $this->info("Successfully queued {$dispatched} weekly digest emails.");
        Log::info("Weekly digest queued for {$dispatched} subscribers.");

        return self::SUCCESS;
    }
}
