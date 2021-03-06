<?php

namespace App\Policies;

use App\JoggingTime;
use App\User;
use App\User\Role;
use Illuminate\Auth\Access\HandlesAuthorization;

class JoggingTimePolicy
{
    use HandlesAuthorization;

    public function before(?User $user): ?bool
    {
        if (!$user) {
            // User must be authenticated.
            return false;
        }
        if ($user->role === Role::ADMIN) {
            // Admin can do whatever.
            return true;
        }

        // Policy rules apply.
        return null;
    }

    public function index(User $user, User $forUser): bool
    {
        // The user ids must be the same.
        return $user->id === $forUser->id;
    }

    public function create(User $user, User $forUser): bool
    {
        return $this->index($user, $forUser);
    }

    /**
     * Determine whether the user can delete the joggingTime.
     *
     * @param  \App\User $user
     * @param  \App\JoggingTime $joggingTime
     * @return mixed
     */
    public function delete(User $user, JoggingTime $joggingTime)
    {
        return $this->update($user, $joggingTime);
    }

    /**
     * Determine whether the user can update the joggingTime.
     *
     * @param  \App\User $user
     * @param  \App\JoggingTime $joggingTime
     * @return mixed
     */
    public function update(User $user, JoggingTime $joggingTime)
    {
        return $this->view($user, $joggingTime);
    }

    /**
     * Determine whether the user can view the joggingTime.
     *
     * @param  \App\User $user
     * @param  \App\JoggingTime $joggingTime
     * @return mixed
     */
    public function view(User $user, JoggingTime $joggingTime)
    {
        return $user->id === $joggingTime->user->id;
    }
}
