<?php

namespace App\Policies;

use App\Models\User;
use App\Models\Branch;

class BranchPolicy
{
    public function before(User $user, string $ability): bool|null
    {
        if ($user->isSuperadmin()) {
            return true;
        }

        return null;
    }

    public function viewAny(User $user): bool
    {
        return $user->isAdmin() || $user->isDentist() || $user->isEmployee();
    }

    public function view(User $user, Branch $branch): bool
    {
        return $user->isAdmin() || $user->isDentist() || $user->isEmployee();
    }

    public function create(User $user): bool
    {
        return $user->isAdmin();
    }

    public function update(User $user, Branch $branch): bool
    {
        return $user->isAdmin();
    }

    public function delete(User $user, Branch $branch): bool
    {
        return false; 
    }

    public function restore(User $user, Branch $branch): bool
    {
        return false; 
    }

    public function forceDelete(User $user, Branch $branch): bool
    {
        return false; 
    }
}