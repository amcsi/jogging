<?php
declare(strict_types=1);

namespace Tests\Feature\Http\Controllers;

use Tests\TestCase;

final class JoggingTimeControllerTest extends TestCase
{
    public function testStoreRequiresAuthenticatedUser(): void
    {
        $response = $this->post('/api/jogging-times', []);
        $response->assertStatus(401);
    }
}
