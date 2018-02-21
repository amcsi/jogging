<?php

namespace App\Http\Controllers;

use App\Common\ApiException;
use App\Common\ApiExceptionCode;
use App\Common\JsonResponder;
use App\Common\UniqueIndex;
use App\Http\Requests\IndexUser;
use App\Http\Requests\UpdateUser;
use App\User;
use App\User\UserListTransformer;
use App\User\UserTransformer;
use Illuminate\Database\QueryException;
use Illuminate\Hashing\HashManager;
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
        $data = $request->validate([
            'email' => 'required|email',
            'password' => 'required|min:4',
        ]);
        $user = new User();
        $user->email = $data['email'];
        $user->password = \Hash::make($data['password']);
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

    public function update(
        User $user,
        UpdateUser $updateUser,
        UserTransformer $userTransformer,
        HashManager $hashManager
    ) {
        $validated = $updateUser->validated();

        if (!empty($validated['email'])) {
            $user->email = $validated['email'];
        }
        if (!empty($validated['password'])) {
            $user->password = $hashManager->make($validated['password']);
        }
        if (!empty($validated['role'])) {
            $user->role = $validated['role'];
        }
        if ($user->isDirty()) {
            $user->save();
        }

        return JsonResponder::respond($user, $userTransformer);
    }
}
