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
        'age',
        'address',
        'registration_branch_id',
    ];

    protected function casts(): array
    {
        return [
            'age' => 'integer',
        ];
    }

    public function getRegistrationBranchNameAttribute(): string
    {
        return $this->registrationBranch->name;
    }

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

    public function getFullNameAttribute()
    {
        return $this->first_name . ' ' . $this->last_name;
    }
}