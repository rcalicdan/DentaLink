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
        if ($user->isAdmin() || $user->isEmployee() || $user->isDentist()) {
            if ($user->isAdmin() && $patient->registration_branch_id !== $user->branch_id) {
                return false;
            }

            return true;
        }

        return false;
    }

    public function export(User $user): bool
    {
        return $user->isAdmin();
    }

    public function create(User $user): bool
    {
        return $user->isAdmin() || $user->isEmployee();
    }

    public function update(User $user, Patient $patient): bool
    {
        if ($user->isDentist()) {
            return false;
        }

        if ($user->isAdmin()) {
            return $patient->registration_branch_id === $user->branch_id;
        }

        return $user->isEmployee();
    }

    public function delete(User $user, Patient $patient): bool
    {
        if ($user->isDentist()) {
            return false;
        }

        if ($user->isAdmin()) {
            return $patient->registration_branch_id === $user->branch_id;
        }

        return $user->isEmployee();
    }
}
