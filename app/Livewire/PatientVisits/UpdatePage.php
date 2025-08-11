<?php

namespace App\Livewire\PatientVisits;

use App\Models\PatientVisit;
use App\Models\Patient;
use App\Models\Branch;
use App\Models\Appointment;
use App\Models\DentalService;
use App\Models\PatientVisitService;
use App\Traits\DispatchFlashMessage;
use Livewire\Component;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Carbon\Carbon;

class UpdatePage extends Component
{
    use DispatchFlashMessage;

    public PatientVisit $patientVisit;
    public $patient_id;
    public $appointment_id;
    public $notes;
    public $branch_id;
    public $visit_type;

    public $patientSearch = '';
    public $showPatientDropdown = false;
    public $selectedPatient = null;

    public $appointmentSearch = '';
    public $showAppointmentDropdown = false;
    public $selectedAppointment = null;

    public $services = [];
    public $serviceSearches = [];
    public $showServiceDropdowns = [];

    public function mount(PatientVisit $patientVisit)
    {
        $this->authorize('update', $patientVisit);

        $this->patientVisit = $patientVisit;
        $this->patient_id = $patientVisit->patient_id;
        $this->appointment_id = $patientVisit->appointment_id;
        $this->notes = $patientVisit->notes;
        $this->branch_id = $patientVisit->branch_id;
        $this->visit_type = $patientVisit->appointment_id ? 'appointment' : 'walk-in';
        $this->selectedPatient = $patientVisit->patient;
        $this->patientSearch = $patientVisit->patient->full_name . ' (ID: ' . $patientVisit->patient->id . ')';
        if ($patientVisit->appointment) {
            $this->selectedAppointment = $patientVisit->appointment;
            $this->appointmentSearch = "Queue #{$patientVisit->appointment->queue_number} - {$patientVisit->appointment->appointment_date->format('M d, Y')} - {$patientVisit->appointment->reason}";
        }

        $this->services = [];
        foreach ($patientVisit->patientVisitServices as $visitService) {
            $this->services[] = [
                'dental_service_id' => $visitService->dental_service_id,
                'quantity' => $visitService->quantity,
                'service_notes' => $visitService->service_notes,
                'service_price' => $visitService->service_price,
            ];
        }

        if (empty($this->services)) {
            $this->services = [
                ['dental_service_id' => '', 'quantity' => 1, 'service_notes' => '', 'service_price' => 0]
            ];
        }

        $this->initializeServiceSearches();
    }

    public function rules()
    {
        $user = Auth::user();

        $rules = [
            'patient_id' => 'required|exists:patients,id',
            'notes' => 'nullable|string|max:1000',
            'visit_type' => 'required|in:walk-in,appointment',
            'services' => 'required|array|min:1',
            'services.*.dental_service_id' => 'required|exists:dental_services,id',
            'services.*.quantity' => 'required|integer|min:1',
            'services.*.service_notes' => 'nullable|string|max:500',
        ];

        if ($this->visit_type === 'appointment') {
            $rules['appointment_id'] = 'required|exists:appointments,id';
        }

        if ($user->isSuperadmin()) {
            $rules['branch_id'] = 'required|exists:branches,id';
        }

        return $rules;
    }

    private function initializeServiceSearches()
    {
        $this->serviceSearches = [];
        $this->showServiceDropdowns = [];

        foreach ($this->services as $index => $service) {
            if (!empty($service['dental_service_id'])) {
                $dentalService = DentalService::find($service['dental_service_id']);
                $this->serviceSearches[$index] = $dentalService ? $dentalService->name : '';
            } else {
                $this->serviceSearches[$index] = '';
            }
            $this->showServiceDropdowns[$index] = false;
        }
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

    public function updatedServiceSearches()
    {
        foreach ($this->serviceSearches as $index => $search) {
            $this->showServiceDropdowns[$index] = !empty($search);
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

    public function addService()
    {
        $newIndex = count($this->services);
        $this->services[] = ['dental_service_id' => '', 'quantity' => 1, 'service_notes' => '', 'service_price' => 0];

        $this->serviceSearches[$newIndex] = '';
        $this->showServiceDropdowns[$newIndex] = false;
    }

    public function removeService($index)
    {
        if (count($this->services) > 1) {
            unset($this->services[$index]);
            unset($this->serviceSearches[$index]);
            unset($this->showServiceDropdowns[$index]);

            $this->services = array_values($this->services);
            $this->serviceSearches = array_values($this->serviceSearches);
            $this->showServiceDropdowns = array_values($this->showServiceDropdowns);
        }
    }

    public function selectService($serviceId, $index)
    {
        $service = DentalService::find($serviceId);
        if ($service) {
            $this->services[$index]['dental_service_id'] = $service->id;
            $this->services[$index]['service_price'] = $service->price;

            if (!$service->is_quantifiable) {
                $this->services[$index]['quantity'] = 1;
            }

            $this->showServiceDropdowns[$index] = false;
            $this->serviceSearches[$index] = $service->name;
        }
    }

    public function updatedServices()
    {
        foreach ($this->services as $index => $service) {
            if (!empty($service['dental_service_id'])) {
                $dentalService = DentalService::find($service['dental_service_id']);
                if ($dentalService) {
                    $this->services[$index]['service_price'] = $dentalService->price;
                }
            }
        }
    }

    public function getTotalAmountProperty()
    {
        $total = 0;
        foreach ($this->services as $service) {
            if (!empty($service['dental_service_id']) && !empty($service['service_price'])) {
                $total += (float)$service['service_price'] * (int)$service['quantity'];
            }
        }
        return number_format($total, 2);
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

    public function getSearchedServicesByIndex($index)
    {
        $searchTerm = $this->serviceSearches[$index] ?? '';

        if (empty($searchTerm)) {
            return DentalService::with('dentalServiceType')->orderBy('name')->limit(10)->get();
        }

        return DentalService::with('dentalServiceType')
            ->where(function ($query) use ($searchTerm) {
                $query->where('name', 'like', '%' . $searchTerm . '%');
            })
            ->orderBy('name')
            ->limit(10)
            ->get();
    }

    public function getSearchedServicesProperty()
    {
        return collect();
    }

    public function update()
    {
        $this->authorize('update', $this->patientVisit);

        if (!Auth::user()->isSuperadmin()) {
            $this->branch_id = Auth::user()->branch_id;
        }

        $validatedData = $this->validate();

        try {
            DB::transaction(function () {
                $totalAmount = 0;
                foreach ($this->services as $service) {
                    if (!empty($service['dental_service_id'])) {
                        $totalAmount += $service['service_price'] * $service['quantity'];
                    }
                }

                $updateData = [
                    'patient_id' => $this->patient_id,
                    'branch_id' => $this->branch_id,
                    'notes' => $this->notes,
                    'total_amount_paid' => $totalAmount,
                ];

                if ($this->visit_type === 'appointment' && $this->appointment_id) {
                    $updateData['appointment_id'] = $this->appointment_id;
                } else {
                    $updateData['appointment_id'] = null;
                }

                $this->patientVisit->update($updateData);

                $this->patientVisit->patientVisitServices()->delete();

                foreach ($this->services as $service) {
                    if (!empty($service['dental_service_id'])) {
                        PatientVisitService::create([
                            'patient_visit_id' => $this->patientVisit->id,
                            'dental_service_id' => $service['dental_service_id'],
                            'service_price' => $service['service_price'],
                            'quantity' => $service['quantity'],
                            'service_notes' => $service['service_notes'],
                        ]);
                    }
                }
            });

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
            'searchedServices' => $this->searchedServices,
            'canUpdateBranch' => $this->canUpdateBranch(),
            'branches' => $this->getBranchesForUser()
        ]);
    }
}
