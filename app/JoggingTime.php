<?php

namespace App;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class JoggingTime extends Model
{
    protected $fillable = ['distance_m', 'minutes', 'day'];

    protected $casts = [
        'minutes' => 'int',
        'distance_m' => 'int',
    ];

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }
}
