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
            'site_name' => 'MeiGen',
            'site_tagline' => 'A thoughtful library of prompts for your next idea.',
            'allow_registrations' => '1',
            'allow_submissions' => '1',
            'require_approval' => '1',
            'prompts_per_page' => '24',
            'default_sort' => 'featured',
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
            'seo_title' => '',
            'seo_title_template' => '%page% — %site%',
            'seo_description' => '',
            'seo_keywords' => '',
            'seo_canonical_base' => '',
            'seo_robots' => 'index,follow',
            'seo_og_image' => '',
            'favicon_url' => '',
            'logo_url' => '',
            'featured_prompt_id' => '',
        ];
    }

    public static function allValues(): array
    {
        return Cache::rememberForever('directory.settings', fn () => static::query()->pluck('value', 'key')->all());
    }

    public static function current(): array
    {
        $values = static::allValues();
        unset($values['mail_password']);

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
        DB::table('site_settings')->where('key', $key)->delete();
        Cache::forget('directory.settings');
    }

    public static function updateValues(array $values): void
    {
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
