<?php

namespace App\Actions\PatientVisits;

use App\Models\PatientVisit;
use Carbon\Carbon;
use TCPDF;

class GeneratePatientVisitsPdfAction
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

        return $this->generatePdf($visits, $filters);
    }

    private function generatePdf($visits, $filters)
    {
        $pdf = new TCPDF('L', PDF_UNIT, PDF_PAGE_FORMAT, true, 'UTF-8', false);

        $pdf->SetCreator(PDF_CREATOR);
        $pdf->SetAuthor('Dental Clinic System');
        $pdf->SetTitle('Patient Visits Report');
        $pdf->SetSubject('Patient Visits');

        $pdf->SetHeaderData('', 0, 'Patient Visits Report', $this->getFilterText($filters));

        $pdf->setHeaderFont([PDF_FONT_NAME_MAIN, '', PDF_FONT_SIZE_MAIN]);
        $pdf->setFooterFont([PDF_FONT_NAME_DATA, '', PDF_FONT_SIZE_DATA]);

        $pdf->SetDefaultMonospacedFont(PDF_FONT_MONOSPACED);

        $pdf->SetMargins(10, 20, 10);
        $pdf->SetHeaderMargin(5);
        $pdf->SetFooterMargin(10);

        $pdf->SetAutoPageBreak(TRUE, 15);

        $pdf->setImageScale(PDF_IMAGE_SCALE_RATIO);

        $pdf->AddPage();

        $pdf->SetFont('helvetica', '', 9);

        $html = $this->generateHtmlContent($visits, $filters);

        $pdf->writeHTML($html, true, false, true, false, '');

        return $pdf->Output('patient_visits_' . date('Y-m-d_His') . '.pdf', 'S');
    }

    private function getFilterText($filters)
    {
        $text = [];

        if (!empty($filters['date_from']) && !empty($filters['date_to'])) {
            $dateFrom = Carbon::parse($filters['date_from']);
            $dateTo = Carbon::parse($filters['date_to']);
            $text[] = $dateFrom->format('M d, Y') . ' to ' . $dateTo->format('M d, Y');
        } elseif (!empty($filters['date'])) {
            $date = Carbon::parse($filters['date']);
            if ($date->isToday()) {
                $text[] = 'Today';
            } elseif ($date->isYesterday()) {
                $text[] = 'Yesterday';
            } else {
                $text[] = $date->format('M d, Y');
            }
        }

        if (!empty($filters['visit_type'])) {
            $text[] = "Type: " . ucfirst($filters['visit_type']);
        }

        if (!empty($filters['branch_name'])) {
            $text[] = "Branch: {$filters['branch_name']}";
        }

        return !empty($text) ? implode(' | ', $text) : 'All Patient Visits';
    }

    private function generateHtmlContent($visits, $filters)
    {
        $html = '<style>
        table {
            border-collapse: collapse;
            width: 100%;
        }
        th {
            background-color: #059669;
            color: white;
            font-weight: bold;
            padding: 8px;
            text-align: left;
            border: 1px solid #047857;
        }
        td {
            padding: 6px;
            border: 1px solid #d1d5db;
        }
        tr:nth-child(even) {
            background-color: #f9fafb;
        }
        .text-right {
            text-align: right;
        }
        .text-center {
            text-align: center;
        }
        .badge {
            padding: 3px 8px;
            border-radius: 4px;
            font-size: 8px;
            font-weight: bold;
        }
        .badge-success {
            background-color: #d1fae5;
            color: #065f46;
        }
        .badge-info {
            background-color: #dbeafe;
            color: #1e40af;
        }
        .summary {
            margin-top: 20px;
            padding: 10px;
            background-color: #f3f4f6;
            border-radius: 5px;
        }
    </style>';

        $html .= '<h2 style="color: #059669; margin-bottom: 15px;">Patient Visits Report</h2>';

        // Filter summary
        if (!empty($filters)) {
            $html .= '<div class="summary" style="margin-bottom: 15px;">';
            $html .= '<strong>Filters Applied:</strong> ';

            $filterTexts = [];
            if (!empty($filters['date_from']) && !empty($filters['date_to'])) {
                $dateFrom = Carbon::parse($filters['date_from']);
                $dateTo = Carbon::parse($filters['date_to']);
                $filterTexts[] = $dateFrom->format('M d, Y') . ' to ' . $dateTo->format('M d, Y');
            } elseif (!empty($filters['date'])) {
                $date = Carbon::parse($filters['date']);
                if ($date->isToday()) {
                    $filterTexts[] = 'Today';
                } elseif ($date->isYesterday()) {
                    $filterTexts[] = 'Yesterday';
                } else {
                    $filterTexts[] = $date->format('M d, Y');
                }
            }
            if (!empty($filters['visit_type'])) {
                $filterTexts[] = ucfirst($filters['visit_type']);
            }
            if (!empty($filters['branch_name'])) {
                $filterTexts[] = $filters['branch_name'];
            }

            $html .= implode(' | ', $filterTexts);
            $html .= '</div>';
        }

        $html .= '<table cellpadding="5" cellspacing="0">';
        $html .= '<thead>
        <tr>
            <th style="width: 25mm;">Date</th>
            <th style="width: 50mm;">Patient</th>
            <th style="width: 30mm;">Type</th>
            <th style="width: 40mm;">Branch</th>
            <th style="width: 35mm; text-align: right;">Amount Paid</th>
            <th style="width: 77mm;">Services</th>
        </tr>
    </thead>
    <tbody>';

        $totalAmount = 0;

        foreach ($visits as $visit) {
            $totalAmount += $visit->total_amount_paid;

            $visitType = $visit->appointment_id
                ? '<span class="badge badge-info">Appointment</span>'
                : '<span class="badge badge-success">Walk-in</span>';

            $services = $visit->patientVisitServices->map(function ($service) {
                return $service->dentalService->name . ' (x' . $service->quantity . ')';
            })->implode(', ');

            $html .= '<tr>
            <td style="width: 25mm;">' . Carbon::parse($visit->visit_date)->format('M d, Y') . '</td>
            <td style="width: 50mm;">' . htmlspecialchars($visit->patient_name) . '</td>
            <td style="width: 30mm;">' . $visitType . '</td>
            <td style="width: 40mm;">' . htmlspecialchars($visit->branch_name) . '</td>
            <td style="width: 35mm; text-align: right;">₱' . number_format($visit->total_amount_paid, 2) . '</td>
            <td style="width: 77mm;">' . htmlspecialchars($services ?: 'N/A') . '</td>
        </tr>';
        }

        $html .= '</tbody>
    <tfoot>
        <tr>
            <td colspan="4" style="text-align: right; font-weight: bold; background-color: #f3f4f6; border-right: none;">Total:</td>
            <td style="text-align: right; font-weight: bold; background-color: #f3f4f6; border-left: none;">₱' . number_format($totalAmount, 2) . '</td>
            <td style="background-color: #f3f4f6;"></td>
        </tr>
        <tr>
            <td colspan="6" style="text-align: center; background-color: #f3f4f6; font-weight: bold;">
                Total Visits: ' . $visits->count() . '
            </td>
        </tr>
    </tfoot>';
        $html .= '</table>';

        $html .= '<div style="margin-top: 20px; font-size: 8px; color: #6b7280;">
        Generated on: ' . Carbon::now()->format('F d, Y g:i A') . '
    </div>';

        return $html;
    }
}