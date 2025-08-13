<?php

namespace App\Policies;

use App\Models\User;
use App\Models\Inventory;

class InventoryPolicy
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

    public function view(User $user, Inventory $inventory): bool
    {
        return true; 
    }

    public function create(User $user): bool
    {
        return $user->isAdmin() || $user->isEmployee();
    }

    public function update(User $user, Inventory $inventory): bool
    {
        return $user->isAdmin() || $user->isEmployee();
    }

    public function delete(User $user, Inventory $inventory): bool
    {
        return $user->isAdmin();
    }

    public function restore(User $user, Inventory $inventory): bool
    {
        return false; 
    }

    public function forceDelete(User $user, Inventory $inventory): bool
    {
        return false; 
    }
}