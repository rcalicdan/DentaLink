<?php

namespace App\Models;

use App\Enums\UserRoles;
use App\Libraries\Audit\Auditable;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;

class User extends Authenticatable
{
    use HasFactory, Notifiable, Auditable;

    protected $fillable = [
        'first_name',
        'last_name',
        'email',
        'password',
        'phone',
        'role',
        'branch_id',
    ];

    protected $hidden = [
        'password',
        'remember_token',
    ];

    protected function casts(): array
    {
        return [
            'email_verified_at' => 'datetime',
            'password' => 'hashed',
            'role' => 'string',
        ];
    }

    public function getFullNameAttribute()
    {
        return "{$this->first_name} {$this->last_name}";
    }

    public function getNameInitialsAttribute(): string
    {
        return "{$this->first_name[0]}{$this->last_name[0]}";
    }

    public function getBranchNameAttribute(): string
    {
        if($this->isSuperadmin()) {
            return 'Super Admin';
        }
        
        return $this->branch->name ?? 'Not yet Assigned';
    }

    public function isSuperadmin(): bool
    {
        return $this->role === UserRoles::SUPER_ADMIN->value;
    }

    public function isAdmin(): bool
    {
        return $this->role === UserRoles::ADMIN->value;
    }

    public function isEmployee(): bool
    {
        return $this->role === UserRoles::EMPLOYEE->value;
    }

    public function branch()
    {
        return $this->belongsTo(Branch::class);
    }

    public function createdAppointments()
    {
        return $this->hasMany(Appointment::class, 'created_by');
    }

    public function createdPatientVisits()
    {
        return $this->hasMany(PatientVisit::class, 'created_by');
    }
}
