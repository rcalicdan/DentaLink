<div class="container mx-auto px-2 py-0">
    <div class="flex justify-between items-center mb-6">
        <div>
            <h1 class="text-2xl font-bold text-slate-800 dark:text-slate-200">Create New Patient</h1>
            <p class="text-slate-600 dark:text-slate-400 mt-1">Register a new patient in the system</p>
        </div>
    </div>

    @if (session()->has('message'))
        <div class="mb-6 p-4 bg-green-50 border border-green-200 rounded-lg">
            <p class="text-green-800">{{ session('message') }}</p>
        </div>
    @endif

    <x-form.container title="Patient Information" subtitle="Fill in the details below to register a new patient"
        wire:submit="save">
        <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
            <x-form.field label="First Name" name="first_name" type="text" placeholder="Enter first name"
                wire:model.live="first_name" required icon="fas fa-user" />

            <x-form.field label="Last Name" name="last_name" type="text" placeholder="Enter last name"
                wire:model.live="last_name" required icon="fas fa-user" />
        </div>

        <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
            <x-form.field label="Phone Number" name="phone" type="tel" placeholder="Enter phone number"
                wire:model.live="phone" required icon="fas fa-phone" />

            <x-form.field label="Email Address" name="email" type="email" placeholder="Enter email address"
                wire:model.live="email" icon="fas fa-envelope" />
        </div>

        <x-form.field label="Birthday" name="birthday" type="date" wire:model.live="birthday" 
            icon="fas fa-birthday-cake" placeholder="Select birthday" 
            help="Age will be calculated automatically from birthday" />

        <x-form.field label="Registration Branch" name="registration_branch_id" type="select"
            wire:model.live="registration_branch_id" :options="$branchOptions" required icon="fas fa-building"
            help="{{ $isAdmin ? 'Patients will be registered to your branch' : 'Select the branch where patient is registering' }}"
            :readonly="$isAdmin" />

        <h3 class="text-lg font-semibold text-slate-700 dark:text-slate-300 mt-6 mb-3">Address Details</h3>
        <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
            <x-form.field label="Street Name / House No." name="street" type="text" placeholder="e.g. 123 Main St."
                wire:model.live="street" icon="fas fa-road" />

            <x-form.field label="Barangay" name="barangay" type="text" placeholder="Enter Barangay"
                wire:model.live="barangay" icon="fas fa-map-marker-alt" />

            <x-form.field label="Town / City" name="town_city" type="text" placeholder="Enter Town or City"
                wire:model.live="town_city" icon="fas fa-city" />

            <x-form.field label="Province" name="province" type="text" placeholder="Enter Province"
                wire:model.live="province" icon="fas fa-map" />
        </div>

        <div class="flex justify-end space-x-3 pt-6">
            <x-utils.link-button href="{{ route('patients.index') }}" buttonText="Cancel" />
            <x-utils.submit-button buttonText="Register Patient" wireTarget="save" />
        </div>
    </x-form.container>
</div>