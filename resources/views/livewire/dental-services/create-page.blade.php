<div class="container mx-auto px-2 py-0">
    <div class="flex justify-between items-center mb-6">
        <div>
            <h1 class="text-2xl font-bold text-slate-800 dark:text-slate-200">Create New Dental Service</h1>
            <p class="text-slate-600 dark:text-slate-400 mt-1">Add a new dental service to the system</p>
        </div>
    </div>

    @if (session()->has('message'))
        <div class="mb-6 p-4 bg-green-50 border border-green-200 rounded-lg">
            <p class="text-green-800">{{ session('message') }}</p>
        </div>
    @endif

    <x-form.container title="Service Information" subtitle="Fill in the details below to create a new dental service"
        wire:submit="save">
        <div class="space-y-6">
            <x-form.field label="Service Name" name="name" type="text" placeholder="Enter service name"
                wire:model="name" required icon="fas fa-tooth" 
                help="Enter a descriptive name for this dental service" />

            <x-form.field label="Service Type" name="dental_service_type_id" type="select" wire:model="dental_service_type_id" 
                :options="$serviceTypeOptions" required icon="fas fa-tags"
                help="Select the category this service belongs to" />

            <x-form.field label="Price" name="price" type="number" placeholder="0.00" step="0.01"
                wire:model="price" required icon="fas fa-dollar-sign" 
                help="Enter the price for this service in your local currency" />
        </div>

        <div class="flex justify-end space-x-3 pt-6">
            <x-utils.link-button href="{{ route('dental-services.index') }}" buttonText="Cancel" />
            <x-utils.submit-button buttonText="Create Service" wireTarget="save" />
        </div>
    </x-form.container>
</div>