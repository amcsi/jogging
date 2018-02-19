<?php

namespace App\Http\Controllers;

use App\Common\ApiException;
use App\Common\ApiExceptionCode;
use App\Common\UniqueIndex;
use App\User;
use Illuminate\Database\QueryException;
use Illuminate\Http\Request;

class UserController extends Controller
{
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
