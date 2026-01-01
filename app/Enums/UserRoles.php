<?php

namespace App\Enums;

enum UserRoles: string
{
    case SUPER_ADMIN = 'superadmin';
    case ADMIN = 'admin';
    case DENTIST = 'dentist';
    case EMPLOYEE = 'employee';

    public static function getAllRoles(): array
    {
        return array_map(fn(UserRoles $role) => $role->value, self::cases());
    }

    public function getLabel(): string
    {
        return match($this) {
            self::SUPER_ADMIN => 'Super Admin',
            self::ADMIN => 'Admin',
            self::DENTIST => 'Dentist',
            self::EMPLOYEE => 'Employee',
        };
    }

    public function getDescription(): string
    {
        return match($this) {
            self::SUPER_ADMIN => 'Full system access',
            self::ADMIN => 'Branch management access',
            self::DENTIST => 'Patient care and treatment access',
            self::EMPLOYEE => 'Basic operational access',
        };
    }
}