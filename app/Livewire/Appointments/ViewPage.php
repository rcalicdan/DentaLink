<?php

namespace App\Livewire\Appointments;

use App\Models\Appointment;
use App\Enums\AppointmentStatuses;
use Livewire\Component;
use Illuminate\Support\Facades\Auth;

class ViewPage extends Component
{
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
            session()->flash('error', 'Invalid status transition.');
            return;
        }

        $this->appointment->refresh();
        session()->flash('success', 'Appointment status updated successfully.');
    }

    public function getAvailableTransitions()
    {
        return $this->appointment->status->getAllowedTransitions(Auth::user());
    }

    public function render()
    {
        return view('livewire.appointments.view-page', [
            'availableTransitions' => $this->getAvailableTransitions()
        ]);
    }
}