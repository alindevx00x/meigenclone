<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Sref extends Model
{
    protected $fillable = [
        'code',
        'title',
        'category',
        'description',
        'preview_image_path',
        'color_palette',
        'tags',
        'likes_count',
        'copies_count',
        'is_featured',
    ];

    protected function casts(): array
    {
        return [
            'color_palette' => 'array',
            'tags' => 'array',
            'is_featured' => 'boolean',
        ];
    }

    public function getPreviewUrlAttribute(): ?string
    {
        if ($this->preview_image_path) {
            return asset('storage/' . $this->preview_image_path);
        }

        return null;
    }
}
