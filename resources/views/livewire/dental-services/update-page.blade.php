<div class="container mx-auto px-2 py-0">
    <div class="flex justify-between items-center mb-6">
        <div>
            <h1 class="text-2xl font-bold text-slate-800 dark:text-slate-200">Update Dental Service</h1>
            <p class="text-slate-600 dark:text-slate-400 mt-1">Update the dental service information</p>
        </div>
    </div>

    @if (session()->has('message'))
        <div class="mb-6 p-4 bg-green-50 border border-green-200 rounded-lg">
            <p class="text-green-800">{{ session('message') }}</p>
        </div>
    @endif

    <x-form.container title="Service Information" subtitle="Update the details below to modify the dental service"
        wire:submit="update">
        <div class="space-y-6">
            <x-form.field label="Service Name" name="name" type="text" placeholder="Enter service name"
                wire:model="name" required icon="fas fa-tooth"
                help="Enter a descriptive name for this dental service" />

            <x-form.field label="Description" name="description" type="textarea" 
                placeholder="Enter service description (optional)"
                wire:model="description" icon="fas fa-align-left" rows="3"
                help="Provide additional details about this service" />

            <x-form.field label="Service Type" name="dental_service_type_id" type="select"
                wire:model="dental_service_type_id" :options="$serviceTypeOptions" required icon="fas fa-tags"
                help="Select the category this service belongs to" />

            <x-form.field label="Default Price" name="price" type="number" placeholder="0.00" step="0.01"
                wire:model="price" icon="fas fa-dollar-sign"
                help="Enter the default price for this service (optional - can be set per visit)" />

            <x-form.field type="checkbox" name="is_quantifiable" label="Allow Multiple Quantities" 
                wire:model="is_quantifiable"
                help="Uncheck for services like 'Dental Cleaning' that are one-time only" />
        </div>

        <div class="flex justify-end space-x-3 pt-6">
            <x-utils.link-button href="{{ route('dental-services.index') }}" buttonText="Cancel" />
            <x-utils.submit-button buttonText="Update Service" wireTarget="update" />
        </div>
    </x-form.container>
</div>