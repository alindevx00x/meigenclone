<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class PromptAnalytic extends Model
{
    protected $table = 'prompt_analytics_daily';

    protected $fillable = [
        'prompt_id',
        'user_id',
        'metric_date',
        'views',
        'copies',
        'favorites',
    ];

    protected function casts(): array
    {
        return [
            'metric_date' => 'date',
            'views' => 'integer',
            'copies' => 'integer',
            'favorites' => 'integer',
        ];
    }

    public function prompt(): BelongsTo
    {
        return $this->belongsTo(Prompt::class);
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }
}
