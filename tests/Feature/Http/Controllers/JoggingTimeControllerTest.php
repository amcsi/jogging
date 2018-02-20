<?php
declare(strict_types=1);

namespace Tests\Feature\Http\Controllers;

use App\JoggingTime;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Laravel\Passport\Passport;
use Tests\TestCase;
use Tests\Tools\UserFixtures;

final class JoggingTimeControllerTest extends TestCase
{
    use RefreshDatabase, UserFixtures;

    public function testStoreRequiresAuthenticatedUser(): void
    {
        $response = $this->post('/api/jogging-times', []);
        $response->assertStatus(401);
    }

    public function testStore(): void
    {
        Passport::actingAs($this->admin);

        $seconds = 300;
        $distance = 1000;
        $day = '2018-02-19';
        $response = $this->post('/api/jogging-times', [
            'seconds' => $seconds,
            'distance' => $distance,
            'day' => $day,
        ]);

        $joggingTimes = JoggingTime::all();
        $this->assertCount(1, $joggingTimes);
        [$joggingTime] = $joggingTimes;

        $this->assertSame($seconds, (int) $joggingTime['seconds']);
        $this->assertSame($distance, (int) $joggingTime['distance']);
        $this->assertSame($day, $joggingTime['day']);

        $responseData = $this->getSuccesfulResponse($response);

        $this->assertSame($seconds, $responseData['seconds']);
        $this->assertSame($distance, $responseData['distance']);
        $this->assertSame($day, $responseData['day']);
    }
}
