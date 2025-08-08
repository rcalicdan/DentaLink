{{-- resources/views/livewire/appointments/update-page.blade.php --}}
<div class="container mx-auto px-6 py-8">
    <div class="flex justify-between items-center mb-6">
        <div>
            <h1 class="text-2xl font-bold text-slate-800 dark:text-slate-200">Update Appointment</h1>
            <p class="text-slate-600 dark:text-slate-400 mt-1">
                Edit appointment details for {{ $appointment->patient->full_name }}
            </p>
        </div>
    </div>

   <x-flash-message/>

    <x-form.container title="Appointment Information" subtitle="Update the details below to modify the appointment"
        wire:submit="update">

        {{-- Current Status Display --}}
        <div class="mb-6 p-4 bg-slate-50 dark:bg-slate-700 rounded-lg">
            <div class="flex items-center justify-between">
                <div>
                    <span class="text-sm font-medium text-slate-700 dark:text-slate-300">Current Status:</span>
                    <span
                        class="ml-2 px-3 py-1 text-xs font-medium rounded-full {{ $appointment->status->getBadgeClass() }}">
                        {{ $appointment->status->getDisplayName() }}
                    </span>
                </div>
                <div class="text-sm text-slate-500 dark:text-slate-400">
                    Queue #{{ $appointment->queue_number }}
                </div>
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

        <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
            <x-form.field label="Appointment Date" name="appointment_date" type="date" wire:model="appointment_date"
                required icon="fas fa-calendar" :readonly="!$canUpdateDate"
                help="{{ !$canUpdateDate ? 'Date can only be changed for waiting appointments or by superadmin/admin' : '' }}" />

            {{--  --}}
            @if ($canUpdateBranch)
                <x-form.field label="Branch" name="branch_id" type="select" wire:model="branch_id" required
                    icon="fas fa-building">
                    <option value="">Select a branch</option>
                    @foreach ($branches as $branch)
                        <option value="{{ $branch->id }}">{{ $branch->name }}</option>
                    @endforeach
                </x-form.field>
            @else
                {{-- Hidden field for non-superadmin users --}}
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
        </div>

        <x-form.field label="Reason for Visit" name="reason" type="text" placeholder="Enter reason for appointment"
            wire:model="reason" required icon="fas fa-notes-medical" />

        <x-form.field label="Notes" name="notes" type="textarea" placeholder="Additional notes (optional)"
            wire:model="notes" icon="fas fa-sticky-note" rows="3" />

        @if ($canUpdateStatus && !empty($availableStatuses))
            <x-form.field label="Status" name="status" type="select" wire:model="status" required icon="fas fa-flag"
                help="Change appointment status">
                @foreach ($availableStatuses as $statusOption)
                    <option value="{{ $statusOption->value }}">{{ $statusOption->getDisplayName() }}</option>
                @endforeach
            </x-form.field>
        @endif

        <div class="flex justify-end space-x-3 pt-6">
            <x-utils.link-button href="{{ route('appointments.index') }}" buttonText="Cancel" />
            <x-utils.submit-button buttonText="Update Appointment" wireTarget="update" />
        </div>
    </x-form.container>
</div>
