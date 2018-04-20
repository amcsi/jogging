<?php
declare(strict_types=1);

namespace Tests\Feature\Http\Controllers\JoggingTime;

use App\JoggingTime;
use Carbon\Carbon;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Laravel\Passport\Passport;
use Tests\TestCase;
use Tests\Tools\UserFixtures;

final class JoggingTimeByWeekControllerTest extends TestCase
{
    use UserFixtures, RefreshDatabase;

    /**
     * Test that given 2 jogging time entries within the (faked) current week, and 1 jogging entry 2 weeks before,
     * the user shouls see the aggregation of the 2 latest jogging time entries first, a hole with no entries next,
     * then the last entry as the third item.
     */
    public function testAggregateByWeek(): void
    {
        Passport::actingAs($this->user);

        // Saturday.
        $testNow = Carbon::create(2018, 1, 13, null, null, null, 'UTC');
        Carbon::setTestNow($testNow);

        $format = 'Y-m-d';
        factory(JoggingTime::class)->create([
            'user_id' => $this->user->id,
            'day' => $testNow->format($format),
            'distance_m' => 300,
            'minutes' => 30,
        ]);
        factory(JoggingTime::class)->create([
            'user_id' => $this->user->id,
            'day' => (clone $testNow)->subDays(2)->format($format),
            'distance_m' => 200,
            'minutes' => 10,
        ]);
        factory(JoggingTime::class)->create([
            'user_id' => $this->user->id,
            'day' => (clone $testNow)->subWeeks(2)->format($format),
            'distance_m' => 1000,
            'minutes' => 90,
        ]);

        $response = $this->get("/api/users/{$this->user->id}/jogging-times/by-week");
        $responseData = $this->assertSuccesfulResponseData($response);
        $this->assertCount(2, $responseData);
        $this->assertArraySubset([
            'distance_m' => 500,
            'minutes' => 40,
            'first_day' => '2018-01-07',
            'last_day' => '2018-01-13',
        ], $responseData[0]);
        $this->assertArraySubset([
            'distance_m' => 1000,
            'minutes' => 90,
            'first_day' => '2017-12-24',
            'last_day' => '2017-12-30',
        ], $responseData[1]);
    }
}
