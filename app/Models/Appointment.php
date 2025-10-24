<?php

namespace App\Models;

use App\Libraries\Audit\Auditable;
use Illuminate\Database\Eloquent\Model;
use App\Enums\AppointmentStatuses;
use Illuminate\Support\Facades\Auth;
use Carbon\Carbon;
use Illuminate\Support\Facades\DB;

class Appointment extends Model
{
    use Auditable;

    protected $fillable = [
        'patient_id',
        'branch_id',
        'appointment_date',
        'start_time',
        'end_time',
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
            'start_time' => 'datetime:H:i',
            'end_time' => 'datetime:H:i',
            'has_visit' => 'boolean',
            'status' => AppointmentStatuses::class,
        ];
    }

    public function getPatientNameAttribute(): string
    {
        return "{$this->patient->first_name} {$this->patient->last_name}";
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

    public function getFormattedTimeRangeAttribute(): ?string
    {
        if (!$this->start_time || !$this->end_time) {
            return null;
        }

        return $this->start_time->format('g:i A') . ' - ' . $this->end_time->format('g:i A');
    }

    /**
     * Update queue number with proper queue management
     */
    public function updateQueueNumber(int $newQueueNumber): bool
    {
        $oldQueueNumber = $this->queue_number;
        $appointmentDate = $this->appointment_date;

        if ($oldQueueNumber === $newQueueNumber) {
            return true; // No change needed
        }

        try {
            DB::transaction(function () use ($newQueueNumber, $oldQueueNumber, $appointmentDate) {
                if ($newQueueNumber > $oldQueueNumber) {
                    // Moving down: shift appointments between old and new position up by 1
                    Appointment::where('appointment_date', $appointmentDate)
                        ->where('queue_number', '>', $oldQueueNumber)
                        ->where('queue_number', '<=', $newQueueNumber)
                        ->where('id', '!=', $this->id)
                        ->decrement('queue_number');
                } else {
                    // Moving up: shift appointments between new and old position down by 1
                    Appointment::where('appointment_date', $appointmentDate)
                        ->where('queue_number', '>=', $newQueueNumber)
                        ->where('queue_number', '<', $oldQueueNumber)
                        ->where('id', '!=', $this->id)
                        ->increment('queue_number');
                }

                // Update this appointment's queue number without triggering events
                $this->withoutEvents(function () use ($newQueueNumber) {
                    $this->update(['queue_number' => $newQueueNumber]);
                });
            });

            return true;
        } catch (\Exception $e) {
            return false;
        }
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
