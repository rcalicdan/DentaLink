<?php

namespace App\Http\Controllers;

use Illuminate\Support\Facades\DB;
use Illuminate\Support\Collection;
use Carbon\Carbon;

class DashboardController extends Controller
{
    public function index()
    {
        return view('contents.dashboard.index', [
            'totalPatients' => $this->getTotalPatients(),
            'patientGrowth' => $this->calculatePatientGrowth(),
            'appointmentsToday' => $this->getAppointmentsToday(),
            'upcomingAppointments' => $this->getUpcomingAppointmentsCount(),
            'monthlyRevenue' => $this->getMonthlyRevenue(),
            'revenueGrowth' => $this->calculateRevenueGrowth(),
            'weeklyAppointments' => $this->getWeeklyAppointmentsData(),
            'servicesBreakdown' => $this->getServicesBreakdownData(),
            'upcomingAppointmentsList' => $this->getUpcomingAppointmentsList()
        ]);
    }

    // ============================================
    // Patient Statistics Methods
    // ============================================

    private function getTotalPatients(): int
    {
        return DB::table('patients')->count();
    }

    private function calculatePatientGrowth(): float
    {
        $patientsThisMonth = $this->getPatientsCountForPeriod(
            Carbon::now()->month,
            Carbon::now()->year
        );

        $lastMonth = Carbon::now()->subMonth();
        $patientsLastMonth = $this->getPatientsCountForPeriod(
            $lastMonth->month,
            $lastMonth->year
        );

        return $this->calculateGrowthPercentage($patientsThisMonth, $patientsLastMonth);
    }

    private function getPatientsCountForPeriod(int $month, int $year): int
    {
        return DB::table('patients')
            ->whereRaw('EXTRACT(MONTH FROM created_at) = ?', [$month])
            ->whereRaw('EXTRACT(YEAR FROM created_at) = ?', [$year])
            ->count();
    }

    // ============================================
    // Appointment Statistics Methods
    // ============================================

    private function getAppointmentsToday(): int
    {
        return DB::table('appointments')
            ->whereDate('appointment_date', Carbon::today())
            ->count();
    }

    private function getUpcomingAppointmentsCount(): int
    {
        return DB::table('appointments')
            ->whereDate('appointment_date', Carbon::today())
            ->whereIn('status', ['waiting', 'in_progress'])
            ->count();
    }

    // ============================================
    // Revenue Statistics Methods
    // ============================================

    private function getMonthlyRevenue(): float
    {
        return $this->getRevenueForPeriod(
            Carbon::now()->month,
            Carbon::now()->year
        );
    }

    private function calculateRevenueGrowth(): float
    {
        $currentRevenue = $this->getRevenueForPeriod(
            Carbon::now()->month,
            Carbon::now()->year
        );

        $lastMonth = Carbon::now()->subMonth();
        $lastRevenue = $this->getRevenueForPeriod(
            $lastMonth->month,
            $lastMonth->year
        );

        return $this->calculateGrowthPercentage($currentRevenue, $lastRevenue);
    }

    private function getRevenueForPeriod(int $month, int $year): float
    {
        return DB::table('patient_visits')
            ->whereRaw('EXTRACT(MONTH FROM visit_date) = ?', [$month])
            ->whereRaw('EXTRACT(YEAR FROM visit_date) = ?', [$year])
            ->sum('total_amount_paid') ?? 0;
    }

    // ============================================
    // Weekly Appointments Chart Methods
    // ============================================

    private function getWeeklyAppointmentsData(): array
    {
        $weekData = $this->getLastSevenDaysAppointments();

        if ($this->hasNoData($weekData['data'])) {
            return $this->getHistoricalAppointmentsData();
        }

        return $weekData;
    }

    private function getLastSevenDaysAppointments(): array
    {
        $days = [];
        $data = [];

        for ($i = 6; $i >= 0; $i--) {
            $date = Carbon::today()->subDays($i);
            $days[] = $date->format('D');
            $data[] = $this->getAppointmentsCountForDate($date);
        }

        return [
            'labels' => $days,
            'data' => $data
        ];
    }

    private function getAppointmentsCountForDate(Carbon $date): int
    {
        return DB::table('appointments')
            ->whereDate('appointment_date', $date->toDateString())
            ->count();
    }

    private function getHistoricalAppointmentsData(): array
    {
        $appointments = $this->getLastThirtyDaysAppointments();

        if ($appointments->isEmpty()) {
            return $this->getLastSevenDaysAppointments();
        }

        return $this->formatAppointmentsChartData($appointments);
    }

    private function getLastThirtyDaysAppointments(): Collection
    {
        return DB::table('appointments')
            ->where('appointment_date', '>=', Carbon::now()->subDays(30))
            ->selectRaw('DATE(appointment_date) as date, COUNT(*) as count')
            ->groupBy('date')
            ->orderBy('date', 'desc')
            ->limit(7)
            ->get();
    }

    private function formatAppointmentsChartData(Collection $appointments): array
    {
        $days = [];
        $data = [];

        foreach ($appointments->reverse() as $appt) {
            $days[] = Carbon::parse($appt->date)->format('D');
            $data[] = (int)$appt->count;
        }

        return [
            'labels' => $days,
            'data' => $data
        ];
    }

    // ============================================
    // Services Breakdown Chart Methods
    // ============================================

    private function getServicesBreakdownData(): array
    {
        $services = $this->getCurrentMonthServices();

        if ($services->isEmpty()) {
            $services = $this->getLastThreeMonthsServices();
        }

        if ($services->isEmpty()) {
            $services = $this->getAllTimeTopServices();
        }

        return $this->formatServicesChartData($services);
    }

    private function getCurrentMonthServices(): Collection
    {
        return DB::table('patient_visit_services as pvs')
            ->join('dental_services as ds', 'pvs.dental_service_id', '=', 'ds.id')
            ->join('patient_visits as pv', 'pvs.patient_visit_id', '=', 'pv.id')
            ->whereRaw('EXTRACT(MONTH FROM pv.visit_date) = ?', [Carbon::now()->month])
            ->whereRaw('EXTRACT(YEAR FROM pv.visit_date) = ?', [Carbon::now()->year])
            ->select('ds.name', DB::raw('COUNT(*) as count'))
            ->groupBy('ds.id', 'ds.name')
            ->orderByDesc('count')
            ->limit(7)
            ->get();
    }

    private function getLastThreeMonthsServices(): Collection
    {
        $threeMonthsAgo = Carbon::now()->subMonths(3)->startOfMonth();

        return DB::table('patient_visit_services as pvs')
            ->join('dental_services as ds', 'pvs.dental_service_id', '=', 'ds.id')
            ->join('patient_visits as pv', 'pvs.patient_visit_id', '=', 'pv.id')
            ->where('pv.visit_date', '>=', $threeMonthsAgo)
            ->select('ds.name', DB::raw('COUNT(*) as count'))
            ->groupBy('ds.id', 'ds.name')
            ->orderByDesc('count')
            ->limit(7)
            ->get();
    }

    private function getAllTimeTopServices(): Collection
    {
        return DB::table('patient_visit_services as pvs')
            ->join('dental_services as ds', 'pvs.dental_service_id', '=', 'ds.id')
            ->select('ds.name', DB::raw('COUNT(*) as count'))
            ->groupBy('ds.id', 'ds.name')
            ->orderByDesc('count')
            ->limit(7)
            ->get();
    }

    private function formatServicesChartData(Collection $services): array
    {
        if ($services->isEmpty()) {
            return [
                'labels' => [],
                'data' => []
            ];
        }

        return [
            'labels' => $services->pluck('name')->toArray(),
            'data' => $services->pluck('count')->map(fn($v) => (int)$v)->toArray()
        ];
    }

    // ============================================
    // Upcoming Appointments List Methods
    // ============================================

    private function getUpcomingAppointmentsList(): Collection
    {
        $appointments = $this->fetchUpcomingAppointments();

        return $appointments->map(fn($appt) => $this->formatAppointmentForDisplay($appt));
    }

    private function fetchUpcomingAppointments(): Collection
    {
        return DB::table('appointments as a')
            ->join('patients as p', 'a.patient_id', '=', 'p.id')
            ->join('branches as b', 'a.branch_id', '=', 'b.id')
            ->where('a.appointment_date', '>=', Carbon::today()->toDateString())
            ->whereIn('a.status', ['waiting', 'in_progress'])
            ->select(
                DB::raw("CONCAT(p.first_name, ' ', p.last_name) as patient_name"),
                'a.appointment_date',
                'a.start_time',
                'a.end_time',
                'b.name as branch_name',
                'a.status'
            )
            ->orderBy('a.appointment_date')
            ->orderBy('a.start_time')
            ->limit(5)
            ->get();
    }

    private function formatAppointmentForDisplay(object $appointment): object
    {
        return (object) [
            'patient_name' => $appointment->patient_name,
            'formatted_date' => $this->formatAppointmentDate($appointment->appointment_date),
            'formatted_time' => $this->formatTimeRange($appointment->start_time, $appointment->end_time),
            'branch_name' => $appointment->branch_name,
            'status_label' => $this->formatStatusLabel($appointment->status),
            'status_class' => $this->getStatusClass($appointment->status)
        ];
    }

    // ============================================
    // Formatting Helper Methods
    // ============================================

    private function formatAppointmentDate(string $date): string
    {
        return Carbon::parse($date)->format('M d, Y');
    }

    private function formatTimeRange(?string $startTime, ?string $endTime): string
    {
        if (empty($startTime) || empty($endTime)) {
            return 'N/A';
        }

        try {
            $start = $this->parseTime($startTime);
            $end = $this->parseTime($endTime);

            return $start->format('g:i A') . ' - ' . $end->format('g:i A');
        } catch (\Exception $e) {
            return $this->formatTimeRangeFallback($startTime, $endTime);
        }
    }

    private function parseTime(string $time): Carbon
    {
        if (strlen($time) > 8) {
            return Carbon::parse($time);
        }

        return Carbon::createFromFormat('H:i:s', $time);
    }

    private function formatTimeRangeFallback(string $startTime, string $endTime): string
    {
        try {
            $start = Carbon::parse($startTime);
            $end = Carbon::parse($endTime);
            return $start->format('g:i A') . ' - ' . $end->format('g:i A');
        } catch (\Exception $e) {
            return $startTime . ' - ' . $endTime;
        }
    }

    private function formatStatusLabel(string $status): string
    {
        return ucfirst(str_replace('_', ' ', $status));
    }

    private function getStatusClass(string $status): string
    {
        return match($status) {
            'waiting' => 'bg-yellow-100 text-yellow-800 dark:bg-yellow-900 dark:text-yellow-300',
            'in_progress' => 'bg-blue-100 text-blue-800 dark:bg-blue-900 dark:text-blue-300',
            'completed' => 'bg-green-100 text-green-800 dark:bg-green-900 dark:text-green-300',
            'cancelled' => 'bg-red-100 text-red-800 dark:bg-red-900 dark:text-red-300',
            default => 'bg-slate-100 text-slate-800 dark:bg-slate-900 dark:text-slate-300'
        };
    }

    // ============================================
    // Calculation Helper Methods
    // ============================================

    private function calculateGrowthPercentage(float $current, float $previous): float
    {
        if ($previous <= 0) {
            return 0;
        }

        return round((($current - $previous) / $previous) * 100, 1);
    }

    private function hasNoData(array $data): bool
    {
        return array_sum($data) === 0;
    }
}