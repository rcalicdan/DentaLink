<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Carbon\Carbon;

class DashboardController extends Controller
{
    public function index()
    {
        $today = Carbon::today();
        $currentMonth = Carbon::now()->month;
        $currentYear = Carbon::now()->year;
        $lastMonth = Carbon::now()->subMonth();

        // Total Patients
        $totalPatients = DB::table('patients')->count();
        
        // Patient Growth (this month vs last month)
        $patientsThisMonth = DB::table('patients')
            ->whereRaw('EXTRACT(MONTH FROM created_at) = ?', [$currentMonth])
            ->whereRaw('EXTRACT(YEAR FROM created_at) = ?', [$currentYear])
            ->count();
        
        $patientsLastMonth = DB::table('patients')
            ->whereRaw('EXTRACT(MONTH FROM created_at) = ?', [$lastMonth->month])
            ->whereRaw('EXTRACT(YEAR FROM created_at) = ?', [$lastMonth->year])
            ->count();
        
        $patientGrowth = $patientsLastMonth > 0 
            ? round((($patientsThisMonth - $patientsLastMonth) / $patientsLastMonth) * 100, 1)
            : 0;

        // Appointments Today
        $appointmentsToday = DB::table('appointments')
            ->whereDate('appointment_date', $today)
            ->count();

        // Upcoming Appointments Today
        $upcomingAppointments = DB::table('appointments')
            ->whereDate('appointment_date', $today)
            ->whereIn('status', ['waiting', 'in_progress'])
            ->count();

        // Monthly Revenue
        $monthlyRevenue = DB::table('patient_visits')
            ->whereRaw('EXTRACT(MONTH FROM visit_date) = ?', [$currentMonth])
            ->whereRaw('EXTRACT(YEAR FROM visit_date) = ?', [$currentYear])
            ->sum('total_amount_paid') ?? 0;

        // Revenue Last Month
        $lastMonthRevenue = DB::table('patient_visits')
            ->whereRaw('EXTRACT(MONTH FROM visit_date) = ?', [$lastMonth->month])
            ->whereRaw('EXTRACT(YEAR FROM visit_date) = ?', [$lastMonth->year])
            ->sum('total_amount_paid') ?? 0;

        // Revenue Growth
        $revenueGrowth = $lastMonthRevenue > 0 
            ? round((($monthlyRevenue - $lastMonthRevenue) / $lastMonthRevenue) * 100, 1)
            : 0;

        // Weekly Appointments (Last 7 days)
        $weeklyAppointments = $this->getWeeklyAppointments();

        // Services Breakdown (Top services this month)
        $servicesBreakdown = $this->getServicesBreakdown($currentMonth, $currentYear);

        // Upcoming Appointments List (Next 5)
        $upcomingAppointmentsList = $this->getUpcomingAppointmentsList();

        return view('contents.dashboard.index', compact(
            'totalPatients',
            'patientGrowth',
            'appointmentsToday',
            'upcomingAppointments',
            'monthlyRevenue',
            'revenueGrowth',
            'weeklyAppointments',
            'servicesBreakdown',
            'upcomingAppointmentsList'
        ));
    }

    private function getWeeklyAppointments()
    {
        $days = [];
        $data = [];

        // Get last 7 days
        for ($i = 6; $i >= 0; $i--) {
            $date = Carbon::today()->subDays($i);
            $days[] = $date->format('D'); // Mon, Tue, etc.
            
            $count = DB::table('appointments')
                ->whereDate('appointment_date', $date->toDateString())
                ->count();
            
            $data[] = $count;
        }

        // If all counts are 0, try to get some historical data for last 30 days
        if (array_sum($data) === 0) {
            $days = [];
            $data = [];
            
            // Get appointments from last 30 days grouped by date
            $appointments = DB::table('appointments')
                ->where('appointment_date', '>=', Carbon::now()->subDays(30))
                ->selectRaw('DATE(appointment_date) as date, COUNT(*) as count')
                ->groupBy('date')
                ->orderBy('date', 'desc')
                ->limit(7)
                ->get();
            
            if ($appointments->isNotEmpty()) {
                foreach ($appointments->reverse() as $appt) {
                    $days[] = Carbon::parse($appt->date)->format('D');
                    $data[] = (int)$appt->count;
                }
            } else {
                // Return last 7 days with zeros if no data at all
                for ($i = 6; $i >= 0; $i--) {
                    $date = Carbon::today()->subDays($i);
                    $days[] = $date->format('D');
                    $data[] = 0;
                }
            }
        }

        return [
            'labels' => $days,
            'data' => $data
        ];
    }

    private function getServicesBreakdown($month, $year)
    {
        // Try current month first
        $services = DB::table('patient_visit_services as pvs')
            ->join('dental_services as ds', 'pvs.dental_service_id', '=', 'ds.id')
            ->join('patient_visits as pv', 'pvs.patient_visit_id', '=', 'pv.id')
            ->whereRaw('EXTRACT(MONTH FROM pv.visit_date) = ?', [$month])
            ->whereRaw('EXTRACT(YEAR FROM pv.visit_date) = ?', [$year])
            ->select('ds.name', DB::raw('COUNT(*) as count'))
            ->groupBy('ds.id', 'ds.name')
            ->orderByDesc('count')
            ->limit(7)
            ->get();

        // If no data for current month, try last 3 months
        if ($services->isEmpty()) {
            $threeMonthsAgo = Carbon::now()->subMonths(3)->startOfMonth();
            
            $services = DB::table('patient_visit_services as pvs')
                ->join('dental_services as ds', 'pvs.dental_service_id', '=', 'ds.id')
                ->join('patient_visits as pv', 'pvs.patient_visit_id', '=', 'pv.id')
                ->where('pv.visit_date', '>=', $threeMonthsAgo)
                ->select('ds.name', DB::raw('COUNT(*) as count'))
                ->groupBy('ds.id', 'ds.name')
                ->orderByDesc('count')
                ->limit(7)
                ->get();
        }

        // If still no data, get all-time top services
        if ($services->isEmpty()) {
            $services = DB::table('patient_visit_services as pvs')
                ->join('dental_services as ds', 'pvs.dental_service_id', '=', 'ds.id')
                ->select('ds.name', DB::raw('COUNT(*) as count'))
                ->groupBy('ds.id', 'ds.name')
                ->orderByDesc('count')
                ->limit(7)
                ->get();
        }

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

    private function getUpcomingAppointmentsList()
    {
        $today = Carbon::today();
        
        $appointments = DB::table('appointments as a')
            ->join('patients as p', 'a.patient_id', '=', 'p.id')
            ->join('branches as b', 'a.branch_id', '=', 'b.id')
            ->where('a.appointment_date', '>=', $today->toDateString())
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
            ->get()
            ->map(function ($appt) {
                return (object) [
                    'patient_name' => $appt->patient_name,
                    'formatted_date' => Carbon::parse($appt->appointment_date)->format('M d, Y'),
                    'formatted_time' => $this->formatTimeRange($appt->start_time, $appt->end_time),
                    'branch_name' => $appt->branch_name,
                    'status_label' => ucfirst(str_replace('_', ' ', $appt->status)),
                    'status_class' => $this->getStatusClass($appt->status)
                ];
            });

        return $appointments;
    }

    private function formatTimeRange($startTime, $endTime)
    {
        try {
            // Try different parsing methods
            if (empty($startTime) || empty($endTime)) {
                return 'N/A';
            }

            // If it's already a Carbon instance or datetime string with date
            if (strlen($startTime) > 8) {
                $start = Carbon::parse($startTime);
                $end = Carbon::parse($endTime);
            } else {
                // If it's just time (HH:MM:SS or HH:MM)
                $start = Carbon::createFromFormat('H:i:s', $startTime);
                $end = Carbon::createFromFormat('H:i:s', $endTime);
            }

            return $start->format('g:i A') . ' - ' . $end->format('g:i A');
        } catch (\Exception $e) {
            // Fallback: try alternative formats
            try {
                $start = Carbon::parse($startTime);
                $end = Carbon::parse($endTime);
                return $start->format('g:i A') . ' - ' . $end->format('g:i A');
            } catch (\Exception $e2) {
                // If all parsing fails, return raw values
                return $startTime . ' - ' . $endTime;
            }
        }
    }

    private function getStatusClass($status)
    {
        return match($status) {
            'waiting' => 'bg-yellow-100 text-yellow-800 dark:bg-yellow-900 dark:text-yellow-300',
            'in_progress' => 'bg-blue-100 text-blue-800 dark:bg-blue-900 dark:text-blue-300',
            'completed' => 'bg-green-100 text-green-800 dark:bg-green-900 dark:text-green-300',
            'cancelled' => 'bg-red-100 text-red-800 dark:bg-red-900 dark:text-red-300',
            default => 'bg-slate-100 text-slate-800 dark:bg-slate-900 dark:text-slate-300'
        };
    }
}