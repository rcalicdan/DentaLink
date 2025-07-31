<?php

namespace App\Enums;

enum UserRoles: string
{
    case ADMIN = 'superadmin';
    case DOCTOR = 'admin';
    case STAFF = 'employee';

    public static function getAllRoles(): array
    {
        return array_map(fn(UserRoles $role) => $role->value, self::cases());
    }
}
