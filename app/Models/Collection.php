<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;

class Collection extends Model
{
    protected $fillable = [
        'user_id',
        'name',
        'slug',
        'description',
        'cover_image_path',
        'is_public',
        'prompts_count',
    ];

    protected function casts(): array
    {
        return [
            'is_public' => 'boolean',
        ];
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function prompts(): BelongsToMany
    {
        return $this->belongsToMany(Prompt::class, 'collection_prompt')
            ->withPivot('notes')
            ->withTimestamps();
    }

    public function getCoverUrlAttribute(): ?string
    {
        if ($this->cover_image_path) {
            return asset('storage/' . $this->cover_image_path);
        }

        // Fallback to first prompt cover if available
        $first = $this->prompts()->first();
        if ($first) {
            return $first->cover_url;
        }

        return null;
    }
}
