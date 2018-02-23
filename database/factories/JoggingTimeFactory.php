<?php

use Carbon\Carbon;
use Faker\Generator as Faker;

/**
 * Need to keep track of dates used for creating new fake jogging times to ensure no duplicate of day entries per user.
 * @var Carbon[] $oldestDates
 **/
$oldestDates = [];

$factory->define(App\JoggingTime::class,
    function (Faker $faker, array $attributes) use (&$oldestDates) {
        $userId = $attributes['user_id'];
        if (isset($oldestDates[$userId])) {
            $date = (clone $oldestDates[$userId])->subDays(1);
        } else {
            // Start from yesterday. This is good
            $date = new Carbon('-1 day');
        }
        $oldestDates[$userId] = $date;

    return [
        'distance_m' => random_int(1, 50) * 100,
        'minutes' => random_int(5, 120),
        'day' => $date->format('Y-m-d'),
    ];
});
