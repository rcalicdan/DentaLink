<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;

class User extends Authenticatable
{
    use HasFactory, Notifiable;

    protected $fillable = [
        'email',
        'password',
        'first_name',
        'last_name',
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

    // Relationships
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
