<?php

namespace App\Observers;

use App\Models\Appointment;
use App\Enums\AppointmentStatuses;

class AppointmentObserver
{
    /**
     * Handle the Appointment "creating" event.
     */
    public function creating(Appointment $appointment): void
    {
        if (!$appointment->queue_number) {
            $appointment->queue_number = Appointment::getNextQueueNumber($appointment->appointment_date);
        } else {
            // If queue number is specified, handle insertion
            $this->handleQueueInsertion($appointment);
        }

        if (!$appointment->status) {
            $appointment->status = AppointmentStatuses::WAITING;
        }
    }

    /**
     * Handle the Appointment "updating" event.
     */
    public function updating(Appointment $appointment): void
    {
        // Prevent queue number updates through normal update (use updateQueueNumber method instead)
        if ($appointment->isDirty('queue_number') && $appointment->exists) {
            $appointment->queue_number = $appointment->getOriginal('queue_number');
        }

        if (
            $appointment->isDirty('appointment_date') &&
            $appointment->status !== AppointmentStatuses::WAITING
        ) {
            $appointment->appointment_date = $appointment->getOriginal('appointment_date');
        }
    }

    /**
     * Handle the Appointment "deleted" event.
     */
    public function deleted(Appointment $appointment): void
    {
        $deletedQueueNumber = $appointment->queue_number;
        $appointmentDate = $appointment->appointment_date;

        // Shift all appointments with higher queue numbers down by 1
        Appointment::where('appointment_date', $appointmentDate)
            ->where('queue_number', '>', $deletedQueueNumber)
            ->orderBy('queue_number')
            ->get()
            ->each(function ($appointment) {
                $appointment->withoutEvents(function () use ($appointment) {
                    $appointment->decrement('queue_number');
                });
            });
    }

    /**
     * Handle queue insertion for new appointments with specified queue numbers
     */
    private function handleQueueInsertion(Appointment $appointment): void
    {
        $targetQueue = $appointment->queue_number;
        $appointmentDate = $appointment->appointment_date;

        // Check if there's an existing appointment with this queue number
        $existingAppointment = Appointment::where('appointment_date', $appointmentDate)
            ->where('queue_number', $targetQueue)
            ->first();

        if ($existingAppointment) {
            // Shift all appointments with queue number >= target up by 1
            Appointment::where('appointment_date', $appointmentDate)
                ->where('queue_number', '>=', $targetQueue)
                ->orderByDesc('queue_number')
                ->get()
                ->each(function ($appointment) {
                    $appointment->withoutEvents(function () use ($appointment) {
                        $appointment->increment('queue_number');
                    });
                });
        }
    }
}