<?php

namespace Database\Factories;

use App\JoggingTime;
use Carbon\Carbon;
use Illuminate\Database\Eloquent\Factories\Factory;

class JoggingTimeFactory extends Factory
{
    /**
     * The name of the factory's corresponding model.
     *
     * @var string
     */
    protected $model = JoggingTime::class;

    /**
     * Define the model's default state.
     *
     * @return array
     */
    public function definition()
    {
        return [
            'distance_m' => random_int(1, 50) * 100,
            'minutes' => random_int(5, 120),
            'day' => (new Carbon('-1 day'))->format('Y-m-d'),
        ];
    }
}
