<?php

namespace App\Http\Livewire\Dashboard;

use Livewire\Component;
use App\Services\GeminiKnowledgeService;
use Illuminate\Support\Facades\DB;
use Carbon\Carbon;

class AiForecast extends Component
{
    public $forecast = null;
    public $generatedAt = null;
    public $isLoading = false;
    public $error = null;
    public $hasGenerated = false;

    protected $geminiService;

    public function boot(GeminiKnowledgeService $geminiService)
    {
        $this->geminiService = $geminiService;
    }

    public function generateForecast()
    {
        $this->isLoading = true;
        $this->error = null;
        $this->forecast = null;

        try {
            $clinicData = $this->prepareClinicDataForForecast();
            $this->forecast = $this->geminiService->generateForecast(
                $clinicData,
                cacheTtl: 18000 // 5 hours
            );
            $this->generatedAt = now()->format('M d, Y g:i A');
            $this->hasGenerated = true;
            
            $this->dispatch('forecast-generated');
            session()->flash('forecast-success', 'AI forecast generated successfully!');
        } catch (\Exception $e) {
            logger()->error('AI Forecast generation failed: ' . $e->getMessage());
            $this->error = 'Unable to generate forecast at this time. Please try again later.';
        } finally {
            $this->isLoading = false;
        }
    }

    public function refreshForecast()
    {
        $this->geminiService->clearCache();
        $this->generateForecast();
    }

    public function render()
    {
        return view('livewire.dashboard.ai-forecast');
    }

    // ============================================
    // Data Preparation Methods
    // ============================================

    private function prepareClinicDataForForecast(): array
    {
        $currentMonth = Carbon::now();
        $lastMonth = $currentMonth->copy()->subMonth();

        return [
            'total_patients' => $this->getTotalPatients(),
            'patients_this_month' => $this->getPatientsCountForPeriod($currentMonth->month, $currentMonth->year),
            'patients_last_month' => $this->getPatientsCountForPeriod($lastMonth->month, $lastMonth->year),
            'patient_growth_rate' => $this->calculatePatientGrowth(),
            'appointments_today' => $this->getAppointmentsToday(),
            'upcoming_appointments' => $this->getUpcomingAppointmentsCount(),
            'appointments_this_month' => $this->getAppointmentsCountForPeriod($currentMonth->month, $currentMonth->year),
            'appointments_last_month' => $this->getAppointmentsCountForPeriod($lastMonth->month, $lastMonth->year),
            'average_appointments_per_day' => $this->getAverageAppointmentsPerDay(),
            'revenue_this_month' => $this->getMonthlyRevenue(),
            'revenue_last_month' => $this->getLastMonthRevenue($lastMonth->month, $lastMonth->year),
            'revenue_growth_rate' => $this->calculateRevenueGrowth(),
            'average_visit_value' => $this->getAverageVisitValue(),
            'total_visits_this_month' => $this->getVisitsCountForPeriod($currentMonth->month, $currentMonth->year),
            'cancellation_rate' => $this->getCancellationRate(),
            'no_show_rate' => $this->getNoShowRate(),
            'current_date' => $currentMonth->format('F Y'),
            'current_day' => $currentMonth->format('l'),
        ];
    }

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

    private function getAppointmentsCountForPeriod(int $month, int $year): int
    {
        return DB::table('appointments')
            ->whereRaw('EXTRACT(MONTH FROM appointment_date) = ?', [$month])
            ->whereRaw('EXTRACT(YEAR FROM appointment_date) = ?', [$year])
            ->count();
    }

    private function getAverageAppointmentsPerDay(): float
    {
        $currentMonth = Carbon::now();
        $daysInMonth = $currentMonth->daysInMonth;
        $totalAppointments = $this->getAppointmentsCountForPeriod($currentMonth->month, $currentMonth->year);

        return $totalAppointments > 0 ? round($totalAppointments / $daysInMonth, 1) : 0;
    }

    private function getMonthlyRevenue(): float
    {
        return $this->getRevenueForPeriod(
            Carbon::now()->month,
            Carbon::now()->year
        );
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
        return $this->getRevenueForPeriod($month, $year);
    }

    private function getRevenueForPeriod(int $month, int $year): float
    {
        return DB::table('patient_visits')
            ->whereRaw('EXTRACT(MONTH FROM visit_date) = ?', [$month])
            ->whereRaw('EXTRACT(YEAR FROM visit_date) = ?', [$year])
            ->sum('total_amount_paid') ?? 0;
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

    private function calculateGrowthPercentage(float $current, float $previous): float
    {
        if ($previous <= 0) {
            return 0;
        }

        return round((($current - $previous) / $previous) * 100, 1);
    }
}