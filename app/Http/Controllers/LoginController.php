<?php
declare(strict_types=1);

namespace App\Http\Controllers;


use Illuminate\Database\DatabaseManager;
use Illuminate\Http\Request;
use Illuminate\Routing\Router;

class LoginController extends Controller
{
    private $client;
    private $databaseManager;
    private $router;

    public function __construct(DatabaseManager $databaseManager, Router $router)
    {
        $this->client = $databaseManager->table('oauth_clients')
            ->where('password_client', 1)
            ->where('revoked', 0)
            ->first();
        $this->databaseManager = $databaseManager;
        $this->router = $router;
    }

    public function login(Request $request)
    {
        $data = $request->validate([
            'email' => 'required|email',
            'password' => 'required',
        ]);

        $request->request->add([
            'username' => $data['email'],
            'password' => $data['password'],
            'grant_type' => 'password',
            'client_id' => $this->client->id,
            'client_secret' => $this->client->secret,
            'scope' => '*'
        ]);

        $proxy = Request::create(
            'oauth/token',
            'POST'
        );

        return $this->router->dispatch($proxy);
    }

    public function refresh(Request $request)
    {
        $request->request->add([
            'grant_type' => 'refresh_token',
            'refresh_token' => $request->refresh_token,
            'client_id' => $this->client->id,
            'client_secret' => $this->client->secret,
        ]);

        $proxy = Request::create(
            '/oauth/token',
            'POST'
        );

        return $this->router->dispatch($proxy);
    }
}
