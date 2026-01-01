<?php
// app/Livewire/Appointments/Calendar.php

namespace App\Livewire\Appointments;

use App\Models\Appointment;
use App\Models\Branch;
use App\Enums\AppointmentStatuses;
use Carbon\Carbon;
use Illuminate\Support\Facades\Auth;
use Livewire\Attributes\Layout;
use Livewire\Component;

#[Layout('components.layouts.app')]
class Calendar extends Component
{
    public $currentMonth;
    public $currentYear;
    public $searchBranch = '';
    public $calendarDays = [];
    public $appointmentData = [];

    public function mount()
    {
        $this->currentMonth = Carbon::now()->month;
        $this->currentYear = Carbon::now()->year;
        
        if (!Auth::user()->isSuperadmin()) {
            $this->searchBranch = Auth::user()->branch_id;
        }
        
        $this->loadCalendarData();
    }

    public function previousMonth()
    {
        $date = Carbon::create($this->currentYear, $this->currentMonth, 1)->subMonth();
        $this->currentMonth = $date->month;
        $this->currentYear = $date->year;
        $this->loadCalendarData();
    }

    public function nextMonth()
    {
        $date = Carbon::create($this->currentYear, $this->currentMonth, 1)->addMonth();
        $this->currentMonth = $date->month;
        $this->currentYear = $date->year;
        $this->loadCalendarData();
    }

    public function goToToday()
    {
        $this->currentMonth = Carbon::now()->month;
        $this->currentYear = Carbon::now()->year;
        $this->loadCalendarData();
    }

    public function updatedSearchBranch()
    {
        $this->loadCalendarData();
    }

    private function loadCalendarData()
    {
        $startOfMonth = Carbon::create($this->currentYear, $this->currentMonth, 1)->startOfDay();
        $endOfMonth = $startOfMonth->copy()->endOfMonth()->endOfDay();
        
        // Get the start of the calendar (previous month days if needed)
        $calendarStart = $startOfMonth->copy()->startOfWeek(Carbon::SUNDAY);
        $calendarEnd = $endOfMonth->copy()->endOfWeek(Carbon::SATURDAY);

        // Fetch appointments for the entire calendar view
        $query = Appointment::whereBetween('appointment_date', [
            $calendarStart->format('Y-m-d'),
            $calendarEnd->format('Y-m-d')
        ]);

        if ($this->searchBranch) {
            $query->where('branch_id', $this->searchBranch);
        }

        $appointments = $query->get()->groupBy(function($appointment) {
            return $appointment->appointment_date->format('Y-m-d');
        });

        // Build appointment data with status counts
        $this->appointmentData = [];
        foreach ($appointments as $date => $dateAppointments) {
            $this->appointmentData[$date] = [
                'total' => $dateAppointments->count(),
                'statuses' => $dateAppointments->groupBy('status')->map->count()->toArray()
            ];
        }

        // Build calendar days
        $this->calendarDays = [];
        $currentDate = $calendarStart->copy();
        
        while ($currentDate <= $calendarEnd) {
            $this->calendarDays[] = [
                'date' => $currentDate->copy(),
                'isCurrentMonth' => $currentDate->month === $this->currentMonth,
                'isToday' => $currentDate->isToday(),
                'dateString' => $currentDate->format('Y-m-d')
            ];
            $currentDate->addDay();
        }
    }

    public function goToDate($date)
    {
        return redirect()->route('appointments.index', ['date' => $date]);
    }

    public function render()
    {
        $this->authorize('viewAny', Appointment::class);
        
        $currentMonthName = Carbon::create($this->currentYear, $this->currentMonth, 1)->format('F Y');
        $branches = Branch::orderBy('name')->get();
        
        return view('livewire.appointments.calendar', [
            'currentMonthName' => $currentMonthName,
            'branches' => $branches,
            'availableStatuses' => AppointmentStatuses::cases(),
        ]);
    }
}