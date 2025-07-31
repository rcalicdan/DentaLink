<?php

namespace App\Policies;

use App\Models\User;
use App\Models\Patient;

class PatientPolicy
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

    public function view(User $user, Patient $patient): bool
    {
        return true; 
    }

    public function create(User $user): bool
    {
        return $user->isAdmin() || $user->isEmployee();
    }

    public function update(User $user, Patient $patient): bool
    {
        return $user->isAdmin() || $user->isEmployee();
    }

    public function delete(User $user, Patient $patient): bool
    {
        return false; 
    }

    public function restore(User $user, Patient $patient): bool
    {
        return false; 
    }

    public function forceDelete(User $user, Patient $patient): bool
    {
        return false; 
    }
}