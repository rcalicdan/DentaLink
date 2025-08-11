<?php

namespace App\Policies;

use App\Models\User;

class UserPolicy
{
    public function before(User $user, string $ability): bool|null
    {
        if ($user->isSuperadmin() && $ability !== 'delete') {
            return true;
        }

        return null;
    }

    public function viewAny(User $user): bool
    {
        return $user->isAdmin() || $user->isSuperadmin();
    }

    public function view(User $user, User $model): bool
    {
        return $user->isAdmin() || $user->isSuperadmin();
    }

    public function create(User $user): bool
    {
        return $user->isAdmin() || $user->isSuperadmin();
    }

    public function update(User $user, User $model): bool
    {
        if ($user->isAdmin() && $model->isSuperadmin()) {
            return false;
        }

        return $user->isAdmin() || $user->isSuperadmin();
    }

    public function delete(User $user, User $model): bool
    {
        if ($user->id === $model->id) {
            return false;
        }

        if ($user->isSuperadmin()) {
            return true;
        }

        if ($user->isAdmin() && $model->isSuperadmin()) {
            return false;
        }

        if ($user->isAdmin()) {
            return $user->branch_id === $model->branch_id;
        }

        return false;
    }

    public function restore(User $user, User $model): bool
    {
        return $user->isSuperadmin();
    }

    public function forceDelete(User $user, User $model): bool
    {
        return $user->isSuperadmin() && $user->id !== $model->id;
    }
}
