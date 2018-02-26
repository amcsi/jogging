<?php
declare(strict_types=1);

namespace App\JoggingTime;

class JoggingTimeAggregateTransformer
{
    public function __invoke($joggingTime): array
    {
        return [
            'distance_m' => (int)$joggingTime->distance_m,
            'minutes' => (int)$joggingTime->minutes,
            'first_day' => $joggingTime->first_day,
            'last_day' => $joggingTime->last_day,
        ];
    }
}
