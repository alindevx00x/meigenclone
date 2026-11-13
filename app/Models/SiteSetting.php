<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Crypt;
use Illuminate\Support\Facades\DB;
use Illuminate\Contracts\Encryption\DecryptException;

class SiteSetting extends Model
{
    protected $primaryKey = 'key';

    public $incrementing = false;

    protected $keyType = 'string';

    protected $fillable = ['key', 'value'];

    public static function defaults(): array
    {
        return [
            'site_name' => 'PromptImage',
            'site_tagline' => 'A thoughtful library of prompts for your next idea.',
            'allow_registrations' => '1',
            'allow_submissions' => '1',
            'require_approval' => '1',
            'prompts_per_page' => '24',
            'default_sort' => 'random',
            'mail_settings_enabled' => '0',
            'mail_mailer' => 'smtp',
            'mail_host' => '',
            'mail_port' => '587',
            'mail_encryption' => 'tls',
            'mail_username' => '',
            'mail_from_address' => '',
            'mail_from_name' => '',
            'mail_reply_to' => '',
            'contact_email' => '',
            'seo_title' => 'PromptImage — AI Art & Prompt Gallery',
            'seo_title_template' => '%page% | PromptImage — Free AI Prompts Gallery',
            'seo_description' => 'Discover 10,000+ free AI prompts for ChatGPT, GPT Image, Nano Banana 2, and Seedance. Copy-paste prompt templates, reverse-engineer image styles, and explore curated community visual prompt galleries.',
            'seo_keywords' => 'free gpt image prompts, gpt image prompts, chatgpt image prompts, nano banana prompts, nano banana 2 prompts, seedance prompts, seedance video prompts, image to prompt, copy paste AI prompts, free AI prompt library, AI prompt gallery, meigen ai prompt, trending AI prompts',
            'seo_canonical_base' => 'https://promptimage.cc',
            'seo_robots' => 'index,follow',
            'seo_og_image' => 'https://images.unsplash.com/photo-1618005182384-a83a8bd57fbe?w=1200&h=630&auto=format&fit=crop&q=80',
            'favicon_url' => '',
            'logo_url' => '',
            'featured_prompt_id' => '',
            'require_email_verification' => '0',
            'storage_driver' => 'local',
            'storage_aws_access_key_id' => '',
            'storage_aws_secret_access_key' => '',
            'storage_aws_default_region' => 'us-east-1',
            'storage_aws_bucket' => '',
            'storage_aws_url' => '',
            'storage_aws_endpoint' => '',
            'stripe_enabled' => '0',
            'stripe_publishable_key' => '',
            'stripe_secret_key' => '',
            'stripe_webhook_secret' => '',
            'paypal_enabled' => '0',
            'paypal_client_id' => '',
            'paypal_secret' => '',
            'paypal_mode' => 'sandbox',
            'cloudflare_turnstile_enabled' => '0',
            'cloudflare_turnstile_site_key' => '',
            'cloudflare_turnstile_secret_key' => '',
            'custom_head_code' => '',
            'custom_body_code' => '',
        ];
    }

    private static ?array $memoryCache = null;

    public static function allValues(): array
    {
        if (static::$memoryCache !== null) {
            return static::$memoryCache;
        }

        return static::$memoryCache = Cache::rememberForever('directory.settings', fn () => static::query()->pluck('value', 'key')->all());
    }

    public static function current(bool $forAdmin = false): array
    {
        $values = static::allValues();
        unset($values['mail_password']);

        if (! $forAdmin) {
            unset(
                $values['storage_aws_secret_access_key'],
                $values['stripe_secret_key'],
                $values['stripe_webhook_secret'],
                $values['paypal_secret'],
                $values['storage_aws_access_key_id'],
                $values['cloudflare_turnstile_secret_key']
            );
        }

        return array_replace(static::defaults(), $values, [
            'mail_password_configured' => static::secretValue('mail_password') !== null,
        ]);
    }

    public static function value(string $key, mixed $default = null): mixed
    {
        return static::allValues()[$key] ?? $default;
    }

    public static function enabled(string $key, bool $default = false): bool
    {
        return filter_var(static::value($key, $default ? '1' : '0'), FILTER_VALIDATE_BOOL);
    }

    public static function secretValue(string $key): ?string
    {
        $encrypted = static::value($key);
        if (! is_string($encrypted) || $encrypted === '') {
            return null;
        }

        try {
            return Crypt::decryptString($encrypted);
        } catch (DecryptException) {
            return null;
        }
    }

    public static function removeValue(string $key): void
    {
        static::$memoryCache = null;
        DB::table('site_settings')->where('key', $key)->delete();
        Cache::forget('directory.settings');
    }

    public static function updateValues(array $values): void
    {
        static::$memoryCache = null;
        $now = now();
        $rows = collect($values)->map(fn ($value, $key) => [
            'key' => $key,
            'value' => (string) $value,
            'created_at' => $now,
            'updated_at' => $now,
        ])->values()->all();

        DB::table('site_settings')->upsert($rows, ['key'], ['value', 'updated_at']);
        Cache::forget('directory.settings');
    }
}
