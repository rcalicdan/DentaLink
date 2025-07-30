<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class PatientVisit extends Model
{
    protected $fillable = [
        'patient_id',
        'branch_id',
        'appointment_id',
        'visit_date',
        'notes',
        'total_amount_paid',
        'created_by',
    ];

    protected function casts(): array
    {
        return [
            'visit_date' => 'datetime',
            'total_amount_paid' => 'decimal:2',
        ];
    }

    // Relationships
    public function patient()
    {
        return $this->belongsTo(Patient::class);
    }

    public function branch()
    {
        return $this->belongsTo(Branch::class);
    }

    public function appointment()
    {
        return $this->belongsTo(Appointment::class);
    }

    public function creator()
    {
        return $this->belongsTo(User::class, 'created_by');
    }

    public function patientVisitServices()
    {
        return $this->hasMany(PatientVisitService::class);
    }
}