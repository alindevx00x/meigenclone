<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;

class Prompt extends Model
{
    protected $fillable = [
        'user_id', 'category_id', 'title', 'slug', 'prompt_text', 'image_url',
        'image_path', 'model', 'status', 'is_featured', 'likes_count', 'approved_at', 'admin_note',
    ];

    protected function casts(): array
    {
        return ['is_featured' => 'boolean', 'approved_at' => 'datetime'];
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

    public function getCoverUrlAttribute(): ?string
    {
        if ($this->image_path) {
            return asset('storage/'.$this->image_path);
        }

        return $this->image_url;
    }
}
