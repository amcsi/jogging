<?php
declare(strict_types=1);

namespace App\Http\Controllers;


use App\Common\ApiException;
use App\Common\ApiExceptionCode;
use App\User;
use Illuminate\Database\DatabaseManager;
use Illuminate\Http\Request;

class LoginController extends Controller
{
    private $client;
    private $clientId;
    private $clientSecret;

    public function __construct(DatabaseManager $databaseManager)
    {
        $query = $databaseManager->table('oauth_clients')
            ->where('password_client', 1)
            ->where('revoked', 0);

        if ($configuredClientId = config('services.passport.password_client_id')) {
            $query->where('id', $configuredClientId);
        }

        $this->client = $query->orderBy('id')->first();

        $this->clientId = config('services.passport.password_client_id') ?? $this->client?->id;

        $configuredSecret = config('services.passport.password_client_secret');
        $databaseSecret = $this->client?->secret;
        $this->clientSecret = $configuredSecret
            ?? (is_string($databaseSecret) && ! str_starts_with($databaseSecret, '$2y$') ? $databaseSecret : null);
    }

    public function login(Request $request)
    {
        $data = $request->validate([
            'email' => 'required|email',
            'password' => 'required',
        ]);

        if (!User::where('email', $data['email'])->count()) {
            throw new ApiException('User with an email not found', ApiExceptionCode::EMAIL_NOT_FOUND, 404);
        }

        $request->request->add([
            'username' => $data['email'],
            'password' => $data['password'],
            'grant_type' => 'password',
            'client_id' => $this->clientId,
            'client_secret' => $this->clientSecret,
            'scope' => '*'
        ]);

        $proxy = Request::create(
            '/oauth/token',
            'POST',
            $request->request->all()
        );

        return $this->dispatchRequest($proxy);
    }

    public function refresh(Request $request)
    {
        $request->request->add([
            'grant_type' => 'refresh_token',
            'refresh_token' => $request->refresh_token,
            'client_id' => $this->clientId,
            'client_secret' => $this->clientSecret,
        ]);

        $proxy = Request::create(
            '/oauth/token',
            'POST',
            $request->request->all()
        );

        return $this->dispatchRequest($proxy);
    }

    /**
     * @param Request $proxy
     * @return \Illuminate\Http\JsonResponse|\Illuminate\Http\Response
     */
    private function dispatchRequest(Request $proxy)
    {
        return app()->handle($proxy);
    }
}
