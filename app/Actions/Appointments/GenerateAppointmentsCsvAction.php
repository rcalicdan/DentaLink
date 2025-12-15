<?php

namespace App\Actions\Appointments;

use App\Models\Appointment;
use Carbon\Carbon;
use League\Csv\Writer;
use App\Enums\AppointmentStatuses;

class GenerateAppointmentsCsvAction
{
    public function execute(array $filters = [])
    {
        $query = Appointment::with(['patient', 'branch', 'creator']);

        if (!empty($filters['date_from']) && !empty($filters['date_to'])) {
            $query->whereBetween('appointment_date', [$filters['date_from'], $filters['date_to']]);
        } elseif (!empty($filters['date'])) {
            $query->whereDate('appointment_date', $filters['date']);
        }

        if (!empty($filters['branch_id'])) {
            $query->where('branch_id', $filters['branch_id']);
        }

        if (!empty($filters['status'])) {
            $query->where('status', $filters['status']);
        }

        $appointments = $query->orderBy('appointment_date', 'desc')
            ->orderBy('queue_number')
            ->get();

        return $this->generateCsv($appointments);
    }

    private function generateCsv($appointments)
    {
        $csv = Writer::createFromString();

        $csv->setOutputBOM(Writer::BOM_UTF8);

        $csv->insertOne([
            'Queue #',
            'Date',
            'Time Range',
            'Patient Name',
            'Branch',
            'Status',
            'Reason',
            'Notes',
            'Created By',
            'Created At'
        ]);

        foreach ($appointments as $appointment) {
            $csv->insertOne([
                $appointment->queue_number,
                Carbon::parse($appointment->appointment_date)->format('M d, Y'),
                $appointment->formatted_time_range ?? 'N/A',
                $appointment->patient_name,
                $appointment->branch->name,
                $appointment->status->getDisplayName(),
                $appointment->reason ?? 'N/A',
                $appointment->notes ?? '',
                $appointment->creator->name ?? 'N/A',
                Carbon::parse($appointment->created_at)->format('M d, Y g:i A')
            ]);
        }

        $csv->insertOne([]);
        $csv->insertOne([
            'Total Appointments:',
            $appointments->count(),
            '',
            '',
            '',
            '',
            '',
            '',
            '',
            ''
        ]);

        $statusCounts = $appointments->groupBy('status')->map->count();
        $csv->insertOne([]);
        $csv->insertOne(['Status Breakdown:']);
        foreach ($statusCounts as $statusString => $count) {
            $statusEnum = AppointmentStatuses::from($statusString);
            $csv->insertOne([
                '',
                $statusEnum->getDisplayName() . ':',
                $count
            ]);
        }

        return $csv->toString();
    }
}