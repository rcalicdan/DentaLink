<?php

namespace App\Models;

use App\Libraries\Audit\Auditable;
use Illuminate\Database\Eloquent\Model;
use Carbon\Carbon;

class Patient extends Model
{
    use Auditable;
    
    protected $fillable = [
        'first_name',
        'last_name',
        'phone',
        'email',
        'birthday',
        'address',
        'registration_branch_id',
    ];

    protected function casts(): array
    {
        return [
            'birthday' => 'date',
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

    public function getAgeAttribute(): ?int
    {
        if (!$this->birthday) {
            return null;
        }

        return Carbon::parse($this->birthday)->age;
    }

    public function getFormattedBirthdayAttribute(): ?string
    {
        if (!$this->birthday) {
            return null;
        }

        return Carbon::parse($this->birthday)->format('M d, Y');
    }
}