<?php

namespace App\Policies;

use App\User;
use App\User\Role;
use Illuminate\Auth\Access\HandlesAuthorization;

class UserPolicy
{
    use HandlesAuthorization;

    public function before(User $user): ?bool
    {
        if ($user->role === Role::ADMIN) {
            // Admins can do anything.
            return true;
        }
        return null;
    }

    public function list(User $user): bool
    {
        return in_array($user->role, [Role::MANAGER, Role::ADMIN], true);
    }

    /**
     * Determine whether the user can view the model.
     *
     * @param  \App\User $user
     * @param  \App\User $targetUser
     * @return mixed
     */
    public function view(User $user, User $targetUser)
    {
        return $user->id === $targetUser->id;
    }

    /**
     * Determine whether the user can create models.
     */
    public function create()
    {
        // Users cannot create new users; however, guests should be able to, bypassing these policies.
        return false;
    }

    /**
     * Determine whether the user can update the model.
     *
     * @param  \App\User $user
     * @param  \App\User $targetUser
     * @return mixed
     */
    public function update(User $user, User $targetUser)
    {
        return $user->id === $targetUser->id ||
            // Managers; they shouldn't be able to update admin accounts though.
            $this->changeEmail($user, $targetUser);
    }

    /**
     * Determine whether the user can delete the model.
     *
     * @param  \App\User $user
     * @param  \App\User $targetUser
     * @return mixed
     */
    public function delete(User $user, User $targetUser)
    {
        // Managers can delete users, but only regular users.
        return $user->role === Role::MANAGER && $targetUser->role === Role::USER;
    }

    public function changeEmail(User $user, User $targetUser)
    {
        return $user->role === Role::MANAGER && $targetUser->role !== Role::ADMIN;
    }

    public function changeRole(User $user, User $targetUser)
    {
        // Only the admin can.
        return false;
    }
}
