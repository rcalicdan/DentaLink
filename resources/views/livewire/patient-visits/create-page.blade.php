{{-- resources/views/livewire/patient-visits/create-page.blade.php --}}
<div class="container mx-auto px-2 py-0">
    <div class="flex justify-between items-center mb-6">
        <div>
            <h1 class="text-2xl font-bold text-slate-800 dark:text-slate-200">Create New Patient Visit</h1>
            <p class="text-slate-600 dark:text-slate-400 mt-1">Record a new patient visit</p>
        </div>
    </div>

    <x-flash-message />

    <x-form.container title="Visit Information" subtitle="Fill in the details below to create a new patient visit"
        wire:submit="save">

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
        <div class="border-t border-slate-200 dark:border-slate-600 pt-8 mt-8">
            <div class="mb-6">
                <div class="flex items-center justify-between mb-4">
                    <div class="flex items-center">
                        <div class="flex items-center justify-center w-10 h-10 bg-blue-100 dark:bg-blue-900/30 rounded-lg mr-3">
                            <i class="fas fa-tooth text-blue-600 dark:text-blue-400"></i>
                        </div>
                        <div>
                            <h3 class="text-lg font-semibold text-slate-900 dark:text-slate-100">
                                Dental Services
                            </h3>
                            <p class="text-sm text-slate-500 dark:text-slate-400">
                                Add the services provided during this visit
                            </p>
                        </div>
                    </div>
                    <button type="button" wire:click="addService"
                        class="inline-flex items-center px-4 py-2 bg-gradient-to-r from-blue-600 to-blue-700 hover:from-blue-700 hover:to-blue-800 text-white font-medium rounded-lg shadow-sm transition-all duration-200 hover:shadow-md focus:outline-none focus:ring-2 focus:ring-blue-500 focus:ring-offset-2 dark:focus:ring-offset-slate-800">
                        <i class="fas fa-plus w-4 h-4 mr-2"></i>
                        Add Service
                    </button>
                </div>

                {{-- Services List --}}
                <div class="space-y-6">
                    @foreach ($services as $index => $service)
                        <div class="group relative">
                            {{-- Service Card --}}
                            <div class="bg-white dark:bg-slate-800 rounded-xl shadow-sm border border-slate-200 dark:border-slate-700 hover:shadow-md transition-all duration-200">
                                {{-- Service Header --}}
                                <div class="flex items-center justify-between px-6 py-4 border-b border-slate-100 dark:border-slate-700">
                                    <div class="flex items-center">
                                        <div class="flex items-center justify-center w-8 h-8 bg-slate-100 dark:bg-slate-700 rounded-full mr-3">
                                            <span class="text-sm font-medium text-slate-600 dark:text-slate-300">{{ $index + 1 }}</span>
                                        </div>
                                        <div>
                                            <h4 class="font-medium text-slate-900 dark:text-slate-100">Service #{{ $index + 1 }}</h4>
                                            <p class="text-xs text-slate-500 dark:text-slate-400">Configure service details</p>
                                        </div>
                                    </div>
                                    
                                    {{-- Remove Button --}}
                                    @if (count($services) > 1)
                                        <button type="button" wire:click="removeService({{ $index }})"
                                            class="inline-flex items-center justify-center w-8 h-8 text-slate-400 hover:text-red-500 hover:bg-red-50 dark:hover:bg-red-900/20 rounded-full transition-all duration-200">
                                            <i class="fas fa-trash-alt w-4 h-4"></i>
                                        </button>
                                    @endif
                                </div>

                                {{-- Service Content --}}
                                <div class="p-6">
                                    <div class="grid grid-cols-1 lg:grid-cols-12 gap-6">
                                        {{-- Service Selection (Left Column) --}}
                                        <div class="lg:col-span-7">
                                            <div class="relative">
                                                <label class="block text-sm font-medium text-slate-700 dark:text-slate-300 mb-2">
                                                    <i class="fas fa-search w-3 h-3 mr-1 text-slate-400"></i>
                                                    Service <span class="text-red-500">*</span>
                                                </label>
                                                
                                                <div class="relative">
                                                    <input type="text" wire:model.live="serviceSearch"
                                                        placeholder="Search dental services..."
                                                        class="w-full px-4 py-3 bg-slate-50 dark:bg-slate-700 border border-slate-300 dark:border-slate-600 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-blue-500 dark:focus:ring-blue-400 transition-colors duration-200 text-sm">
                                                    <div class="absolute inset-y-0 right-0 flex items-center pr-3">
                                                        <i class="fas fa-search text-slate-400 w-4 h-4"></i>
                                                    </div>
                                                </div>

                                                {{-- Service Dropdown --}}
                                                @if ($showServiceDropdown)
                                                    <div class="absolute z-50 mt-2 w-full bg-white dark:bg-slate-800 border border-slate-200 dark:border-slate-600 rounded-lg shadow-lg max-h-64 overflow-auto">
                                                        @forelse($searchedServices as $searchService)
                                                            <button type="button"
                                                                wire:click="selectService({{ $searchService->id }}, {{ $index }})"
                                                                class="w-full px-4 py-3 text-left hover:bg-blue-50 dark:hover:bg-blue-900/20 border-b border-slate-100 dark:border-slate-700 last:border-b-0 transition-colors duration-150">
                                                                <div class="flex items-center justify-between">
                                                                    <div class="flex-1 min-w-0">
                                                                        <div class="font-medium text-slate-900 dark:text-slate-100 truncate">
                                                                            {{ $searchService->name }}
                                                                        </div>
                                                                        <div class="text-sm text-slate-500 dark:text-slate-400 truncate">
                                                                            {{ $searchService->serviceTypeName }}
                                                                        </div>
                                                                    </div>
                                                                    <div class="ml-4 flex-shrink-0">
                                                                        <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-green-100 text-green-800 dark:bg-green-900 dark:text-green-200">
                                                                            ₱{{ number_format($searchService->price, 2) }}
                                                                        </span>
                                                                    </div>
                                                                </div>
                                                            </button>
                                                        @empty
                                                            <div class="px-4 py-8 text-center text-slate-500 dark:text-slate-400">
                                                                <i class="fas fa-search mb-2 text-slate-300 dark:text-slate-600"></i>
                                                                <p>No services found</p>
                                                            </div>
                                                        @endforelse
                                                    </div>
                                                @endif

                                                {{-- Selected Service Display --}}
                                                @if (!empty($service['dental_service_id']))
                                                    @php
                                                        $selectedService = \App\Models\DentalService::with('dentalServiceType')->find(
                                                            $service['dental_service_id'],
                                                        );
                                                    @endphp
                                                    @if ($selectedService)
                                                        <div class="mt-3 p-4 bg-gradient-to-r from-green-50 to-emerald-50 dark:from-green-900/20 dark:to-emerald-900/20 border border-green-200 dark:border-green-700 rounded-lg">
                                                            <div class="flex items-center justify-between">
                                                                <div class="flex items-center min-w-0 flex-1">
                                                                    <div class="flex items-center justify-center w-8 h-8 bg-green-100 dark:bg-green-800 rounded-full mr-3 flex-shrink-0">
                                                                        <i class="fas fa-check text-green-600 dark:text-green-300 w-3 h-3"></i>
                                                                    </div>
                                                                    <div class="min-w-0 flex-1">
                                                                        <div class="font-medium text-green-900 dark:text-green-100 truncate">
                                                                            {{ $selectedService->name }}
                                                                        </div>
                                                                        <div class="text-sm text-green-700 dark:text-green-300 truncate">
                                                                            {{ $selectedService->serviceTypeName }}
                                                                        </div>
                                                                    </div>
                                                                </div>
                                                                <div class="ml-4 flex-shrink-0">
                                                                    <span class="inline-flex items-center px-3 py-1 rounded-full text-sm font-medium bg-green-100 text-green-800 dark:bg-green-800 dark:text-green-200">
                                                                        ₱{{ number_format($selectedService->price, 2) }}
                                                                    </span>
                                                                </div>
                                                            </div>
                                                        </div>
                                                    @endif
                                                @endif

                                                <input type="hidden" wire:model="services.{{ $index }}.dental_service_id">
                                                <input type="hidden" wire:model="services.{{ $index }}.service_price">
                                                @error("services.{$index}.dental_service_id")
                                                    <p class="mt-2 text-sm text-red-600 dark:text-red-400 flex items-center">
                                                        <i class="fas fa-exclamation-circle w-3 h-3 mr-1"></i>
                                                        {{ $message }}
                                                    </p>
                                                @enderror
                                            </div>
                                        </div>

                                        {{-- Quantity and Subtotal (Right Column) --}}
                                        <div class="lg:col-span-5">
                                            <div class="grid grid-cols-2 gap-4">
                                                {{-- Quantity --}}
                                                <div>
                                                    <label class="block text-sm font-medium text-slate-700 dark:text-slate-300 mb-2">
                                                        <i class="fas fa-hashtag w-3 h-3 mr-1 text-slate-400"></i>
                                                        Quantity <span class="text-red-500">*</span>
                                                    </label>
                                                    <div class="relative">
                                                        <input type="number" wire:model.live="services.{{ $index }}.quantity"
                                                            min="1" 
                                                            class="w-full px-4 py-3 bg-slate-50 dark:bg-slate-700 border border-slate-300 dark:border-slate-600 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-blue-500 dark:focus:ring-blue-400 transition-colors duration-200 text-sm text-center font-medium">
                                                    </div>
                                                    @error("services.{$index}.quantity")
                                                        <p class="mt-1 text-xs text-red-600 dark:text-red-400">{{ $message }}</p>
                                                    @enderror
                                                </div>

                                                {{-- Subtotal --}}
                                                <div>
                                                    <label class="block text-sm font-medium text-slate-700 dark:text-slate-300 mb-2">
                                                        <i class="fas fa-calculator w-3 h-3 mr-1 text-slate-400"></i>
                                                        Subtotal
                                                    </label>
                                                    <div class="px-4 py-3 bg-gradient-to-r from-blue-50 to-indigo-50 dark:from-blue-900/20 dark:to-indigo-900/20 border border-blue-200 dark:border-blue-700 rounded-lg">
                                                        <div class="text-lg font-bold text-blue-900 dark:text-blue-100 text-center">
                                                            ₱{{ number_format(($service['service_price'] ?? 0) * ($service['quantity'] ?? 1), 2) }}
                                                        </div>
                                                    </div>
                                                </div>
                                            </div>
                                        </div>
                                    </div>

                                    {{-- Service Notes --}}
                                    <div class="mt-6">
                                        <label class="block text-sm font-medium text-slate-700 dark:text-slate-300 mb-2">
                                            <i class="fas fa-sticky-note w-3 h-3 mr-1 text-slate-400"></i>
                                            Service Notes
                                            <span class="text-xs text-slate-500 dark:text-slate-400 font-normal">(Optional)</span>
                                        </label>
                                        <textarea wire:model="services.{{ $index }}.service_notes" rows="3"
                                            placeholder="Add any specific notes or instructions for this service..."
                                            class="w-full px-4 py-3 bg-slate-50 dark:bg-slate-700 border border-slate-300 dark:border-slate-600 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-blue-500 dark:focus:ring-blue-400 transition-colors duration-200 text-sm resize-none"></textarea>
                                        @error("services.{$index}.service_notes")
                                            <p class="mt-2 text-sm text-red-600 dark:text-red-400 flex items-center">
                                                <i class="fas fa-exclamation-circle w-3 h-3 mr-1"></i>
                                                {{ $message }}
                                            </p>
                                        @enderror
                                    </div>
                                </div>
                            </div>
                        </div>
                    @endforeach
                </div>

                {{-- Total Amount Display --}}
                <div class="mt-8 p-6 bg-gradient-to-r from-emerald-50 via-teal-50 to-cyan-50 dark:from-emerald-900/20 dark:via-teal-900/20 dark:to-cyan-900/20 border border-emerald-200 dark:border-emerald-700 rounded-xl shadow-sm">
                    <div class="flex items-center justify-between">
                        <div class="flex items-center">
                            <div class="flex items-center justify-center w-12 h-12 bg-emerald-100 dark:bg-emerald-800 rounded-full mr-4">
                                <i class="fas fa-calculator text-emerald-600 dark:text-emerald-300"></i>
                            </div>
                            <div>
                                <h4 class="text-lg font-semibold text-emerald-900 dark:text-emerald-100">
                                    Total Amount
                                </h4>
                                <p class="text-sm text-emerald-700 dark:text-emerald-300">
                                    Sum of all selected services
                                </p>
                            </div>
                        </div>
                        <div class="text-right">
                            <div class="text-3xl font-bold text-emerald-900 dark:text-emerald-100">
                                ₱{{ $this->totalAmount }}
                            </div>
                            <div class="text-sm text-emerald-700 dark:text-emerald-300">
                                {{ count($services) }} {{ Str::plural('service', count($services)) }}
                            </div>
                        </div>
                    </div>
                </div>
            </div>
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
            <x-utils.submit-button buttonText="Create Visit" wireTarget="save" />
        </div>
    </x-form.container>
</div>