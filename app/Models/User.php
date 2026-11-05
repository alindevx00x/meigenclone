<?php

namespace App\Models;

// use Illuminate\Contracts\Auth\MustVerifyEmail;
use Database\Factories\UserFactory;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;

class User extends Authenticatable
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
        'monetization_enabled',
        'tips_earned_total',
        'openai_api_key',
        'replicate_api_token',
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
