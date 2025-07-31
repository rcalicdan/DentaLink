<?php

namespace App\Enums;

enum UserRoles: string
{
    case SUPER_ADMIN = 'superadmin';
    case ADMIN = 'admin';
    case EMPLOYEE = 'employee';

    public static function getAllRoles(): array
    {
        return array_map(fn(UserRoles $role) => $role->value, self::cases());
    }
}
