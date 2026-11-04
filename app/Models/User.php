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
        'email',
        'password',
        'role',
        'status',
        'bio',
        'website',
        'avatar_url',
        'credits',
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
        ];
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
