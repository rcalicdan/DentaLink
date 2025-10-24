<?php

namespace App\Livewire\Appointments;

use App\Models\Appointment;
use App\Models\Patient;
use App\Models\Branch;
use App\Enums\AppointmentStatuses;
use Illuminate\Validation\Rule;
use Livewire\Component;
use Illuminate\Support\Facades\Auth;
use App\Traits\DispatchFlashMessage;

class UpdatePage extends Component
{
    use DispatchFlashMessage;

    public Appointment $appointment;
    public $patient_id;
    public $appointment_date;
    public $reason;
    public $notes;
    public $status;
    public $branch_id;
    public $queue_number;
    public $patientSearch = '';
    public $showPatientDropdown = false;
    public $selectedPatient = null;

    public function mount(Appointment $appointment)
    {
        $this->authorize('update', $appointment);

        $this->appointment = $appointment;
        $this->patient_id = $appointment->patient_id;
        $this->appointment_date = $appointment->appointment_date->format('Y-m-d');
        $this->reason = $appointment->reason;
        $this->notes = $appointment->notes;
        $this->status = $appointment->status->value;
        $this->branch_id = $appointment->branch_id;
        $this->queue_number = $appointment->queue_number;

        // Set initial patient selection
        $this->selectedPatient = $appointment->patient;
        $this->patientSearch = $appointment->patient->full_name . ' (ID: ' . $appointment->patient->id . ')';
    }

    public function rules()
    {
        $user = Auth::user();

        $rules = [
            'patient_id' => 'required|exists:patients,id',
            'reason' => 'required|string|min:5|max:255',
            'notes' => 'nullable|string|max:500',
        ];

        if ($user->isSuperadmin()) {
            $rules['branch_id'] = 'required|exists:branches,id';
            $rules['queue_number'] = 'required|integer|min:1';
        } else {
            $rules['branch_id'] = [
                'required',
                'exists:branches,id',
                Rule::in([$user->branch_id])
            ];
        }

        if ($user->isSuperadmin() || $user->isAdmin() || $this->appointment->status === AppointmentStatuses::WAITING) {
            $rules['appointment_date'] = 'required|date|after_or_equal:today';
        }

        if ($user->isSuperadmin() || $user->isAdmin()) {
            $rules['status'] = ['required', Rule::in(array_map(fn($s) => $s->value, AppointmentStatuses::cases()))];
        } else {
            $allowedStatuses = $this->appointment->status->getAllowedTransitions($user);
            if (!empty($allowedStatuses)) {
                $rules['status'] = ['required', Rule::in(array_map(fn($s) => $s->value, $allowedStatuses))];
            }
        }

        return $rules;
    }

    public function updatedAppointmentDate()
    {
        // Reset queue number when date changes for superadmin
        if (Auth::user()->isSuperadmin()) {
            $this->queue_number = 1;
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
        $date = $this->appointment_date ?: $this->appointment->appointment_date->format('Y-m-d');

        return Appointment::where('appointment_date', $date)
            ->where('id', '!=', $this->appointment->id)
            ->max('queue_number') ?? 0;
    }

    public function update()
    {
        $this->authorize('update', $this->appointment);

        if (!Auth::user()->isSuperadmin()) {
            $this->branch_id = Auth::user()->branch_id;
        }

        $validatedData = $this->validate();

        try {
            $patientChanged = $validatedData['patient_id'] != $this->appointment->patient_id;
            $dateChanged = isset($validatedData['appointment_date']) &&
                $validatedData['appointment_date'] !== $this->appointment->appointment_date->format('Y-m-d');

            if ($patientChanged || $dateChanged) {
                $checkPatientId = $validatedData['patient_id'];
                $checkDate = $validatedData['appointment_date'] ?? $this->appointment->appointment_date->format('Y-m-d');

                Appointment::checkPatientAppointmentConflict(
                    $checkPatientId,
                    $checkDate,
                    $this->appointment->id
                );
            }

            // Handle queue number changes for superadmin
            if (Auth::user()->isSuperadmin() && isset($validatedData['queue_number'])) {
                $newQueueNumber = $validatedData['queue_number'];
                $maxQueue = $this->maxQueueNumber;

                // If new queue number is greater than max, set it to max + 1
                if ($newQueueNumber > $maxQueue + 1) {
                    $newQueueNumber = $maxQueue + 1;
                    $this->queue_number = $newQueueNumber;
                }

                // Handle queue number swapping
                if ($newQueueNumber != $this->appointment->queue_number) {
                    $this->appointment->updateQueueNumber($newQueueNumber);
                }

                unset($validatedData['queue_number']);
            }

            if (isset($validatedData['status'])) {
                $newStatus = AppointmentStatuses::from($validatedData['status']);
                if ($this->appointment->status !== $newStatus) {
                    if (!$this->appointment->updateStatus($newStatus, Auth::user())) {
                        session()->flash('error', 'Invalid status transition.');
                        return;
                    }
                }
                unset($validatedData['status']);
            }

            if (!empty($validatedData)) {
                $this->appointment->update($validatedData);
            }

            session()->flash('success', 'Appointment updated successfully!');
            return $this->redirect(route('appointments.view', $this->appointment->id), navigate: true);
        } catch (\Exception $e) {
            $this->dispatchErrorMessage($e->getMessage());
        }
    }

    public function canUpdateDate()
    {
        return Auth::user()->isSuperadmin() || Auth::user()->isAdmin() || $this->appointment->status === AppointmentStatuses::WAITING;
    }

    public function canUpdateStatus()
    {
        return Auth::user()->isSuperadmin() || Auth::user()->isAdmin() || !empty($this->appointment->status->getAllowedTransitions(Auth::user()));
    }

    public function canUpdateBranch()
    {
        return Auth::user()->isSuperadmin();
    }

    public function canEditQueueNumber()
    {
        return Auth::user()->isSuperadmin();
    }

    public function getAvailableStatuses()
    {
        if (Auth::user()->isSuperadmin() || Auth::user()->isAdmin()) {
            return AppointmentStatuses::cases();
        }

        return $this->appointment->status->getAllowedTransitions(Auth::user());
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
        $availableStatuses = $this->getAvailableStatuses();

        return view('livewire.appointments.update-page', [
            'searchedPatients' => $this->searchedPatients,
            'availableStatuses' => $availableStatuses,
            'canUpdateDate' => $this->canUpdateDate(),
            'canUpdateStatus' => $this->canUpdateStatus(),
            'canUpdateBranch' => $this->canUpdateBranch(),
            'canEditQueueNumber' => $this->canEditQueueNumber(),
            'branches' => $this->getBranchesForUser(),
            'maxQueueNumber' => $this->maxQueueNumber
        ]);
    }
}
