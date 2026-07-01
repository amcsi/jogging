<?php
declare(strict_types=1);


namespace Tests\Feature\Http\Controllers;

use App\Common\ApiExceptionCode;
use App\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Laravel\Passport\ClientRepository;
use Tests\TestCase;

final class LoginControllerTest extends TestCase
{
    use RefreshDatabase;

    public function setUp(): void
    {
        parent::setUp();

        $user = User::factory()->create();
        $client = app(ClientRepository::class)->createPasswordGrantClient('Laravel', 'users', true);
        $client->forceFill(['user_id' => $user->id])->save();

        config([
            'services.passport.password_client_id' => $client->id,
            'services.passport.password_client_secret' => $client->plainSecret,
        ]);
    }

    public function testLoginAndFetchMeWhenUserIdMatchesClientId(): void
    {
        \Illuminate\Support\Facades\DB::table('oauth_clients')->delete();
        \Illuminate\Support\Facades\DB::table('users')->delete();

        $user = User::factory()->create(['id' => 1]);

        $client = app(ClientRepository::class)->createPasswordGrantClient('Laravel', 'users', true);
        $client->forceFill([
            'id' => 1,
            'user_id' => $user->id,
        ])->save();

        config([
            'services.passport.password_client_id' => $client->id,
            'services.passport.password_client_secret' => $client->plainSecret,
        ]);

        $loginResponse = $this->post('/api/login', [
            'email' => $user->email,
            'password' => 'secret',
        ]);

        $loginResponse->assertStatus(200);
        $token = $loginResponse->json('access_token');
        self::assertIsString($token);

        $this->withHeader('Authorization', 'Bearer '.$token)
            ->get('/api/users/me')
            ->assertStatus(200)
            ->assertJsonPath('data.id', 1);
    }

    public function testMeIsUnauthenticatedWhenUserIdMatchesUnownedFirstPartyClientId(): void
    {
        \Illuminate\Support\Facades\DB::table('oauth_clients')->delete();
        \Illuminate\Support\Facades\DB::table('users')->delete();

        $user = User::factory()->create(['id' => 1]);

        $client = app(ClientRepository::class)->createPasswordGrantClient('Laravel', 'users', true);
        $client->forceFill(['id' => 1])->save();

        config([
            'services.passport.password_client_id' => $client->id,
            'services.passport.password_client_secret' => $client->plainSecret,
        ]);

        $loginResponse = $this->post('/api/login', [
            'email' => $user->email,
            'password' => 'secret',
        ]);

        $loginResponse->assertStatus(200);

        $this->withHeader('Authorization', 'Bearer '.$loginResponse->json('access_token'))
            ->get('/api/users/me')
            ->assertStatus(401);
    }

    public function testLogin()
    {
        $user = User::factory()->create();

        $oldErrorReporting = error_reporting(error_reporting() & ~E_USER_DEPRECATED);

        $response = $this->post('/api/login', [
            'email' => $user->email,
            'password' => 'secret',
        ]);

        $response->assertStatus(200);
        $data = $response->json();
        self::assertIsString($data['access_token']);
        self::assertIsString($data['refresh_token']);

        error_reporting($oldErrorReporting);
    }

    public function test404WhenEmailNotFound(): void
    {
        $this->post('/api/login', [
            'email' => 'nonexistingemail@example.com',
            'password' => 'secret',
        ])->assertStatus(404)->assertJson(['error' => ApiExceptionCode::EMAIL_NOT_FOUND]);
    }
}
