<?php

namespace App\Http\Controllers;

use Illuminate\Support\Facades\DB;
use Illuminate\Support\Collection;
use Carbon\Carbon;
use App\Services\GeminiKnowledgeService;

class DashboardController extends Controller
{
    private ?int $appointmentsTodayCache = null;
    private ?float $monthlyRevenueCache = null;
    private ?float $lastMonthRevenueCache = null;
    private GeminiKnowledgeService $geminiService;

    public function __construct(GeminiKnowledgeService $geminiService)
    {
        $this->geminiService = $geminiService;
    }

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
            'upcomingAppointmentsList' => $this->getUpcomingAppointmentsList(),
            'aiForecast' => $this->getAIForecastAndPredictions()
        ]);
    }

    /**
     * Manually refresh AI forecast by clearing cache
     */
    public function refreshForecast()
    {
        $this->geminiService->clearCache();
        
        return redirect()->route('dashboard')
            ->with('success', 'AI forecast cache cleared. New forecast will be generated.');
    }

    // ============================================
    // AI Forecast & Predictions Methods
    // ============================================

    /**
     * Get AI-powered forecast using Gemini's built-in caching (5 hours)
     */
    private function getAIForecastAndPredictions(): array
    {
        try {
            $clinicData = $this->prepareClinicDataForForecast();
            $forecast = $this->generateAIForecast($clinicData);

            return [
                'success' => true,
                'forecast' => $forecast,
                'generated_at' => now()->format('M d, Y g:i A'),
                'cache_info' => 'Cached for 5 hours by Gemini Client',
            ];
        } catch (\Exception $e) {
            logger()->error('AI Forecast generation failed: ' . $e->getMessage());
            
            return [
                'success' => false,
                'error' => 'Unable to generate forecast at this time. Please try again later.',
                'generated_at' => now()->format('M d, Y g:i A'),
            ];
        }
    }

    /**
     * Generate AI forecast using Gemini service with 5-hour cache
     */
    private function generateAIForecast(array $clinicData): string
    {
        return $this->geminiService->generateForecast(
            $clinicData,
            cacheTtl: 18000 // 5 hours in seconds
        );
    }

    /**
     * Prepare comprehensive clinic data for AI analysis
     */
    private function prepareClinicDataForForecast(): array
    {
        $currentMonth = Carbon::now();
        $lastMonth = $currentMonth->copy()->subMonth();

        return [
            // Patient metrics
            'total_patients' => $this->getTotalPatients(),
            'patients_this_month' => $this->getPatientsCountForPeriod($currentMonth->month, $currentMonth->year),
            'patients_last_month' => $this->getPatientsCountForPeriod($lastMonth->month, $lastMonth->year),
            'patient_growth_rate' => $this->calculatePatientGrowth(),

            // Appointment metrics
            'appointments_today' => $this->getAppointmentsToday(),
            'upcoming_appointments' => $this->getUpcomingAppointmentsCount(),
            'appointments_this_month' => $this->getAppointmentsCountForPeriod($currentMonth->month, $currentMonth->year),
            'appointments_last_month' => $this->getAppointmentsCountForPeriod($lastMonth->month, $lastMonth->year),
            'average_appointments_per_day' => $this->getAverageAppointmentsPerDay(),

            // Revenue metrics
            'revenue_this_month' => $this->getMonthlyRevenue(),
            'revenue_last_month' => $this->getLastMonthRevenue($lastMonth->month, $lastMonth->year),
            'revenue_growth_rate' => $this->calculateRevenueGrowth(),
            'average_visit_value' => $this->getAverageVisitValue(),
            'total_visits_this_month' => $this->getVisitsCountForPeriod($currentMonth->month, $currentMonth->year),

            // Operational metrics
            'cancellation_rate' => $this->getCancellationRate(),
            'no_show_rate' => $this->getNoShowRate(),

            // Time context
            'current_date' => $currentMonth->format('F Y'),
            'current_day' => $currentMonth->format('l'),
        ];
    }

    // ============================================
    // Supporting Data Methods for AI Forecast
    // ============================================

    private function getAppointmentsCountForPeriod(int $month, int $year): int
    {
        return DB::table('appointments')
            ->whereRaw('EXTRACT(MONTH FROM appointment_date) = ?', [$month])
            ->whereRaw('EXTRACT(YEAR FROM appointment_date) = ?', [$year])
            ->count();
    }

    private function getAverageVisitValue(): float
    {
        $currentMonth = Carbon::now();
        
        $totalRevenue = $this->getRevenueForPeriod($currentMonth->month, $currentMonth->year);
        $totalVisits = $this->getVisitsCountForPeriod($currentMonth->month, $currentMonth->year);

        return $totalVisits > 0 ? round($totalRevenue / $totalVisits, 2) : 0;
    }

    private function getVisitsCountForPeriod(int $month, int $year): int
    {
        return DB::table('patient_visits')
            ->whereRaw('EXTRACT(MONTH FROM visit_date) = ?', [$month])
            ->whereRaw('EXTRACT(YEAR FROM visit_date) = ?', [$year])
            ->count();
    }

    private function getAverageAppointmentsPerDay(): float
    {
        $currentMonth = Carbon::now();
        $daysInMonth = $currentMonth->daysInMonth;
        $totalAppointments = $this->getAppointmentsCountForPeriod($currentMonth->month, $currentMonth->year);

        return $totalAppointments > 0 ? round($totalAppointments / $daysInMonth, 1) : 0;
    }

    private function getCancellationRate(): float
    {
        $currentMonth = Carbon::now();
        
        $total = DB::table('appointments')
            ->whereRaw('EXTRACT(MONTH FROM appointment_date) = ?', [$currentMonth->month])
            ->whereRaw('EXTRACT(YEAR FROM appointment_date) = ?', [$currentMonth->year])
            ->count();

        $cancelled = DB::table('appointments')
            ->whereRaw('EXTRACT(MONTH FROM appointment_date) = ?', [$currentMonth->month])
            ->whereRaw('EXTRACT(YEAR FROM appointment_date) = ?', [$currentMonth->year])
            ->where('status', 'cancelled')
            ->count();

        return $total > 0 ? round(($cancelled / $total) * 100, 1) : 0;
    }

    private function getNoShowRate(): float
    {
        $currentMonth = Carbon::now();
        
        $total = DB::table('appointments')
            ->whereRaw('EXTRACT(MONTH FROM appointment_date) = ?', [$currentMonth->month])
            ->whereRaw('EXTRACT(YEAR FROM appointment_date) = ?', [$currentMonth->year])
            ->count();

        $noShows = DB::table('appointments')
            ->whereRaw('EXTRACT(MONTH FROM appointment_date) = ?', [$currentMonth->month])
            ->whereRaw('EXTRACT(YEAR FROM appointment_date) = ?', [$currentMonth->year])
            ->where('status', 'no_show')
            ->count();

        return $total > 0 ? round(($noShows / $total) * 100, 1) : 0;
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
        if ($this->appointmentsTodayCache !== null) {
            return $this->appointmentsTodayCache;
        }

        $this->appointmentsTodayCache = DB::table('appointments')
            ->whereDate('appointment_date', Carbon::today())
            ->count();

        return $this->appointmentsTodayCache;
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
        if ($this->monthlyRevenueCache !== null) {
            return $this->monthlyRevenueCache;
        }

        $this->monthlyRevenueCache = $this->getRevenueForPeriod(
            Carbon::now()->month,
            Carbon::now()->year
        );

        return $this->monthlyRevenueCache;
    }

    private function calculateRevenueGrowth(): float
    {
        $currentRevenue = $this->getMonthlyRevenue();

        $lastMonth = Carbon::now()->subMonth();
        $lastRevenue = $this->getLastMonthRevenue($lastMonth->month, $lastMonth->year);

        return $this->calculateGrowthPercentage($currentRevenue, $lastRevenue);
    }

    private function getLastMonthRevenue(int $month, int $year): float
    {
        if ($this->lastMonthRevenueCache !== null) {
            return $this->lastMonthRevenueCache;
        }

        $this->lastMonthRevenueCache = $this->getRevenueForPeriod($month, $year);

        return $this->lastMonthRevenueCache;
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
        $startDate = Carbon::today()->subDays(6);
        $endDate = Carbon::today();

        $appointments = DB::table('appointments')
            ->whereBetween('appointment_date', [$startDate->toDateString(), $endDate->toDateString()])
            ->selectRaw('DATE(appointment_date) as date, COUNT(*) as count')
            ->groupBy('date')
            ->orderBy('date')
            ->get()
            ->keyBy('date');

        $days = [];
        $data = [];

        for ($i = 6; $i >= 0; $i--) {
            $date = Carbon::today()->subDays($i);
            $dateString = $date->toDateString();

            $days[] = $date->format('D');

            $data[] = isset($appointments[$dateString])
                ? (int)$appointments[$dateString]->count
                : 0;
        }

        return [
            'labels' => $days,
            'data' => $data
        ];
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
        return match ($status) {
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