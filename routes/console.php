<?php

use Illuminate\Foundation\Inspiring;
use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\Schedule;

Artisan::command('inspire', function () {
    $this->comment(Inspiring::quote());
})->purpose('Display an inspiring quote');

// Automated Scraping Scheduler (Every 12 Hours: fetches & deduplicates latest prompts, registers new categories and AI models)
Schedule::command('prompts:sync --latest-posts=100')
    ->twiceDaily(1, 13)
    ->withoutOverlapping()
    ->appendOutputTo(storage_path('logs/scraper.log'))
    ->runInBackground();

// Weekly maintenance: deduplicate prompt titles and optimize storage
Schedule::command('prompts:remove-duplicates')
    ->weeklyOn(1, '03:00')
    ->withoutOverlapping()
    ->runInBackground();

// Weekly Email Digest: send Top 10 curated prompts to newsletter subscribers every Monday at 09:00
Schedule::command('newsletter:send-weekly-digest')
    ->weeklyOn(1, '09:00')
    ->withoutOverlapping()
    ->appendOutputTo(storage_path('logs/weekly-digest.log'))
    ->runInBackground();


