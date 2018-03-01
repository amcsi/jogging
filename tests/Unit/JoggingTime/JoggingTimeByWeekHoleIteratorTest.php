<?php
declare(strict_types=1);

namespace Tests\Unit\JoggingTime;

use App\JoggingTime\JoggingTimeByWeekHoleIterator;
use Carbon\Carbon;
use PHPUnit\Framework\TestCase;

/**
 * Class for iterating jogging times by week, but such that it places holes for weeks where there was not jogging.
 */
class JoggingTimeByWeekHoleIteratorTest extends TestCase
{
    public function testIterateWithHoles(): void
    {
        Carbon::setWeekStartsAt(Carbon::SUNDAY);
        // Saturday.
        $testNow = Carbon::create(2018, 1, 13, null, null, null, 'UTC');

        $expected = [];

        $entry = new \stdClass();
        $entry->distance_m = 0;
        $entry->minutes = 0;
        $entry->first_day = '2018-01-07';
        $entry->last_day = '2018-01-13';
        $expected[] = $entry;

        $entry = new \stdClass();
        $entry->distance_m = 1;
        $entry->minutes = 1;
        $entry->first_day = '2017-12-31';
        $entry->last_day = '2018-01-06';
        $expected[] = $entry;

        $entry = new \stdClass();
        $entry->distance_m = 0;
        $entry->minutes = 0;
        $entry->first_day = '2017-12-24';
        $entry->last_day = '2017-12-30';
        $expected[] = $entry;

        $entry = new \stdClass();
        $entry->distance_m = 0;
        $entry->minutes = 0;
        $entry->first_day = '2017-12-17';
        $entry->last_day = '2017-12-23';
        $expected[] = $entry;

        $entry = new \stdClass();
        $entry->distance_m = 2;
        $entry->minutes = 2;
        $entry->first_day = '2017-12-10';
        $entry->last_day = '2017-12-16';
        $expected[] = $entry;

        // Let's create an array with the entries that have distances/minutes.
        $input = [$expected[1], $expected[4]];

        $instance = new JoggingTimeByWeekHoleIterator($testNow, new \ArrayIterator($input));
        $array = iterator_to_array($instance, false);

        $this->assertEquals($expected[0], $array[0]);
        $this->assertEquals($expected[1], $array[1]);
        $this->assertEquals($expected[2], $array[2]);
        $this->assertEquals($expected[3], $array[3]);
        $this->assertEquals($expected[4], $array[4]);
    }
}
