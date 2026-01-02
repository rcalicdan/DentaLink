<?php

namespace App\Policies;

use App\Models\User;
use App\Models\Appointment;

class AppointmentPolicy
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

    public function view(User $user, Appointment $appointment): bool
    {
        return true; 
    }

    public function create(User $user): bool
    {
        return $user->isAdmin() || $user->isEmployee();
    }

    public function update(User $user, Appointment $appointment): bool
    {
        return $user->isAdmin() || $user->isEmployee();
    }

    public function delete(User $user, Appointment $appointment): bool
    {
        return false; 
    }

    public function restore(User $user, Appointment $appointment): bool
    {
        return false; 
    }

    public function forceDelete(User $user, Appointment $appointment): bool
    {
        return false; 
    }

      public function export(User $user): bool
    {
        return $user->isAdmin();
    }
}