<?php
declare(strict_types=1);

namespace App\JoggingTime;

use Carbon\Carbon;

/**
 * Class for iterating jogging times by week, but such that it places holes for weeks where there was not jogging.
 */
class JoggingTimeByWeekHoleIterator extends \IteratorIterator
{
    private $joggingTimesByWeek;
    private $now;
    /** @var Carbon */
    private $currentDate;
    private $currentIndex;

    public function __construct(Carbon $now, iterable $joggingTimes)
    {
        if ($joggingTimes instanceof \IteratorAggregate) {
            $joggingTimes = $joggingTimes->getIterator();
        }
        parent::__construct($joggingTimes);
        $this->joggingTimesByWeek = $joggingTimes;
        $this->now = $now;
    }

    public function rewind()
    {
        parent::rewind();
        $this->currentIndex = 0;
        $this->currentDate = (clone $this->now)->startOfWeek()->setTime(0, 0, 0);
    }

    public function current()
    {
        $innerCurrent = parent::current();
        if ($innerCurrent->first_day >= ($firstDayFormatted = $this->currentDate->format('Y-m-d'))) {
            // Current first day matches inner first day; return it.
            return $innerCurrent;
        }

        // Return a 'hole', because there weren't any jogging times this week.
        $return = new \stdClass();
        $return->distance_m = 0;
        $return->minutes = 0;
        $return->first_day = $firstDayFormatted;
        $return->last_day = (clone $this->currentDate)->addDays(6)->format('Y-m-d');
        return $return;
    }

    public function next(): void
    {
        ++$this->currentIndex;
        $this->currentDate = (clone $this->currentDate)->subWeek(1);
        $innerCurrent = parent::current();
        if ($innerCurrent->first_day > $this->currentDate->format('Y-m-d')) {
            // We've passed the inner weekly jog's first day, so let's advance the child's iterator.
            parent::next();
        }
    }

    public function key(): int
    {
        return $this->currentIndex;
    }
}
