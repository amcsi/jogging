<?php
declare(strict_types=1);

namespace App\Http\Controllers;


use App\Common\ApiException;
use App\Common\ApiExceptionCode;
use App\User;
use Illuminate\Database\DatabaseManager;
use Illuminate\Http\Request;
use Illuminate\Routing\Router;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;

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

        if (!User::where('email', $data['email'])->count()) {
            throw new ApiException('User with an email not found', ApiExceptionCode::EMAIL_NOT_FOUND, 404);
        }

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

        return $this->dispatchRequest($proxy);
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

        return $this->dispatchRequest($proxy);
    }

    /**
     * Dispatches a request in a way that the container gets unset from the route to work with Swoole.
     *
     * https://github.com/swooletw/laravel-swoole/issues/74#issuecomment-395798995
     *
     * @param Request $proxy
     * @return \Illuminate\Http\JsonResponse|\Illuminate\Http\Response
     */
    private function dispatchRequest(Request $proxy)
    {
        $router = $this->router;
        $application = app();
        $closure = function () use ($application, $proxy) {
            /** @var @var Router $this */
            $route = $this->routes->match($proxy);
            // clear resolved controller
            if (property_exists($route, 'container')) {
                $route->controller = null;
            }
            // rebind matched route's container
            $route->setContainer($application);
        };

        $resetRouter = $closure->bindTo($router, $router);
        $resetRouter();

        return $router->dispatch($proxy);
    }
}
