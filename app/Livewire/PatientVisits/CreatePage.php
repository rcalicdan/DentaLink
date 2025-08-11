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
use Carbon\Carbon;
use Illuminate\Support\Facades\DB;

class CreatePage extends Component
{
    use DispatchFlashMessage;

    public $patient_id = '';
    public $appointment_id = '';
    public $notes = '';
    public $branch_id = '';
    public $visit_type = 'walk-in';

    public $patientSearch = '';
    public $showPatientDropdown = false;
    public $selectedPatient = null;

    public $appointmentSearch = '';
    public $showAppointmentDropdown = false;
    public $selectedAppointment = null;

    public $services = [];
    public $serviceSearches = [];
    public $showServiceDropdowns = [];

    public function mount()
    {
        $this->branch_id = Auth::user()->branch_id;

        $this->services = [
            ['dental_service_id' => '', 'quantity' => 1, 'service_notes' => '', 'service_price' => 0]
        ];

        $this->initializeServiceSearches();

        if (request()->has('appointment_id') && request()->has('patient_id')) {
            $appointmentId = request()->get('appointment_id');
            $patientId = request()->get('patient_id');

            $appointment = Appointment::with('patient')
                ->where('id', $appointmentId)
                ->where('patient_id', $patientId)
                ->first();

            if ($appointment) {
                $this->visit_type = 'appointment';
                $this->appointment_id = $appointment->id;
                $this->patient_id = $appointment->patient_id;

                // Set selected appointment and patient
                $this->selectedAppointment = $appointment;
                $this->selectedPatient = $appointment->patient;

                // Pre-fill the search fields
                $this->patientSearch = $appointment->patient->full_name . ' (ID: ' . $appointment->patient->id . ')';
                $this->appointmentSearch = "Queue #{$appointment->queue_number} - {$appointment->appointment_date->format('M d, Y')} - {$appointment->reason}";
            }
        }
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

    public function getSearchedServicesProperty()
    {
        return collect();
    }

    public function save()
    {
        $this->authorize('create', PatientVisit::class);

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

                $visitData = [
                    'patient_id' => $this->patient_id,
                    'branch_id' => $this->branch_id,
                    'visit_date' => Carbon::now(),
                    'notes' => $this->notes,
                    'total_amount_paid' => $totalAmount,
                    'created_by' => Auth::id(),
                ];

                if ($this->visit_type === 'appointment' && $this->appointment_id) {
                    $visitData['appointment_id'] = $this->appointment_id;
                }

                $patientVisit = PatientVisit::create($visitData);

                foreach ($this->services as $service) {
                    if (!empty($service['dental_service_id'])) {
                        PatientVisitService::create([
                            'patient_visit_id' => $patientVisit->id,
                            'dental_service_id' => $service['dental_service_id'],
                            'service_price' => $service['service_price'],
                            'quantity' => $service['quantity'],
                            'service_notes' => $service['service_notes'],
                        ]);
                    }
                }
            });

            if ($this->appointment_id) {
                session()->flash('success', 'Patient visit created successfully! You can view it from the patient visits list.');
                return $this->redirect(route('appointments.view', $this->appointment_id), navigate: true);
            } else {
                session()->flash('success', 'Patient visit created successfully!');
                return $this->redirect(route('patient-visits.index'), navigate: true);
            }
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
            'searchedServices' => $this->searchedServices,
            'branches' => $this->getBranchesForUser(),
            'canUpdateBranch' => $this->canUpdateBranch()
        ]);
    }
}
