<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;

class PromptPack extends Model
{
    protected $fillable = [
        'user_id',
        'title',
        'slug',
        'description',
        'cover_image_path',
        'price',
        'is_free',
        'prompt_count',
        'downloads_count',
        'is_featured',
        'status',
    ];

    protected function casts(): array
    {
        return [
            'price' => 'decimal:2',
            'is_free' => 'boolean',
            'is_featured' => 'boolean',
        ];
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function prompts(): BelongsToMany
    {
        return $this->belongsToMany(Prompt::class, 'prompt_pack_items')
            ->withPivot('sort_order')
            ->withTimestamps();
    }

    public function getCoverUrlAttribute(): ?string
    {
        if ($this->cover_image_path) {
            return asset('storage/' . $this->cover_image_path);
        }

        return null;
    }
}
