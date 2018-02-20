<?php

use Faker\Generator as Faker;

$factory->define(App\JoggingTime::class, function (Faker $faker) {

    $distance = random_int(100, 2000);
    $seconds = random_int(300, 7000);

    $random = random_int(0, 100);
    if ($random < 20) {
        // No running on this day.
        $distance = 0;
        $seconds = 0;
    }
    return [
        'distance' => $distance,
        'seconds' => $seconds,
    ];
});
