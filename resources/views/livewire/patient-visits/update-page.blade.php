<div class="container mx-auto px-6 py-8">
    <div class="flex justify-between items-center mb-6">
        <div>
            <h1 class="text-2xl font-bold text-slate-800 dark:text-slate-200">Update Patient Visit</h1>
            <p class="text-slate-600 dark:text-slate-400 mt-1">
                Edit visit details for {{ $patientVisit->patient->full_name }}
            </p>
        </div>
    </div>

    <x-flash-message />

    <x-form.container title="Visit Information" subtitle="Update the details below to modify the patient visit"
        wire:submit="update">

        {{-- Current Visit Info Display --}}
        <div class="mb-6 p-4 bg-slate-50 dark:bg-slate-700 rounded-lg">
            <div class="flex items-center justify-between">
                <div>
                    <span class="text-sm font-medium text-slate-700 dark:text-slate-300">Visit Type:</span>
                    @if ($patientVisit->appointment_id)
                        <span
                            class="ml-2 inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-blue-100 text-blue-800 dark:bg-blue-900 dark:text-blue-200">
                            <i class="fas fa-calendar-check mr-1"></i>
                            Appointment Visit
                        </span>
                    @else
                        <span
                            class="ml-2 inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-green-100 text-green-800 dark:bg-green-900 dark:text-green-200">
                            <i class="fas fa-walking mr-1"></i>
                            Walk-in Visit
                        </span>
                    @endif
                </div>
                <div class="text-sm text-slate-500 dark:text-slate-400">
                    Visit ID: {{ $patientVisit->id }}
                </div>
            </div>
        </div>

        {{-- Visit Type Selection --}}
        <div class="mb-6">
            <label class="block text-sm font-medium text-slate-700 dark:text-slate-300 mb-3">
                Visit Type <span class="text-red-500">*</span>
            </label>
            <div class="flex space-x-4">
                <label class="flex items-center cursor-pointer">
                    <input type="radio" wire:model.live="visit_type" value="walk-in"
                        class="w-4 h-4 text-green-600 bg-white border-slate-300 focus:ring-green-500 dark:focus:ring-green-600 dark:ring-offset-slate-800 focus:ring-2 dark:bg-slate-700 dark:border-slate-600">
                    <span class="ml-2 text-sm font-medium text-slate-900 dark:text-slate-300 flex items-center">
                        <i class="fas fa-walking text-green-600 mr-2"></i>
                        Walk-in Visit
                    </span>
                </label>
                <label class="flex items-center cursor-pointer">
                    <input type="radio" wire:model.live="visit_type" value="appointment"
                        class="w-4 h-4 text-blue-600 bg-white border-slate-300 focus:ring-blue-500 dark:focus:ring-blue-600 dark:ring-offset-slate-800 focus:ring-2 dark:bg-slate-700 dark:border-slate-600">
                    <span class="ml-2 text-sm font-medium text-slate-900 dark:text-slate-300 flex items-center">
                        <i class="fas fa-calendar-check text-blue-600 mr-2"></i>
                        Appointment Visit
                    </span>
                </label>
            </div>
        </div>

        {{-- Patient Search Field --}}
        <div class="relative">
            <x-form.field label="Patient" name="patient_id" type="text" placeholder="Search for a patient..."
                wire:model.live="patientSearch" required icon="fas fa-user" />

            @if ($showPatientDropdown)
                <div
                    class="absolute z-50 mt-1 w-full bg-white dark:bg-slate-800 border border-slate-300 dark:border-slate-600 rounded-md shadow-lg max-h-60 overflow-auto">
                    @forelse($searchedPatients as $patient)
                        <button type="button" wire:click="selectPatient({{ $patient->id }})"
                            class="w-full px-4 py-2 text-left hover:bg-slate-50 dark:hover:bg-slate-700 border-b border-slate-200 dark:border-slate-600 last:border-b-0">
                            <div class="font-medium text-slate-900 dark:text-slate-100">
                                {{ $patient->full_name }}
                            </div>
                            <div class="text-sm text-slate-500 dark:text-slate-400">
                                ID: {{ $patient->id }} | {{ $patient->email }}
                            </div>
                        </button>
                    @empty
                        <div class="px-4 py-2 text-slate-500 dark:text-slate-400">
                            No patients found
                        </div>
                    @endforelse
                </div>
            @endif

            @if ($selectedPatient)
                <div
                    class="mt-2 p-3 bg-green-50 dark:bg-green-900/20 border border-green-200 dark:border-green-800 rounded-md">
                    <div class="flex justify-between items-start">
                        <div>
                            <p class="font-medium text-green-800 dark:text-green-200">
                                Selected: {{ $selectedPatient->full_name }}
                            </p>
                            <p class="text-sm text-green-600 dark:text-green-300">
                                ID: {{ $selectedPatient->id }} | {{ $selectedPatient->email }}
                            </p>
                        </div>
                        <button type="button" wire:click="clearPatientSelection"
                            class="text-green-600 hover:text-green-800 dark:text-green-400 dark:hover:text-green-200">
                            <i class="fas fa-times"></i>
                        </button>
                    </div>
                </div>
            @endif
        </div>

        {{-- Appointment Search Field (only for appointment visits) --}}
        @if ($visit_type === 'appointment')
            <div class="relative">
                <x-form.field label="Appointment" name="appointment_id" type="text"
                    placeholder="Search for an appointment..." wire:model.live="appointmentSearch" required
                    icon="fas fa-calendar-check" />

                @if ($showAppointmentDropdown && $selectedPatient)
                    <div
                        class="absolute z-50 mt-1 w-full bg-white dark:bg-slate-800 border border-slate-300 dark:border-slate-600 rounded-md shadow-lg max-h-60 overflow-auto">
                        @forelse($searchedAppointments as $appointment)
                            <button type="button" wire:click="selectAppointment({{ $appointment->id }})"
                                class="w-full px-4 py-2 text-left hover:bg-slate-50 dark:hover:bg-slate-700 border-b border-slate-200 dark:border-slate-600 last:border-b-0">
                                <div class="font-medium text-slate-900 dark:text-slate-100">
                                    Queue #{{ $appointment->queue_number }} -
                                    {{ $appointment->appointment_date->format('M d, Y') }}
                                </div>
                                <div class="text-sm text-slate-500 dark:text-slate-400">
                                    {{ $appointment->reason }}
                                </div>
                                <div class="text-xs text-slate-400 dark:text-slate-500">
                                    Status: {{ $appointment->status->getDisplayName() }}
                                </div>
                            </button>
                        @empty
                            <div class="px-4 py-2 text-slate-500 dark:text-slate-400">
                                No appointments found for this patient
                            </div>
                        @endforelse
                    </div>
                @endif

                @if ($selectedAppointment)
                    <div
                        class="mt-2 p-3 bg-blue-50 dark:bg-blue-900/20 border border-blue-200 dark:border-blue-800 rounded-md">
                        <div class="flex justify-between items-start">
                            <div>
                                <p class="font-medium text-blue-800 dark:text-blue-200">
                                    Queue #{{ $selectedAppointment->queue_number }}
                                </p>
                                <p class="text-sm text-blue-600 dark:text-blue-300">
                                    {{ $selectedAppointment->appointment_date->format('M d, Y') }} -
                                    {{ $selectedAppointment->reason }}
                                </p>
                            </div>
                            <button type="button" wire:click="clearAppointmentSelection"
                                class="text-blue-600 hover:text-blue-800 dark:text-blue-400 dark:hover:text-blue-200">
                                <i class="fas fa-times"></i>
                            </button>
                        </div>
                    </div>
                @endif
            </div>
        @endif

        {{-- Dental Services Section --}}
        <div class="border-t border-slate-200 dark:border-slate-600 pt-6 mt-6">
            <div class="flex justify-between items-center mb-4">
                <h3 class="text-lg font-medium text-slate-900 dark:text-slate-100">
                    <i class="fas fa-tooth text-blue-500 mr-2"></i>
                    Dental Services
                </h3>
                <button type="button" wire:click="addService"
                    class="inline-flex items-center px-3 py-2 border border-transparent text-sm leading-4 font-medium rounded-md text-white bg-blue-600 hover:bg-blue-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-blue-500">
                    <i class="fas fa-plus mr-1"></i>
                    Add Service
                </button>
            </div>

            @foreach ($services as $index => $service)
                <div
                    class="mb-4 p-4 border border-slate-200 dark:border-slate-600 rounded-lg bg-slate-50 dark:bg-slate-700/50">
                    <div class="flex justify-between items-start mb-3">
                        <h4 class="font-medium text-slate-900 dark:text-slate-100">Service #{{ $index + 1 }}</h4>
                        @if (count($services) > 1)
                            <button type="button" wire:click="removeService({{ $index }})"
                                class="text-red-600 hover:text-red-800 dark:text-red-400 dark:hover:text-red-200">
                                <i class="fas fa-times"></i>
                            </button>
                        @endif
                    </div>

                    <div class="grid grid-cols-1 md:grid-cols-3 gap-4">
                        {{-- Service Search --}}
                        <div class="relative">
                            <label class="block text-sm font-medium text-slate-700 dark:text-slate-300 mb-2">
                                Service <span class="text-red-500">*</span>
                            </label>
                            <div class="relative">
                                <input type="text" wire:model.live="serviceSearch"
                                    placeholder="Search for a service..."
                                    class="mt-1 block w-full rounded-md border-slate-300 shadow-sm focus:border-blue-500 focus:ring-blue-500 dark:bg-slate-800 dark:border-slate-600 dark:text-white sm:text-sm">

                                @if ($showServiceDropdown)
                                    <div
                                        class="absolute z-50 mt-1 w-full bg-white dark:bg-slate-800 border border-slate-300 dark:border-slate-600 rounded-md shadow-lg max-h-48 overflow-auto">
                                        @forelse($searchedServices as $searchService)
                                            <button type="button"
                                                wire:click="selectService({{ $searchService->id }}, {{ $index }})"
                                                class="w-full px-4 py-2 text-left hover:bg-slate-50 dark:hover:bg-slate-700 border-b border-slate-200 dark:border-slate-600 last:border-b-0">
                                                <div class="font-medium text-slate-900 dark:text-slate-100">
                                                    {{ $searchService->name }}
                                                </div>
                                                <div class="text-sm text-slate-500 dark:text-slate-400">
                                                    {{ $searchService->serviceTypeName }} -
                                                    ₱{{ number_format($searchService->price, 2) }}
                                                </div>
                                            </button>
                                        @empty
                                            <div class="px-4 py-2 text-slate-500 dark:text-slate-400">
                                                No services found
                                            </div>
                                        @endforelse
                                    </div>
                                @endif
                            </div>

                            {{-- Selected Service Display --}}
                            @if (!empty($service['dental_service_id']))
                                @php
                                    $selectedService = \App\Models\DentalService::with('dentalServiceType')->find(
                                        $service['dental_service_id'],
                                    );
                                @endphp
                                @if ($selectedService)
                                    <div
                                        class="mt-2 p-2 bg-green-50 dark:bg-green-900/20 border border-green-200 dark:border-green-800 rounded-md">
                                        <div class="text-sm">
                                            <div class="font-medium text-green-800 dark:text-green-200">
                                                {{ $selectedService->name }}
                                            </div>
                                            <div class="text-green-600 dark:text-green-300">
                                                {{ $selectedService->serviceTypeName }} -
                                                ₱{{ number_format($selectedService->price, 2) }}
                                            </div>
                                        </div>
                                    </div>
                                @endif
                            @endif

                            <input type="hidden" wire:model="services.{{ $index }}.dental_service_id">
                            <input type="hidden" wire:model="services.{{ $index }}.service_price">
                            @error("services.{$index}.dental_service_id")
                                <p class="mt-1 text-sm text-red-600 dark:text-red-400">{{ $message }}</p>
                            @enderror
                        </div>

                        {{-- Quantity --}}
                        <div>
                            <label class="block text-sm font-medium text-slate-700 dark:text-slate-300 mb-2">
                                Quantity <span class="text-red-500">*</span>
                            </label>
                            <input type="number" wire:model.live="services.{{ $index }}.quantity"
                                min="1"
                                class="mt-1 block w-full rounded-md border-slate-300 shadow-sm focus:border-blue-500 focus:ring-blue-500 dark:bg-slate-800 dark:border-slate-600 dark:text-white sm:text-sm">
                            @error("services.{$index}.quantity")
                                <p class="mt-1 text-sm text-red-600 dark:text-red-400">{{ $message }}</p>
                            @enderror
                        </div>

                        {{-- Subtotal Display --}}
                        <div>
                            <label class="block text-sm font-medium text-slate-700 dark:text-slate-300 mb-2">
                                Subtotal
                            </label>
                            <div
                                class="mt-1 px-3 py-2 bg-slate-100 dark:bg-slate-600 border border-slate-300 dark:border-slate-500 rounded-md text-slate-900 dark:text-slate-100">
                                ₱{{ number_format(($service['service_price'] ?? 0) * ($service['quantity'] ?? 1), 2) }}
                            </div>
                        </div>
                    </div>

                    {{-- Service Notes --}}
                    <div class="mt-4">
                        <label class="block text-sm font-medium text-slate-700 dark:text-slate-300 mb-2">
                            Service Notes
                        </label>
                        <textarea wire:model="services.{{ $index }}.service_notes" rows="2"
                            placeholder="Additional notes for this service (optional)"
                            class="mt-1 block w-full rounded-md border-slate-300 shadow-sm focus:border-blue-500 focus:ring-blue-500 dark:bg-slate-800 dark:border-slate-600 dark:text-white sm:text-sm"></textarea>
                        @error("services.{$index}.service_notes")
                            <p class="mt-1 text-sm text-red-600 dark:text-red-400">{{ $message }}</p>
                        @enderror
                    </div>
                </div>
            @endforeach

            {{-- Total Amount Display --}}
            <div
                class="mt-4 p-4 bg-blue-50 dark:bg-blue-900/20 border border-blue-200 dark:border-blue-800 rounded-lg">
                <div class="flex justify-between items-center">
                    <span class="text-lg font-medium text-blue-800 dark:text-blue-200">
                        Total Amount:
                    </span>
                    <span class="text-2xl font-bold text-blue-900 dark:text-blue-100">
                        ₱{{ $this->totalAmount }}
                    </span>
                </div>
            </div>
        </div>

        <div class="grid grid-cols-1 md:grid-cols-1 gap-6">
            <x-form.field label="Visit Date & Time" name="visit_date" type="datetime-local" wire:model="visit_date"
                required icon="fas fa-calendar-alt" />
        </div>

        {{-- Branch Selection (only for superadmin) --}}
        @if ($canUpdateBranch)
            <x-form.field label="Branch" name="branch_id" type="select" wire:model="branch_id" required
                icon="fas fa-building">
                <option value="">Select a branch</option>
                @foreach ($branches as $branch)
                    <option value="{{ $branch->id }}">{{ $branch->name }}</option>
                @endforeach
            </x-form.field>
        @else
            <input type="hidden" wire:model="branch_id" />
            <div class="p-4 bg-blue-50 dark:bg-blue-900/20 border border-blue-200 dark:border-blue-800 rounded-lg">
                <div class="flex items-center">
                    <i class="fas fa-building text-blue-500 mr-2"></i>
                    <span class="text-sm text-blue-700 dark:text-blue-300">
                        <strong>Branch:</strong> {{ auth()->user()->branch->name ?? 'Not Assigned' }}
                    </span>
                </div>
            </div>
        @endif

        <x-form.field label="Visit Notes" name="notes" type="textarea"
            placeholder="Additional notes about the visit (optional)" wire:model="notes" icon="fas fa-sticky-note"
            rows="4" />

        <div class="flex justify-end space-x-3 pt-6">
            <x-utils.link-button href="{{ route('patient-visits.index') }}" buttonText="Cancel" />
            <x-utils.submit-button buttonText="Update Visit" wireTarget="update" />
        </div>
    </x-form.container>
</div>
