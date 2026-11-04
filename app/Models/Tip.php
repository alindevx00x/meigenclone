<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Tip extends Model
{
    protected $fillable = [
        'sender_id',
        'creator_id',
        'prompt_id',
        'amount',
        'currency',
        'message',
        'status',
        'transaction_reference',
    ];

    protected function casts(): array
    {
        return [
            'amount' => 'decimal:2',
        ];
    }

    public function sender(): BelongsTo
    {
        return $this->belongsTo(User::class, 'sender_id');
    }

    public function creator(): BelongsTo
    {
        return $this->belongsTo(User::class, 'creator_id');
    }

    public function prompt(): BelongsTo
    {
        return $this->belongsTo(Prompt::class);
    }
}
