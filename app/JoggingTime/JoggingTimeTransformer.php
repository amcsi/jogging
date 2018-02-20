<?php
declare(strict_types=1);

namespace App\JoggingTime;

use App\JoggingTime;

class JoggingTimeTransformer
{
    public function __invoke(JoggingTime $joggingTime): array
    {
        return [
            'distance' => (int) $joggingTime->distance,
            'seconds' => (int) $joggingTime->seconds,
            'day' => $joggingTime->day,
        ];
    }
}
