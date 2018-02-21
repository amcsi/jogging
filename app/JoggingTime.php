<?php

namespace App;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class JoggingTime extends Model
{
    protected $fillable = ['distance', 'seconds', 'day'];

    protected $casts = [
        'seconds' => 'int',
        'distance' => 'int',
    ];

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }
}
