<div class="container mx-auto px-2 py-0">
    <div class="flex justify-between items-center mb-6">
        <div>
            <h1 class="text-2xl font-bold text-slate-800 dark:text-slate-200">Create New Appointment</h1>
            <p class="text-slate-600 dark:text-slate-400 mt-1">Schedule a new appointment for a patient</p>
        </div>
    </div>

    <x-flash-message />

    <x-form.container title="Appointment Information" subtitle="Fill in the details below to create a new appointment"
        wire:submit="save">

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
            <x-form.field label="Appointment Date" name="appointment_date" type="date"
                wire:model.live="appointment_date" required icon="fas fa-calendar" />

            @if ($canEditQueueNumber)
                <x-form.field label="Queue Number" name="queue_number" type="number" wire:model="queue_number"
                    placeholder="Leave blank for auto-assignment" icon="fas fa-hashtag"
                    help="Current max queue: {{ $maxQueueNumber }}" />
            @endif
        </div>

        {{-- Branch and Dentist Fields --}}
        <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
            @if ($canUpdateBranch)
                <x-form.field label="Branch" name="branch_id" type="select" wire:model.live="branch_id" required
                    icon="fas fa-building">
                    <option value="">Select a branch</option>
                    @foreach ($branches as $branch)
                        <option value="{{ $branch->id }}">{{ $branch->name }}</option>
                    @endforeach
                </x-form.field>
            @else
                {{-- Hidden field and display for non-superadmin users --}}
                <div>
                    <input type="hidden" wire:model="branch_id" />
                    <div
                        class="p-4 bg-blue-50 dark:bg-blue-900/20 border border-blue-200 dark:border-blue-800 rounded-lg">
                        <div class="flex items-center">
                            <i class="fas fa-building text-blue-500 mr-2"></i>
                            <span class="text-sm text-blue-700 dark:text-blue-300">
                                <strong>Branch:</strong> {{ auth()->user()->branch->name ?? 'Not Assigned' }}
                            </span>
                        </div>
                    </div>
                </div>
            @endif

            {{-- Dentist Dropdown --}}
            <x-form.field label="Assign Dentist (Optional)" name="dentist_id" type="select" wire:model="dentist_id"
                icon="fas fa-user-md" help="Select a dentist for this appointment">
                <option value="">No Dentist Assigned</option>
                @forelse($dentists as $dentist)
                    <option value="{{ $dentist->id }}">
                        Dr. {{ $dentist->full_name }}
                        @if (auth()->user()->isSuperadmin())
                            ({{ $dentist->branch_name }})
                        @endif
                    </option>
                @empty
                    <option value="" disabled>No dentists available</option>
                @endforelse
            </x-form.field>
        </div>

        <!-- Time Fields (Optional) -->
        <div class="bg-blue-50 dark:bg-blue-900/20 border border-blue-200 dark:border-blue-800 rounded-lg p-4 mb-6">
            <div class="flex items-center mb-3">
                <i class="fas fa-clock text-blue-600 dark:text-blue-400 mr-2"></i>
                <h3 class="text-sm font-semibold text-blue-800 dark:text-blue-200">Appointment Time (Optional)</h3>
            </div>
            <p class="text-xs text-blue-600 dark:text-blue-300 mb-4">
                Specify a time range if you want to schedule the appointment for a specific time slot.
                @if ($dentist_id)
                    <span class="font-semibold">Required when dentist is assigned to check for conflicts.</span>
                @endif
            </p>
            <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                <x-form.field label="Start Time" name="start_time" type="time" wire:model="start_time"
                    icon="fas fa-clock" placeholder="--:-- --" />

                <x-form.field label="End Time" name="end_time" type="time" wire:model="end_time" icon="fas fa-clock"
                    placeholder="--:-- --" help="Must be after start time" />
            </div>
        </div>

        <x-form.field label="Reason for Visit" name="reason" type="text" placeholder="Enter reason for appointment"
            wire:model="reason" required icon="fas fa-notes-medical" />

        <x-form.field label="Notes" name="notes" type="textarea" placeholder="Additional notes (optional)"
            wire:model="notes" icon="fas fa-sticky-note" rows="3" />
        <div class="flex justify-end space-x-3 pt-6">
            <x-utils.link-button href="{{ route('appointments.index') }}" buttonText="Cancel" />
            <x-utils.submit-button buttonText="Create Appointment" wireTarget="save" />
        </div>
    </x-form.container>
</div>
