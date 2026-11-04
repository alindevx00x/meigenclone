<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Generation extends Model
{
    protected $fillable = [
        'user_id',
        'prompt_id',
        'provider',
        'model',
        'prompt_text',
        'aspect_ratio',
        'output_image_path',
        'status',
        'error_message',
        'credits_used',
        'ip_address',
    ];

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function prompt(): BelongsTo
    {
        return $this->belongsTo(Prompt::class);
    }

    public function getImageUrlAttribute(): ?string
    {
        if ($this->output_image_path) {
            return asset('storage/' . $this->output_image_path);
        }

        return null;
    }
}
