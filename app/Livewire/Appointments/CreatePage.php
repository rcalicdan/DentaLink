<?php

namespace App\Livewire\Appointments;

use App\Models\Appointment;
use App\Models\Patient;
use App\Models\Branch;
use App\Enums\AppointmentStatuses;
use Illuminate\Validation\Rule;
use Livewire\Component;
use Illuminate\Support\Facades\Auth;
use Carbon\Carbon;

class CreatePage extends Component
{
    public $patient_id = '';
    public $appointment_date = '';
    public $reason = '';
    public $notes = '';
    public $branch_id = '';

    public $patientSearch = '';
    public $showPatientDropdown = false;
    public $selectedPatient = null;

    public function mount()
    {
        $this->appointment_date = Carbon::today()->format('Y-m-d');
        $this->branch_id = Auth::user()->branch_id;
    }

    public function rules()
    {
        $user = Auth::user();

        $rules = [
            'patient_id' => 'required|exists:patients,id',
            'appointment_date' => 'required|date|after_or_equal:today',
            'reason' => 'required|string|min:5|max:255',
            'notes' => 'nullable|string|max:500',
        ];

        // Branch validation
        if ($user->isSuperadmin()) {
            $rules['branch_id'] = 'required|exists:branches,id';
        } else {
            // For non-superadmin users, branch must be their assigned branch
            $rules['branch_id'] = [
                'required',
                'exists:branches,id',
                Rule::in([$user->branch_id]) // Only allow their assigned branch
            ];
        }

        return $rules;
    }

    public function updatedPatientSearch()
    {
        $this->showPatientDropdown = !empty($this->patientSearch);
        if (empty($this->patientSearch)) {
            $this->selectedPatient = null;
            $this->patient_id = '';
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
        }
    }

    public function clearPatientSelection()
    {
        $this->patient_id = '';
        $this->selectedPatient = null;
        $this->patientSearch = '';
        $this->showPatientDropdown = false;
    }

    public function getSearchedPatientsProperty()
    {
        if (empty($this->patientSearch)) {
            return Patient::orderBy('first_name')
                ->limit(15)
                ->get();
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

    public function save()
    {
        $this->authorize('create', Appointment::class);

        // Ensure non-superadmin users can only use their assigned branch
        if (!Auth::user()->isSuperadmin()) {
            $this->branch_id = Auth::user()->branch_id;
        }

        $this->validate();

        try {
            Appointment::checkPatientAppointmentConflict(
                $this->patient_id,
                $this->appointment_date
            );

            $appointment = Appointment::create([
                'patient_id' => $this->patient_id,
                'appointment_date' => $this->appointment_date,
                'reason' => $this->reason,
                'notes' => $this->notes,
                'branch_id' => $this->branch_id,
                'status' => AppointmentStatuses::WAITING,
                'queue_number' => Appointment::getNextQueueNumber($this->appointment_date),
            ]);

            session()->flash('success', 'Appointment created successfully!');

            return $this->redirect(route('appointments.index'), navigate: true);
        } catch (\Exception $e) {
            session()->flash('error', $e->getMessage());
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

        if ($user->isAdmin()) {
            return $user->branch ? [$user->branch] : [];
        }

        return $user->branch ? [$user->branch] : [];
    }

    public function render()
    {
        $this->authorize('create', Appointment::class);

        return view('livewire.appointments.create-page', [
            'searchedPatients' => $this->searchedPatients,
            'branches' => $this->getBranchesForUser(),
            'canUpdateBranch' => $this->canUpdateBranch()
        ]);
    }
}
