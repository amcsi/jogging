<?php

use Faker\Generator as Faker;

$factory->define(App\JoggingTime::class, function (Faker $faker) {
    return [
        'distance_m' => random_int(1, 50) * 100,
        'minutes' => random_int(5, 120),
        'day' => $faker->unique()->dateTimeBetween('-1 month')->format('Y-m-d'),
    ];
});
