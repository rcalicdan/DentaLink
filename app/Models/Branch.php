<?php

namespace App\Models;

use App\Libraries\Audit\Auditable;
use Illuminate\Database\Eloquent\Model;

class Branch extends Model
{
    use Auditable;
    
    protected $fillable = [
        'name',
        'address',
        'phone',
        'email',
    ];

    // Relationships
    public function users()
    {
        return $this->hasMany(User::class);
    }

    public function patients()
    {
        return $this->hasMany(Patient::class, 'registration_branch_id');
    }

    public function appointments()
    {
        return $this->hasMany(Appointment::class);
    }

    public function patientVisits()
    {
        return $this->hasMany(PatientVisit::class);
    }

    public function inventoryItems()
    {
        return $this->hasMany(Inventory::class);
    }
}
