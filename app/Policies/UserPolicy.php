<?php

namespace App\Policies;

use App\User;
use App\User\Role;
use Illuminate\Auth\Access\HandlesAuthorization;

class UserPolicy
{
    use HandlesAuthorization;

    public function list(User $user): bool
    {
        return $user->role === Role::ADMIN ||
            in_array($user->role, [Role::MANAGER, Role::ADMIN], true);
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
        return $user->role === Role::ADMIN || $user->id === $targetUser->id;
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
        return $user->role === Role::ADMIN ||
            $user->id === $targetUser->id ||
            // Managers; they shouldn't be able to update admin accounts though.
            $this->changeEmail($user, $targetUser);
    }

    /**
     * Determine whether the user can delete the model.
     */
    public function delete(User $user, User $targetUser)
    {
        if ($user->id === $targetUser->id) {
            /*
             * No one can delete themselves, not even admins.
             * Since admins can only be deleted by other admins, this addition should guarantee that there would
             * always be at least one admin.
             */
            return false;
        }

        // Managers can delete users, but only regular users.
        return ($user->role === Role::MANAGER && $targetUser->role === Role::USER) ||
            $user->role === Role::ADMIN;
    }

    public function changeEmail(User $user, User $targetUser)
    {
        return ($user->role === Role::MANAGER && $targetUser->role !== Role::ADMIN) ||
            $user->role === Role::ADMIN;
    }

    public function changeRole(User $user, User $targetUser)
    {
        if ($user->id === $targetUser->id) {
            // One cannot promote nor demote themselves.
            return false;
        }

        // Only the admin can.
        return $user->role === Role::ADMIN;
    }
}
