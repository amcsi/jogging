<?php
declare(strict_types=1);

namespace App\Http\Controllers\JoggingTime;

use App\Common\JsonResponder;
use App\Http\Controllers\Controller;
use App\JoggingTime;
use App\JoggingTime\JoggingTimeAggregateTransformer;
use App\JoggingTime\JoggingTimeByWeekHoleIterator;
use App\User;
use Carbon\Carbon;
use Illuminate\Database\Connection;

/**
 * Controller for jogging times aggregated by week.
 */
class JoggingTimeByWeekController extends Controller
{
    public function index(User $user, JoggingTimeAggregateTransformer $transformer, Connection $connection)
    {
        $this->authorize('index', [JoggingTime::class, $user]);

        $result = $connection->table('jogging_times')
            ->select(
                $connection->raw("strftime('%W', day) WeekNumber"),
                $connection->raw("max(date(day, 'weekday 0', '-7 day')) first_day"),
                $connection->raw("max(date(day, 'weekday 0', '-1 day')) last_day"),
                $connection->raw("COUNT(*) AS GroupedValues"),
                $connection->raw("SUM(distance_m) AS distance_m"),
                $connection->raw("SUM(minutes) AS minutes")
            )
            ->orderBy('day', 'desc')
            ->where('user_id', $user->id)
            ->groupBy('WeekNumber')
            ->get();

        return JsonResponder::respond($result, $transformer);
    }
}
