<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Prompt extends Model
{
    protected $fillable = [
        'user_id', 'category_id', 'title', 'slug', 'prompt_text', 'image_url',
        'image_path', 'model', 'status', 'is_featured', 'likes_count',
        'views_count', 'copies_count', 'approved_at', 'admin_note',
    ];

    protected function casts(): array
    {
        return [
            'is_featured' => 'boolean',
            'likes_count' => 'integer',
            'views_count' => 'integer',
            'copies_count' => 'integer',
            'approved_at' => 'datetime',
        ];
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function category(): BelongsTo
    {
        return $this->belongsTo(Category::class);
    }

    public function favoritedBy(): BelongsToMany
    {
        return $this->belongsToMany(User::class, 'favorites')->withTimestamps();
    }

    public function collections(): BelongsToMany
    {
        return $this->belongsToMany(Collection::class, 'collection_prompt')->withTimestamps();
    }

    public function packs(): BelongsToMany
    {
        return $this->belongsToMany(PromptPack::class, 'prompt_pack_items')->withTimestamps();
    }

    public function generations(): HasMany
    {
        return $this->hasMany(Generation::class);
    }

    public function analytics(): HasMany
    {
        return $this->hasMany(PromptAnalytic::class);
    }

    public function getCoverUrlAttribute(): ?string
    {
        if ($this->image_path) {
            return asset('storage/'.$this->image_path);
        }

        return $this->image_url;
    }
}
