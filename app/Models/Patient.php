<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Patient extends Model
{
    protected $fillable = [
        'first_name',
        'last_name',
        'phone',
        'email',
        'date_of_birth',
        'address',
        'registration_branch_id',
    ];

    protected function casts(): array
    {
        return [
            'date_of_birth' => 'date',
        ];
    }

    // Relationships
    public function registrationBranch()
    {
        return $this->belongsTo(Branch::class, 'registration_branch_id');
    }

    public function appointments()
    {
        return $this->hasMany(Appointment::class);
    }

    public function patientVisits()
    {
        return $this->hasMany(PatientVisit::class);
    }

    // Accessor for full name
    public function getFullNameAttribute()
    {
        return $this->first_name . ' ' . $this->last_name;
    }
}