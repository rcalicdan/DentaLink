<?php

namespace App\Policies;

use App\Models\User;

class DentistPolicy
{
    public function viewDentistData(User $user): bool
    {
        return $user->isSuperadmin() || $user->isAdmin() || $user->isDentist();
    }

    public function assignDentist(User $user): bool
    {
        return $user->isSuperadmin() || $user->isAdmin();
    }

    public function viewOwnAppointments(User $user): bool
    {
        return $user->isDentist();
    }

    public function accessAppointment(User $user, $appointment): bool
    {
        if ($user->isSuperadmin() || $user->isAdmin()) {
            return true;
        }

        if ($user->isDentist()) {
            return $appointment->dentist_id === $user->id;
        }

        return false;
    }
}