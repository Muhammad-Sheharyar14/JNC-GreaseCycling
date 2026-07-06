<?php

namespace App\Policies;

use App\Models\User;

class UserPolicy
{
    /**
     * Perform pre-authorization checks.
     */
    public function before(User $auth, string $ability): ?bool
    {
        if ($auth->hasRole('Admin')) {
            return true;
        }
        return null;
    }

    public function viewAny(User $auth): bool
    {
        return false;
    }

    public function view(User $auth, User $user): bool
    {
        return false;
    }

    public function create(User $auth): bool
    {
        return false;
    }

    public function update(User $auth, User $user): bool
    {
        return false;
    }

    public function delete(User $auth, User $user): bool
    {
        return false;
    }

    public function restore(User $auth, User $user): bool
    {
        return false;
    }

    public function forceDelete(User $auth, User $user): bool
    {
        return false;
    }
}
