<div class="container mx-auto px-6 py-8">
    <div class="flex justify-between items-center mb-6">
        <div>
            <h1 class="text-2xl font-bold text-slate-800 dark:text-slate-200">Update Dental Service Type</h1>
            <p class="text-slate-600 dark:text-slate-400 mt-1">Edit service type information for {{ $dentalServiceType->name }}</p>
        </div>
    </div>

    @if (session()->has('message'))
        <div class="mb-6 p-4 bg-green-50 border border-green-200 rounded-lg">
            <p class="text-green-800">{{ session('message') }}</p>
        </div>
    @endif

    <x-form.container title="Service Type Information" subtitle="Update the details below to modify the dental service type"
        wire:submit="update">
        <div class="space-y-6">
            <x-form.field label="Service Type Name" name="name" type="text" placeholder="Enter service type name"
                wire:model="name" required icon="fas fa-tooth" 
                help="Enter a unique name for this dental service type" />

            <x-form.field label="Description" name="description" type="textarea" placeholder="Enter description (optional)"
                wire:model="description" icon="fas fa-align-left" 
                help="Optional description of what this service type includes" />
        </div>

        <div class="flex justify-end space-x-3 pt-6">
            <x-utils.link-button href="{{ route('dental-service-types.index') }}" buttonText="Cancel" />
            <x-utils.submit-button buttonText="Update Service Type" wireTarget="update" />
        </div>
    </x-form.container>
</div>