<?php
declare(strict_types=1);

namespace App\JoggingTime;

use App\Common\ModelTransformer;
use App\JoggingTime;

class JoggingTimeTransformer
{
    private $modelTransformer;

    public function __construct(ModelTransformer $modelTransformer)
    {
        $this->modelTransformer = $modelTransformer;
    }

    public function __invoke(JoggingTime $joggingTime): array
    {
        return array_replace($this->modelTransformer->__invoke($joggingTime),
            [
                'distance_m' => (int)$joggingTime->distance_m,
                'minutes' => (int)$joggingTime->minutes,
                'day' => $joggingTime->day,
            ]);
    }
}
