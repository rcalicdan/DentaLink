<?php

namespace App\Policies;

use App\Models\AuditLog;
use App\Models\User;
use App\Enums\UserRoles;

class AuditLogPolicy
{
    /**
     * Determine whether the user can view any audit logs.
     */
    public function viewAny(User $user): bool
    {
        return $user->isSuperadmin();
    }

    /**
     * Determine whether the user can view the audit log.
     */
    public function view(User $user, AuditLog $auditLog): bool
    {
        return $user->isSuperadmin();
    }
}