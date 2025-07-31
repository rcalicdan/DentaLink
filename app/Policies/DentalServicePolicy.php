<?php

namespace App\Policies;

use App\Models\User;
use App\Models\DentalService;

class DentalServicePolicy
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
        return true; 
    }

    public function view(User $user, DentalService $dentalService): bool
    {
        return true; 
    }

    public function create(User $user): bool
    {
        return $user->isAdmin() || $user->isEmployee();
    }

    public function update(User $user, DentalService $dentalService): bool
    {
        return $user->isAdmin() || $user->isEmployee();
    }

    public function delete(User $user, DentalService $dentalService): bool
    {
        return false; 
    }

    public function restore(User $user, DentalService $dentalService): bool
    {
        return false; 
    }

    public function forceDelete(User $user, DentalService $dentalService): bool
    {
        return false; 
    }
}