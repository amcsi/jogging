<?php

namespace Tests;

use Illuminate\Foundation\Testing\TestCase as BaseTestCase;
use Illuminate\Foundation\Testing\TestResponse;

abstract class TestCase extends BaseTestCase
{
    protected $defaultHeaders = [
        'Accept' => 'application/json',
    ];

    /**
     * Asserts that the response is successful, has a 'data' property in its data structure, and
     * unwraps the data from the envelope.
     *
     * @param TestResponse $response
     * @return array
     */
    protected function getSuccesfulResponse(TestResponse $response)
    {
        $response->assertStatus(200);
        $response->assertJsonStructure(['data']);
        return $response->json('data');
    }

    use CreatesApplication;
}
