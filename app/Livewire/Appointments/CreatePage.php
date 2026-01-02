<?php

namespace App\Livewire\Appointments;

use App\Models\Appointment;
use App\Models\Patient;
use App\Models\Branch;
use App\Models\User;
use App\Enums\AppointmentStatuses;
use App\Enums\UserRoles;
use App\Traits\DispatchFlashMessage;
use Illuminate\Validation\Rule;
use Livewire\Component;
use Illuminate\Support\Facades\Auth;
use Carbon\Carbon;

class CreatePage extends Component
{
    use DispatchFlashMessage;

    public $patient_id = '';
    public $appointment_date = '';
    public $start_time = '';
    public $end_time = '';
    public $reason = '';
    public $notes = '';
    public $branch_id = '';
    public $dentist_id = '';
    public $queue_number = null;

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
            'start_time' => 'nullable|date_format:H:i',
            'end_time' => 'nullable|date_format:H:i|after:start_time',
            'reason' => 'required|string|min:5|max:255',
            'notes' => 'nullable|string|max:500',
            'dentist_id' => 'nullable|exists:users,id',
        ];

        if ($user->isSuperadmin()) {
            $rules['branch_id'] = 'required|exists:branches,id';
            $rules['queue_number'] = 'nullable|integer|min:1';
        } else {
            $rules['branch_id'] = [
                'required',
                'exists:branches,id',
                Rule::in([$user->branch_id])
            ];
            
            if ($this->dentist_id) {
                $rules['dentist_id'] = [
                    'nullable',
                    'exists:users,id',
                    Rule::exists('users', 'id')->where(function ($query) use ($user) {
                        $query->where('role', UserRoles::DENTIST->value)
                              ->where('branch_id', $user->branch_id);
                    })
                ];
            }
        }

        return $rules;
    }

    public function updatedBranchId()
    {
        $this->dentist_id = '';
    }

    public function updatedAppointmentDate()
    {
        if (Auth::user()->isSuperadmin()) {
            $this->queue_number = null;
        }
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
            $query->where('first_name', 'ilike', '%' . $this->patientSearch . '%')
                ->orWhere('last_name', 'ilike', '%' . $this->patientSearch . '%')
                ->orWhere('id', 'ilike', '%' . $this->patientSearch . '%')
                ->orWhereRaw("CONCAT(first_name, ' ', last_name) LIKE ?", ['%' . $this->patientSearch . '%']);
        })
            ->orderBy('first_name')
            ->limit(15)
            ->get();
    }

    public function getMaxQueueNumberProperty()
    {
        if (!$this->appointment_date) {
            return 0;
        }

        return Appointment::where('appointment_date', $this->appointment_date)->max('queue_number') ?? 0;
    }

    public function save()
    {
        $this->authorize('create', Appointment::class);

        if (!Auth::user()->isSuperadmin()) {
            $this->branch_id = Auth::user()->branch_id;
        }

        $this->validate();

        try {
            Appointment::checkPatientAppointmentConflict(
                $this->patient_id,
                $this->appointment_date
            );

            // Check dentist conflict if dentist is assigned and time is specified
            if ($this->dentist_id && $this->start_time && $this->end_time) {
                Appointment::checkDentistConflict(
                    $this->dentist_id,
                    $this->appointment_date,
                    $this->start_time,
                    $this->end_time
                );
            }

            $queueNumber = $this->queue_number;

            if (Auth::user()->isSuperadmin() && $queueNumber) {
                $maxQueue = $this->maxQueueNumber;
                if ($queueNumber > $maxQueue + 1) {
                    $queueNumber = $maxQueue + 1;
                }
            }

            $appointment = Appointment::create([
                'patient_id' => $this->patient_id,
                'appointment_date' => $this->appointment_date,
                'start_time' => $this->start_time ?: null,
                'end_time' => $this->end_time ?: null,
                'reason' => $this->reason,
                'notes' => $this->notes,
                'branch_id' => $this->branch_id,
                'dentist_id' => $this->dentist_id ?: null,
                'status' => AppointmentStatuses::WAITING,
                'queue_number' => $queueNumber,
            ]);

            session()->flash('success', 'Appointment created successfully!');

            return $this->redirect(route('appointments.view', $appointment->id), navigate: true);
        } catch (\Exception $e) {
            $this->dispatchErrorMessage($e->getMessage());
        }
    }

    public function canUpdateBranch()
    {
        return Auth::user()->isSuperadmin();
    }

    public function canEditQueueNumber()
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

    private function getDentistsForUser()
    {
        $user = Auth::user();
        
        $branchId = $this->branch_id ?: $user->branch_id;
        
        if ($user->isSuperadmin() && $this->branch_id) {
            return User::where('role', UserRoles::DENTIST->value)
                ->where('branch_id', $this->branch_id)
                ->orderBy('first_name')
                ->orderBy('last_name')
                ->get();
        } elseif ($user->isSuperadmin()) {
            return User::where('role', UserRoles::DENTIST->value)
                ->orderBy('first_name')
                ->orderBy('last_name')
                ->get();
        } else {
            return User::where('role', UserRoles::DENTIST->value)
                ->where('branch_id', $user->branch_id)
                ->orderBy('first_name')
                ->orderBy('last_name')
                ->get();
        }
    }

    public function render()
    {
        $this->authorize('create', Appointment::class);

        return view('livewire.appointments.create-page', [
            'searchedPatients' => $this->searchedPatients,
            'branches' => $this->getBranchesForUser(),
            'dentists' => $this->getDentistsForUser(),
            'canUpdateBranch' => $this->canUpdateBranch(),
            'canEditQueueNumber' => $this->canEditQueueNumber(),
            'maxQueueNumber' => $this->maxQueueNumber
        ]);
    }
}