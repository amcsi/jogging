<?php

namespace App\Http\Controllers;

use App\Common\ApiException;
use App\Common\ApiExceptionCode;
use App\Common\JsonResponder;
use App\Common\UniqueIndex;
use App\Http\Requests\IndexUser;
use App\User;
use App\User\UserListTransformer;
use Illuminate\Database\QueryException;
use Illuminate\Http\Request;

class UserController extends Controller
{
    public function index(IndexUser $request, UserListTransformer $userListTransformer)
    {
        $this->authorize('list', User::class);

        return JsonResponder::respond(User::latest()->paginate($request->getLimit()), $userListTransformer);
    }

    public function store(Request $request): array
    {
        $user = new User();
        $user->email = $request['email'];
        $user->password = \Hash::make($request['password']);
        try {
            $user->save();
        } catch (QueryException $exception) {
            if (UniqueIndex::isUniqueIndexException($exception)) {
                throw new ApiException(
                    'Email already exists',
                    ApiExceptionCode::EMAIL_ALREADY_EXISTS,
                    409,
                    $exception
                );
            } else {
                throw $exception;
            }
        }

        return $user->only('email');
    }
}
