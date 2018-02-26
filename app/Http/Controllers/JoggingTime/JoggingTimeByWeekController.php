<?php
declare(strict_types=1);

namespace App\Http\Controllers\JoggingTime;

use App\Common\JsonResponder;
use App\Http\Controllers\Controller;
use App\JoggingTime;
use App\JoggingTime\JoggingTimeAggregateTransformer;
use App\User;

/**
 * Controller for jogging times aggregated by week.
 */
class JoggingTimeByWeekController extends Controller
{
    public function index(User $user, JoggingTimeAggregateTransformer $transformer)
    {
        $this->authorize('index', [JoggingTime::class, $user]);

        $result = \DB::select(\DB::raw(file_get_contents(__DIR__ . '/aggregateByWeek.sql')));

        return JsonResponder::respond($result, $transformer);
    }
}
