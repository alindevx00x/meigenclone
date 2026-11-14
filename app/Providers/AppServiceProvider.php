<?php

namespace App\Providers;

use App\Models\User;
use App\Models\Category;
use App\Models\SiteSetting;
use Illuminate\Pagination\Paginator;
use Illuminate\Support\ServiceProvider;
use Illuminate\Support\Facades\Gate;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\View;

class AppServiceProvider extends ServiceProvider
{
    /**
     * Register any application services.
     */
    public function register(): void
    {
        //
    }

    /**
     * Bootstrap any application services.
     */
    public function boot(): void
    {
        Paginator::defaultView('partials.pagination');
        Paginator::defaultSimpleView('partials.pagination');

        Gate::define('admin', fn (User $user) => $user->isAdmin());

        $hasSiteSettings = rescue(fn () => Schema::hasTable('site_settings'), false, false);
        if ($hasSiteSettings && SiteSetting::enabled('mail_settings_enabled')) {
            $settings = SiteSetting::current();
            $mailer = $settings['mail_mailer'];
            $mailConfig = [
                'mail.default' => $mailer,
                'mail.from.address' => $settings['mail_from_address'] ?: config('mail.from.address'),
                'mail.from.name' => $settings['mail_from_name'] ?: config('mail.from.name'),
            ];

            if ($settings['mail_reply_to'] !== '') {
                $mailConfig["mail.mailers.{$mailer}.reply_to"] = [
                    'address' => $settings['mail_reply_to'],
                    'name' => $settings['mail_from_name'] ?: config('mail.from.name'),
                ];
            }

            if ($mailer === 'smtp') {
                $mailConfig += [
                    'mail.mailers.smtp.host' => $settings['mail_host'],
                    'mail.mailers.smtp.port' => (int) $settings['mail_port'],
                    'mail.mailers.smtp.scheme' => $settings['mail_encryption'] === 'ssl' ? 'smtps' : 'smtp',
                    'mail.mailers.smtp.auto_tls' => $settings['mail_encryption'] !== 'none',
                    'mail.mailers.smtp.username' => $settings['mail_username'] ?: null,
                    'mail.mailers.smtp.password' => SiteSetting::secretValue('mail_password'),
                ];
            }

            config($mailConfig);
        }

        View::composer('*', function ($view) {
            $view->with('directorySettings', SiteSetting::current());
        });
        View::composer('layouts.app', function ($view) {
            $view->with('sidebarCategories', Category::withCount([
                'prompts as prompts_count' => fn ($query) => $query->where('status', 'approved'),
            ])->orderBy('id')->get());
        });
    }
}
