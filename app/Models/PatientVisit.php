<?php

namespace App\Models;

use App\Libraries\Audit\Auditable;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Auth;

class PatientVisit extends Model
{
    use Auditable;
    
    protected $fillable = [
        'patient_id',
        'branch_id',
        'dentist_id',
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

    public function getPatientNameAttribute(): string
    {
        return "{$this->patient->first_name} {$this->patient->last_name}";
    }

    public function getBranchNameAttribute(): string
    {
        return $this->branch->name ?? 'N/A';
    }

    public function getDentistNameAttribute(): ?string
    {
        return $this->dentist?->full_name;
    }

    public function getAppointmentDateAttribute(): string
    {
        return $this->appointment_id ? $this->appointment->date : 'walk-in' ?? "N/A";
    }

    public function getVisitTypeAttribute(): string
    {
        return $this->appointment_id ? 'appointment' : 'walk-in';
    }

    public function patient()
    {
        return $this->belongsTo(Patient::class);
    }

    public function branch()
    {
        return $this->belongsTo(Branch::class);
    }

    public function dentist()
    {
        return $this->belongsTo(User::class, 'dentist_id');
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

    protected static function boot()
    {
        parent::boot();

        static::creating(function ($visit) {
            if ($visit->appointment_id && !$visit->dentist_id) {
                $appointment = Appointment::find($visit->appointment_id);
                if ($appointment && $appointment->dentist_id) {
                    $visit->dentist_id = $appointment->dentist_id;
                }
            }

            if (!$visit->created_by) {
                $visit->created_by = Auth::id();
            }

            if (!$visit->branch_id && Auth::user()) {
                $visit->branch_id = Auth::user()->branch_id;
            }
        });
    }
}