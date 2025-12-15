<?php

namespace App\Actions\PatientVisits;

use App\Models\PatientVisit;
use Carbon\Carbon;
use League\Csv\Writer;

class GeneratePatientVisitsCsvAction
{
    public function execute(array $filters = [])
    {
        $query = PatientVisit::with(['patient', 'branch', 'appointment', 'patientVisitServices.dentalService']);

        // Apply date range filters
        if (!empty($filters['date_from']) && !empty($filters['date_to'])) {
            $query->whereBetween('visit_date', [$filters['date_from'], $filters['date_to']]);
        } elseif (!empty($filters['date'])) {
            $query->whereDate('visit_date', $filters['date']);
        }

        if (!empty($filters['branch_id'])) {
            $query->where('branch_id', $filters['branch_id']);
        }

        if (!empty($filters['visit_type'])) {
            if ($filters['visit_type'] === 'walk-in') {
                $query->whereNull('appointment_id');
            } elseif ($filters['visit_type'] === 'appointment') {
                $query->whereNotNull('appointment_id');
            }
        }

        $visits = $query->orderBy('visit_date', 'desc')
            ->orderBy('created_at', 'desc')
            ->get();

        return $this->generateCsv($visits);
    }

    private function generateCsv($visits)
    {
        $csv = Writer::createFromString();

        $csv->setOutputBOM(Writer::BOM_UTF8);

        $csv->insertOne([
            'Date',
            'Patient Name',
            'Visit Type',
            'Branch',
            'Amount Paid',
            'Services',
            'Notes',
            'Created At'
        ]);

        foreach ($visits as $visit) {
            $visitType = $visit->appointment_id ? 'Appointment' : 'Walk-in';
            
            $services = $visit->patientVisitServices->map(function ($service) {
                return $service->dentalService->name . ' (x' . $service->quantity . ')';
            })->implode(', ');

            $csv->insertOne([
                Carbon::parse($visit->visit_date)->format('M d, Y'),
                $visit->patient_name,
                $visitType,
                $visit->branch_name,
                number_format($visit->total_amount_paid, 2),
                $services ?: 'N/A',
                $visit->notes ?? '',
                Carbon::parse($visit->created_at)->format('M d, Y g:i A')
            ]);
        }

        $totalAmount = $visits->sum('total_amount_paid');
        $csv->insertOne([]);
        $csv->insertOne([
            'Total Visits:',
            $visits->count(),
            '',
            'Total Amount:',
            number_format($totalAmount, 2)
        ]);

        return $csv->toString();
    }
}