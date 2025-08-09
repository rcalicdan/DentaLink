<?php

namespace App\Livewire\PatientVisits;

use App\Models\PatientVisit;
use App\Models\Patient;
use App\Models\Branch;
use App\Models\Appointment;
use App\Traits\DispatchFlashMessage;
use Livewire\Component;
use Illuminate\Support\Facades\Auth;
use Carbon\Carbon;

class CreatePage extends Component
{
    use DispatchFlashMessage;
    
    public $patient_id = '';
    public $appointment_id = '';
    public $visit_date = '';
    public $notes = '';
    public $total_amount_paid = 0;
    public $branch_id = '';
    public $visit_type = 'walk-in'; 

    public $patientSearch = '';
    public $showPatientDropdown = false;
    public $selectedPatient = null;

    public $appointmentSearch = '';
    public $showAppointmentDropdown = false;
    public $selectedAppointment = null;

    public function mount()
    {
        $this->visit_date = Carbon::now()->format('Y-m-d\TH:i');
        $this->branch_id = Auth::user()->branch_id;
    }

    public function rules()
    {
        $user = Auth::user();

        $rules = [
            'patient_id' => 'required|exists:patients,id',
            'visit_date' => 'required|date|before_or_equal:now',
            'notes' => 'nullable|string|max:1000',
            'total_amount_paid' => 'required|numeric|min:0',
            'visit_type' => 'required|in:walk-in,appointment',
        ];

        if ($this->visit_type === 'appointment') {
            $rules['appointment_id'] = 'required|exists:appointments,id';
        }

        if ($user->isSuperadmin()) {
            $rules['branch_id'] = 'required|exists:branches,id';
        }

        return $rules;
    }

    public function updatedVisitType()
    {
        if ($this->visit_type === 'walk-in') {
            $this->appointment_id = '';
            $this->selectedAppointment = null;
            $this->appointmentSearch = '';
            $this->showAppointmentDropdown = false;
        }
    }

    public function updatedPatientSearch()
    {
        $this->showPatientDropdown = !empty($this->patientSearch);
        if (empty($this->patientSearch)) {
            $this->selectedPatient = null;
            $this->patient_id = '';
        }
        // Clear appointment when patient changes
        $this->clearAppointmentSelection();
    }

    public function updatedAppointmentSearch()
    {
        $this->showAppointmentDropdown = !empty($this->appointmentSearch) && $this->visit_type === 'appointment';
        if (empty($this->appointmentSearch)) {
            $this->selectedAppointment = null;
            $this->appointment_id = '';
        }
    }

    public function selectPatient($patientId)
    {
        $patient = Patient::find($patientId);
        if ($patient) {
            $this->patient_id = $patient->id;
            $this->selectedPatient = $patient;
            $this->patientSearch = $patient->full_name . ' (ID: ' . $patient->id . ')';
            $this->showPatientDropdown = false;
            $this->clearAppointmentSelection(); // Clear appointment when patient changes
        }
    }

    public function selectAppointment($appointmentId)
    {
        $appointment = Appointment::with('patient')->find($appointmentId);
        if ($appointment && $this->visit_type === 'appointment') {
            $this->appointment_id = $appointment->id;
            $this->selectedAppointment = $appointment;
            $this->appointmentSearch = "Queue #{$appointment->queue_number} - {$appointment->appointment_date->format('M d, Y')} - {$appointment->reason}";
            $this->showAppointmentDropdown = false;
            
            // Auto-fill patient if not selected
            if (!$this->selectedPatient) {
                $this->selectPatient($appointment->patient_id);
            }
        }
    }

    public function clearPatientSelection()
    {
        $this->patient_id = '';
        $this->selectedPatient = null;
        $this->patientSearch = '';
        $this->showPatientDropdown = false;
        $this->clearAppointmentSelection();
    }

    public function clearAppointmentSelection()
    {
        $this->appointment_id = '';
        $this->selectedAppointment = null;
        $this->appointmentSearch = '';
        $this->showAppointmentDropdown = false;
    }

    public function getSearchedPatientsProperty()
    {
        if (empty($this->patientSearch)) {
            return Patient::orderBy('first_name')->limit(15)->get();
        }

        return Patient::where(function ($query) {
            $query->where('first_name', 'like', '%' . $this->patientSearch . '%')
                ->orWhere('last_name', 'like', '%' . $this->patientSearch . '%')
                ->orWhere('id', 'like', '%' . $this->patientSearch . '%')
                ->orWhereRaw("CONCAT(first_name, ' ', last_name) LIKE ?", ['%' . $this->patientSearch . '%']);
        })
        ->orderBy('first_name')
        ->limit(15)
        ->get();
    }

    public function getSearchedAppointmentsProperty()
    {
        if ($this->visit_type !== 'appointment' || empty($this->appointmentSearch) || !$this->selectedPatient) {
            return collect();
        }

        return Appointment::with('patient')
            ->where('patient_id', $this->selectedPatient->id)
            ->where(function ($query) {
                $query->where('reason', 'like', '%' . $this->appointmentSearch . '%')
                    ->orWhere('queue_number', 'like', '%' . $this->appointmentSearch . '%');
            })
            ->whereIn('status', ['waiting', 'in_progress', 'completed'])
            ->orderBy('appointment_date', 'desc')
            ->limit(10)
            ->get();
    }

    public function save()
    {
        $this->authorize('create', PatientVisit::class);

        if (!Auth::user()->isSuperadmin()) {
            $this->branch_id = Auth::user()->branch_id;
        }

        $validatedData = $this->validate();

        try {
            $visitData = [
                'patient_id' => $this->patient_id,
                'branch_id' => $this->branch_id,
                'visit_date' => $this->visit_date,
                'notes' => $this->notes,
                'total_amount_paid' => $this->total_amount_paid,
                'created_by' => Auth::id(),
            ];

            if ($this->visit_type === 'appointment' && $this->appointment_id) {
                $visitData['appointment_id'] = $this->appointment_id;
            }

            PatientVisit::create($visitData);

            session()->flash('success', 'Patient visit created successfully!');

            return $this->redirect(route('patient-visits.index'), navigate: true);
        } catch (\Exception $e) {
            $this->dispatchErrorMessage($e->getMessage());
        }
    }

    public function canUpdateBranch()
    {
        return Auth::user()->isSuperadmin();
    }

    private function getBranchesForUser()
    {
        $user = Auth::user();

        if ($user->isSuperadmin()) {
            return Branch::orderBy('name')->get();
        }

        return $user->branch ? [$user->branch] : [];
    }

    public function render()
    {
        $this->authorize('create', PatientVisit::class);

        return view('livewire.patient-visits.create-page', [
            'searchedPatients' => $this->searchedPatients,
            'searchedAppointments' => $this->searchedAppointments,
            'branches' => $this->getBranchesForUser(),
            'canUpdateBranch' => $this->canUpdateBranch()
        ]);
    }
}