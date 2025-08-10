<?php

namespace App\Livewire\Appointments;

use App\Models\Appointment;
use App\Models\PatientVisit;
use App\Enums\AppointmentStatuses;
use App\Traits\DispatchFlashMessage;
use Livewire\Component;
use Illuminate\Support\Facades\Auth;

class ViewPage extends Component
{
    use DispatchFlashMessage;
    public Appointment $appointment;

    public function mount(Appointment $appointment)
    {
        $this->authorize('view', $appointment);
        $this->appointment = $appointment->load(['patient', 'branch', 'creator']);
    }

    public function updateStatus($newStatus)
    {
        $this->authorize('update', $this->appointment);

        $status = AppointmentStatuses::from($newStatus);

        if (!$this->appointment->updateStatus($status, Auth::user())) {
            $this->dispatchErrorMessage('Invalid status transition.');
            return;
        }

        $this->appointment->refresh();
        $this->dispatchSuccessMessage('Appointment status updated successfully.');
    }

    public function getAvailableTransitions()
    {
        if (Auth::user()->isSuperadmin()) {
            return AppointmentStatuses::cases();
        }

        return $this->appointment->status->getAllowedTransitions(Auth::user());
    }

    public function createPatientVisit()
    {
        $this->authorize('create', PatientVisit::class);

        if ($this->appointment->has_visit) {
            $this->dispatchErrorMessage('Patient visit already created.');
            return;
        }
        
        return $this->redirect(
            route('patient-visits.create', [
                'appointment_id' => $this->appointment->id,
                'patient_id' => $this->appointment->patient_id
            ]), 
            navigate: true
        );
    }

    public function canCreatePatientVisit()
    {
        return Auth::user()->can('create', PatientVisit::class) && 
               !$this->appointment->has_visit &&
               in_array($this->appointment->status->value, ['waiting', 'in_progress', 'completed']);
    }

    public function render()
    {
        return view('livewire.appointments.view-page', [
            'availableTransitions' => $this->getAvailableTransitions(),
            'canCreatePatientVisit' => $this->canCreatePatientVisit()
        ]);
    }
}