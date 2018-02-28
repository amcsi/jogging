<?php
declare(strict_types=1);


namespace Tests\Feature\Http\Controllers;

use App\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

final class LoginControllerTest extends TestCase
{
    use RefreshDatabase;

    public function testLogin()
    {
        // create password grant client
        // https://laravel.com/docs/5.4/passport#password-grant-tokens
        $this->artisan('passport:client', ['--password' => null, '--no-interaction' => true]);

        $user = factory(User::class)->create();

        $response = $this->post('/api/login', [
            'email' => $user->email,
            'password' => 'secret',
        ]);

        $response->assertStatus(200);
        $data = $response->json();
        self::assertInternalType('string', $data['access_token']);
        self::assertInternalType('string', $data['refresh_token']);
    }
}
