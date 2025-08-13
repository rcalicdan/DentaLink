<?php

namespace App\Models;

use App\Libraries\Audit\Auditable;
use Illuminate\Database\Eloquent\Model;

class Patient extends Model
{
    use Auditable;
    
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

    public function getRegistrationBranchNameAttribute(): string
    {
        return $this->registrationBranch->name;
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

    public function getAgeAttribute()
    {
        return $this->date_of_birth ? $this->date_of_birth->age : null;
    }
}
