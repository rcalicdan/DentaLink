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

    public function isDentist(): bool
    {
        return $this->role === UserRoles::DENTIST->value;
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

    public function dentistAppointments()
    {
        return $this->hasMany(Appointment::class, 'dentist_id');
    }

    public function dentistPatientVisits()
    {
        return $this->hasMany(PatientVisit::class, 'dentist_id');
    }

    public static function getDentists(?int $branchId = null)
    {
        $query = self::where('role', UserRoles::DENTIST->value)
            ->orderBy('first_name')
            ->orderBy('last_name');

        if ($branchId) {
            $query->where('branch_id', $branchId);
        }

        return $query->get();
    }

    public static function getDentistOptions(?int $branchId = null): array
    {
        $dentists = self::getDentists($branchId);
        
        $options = ['' => 'Select Dentist'];
        
        foreach ($dentists as $dentist) {
            $options[$dentist->id] = $dentist->full_name;
        }
        
        return $options;
    }
}