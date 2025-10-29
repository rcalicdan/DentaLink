<?php

namespace App\Services;

use App\Models\Appointment;
use App\Enums\AppointmentStatuses;
use Carbon\Carbon;
use Hibla\Promise\Promise;
use Illuminate\Support\Collection;

use function Hibla\async;
use function Hibla\await;

class AppointmentReminderService
{
    public function __construct(
        private SmsReminderService $smsService,
        private EmailReminderService $emailService
    ) {}

    /**
     * Get today's pending appointments that need reminders
     */
    public function getTodaysAppointments(bool $sendSms, bool $sendEmail): Collection
    {
        return Appointment::with(['patient', 'branch'])
            ->whereDate('appointment_date', Carbon::today())
            ->where('status', AppointmentStatuses::WAITING)
            ->whereHas('patient', function ($query) use ($sendSms, $sendEmail) {
                $query->where(function ($q) use ($sendSms, $sendEmail) {
                    if ($sendSms) {
                        $q->whereNotNull('phone');
                    }
                    if ($sendEmail) {
                        $q->orWhereNotNull('email');
                    }
                });
            })
            ->get();
    }

    /**
     * Send SMS reminders concurrently
     */
    public function sendSmsReminders(Collection $appointments): array
    {
        return await(async(function () use ($appointments) {
            $promises = [];

            foreach ($appointments as $appointment) {
                if ($appointment->patient->phone) {
                    $promises[$appointment->id] = $this->smsService->send($appointment);
                }
            }

            return await(Promise::allSettled($promises));
        }));
    }

    /**
     * Send email reminders concurrently
     */
    public function sendEmailReminders(Collection $appointments): array
    {
        return await(async(function () use ($appointments) {
            $promises = [];

            foreach ($appointments as $appointment) {
                if ($appointment->patient->email) {
                    $promises[$appointment->id] = $this->emailService->send($appointment);
                }
            }

            return await(Promise::allSettled($promises));
        }));
    }

    /**
     * Calculate statistics from results
     */
    public function calculateStats(array $results): array
    {
        $stats = [
            'total' => 0,
            'success' => 0,
            'failed' => 0,
            'failed_items' => []
        ];

        foreach ($results as $result) {
            if ($result['status'] === 'fulfilled') {
                $value = $result['value'];
                $stats['total']++;

                if ($value['success']) {
                    $stats['success']++;
                } else {
                    $stats['failed']++;
                    $stats['failed_items'][] = [
                        'patient' => $value['patient'],
                        'recipient' => $value['recipient'],
                        'reason' => $value['error'] ?? $value['reason'] ?? 'Unknown'
                    ];
                }
            }
        }

        return $stats;
    }
}