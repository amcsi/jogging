<?php

namespace Tests;

use Illuminate\Foundation\Testing\TestCase as BaseTestCase;

abstract class TestCase extends BaseTestCase
{
    protected $defaultHeaders = [
        'Accept' => 'application/json',
    ];

    use CreatesApplication;
}
