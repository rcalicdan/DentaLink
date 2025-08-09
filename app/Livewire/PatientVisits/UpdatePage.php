<?php

namespace App\Livewire\PatientVisits;

use App\Models\PatientVisit;
use App\Models\Patient;
use App\Models\Branch;
use App\Models\Appointment;
use App\Traits\DispatchFlashMessage;
use Livewire\Component;
use Illuminate\Support\Facades\Auth;

class UpdatePage extends Component
{
    use DispatchFlashMessage;
    
    public PatientVisit $patientVisit;
    public $patient_id;
    public $appointment_id;
    public $visit_date;
    public $notes;
    public $total_amount_paid;
    public $branch_id;
    public $visit_type;

    public $patientSearch = '';
    public $showPatientDropdown = false;
    public $selectedPatient = null;

    public $appointmentSearch = '';
    public $showAppointmentDropdown = false;
    public $selectedAppointment = null;

    public function mount(PatientVisit $patientVisit)
    {
        $this->authorize('update', $patientVisit);
        
        $this->patientVisit = $patientVisit;
        $this->patient_id = $patientVisit->patient_id;
        $this->appointment_id = $patientVisit->appointment_id;
        $this->visit_date = $patientVisit->visit_date->format('Y-m-d\TH:i');
        $this->notes = $patientVisit->notes;
        $this->total_amount_paid = $patientVisit->total_amount_paid;
        $this->branch_id = $patientVisit->branch_id;
        $this->visit_type = $patientVisit->appointment_id ? 'appointment' : 'walk-in';

        // Set initial patient selection
        $this->selectedPatient = $patientVisit->patient;
        $this->patientSearch = $patientVisit->patient->full_name . ' (ID: ' . $patientVisit->patient->id . ')';

        // Set initial appointment selection if exists
        if ($patientVisit->appointment) {
            $this->selectedAppointment = $patientVisit->appointment;
            $this->appointmentSearch = "Queue #{$patientVisit->appointment->queue_number} - {$patientVisit->appointment->appointment_date->format('M d, Y')} - {$patientVisit->appointment->reason}";
        }
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
            $this->clearAppointmentSelection();
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

    public function update()
    {
        $this->authorize('update', $this->patientVisit);
        
        if (!Auth::user()->isSuperadmin()) {
            $this->branch_id = Auth::user()->branch_id;
        }
        
        $validatedData = $this->validate();

        try {
            $updateData = [
                'patient_id' => $this->patient_id,
                'branch_id' => $this->branch_id,
                'visit_date' => $this->visit_date,
                'notes' => $this->notes,
                'total_amount_paid' => $this->total_amount_paid,
            ];

            if ($this->visit_type === 'appointment' && $this->appointment_id) {
                $updateData['appointment_id'] = $this->appointment_id;
            } else {
                $updateData['appointment_id'] = null;
            }

            $this->patientVisit->update($updateData);

            session()->flash('success', 'Patient visit updated successfully!');
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
        return view('livewire.patient-visits.update-page', [
            'searchedPatients' => $this->searchedPatients,
            'searchedAppointments' => $this->searchedAppointments,
            'canUpdateBranch' => $this->canUpdateBranch(),
            'branches' => $this->getBranchesForUser()
        ]);
    }
}