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

        $minutes = 5;
        $distanceM = 1000;
        $day = '2018-02-19';
        $response = $this->post('/api/jogging-times', [
            'minutes' => $minutes,
            'distance_m' => $distanceM,
            'day' => $day,
        ]);

        $joggingTimes = JoggingTime::all();
        $this->assertCount(1, $joggingTimes);
        [$joggingTime] = $joggingTimes;

        $this->assertSame($minutes, (int) $joggingTime['minutes']);
        $this->assertSame($distanceM, (int) $joggingTime['distance_m']);
        $this->assertSame($day, $joggingTime['day']);

        $responseData = $this->assertSuccesfulResponseData($response);

        $this->assertSame($minutes, $responseData['minutes']);
        $this->assertSame($distanceM, $responseData['distance_m']);
        $this->assertSame($day, $responseData['day']);
    }

    public function testIndex(): void
    {
        Passport::actingAs($this->admin);

        factory(JoggingTime::class, 5)->create(['user_id' => $this->admin->id]);

        $response = $this->get('/api/jogging-times?limit=2');

        $this->assertCount(2, $this->assertSuccesfulResponseData($response));
        $paginationData = $this->assertPagination($response);
        $this->assertSame(2, $paginationData['per_page']);
        $this->assertSame(1, $paginationData['current_page']);
        $this->assertSame(5, $paginationData['total']);

        $response = $this->get('/api/jogging-times?limit=2&page=2');

        $this->assertCount(2, $this->assertSuccesfulResponseData($response));
        $paginationData = $this->assertPagination($response);
        $this->assertSame(2, $paginationData['per_page']);
        $this->assertSame(2, $paginationData['current_page']);
        $this->assertSame(5, $paginationData['total']);

        $response = $this->get('/api/jogging-times?limit=2&page=3');

        $this->assertCount(1, $this->assertSuccesfulResponseData($response));
        $paginationData = $this->assertPagination($response);
        $this->assertSame(2, $paginationData['per_page']);
        $this->assertSame(3, $paginationData['current_page']);
        $this->assertSame(5, $paginationData['total']);
    }

    public function testDestroy(): void
    {
        Passport::actingAs($this->admin);

        $joggingTime = factory(JoggingTime::class)->create(['user_id' => $this->admin->id]);

        $this->assertCount(1, JoggingTime::all());

        $response = $this->delete('/api/jogging-times/' . $joggingTime->id);
        $response->assertStatus(204);

        $this->assertCount(0, JoggingTime::all());
    }

    public function testUpdate()
    {
        Passport::actingAs($this->admin);

        $joggingTime = factory(JoggingTime::class)->create(['user_id' => $this->admin->id]);
        $joggingTimeId = $joggingTime->id;

        $this->assertCount(1, JoggingTime::all());

        $response = $this->put('/api/jogging-times/' . $joggingTime->id,
            [
                'distance_m' => 1,
                'minutes' => 1,
            ]);
        $responseData = $this->assertSuccesfulResponseData($response);

        $this->assertSame(1, $responseData['distance_m']);
        $this->assertSame(1, $responseData['minutes']);

        $joggingTimes = JoggingTime::all();
        $this->assertCount(1, $joggingTimes);
        $joggingTime = $joggingTimes[0];
        $this->assertSame($joggingTimeId, $joggingTime->id);
        $this->assertSame(1, $joggingTime->distance_m);
        $this->assertSame(1, $joggingTime->minutes);
    }
}
