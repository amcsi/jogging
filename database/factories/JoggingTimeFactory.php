<?php

use Faker\Generator as Faker;

$factory->define(App\JoggingTime::class, function (Faker $faker) {
    return [
        'distance' => random_int(100, 2000),
        'seconds' => random_int(300, 7000),
        'day' => $faker->unique()->dateTimeBetween('-1 month')->format('Y-m-d'),
    ];
});
