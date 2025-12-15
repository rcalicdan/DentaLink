<?php

namespace App\Actions\Appointments;

use App\Models\Appointment;
use Carbon\Carbon;
use TCPDF;
use App\Enums\AppointmentStatuses;

class GenerateAppointmentsPdfAction
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

        return $this->generatePdf($appointments, $filters);
    }

    private function generatePdf($appointments, $filters)
    {
        $pdf = new TCPDF('L', PDF_UNIT, PDF_PAGE_FORMAT, true, 'UTF-8', false);

        $pdf->SetCreator(PDF_CREATOR);
        $pdf->SetAuthor('Dental Clinic System');
        $pdf->SetTitle('Appointments Report');
        $pdf->SetSubject('Appointments');

        $pdf->SetHeaderData('', 0, 'Appointments Report', $this->getFilterText($filters));

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

        $html = $this->generateHtmlContent($appointments, $filters);

        $pdf->writeHTML($html, true, false, true, false, '');

        return $pdf->Output('appointments_' . date('Y-m-d_His') . '.pdf', 'S');
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

        if (!empty($filters['status_name'])) {
            $text[] = "Status: {$filters['status_name']}";
        }

        if (!empty($filters['branch_name'])) {
            $text[] = "Branch: {$filters['branch_name']}";
        }

        return !empty($text) ? implode(' | ', $text) : 'All Appointments';
    }

    private function generateHtmlContent($appointments, $filters)
    {
        $html = '<style>
            table {
                border-collapse: collapse;
                width: 100%;
            }
            th {
                background-color: #2563eb;
                color: white;
                font-weight: bold;
                padding: 8px;
                text-align: left;
                border: 1px solid #1e40af;
            }
            td {
                padding: 6px;
                border: 1px solid #d1d5db;
            }
            tr:nth-child(even) {
                background-color: #f9fafb;
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
            .summary {
                margin-top: 20px;
                padding: 10px;
                background-color: #f3f4f6;
                border-radius: 5px;
            }
        </style>';

        $html .= '<h2 style="color: #2563eb; margin-bottom: 15px;">Appointments Report</h2>';

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
            if (!empty($filters['status_name'])) {
                $filterTexts[] = $filters['status_name'];
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
                <th style="width: 20mm;">Queue #</th>
                <th style="width: 30mm;">Date</th>
                <th style="width: 35mm;">Time</th>
                <th style="width: 50mm;">Patient</th>
                <th style="width: 40mm;">Branch</th>
                <th style="width: 30mm;">Status</th>
                <th style="width: 52mm;">Reason</th>
            </tr>
        </thead>
        <tbody>';

        foreach ($appointments as $appointment) {
            $statusColor = match($appointment->status->value) {
                'waiting' => '#dbeafe:#1e40af',
                'in_progress' => '#fef3c7:#92400e',
                'completed' => '#d1fae5:#065f46',
                'cancelled' => '#fee2e2:#991b1b',
                'missed' => '#fef3c7:#92400e', 
                default => '#f3f4f6:#374151'
            };
            list($bgColor, $textColor) = explode(':', $statusColor);

            $html .= '<tr>
                <td style="width: 20mm; text-align: center;">' . $appointment->queue_number . '</td>
                <td style="width: 30mm;">' . Carbon::parse($appointment->appointment_date)->format('M d, Y') . '</td>
                <td style="width: 35mm;">' . ($appointment->formatted_time_range ?? 'N/A') . '</td>
                <td style="width: 50mm;">' . htmlspecialchars($appointment->patient_name) . '</td>
                <td style="width: 40mm;">' . htmlspecialchars($appointment->branch->name) . '</td>
                <td style="width: 30mm;">
                    <span style="background-color: ' . $bgColor . '; color: ' . $textColor . '; padding: 3px 8px; border-radius: 4px; font-size: 8px; font-weight: bold;">
                        ' . $appointment->status->getDisplayName() . '
                    </span>
                </td>
                <td style="width: 52mm;">' . htmlspecialchars($appointment->reason ?? 'N/A') . '</td>
            </tr>';
        }

        $html .= '</tbody>
        <tfoot>
            <tr>
                <td colspan="7" style="text-align: center; background-color: #f3f4f6; font-weight: bold;">
                    Total Appointments: ' . $appointments->count() . '
                </td>
            </tr>
        </tfoot>';
        $html .= '</table>';

        $statusCounts = $appointments->groupBy('status')->map->count();
        if ($statusCounts->isNotEmpty()) {
            $html .= '<div style="margin-top: 20px;">
                <h3 style="color: #2563eb; margin-bottom: 10px;">Status Breakdown</h3>
                <table cellpadding="5" cellspacing="0" style="width: 50%;">
                    <thead>
                        <tr>
                            <th>Status</th>
                            <th style="text-align: right;">Count</th>
                        </tr>
                    </thead>
                    <tbody>';
            
            foreach ($statusCounts as $statusString => $count) {
                $statusEnum = AppointmentStatuses::from($statusString);
                $html .= '<tr>
                    <td>' . $statusEnum->getDisplayName() . '</td>
                    <td style="text-align: right;">' . $count . '</td>
                </tr>';
            }
            
            $html .= '</tbody></table></div>';
        }

        $html .= '<div style="margin-top: 20px; font-size: 8px; color: #6b7280;">
            Generated on: ' . Carbon::now()->format('F d, Y g:i A') . '
        </div>';

        return $html;
    }
}