<?php

namespace App\Models;

use Database\Factories\UserFactory;
use Illuminate\Contracts\Auth\MustVerifyEmail;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;

class User extends Authenticatable implements MustVerifyEmail
{
    /** @use HasFactory<UserFactory> */
    use HasFactory, Notifiable;

    /**
     * The attributes that are mass assignable.
     *
     * @var list<string>
     */
    protected $fillable = [
        'name',
        'username',
        'email',
        'password',
        'role',
        'status',
        'bio',
        'website',
        'avatar_url',
        'credits',
        'is_verified',
        'tip_link',
        'tip_goal',
        'stripe_account_id',
        'paypal_email',
        'monetization_enabled',
        'tips_earned_total',
        'openai_api_key',
        'replicate_api_token',
        'email_verified_at',
    ];

    /**
     * The attributes that should be hidden for serialization.
     *
     * @var list<string>
     */
    protected $hidden = [
        'password',
        'remember_token',
        'openai_api_key',
        'replicate_api_token',
    ];

    /**
     * Get the attributes that should be cast.
     *
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'email_verified_at' => 'datetime',
            'password' => 'hashed',
            'credits' => 'integer',
            'is_verified' => 'boolean',
            'monetization_enabled' => 'boolean',
            'tips_earned_total' => 'decimal:2',
        ];
    }

    /**
     * Reserved keywords that cannot be registered or claimed as usernames.
     *
     * @var array<int, string>
     */
    public static array $reservedUsernames = [
        // System and Administration
        'admin', 'administrator', 'root', 'superuser', 'system', 'sysadmin',
        'moderator', 'mod', 'staff', 'team', 'support', 'help', 'info',
        'contact', 'security', 'official', 'billing', 'finance', 'legal',
        'compliance', 'privacy', 'terms', 'tos', 'abuse', 'webmaster',
        'hostmaster', 'postmaster', 'mailer-daemon',

        // Application Routes & Core Entity names
        'api', 'dashboard', 'prompt', 'prompts', 'category', 'categories',
        'model', 'models', 'creator', 'creators', 'collection', 'collections',
        'pack', 'packs', 'sref', 'srefs', 'studio', 'remix', 'generate',
        'explore', 'discover', 'search', 'favorite', 'favorites', 'saved',
        'user', 'users', 'member', 'members', 'profile', 'profiles',
        'account', 'settings', 'me', 'auth', 'login', 'logout', 'register',
        'signin', 'signup', 'signout', 'password', 'reset', 'verify',
        'verification', 'email', 'newsletter', 'newsletters', 'feed', 'rss',
        'sitemap', 'robots', 'up', 'health', 'app', 'home',

        // Brand and Technical Terms
        'promptimage', 'meigen', 'meigenclone', 'anonymous', 'guest',
        'null', 'undefined', 'void', 'test', 'demo', 'bot', 'crawler',
        'spider', 'webhook', 'webhooks', 'callback', 'stripe', 'paypal',
        'public', 'static', 'assets', 'build', 'storage', 'vendor',
    ];

    public static function reservedUsernames(): array
    {
        return static::$reservedUsernames;
    }

    public static function isReservedUsername(string $username): bool
    {
        $clean = strtolower(ltrim(trim($username), '@'));
        return in_array($clean, static::$reservedUsernames, true);
    }

    /**
     * Custom route key for URLs like /creators/@username or /creators/username
     */
    public function getRouteKey()
    {
        return $this->username ? '@' . $this->username : (string) $this->id;
    }

    /**
     * Resolve route binding for @username, username, or id
     */
    public function resolveRouteBinding($value, $field = null)
    {
        $clean = ltrim((string) $value, '@');

        if (preg_match('/^user[_-](\d+)$/i', $clean, $matches)) {
            $user = $this->where('username', $clean)->orWhere('id', (int) $matches[1])->first();
            if ($user) {
                return $user;
            }
        }

        return $this->where('username', $clean)
            ->orWhere('id', is_numeric($clean) ? (int) $clean : 0)
            ->first() ?? abort(404, 'Creator not found.');
    }

    public function getHandleAttribute(): string
    {
        return '@' . ($this->username ?: 'user_' . $this->id);
    }

    public function prompts(): HasMany
    {
        return $this->hasMany(Prompt::class);
    }

    public function favorites(): BelongsToMany
    {
        return $this->belongsToMany(Prompt::class, 'favorites')->withTimestamps();
    }

    public function generations(): HasMany
    {
        return $this->hasMany(Generation::class);
    }

    public function collections(): HasMany
    {
        return $this->hasMany(Collection::class);
    }

    public function promptPacks(): HasMany
    {
        return $this->hasMany(PromptPack::class);
    }

    public function receivedTips(): HasMany
    {
        return $this->hasMany(Tip::class, 'creator_id');
    }

    public function promptAnalytics(): HasMany
    {
        return $this->hasMany(PromptAnalytic::class);
    }

    public function followers(): BelongsToMany
    {
        return $this->belongsToMany(User::class, 'follows', 'following_id', 'follower_id')->withTimestamps();
    }

    public function following(): BelongsToMany
    {
        return $this->belongsToMany(User::class, 'follows', 'follower_id', 'following_id')->withTimestamps();
    }

    public function isFollowing(User $user): bool
    {
        return $this->following()->whereKey($user->id)->exists();
    }

    public function isImported(): bool
    {
        return str_ends_with($this->email, '@community.promptimage.cc');
    }

    public function getIsImportedAttribute(): bool
    {
        return $this->isImported();
    }

    public function isAdmin(): bool
    {
        return $this->role === 'admin';
    }

    public function isSuspended(): bool
    {
        return $this->status === 'suspended';
    }

    public function isActive(): bool
    {
        return $this->status !== 'suspended';
    }
}
