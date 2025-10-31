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
use Illuminate\Validation\UnauthorizedException;

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

    public $isReadonly = false;

    public function mount(PatientVisit $patientVisit)
    {
        $this->authorize('update', $patientVisit);

        $this->isReadonly = Auth::user()->isEmployee();

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
            $dentalService = DentalService::find($visitService->dental_service_id);

            // Determine if this was saved with manual total
            // If quantity is 1 and service is quantifiable, it might be a manual total
            $useManualTotal = false;
            $manualTotal = 0;

            if ($dentalService && $dentalService->is_quantifiable && $visitService->quantity == 1) {
                // Check if the stored price differs from the service's base price
                if ($visitService->service_price != $dentalService->price) {
                    $useManualTotal = true;
                    $manualTotal = $visitService->service_price;
                }
            } elseif ($dentalService && !$dentalService->is_quantifiable && $visitService->service_price != $dentalService->price) {
                $useManualTotal = true;
                $manualTotal = $visitService->service_price;
            }

            $this->services[] = [
                'dental_service_id' => $visitService->dental_service_id,
                'quantity' => $visitService->quantity,
                'service_notes' => $visitService->service_notes,
                'service_price' => $dentalService ? $dentalService->price : $visitService->service_price,
                'use_manual_total' => $useManualTotal,
                'manual_total' => $useManualTotal ? $manualTotal : 0,
            ];
        }

        if (empty($this->services)) {
            $this->services = [
                [
                    'dental_service_id' => '',
                    'quantity' => 1,
                    'service_notes' => '',
                    'service_price' => 0,
                    'use_manual_total' => false,
                    'manual_total' => 0
                ]
            ];
        }

        $this->initializeServiceSearches();
    }

    private function checkWritePermission()
    {
        if (Auth::user()->isEmployee()) {
            throw new UnauthorizedException('Employees do not have permission to modify patient visits.');
        }
    }

    public function rules()
    {
        $this->checkWritePermission();

        $user = Auth::user();

        $rules = [
            'patient_id' => 'required|exists:patients,id',
            'notes' => 'nullable|string|max:1000',
            'visit_type' => 'required|in:walk-in,appointment',
            'services' => 'required|array|min:1',
            'services.*.dental_service_id' => 'required|exists:dental_services,id',
            'services.*.quantity' => 'required|integer|min:1',
            'services.*.service_notes' => 'nullable|string|max:500',
            'services.*.use_manual_total' => 'boolean',
            'services.*.manual_total' => 'nullable|numeric|min:0',
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
        $this->checkWritePermission();

        if ($this->visit_type === 'walk-in') {
            $this->appointment_id = '';
            $this->selectedAppointment = null;
            $this->appointmentSearch = '';
            $this->showAppointmentDropdown = false;
        }
    }

    public function updatedPatientSearch()
    {
        $this->checkWritePermission();

        $this->showPatientDropdown = !empty($this->patientSearch);
        if (empty($this->patientSearch)) {
            $this->selectedPatient = null;
            $this->patient_id = '';
        }
        $this->clearAppointmentSelection();
    }

    public function updatedAppointmentSearch()
    {
        $this->checkWritePermission();

        $this->showAppointmentDropdown = !empty($this->appointmentSearch) && $this->visit_type === 'appointment';
        if (empty($this->appointmentSearch)) {
            $this->selectedAppointment = null;
            $this->appointment_id = '';
        }
    }

    public function updatedServiceSearches()
    {
        $this->checkWritePermission();

        foreach ($this->serviceSearches as $index => $search) {
            $this->showServiceDropdowns[$index] = !empty($search);
        }
    }

    public function selectPatient($patientId)
    {
        $this->checkWritePermission();

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
        $this->checkWritePermission();

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
        $this->checkWritePermission();

        $this->patient_id = '';
        $this->selectedPatient = null;
        $this->patientSearch = '';
        $this->showPatientDropdown = false;
        $this->clearAppointmentSelection();
    }

    public function clearAppointmentSelection()
    {
        $this->checkWritePermission();

        $this->appointment_id = '';
        $this->selectedAppointment = null;
        $this->appointmentSearch = '';
        $this->showAppointmentDropdown = false;
    }

    public function addService()
    {
        $this->checkWritePermission();

        $newIndex = count($this->services);
        $this->services[] = [
            'dental_service_id' => '',
            'quantity' => 1,
            'service_notes' => '',
            'service_price' => 0,
            'use_manual_total' => false,
            'manual_total' => 0
        ];

        $this->serviceSearches[$newIndex] = '';
        $this->showServiceDropdowns[$newIndex] = false;
    }

    public function removeService($index)
    {
        $this->checkWritePermission();

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
        $this->checkWritePermission();

        $service = DentalService::find($serviceId);
        if ($service) {
            $this->services[$index]['dental_service_id'] = $service->id;
            $this->services[$index]['service_price'] = $service->price ?? 0;

            if (!$service->is_quantifiable) {
                $this->services[$index]['quantity'] = 1;
            }

            $this->showServiceDropdowns[$index] = false;
            $this->serviceSearches[$index] = $service->name;
        }
    }

    public function toggleManualTotal($index)
    {
        $this->checkWritePermission();

        $this->services[$index]['use_manual_total'] = !$this->services[$index]['use_manual_total'];

        if ($this->services[$index]['use_manual_total']) {
            // Initialize manual total with current calculated total
            $currentTotal = (float)$this->services[$index]['service_price'] * (int)$this->services[$index]['quantity'];
            $this->services[$index]['manual_total'] = $currentTotal;
        } else {
            $this->services[$index]['manual_total'] = 0;
        }
    }

    public function updatedServices($value, $key)
    {
        $this->checkWritePermission();

        // Parse the key to get index and field
        $parts = explode('.', $key);
        if (count($parts) >= 2) {
            $index = $parts[0];
            $field = $parts[1];

            // If quantity or service changes and not using manual total, recalculate
            if (($field === 'quantity' || $field === 'dental_service_id') &&
                !$this->services[$index]['use_manual_total']
            ) {

                if (!empty($this->services[$index]['dental_service_id'])) {
                    $dentalService = DentalService::find($this->services[$index]['dental_service_id']);
                    if ($dentalService) {
                        $this->services[$index]['service_price'] = $dentalService->price;
                    }
                }
            }
        }
    }

    public function getServiceTotal($index)
    {
        $service = $this->services[$index];

        if ($service['use_manual_total']) {
            return (float)$service['manual_total'];
        }

        if (!empty($service['dental_service_id']) && !empty($service['service_price'])) {
            return (float)$service['service_price'] * (int)$service['quantity'];
        }

        return 0;
    }

    public function getTotalAmountProperty()
    {
        $total = 0;
        foreach ($this->services as $index => $service) {
            $total += $this->getServiceTotal($index);
        }
        return number_format($total, 2);
    }

    public function getSearchedPatientsProperty()
    {
        if (empty($this->patientSearch)) {
            return Patient::orderBy('first_name')->limit(15)->get();
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

    public function getSearchedAppointmentsProperty()
    {
        if ($this->visit_type !== 'appointment' || empty($this->appointmentSearch) || !$this->selectedPatient) {
            return collect();
        }

        return Appointment::with('patient')
            ->where('patient_id', $this->selectedPatient->id)
            ->where(function ($query) {
                $query->where('reason', 'ilike', '%' . $this->appointmentSearch . '%')
                    ->orWhere('queue_number', 'ilike', '%' . $this->appointmentSearch . '%');
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
                $query->where('name', 'ilike', '%' . $searchTerm . '%');
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
        $this->checkWritePermission();

        if (!Auth::user()->isSuperadmin()) {
            $this->branch_id = Auth::user()->branch_id;
        }

        $validatedData = $this->validate();

        try {
            DB::transaction(function () {
                $totalAmount = 0;
                foreach ($this->services as $index => $service) {
                    if (!empty($service['dental_service_id'])) {
                        $totalAmount += $this->getServiceTotal($index);
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

                foreach ($this->services as $index => $service) {
                    if (!empty($service['dental_service_id'])) {
                        $serviceTotal = $this->getServiceTotal($index);

                        PatientVisitService::create([
                            'patient_visit_id' => $this->patientVisit->id,
                            'dental_service_id' => $service['dental_service_id'],
                            'service_price' => $service['use_manual_total']
                                ? $service['manual_total']
                                : $service['service_price'],
                            'quantity' => $service['use_manual_total'] ? 1 : $service['quantity'],
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

    public function isReadonlyMode()
    {
        return $this->isReadonly;
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
            'branches' => $this->getBranchesForUser(),
            'isReadonly' => $this->isReadonlyMode()
        ]);
    }
}
