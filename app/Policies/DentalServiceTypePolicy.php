<?php

namespace App\Policies;

use App\Models\User;
use App\Models\DentalServiceType;

class DentalServiceTypePolicy
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

    public function view(User $user, DentalServiceType $dentalServiceType): bool
    {
        return true;
    }

    public function create(User $user): bool
    {
        return $user->isAdmin() || $user->isEmployee();
    }

    public function update(User $user, DentalServiceType $dentalServiceType): bool
    {
        return $user->isAdmin() || $user->isEmployee();
    }

    public function delete(User $user, DentalServiceType $dentalServiceType): bool
    {
        return false; 
    }

    public function restore(User $user, DentalServiceType $dentalServiceType): bool
    {
        return false; 
    }

    public function forceDelete(User $user, DentalServiceType $dentalServiceType): bool
    {
        return false; 
    }
}