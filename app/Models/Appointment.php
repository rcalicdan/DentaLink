<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use App\Enums\AppointmentStatuses;
use Illuminate\Support\Facades\Auth;
use Carbon\Carbon;

class Appointment extends Model
{
    protected $fillable = [
        'patient_id',
        'branch_id',
        'appointment_date',
        'queue_number',
        'status',
        'reason',
        'notes',
        'has_visit',
        'created_by',
    ];

    protected function casts(): array
    {
        return [
            'appointment_date' => 'date',
            'has_visit' => 'boolean',
            'status' => AppointmentStatuses::class,
        ];
    }

    public function patient()
    {
        return $this->belongsTo(Patient::class);
    }

    public function branch()
    {
        return $this->belongsTo(Branch::class);
    }

    public function creator()
    {
        return $this->belongsTo(User::class, 'created_by');
    }

    public function patientVisits()
    {
        return $this->hasMany(PatientVisit::class);
    }

    protected static function boot()
    {
        parent::boot();
        
        static::creating(function ($appointment) {
            $appointment->created_by = Auth::id();
            $appointment->branch_id = Auth::user()->branch_id ?? $appointment->branch_id;
            
            if (!$appointment->status) {
                $appointment->status = AppointmentStatuses::WAITING;
            }
        });
    }

    public static function getNextQueueNumber($date)
    {
        $lastAppointment = static::whereDate('appointment_date', $date)
            ->orderBy('queue_number', 'desc')
            ->first();

        return $lastAppointment ? $lastAppointment->queue_number + 1 : 1;
    }

    public static function checkPatientAppointmentConflict($patientId, $date, $excludeId = null)
    {
        $query = static::where('patient_id', $patientId)
            ->whereDate('appointment_date', $date)
            ->whereIn('status', [
                AppointmentStatuses::WAITING->value,
                AppointmentStatuses::IN_PROGRESS->value
            ]);

        if ($excludeId) {
            $query->where('id', '!=', $excludeId);
        }

        if ($query->exists()) {
            throw new \Exception('Patient already has an active appointment on this date.');
        }
    }

    public function updateStatus(AppointmentStatuses $newStatus, $user = null)
    {
        if ($user && $user->isSuperadmin()) {
            $this->update(['status' => $newStatus]);
            return true;
        }

        if (!$this->status->canTransitionTo($newStatus, $user)) {
            return false;
        }

        $this->update(['status' => $newStatus]);
        return true;
    }

    public function canBeModified()
    {
        if (Auth::user() && Auth::user()->isSuperadmin()) {
            return true;
        }

        return !$this->status->isFinalState(Auth::user());
    }

    public function getStatusBadgeAttribute()
    {
        return $this->status->getBadgeClass();
    }

    public function getStatusIconAttribute()
    {
        return $this->status->getIcon();
    }

    public function getFormattedDateAttribute()
    {
        return $this->appointment_date->format('M d, Y');
    }
}