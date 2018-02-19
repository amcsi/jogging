<?php

namespace Tests\Feature\Http\Controllers;

use App\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class UserControllerTest extends TestCase
{
    use RefreshDatabase;

    /**
     * Test that we can register a user, the user is created, and we cannot register another user with the same email.
     */
    public function testBasicTest(): void
    {
        $this->assertEmpty(User::all());
        $email = 'example@gmail.com';
        $password = 'myPassword';
        $requestData = [
            'email' => $email,
            'password' => $password,
        ];
        $response = $this->post('/api/users', $requestData);

        $response->assertStatus(200);
        $response->assertJson([
            'email' => 'example@gmail.com',
        ]);
        $users = User::all();
        self::assertCount(1, $users);
        $this->assertSame($email, $users[0]->email);
        $this->assertTrue(\Hash::check($password, $users[0]->password));

        $response = $this->post('/api/users', $requestData);
        $response->assertStatus(409);
    }
}
