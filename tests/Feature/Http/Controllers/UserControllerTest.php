<?php

namespace Tests\Feature\Http\Controllers;

use App\User;
use App\User\Role;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Laravel\Passport\Passport;
use Tests\TestCase;
use Tests\Tools\UserFixtures;

class UserControllerTest extends TestCase
{
    use RefreshDatabase, UserFixtures;

    /**
     * Test that we can register a user, the user is created, and we cannot register another user with the same email.
     */
    public function testRegisterAndLogin(): void
    {
        User::truncate();

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

    public function testRegularUserCannotListUsers(): void
    {
        $user = factory(User::class)->create(['role' => Role::USER]);

        Passport::actingAs($user);

        $this->get('/api/users')->assertStatus(403);
    }

    public function testIndex(): void
    {
        Passport::actingAs($this->admin);

        $response = $this->get('/api/users');
        $responseData = $this->assertSuccesfulResponseData($response);
        $this->assertNotEmpty($responseData);
        $userData = $responseData[0];
        $this->assertNotEmpty($userData['id']);
        $this->assertNotEmpty($userData['created_at']);
        $this->assertNotEmpty($userData['updated_at']);
        $this->assertNotEmpty($userData['email']);
        $this->assertNotEmpty($userData['role']);
        $this->assertPagination($response);
    }
}
