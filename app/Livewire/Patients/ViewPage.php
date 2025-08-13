<?php

namespace App\Livewire\Patients;

use App\Models\Patient;
use Illuminate\Support\Facades\Auth;
use Livewire\Component;
use Livewire\WithPagination;

class ViewPage extends Component
{
    use WithPagination;

    public Patient $patient;
    public $activeTab = 'overview';
    public $appointmentsPerPage = 10;
    public $visitsPerPage = 10;

    public function mount(Patient $patient)
    {
        if (Auth::user()->isAdmin() && $patient->registration_branch_id !== Auth::user()->branch_id) {
            abort(403, 'You can only view patients in your branch.');
        }

        $this->patient = $patient;
    }

    public function setActiveTab($tab)
    {
        $this->activeTab = $tab;
        $this->resetPage();
    }

    public function getAppointmentsProperty()
    {
        return $this->patient->appointments()
            ->with(['branch', 'creator'])
            ->orderBy('appointment_date', 'desc')
            ->orderBy('queue_number', 'asc')
            ->paginate($this->appointmentsPerPage, ['*'], 'appointmentsPage');
    }

    public function getVisitsProperty()
    {
        return $this->patient->patientVisits()
            ->with(['branch', 'appointment', 'creator', 'patientVisitServices.dentalService'])
            ->orderBy('visit_date', 'desc')
            ->paginate($this->visitsPerPage, ['*'], 'visitsPage');
    }

    public function getPatientStatsProperty()
    {
        return [
            'total_appointments' => $this->patient->appointments()->count(),
            'completed_appointments' => $this->patient->appointments()
                ->where('status', 'completed')
                ->count(),
            'pending_appointments' => $this->patient->appointments()
                ->whereIn('status', ['waiting', 'in_progress'])
                ->count(),
            'total_visits' => $this->patient->patientVisits()->count(),
            'total_spent' => $this->patient->patientVisits()
                ->sum('total_amount_paid'),
            'last_visit' => $this->patient->patientVisits()
                ->orderBy('visit_date', 'desc')
                ->first()?->visit_date,
            'next_appointment' => $this->patient->appointments()
                ->where('appointment_date', '>=', now())
                ->whereIn('status', ['waiting', 'confirmed'])
                ->orderBy('appointment_date', 'asc')
                ->first(),
        ];
    }

    public function render()
    {
        $this->authorize('view', $this->patient);
        
        return view('livewire.patients.view-page', [
            'patientStats' => $this->getPatientStatsProperty(),
            'appointments' => $this->appointments,
            'visits' => $this->visits,
        ]);
    }
}