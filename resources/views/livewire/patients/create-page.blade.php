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
                wire:model="first_name" required icon="fas fa-user" />

            <x-form.field label="Last Name" name="last_name" type="text" placeholder="Enter last name"
                wire:model="last_name" required icon="fas fa-user" />
        </div>

        <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
            <x-form.field label="Phone Number" name="phone" type="tel" placeholder="Enter phone number"
                wire:model="phone" required icon="fas fa-phone" />

            <x-form.field label="Email Address" name="email" type="email" placeholder="Enter email address"
                wire:model="email" icon="fas fa-envelope" />
        </div>

        <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
            <x-form.field label="Date of Birth" name="date_of_birth" type="date" 
                wire:model="date_of_birth" icon="fas fa-calendar" />

            <x-form.field label="Registration Branch" name="registration_branch_id" type="select" 
                wire:model="registration_branch_id" :options="$branchOptions" required icon="fas fa-building"
                help="{{ $isAdmin ? 'Patients will be registered to your branch' : 'Select the branch where patient is registering' }}"
                :readonly="$isAdmin" />
        </div>

        <div class="grid grid-cols-1 gap-6">
            <x-form.field label="Address" name="address" type="textarea" placeholder="Enter patient address"
                wire:model="address" icon="fas fa-map-marker-alt" rows="3" />
        </div>

        <div class="flex justify-end space-x-3 pt-6">
            <x-utils.link-button href="{{ route('patients.index') }}" buttonText="Cancel" />
            <x-utils.submit-button buttonText="Register Patient" wireTarget="save" />
        </div>
    </x-form.container>
</div>