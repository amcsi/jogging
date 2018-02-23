<?php
declare(strict_types=1);

namespace App\JoggingTime;

use App\JoggingTime;

class JoggingTimeTransformer
{
    public function __invoke(JoggingTime $joggingTime): array
    {
        return [
            'distance_m' => (int) $joggingTime->distance_m,
            'minutes' => (int) $joggingTime->minutes,
            'day' => $joggingTime->day,
        ];
    }
}
