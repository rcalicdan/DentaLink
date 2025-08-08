<div class="container mx-auto px-2 py-0">
    <div class="flex justify-between items-center mb-6">
        <div>
            <h1 class="text-2xl font-bold text-slate-800 dark:text-slate-200">Create New Dental Service Type</h1>
            <p class="text-slate-600 dark:text-slate-400 mt-1">Add a new dental service type to the system</p>
        </div>
    </div>

    @if (session()->has('message'))
        <div class="mb-6 p-4 bg-green-50 border border-green-200 rounded-lg">
            <p class="text-green-800">{{ session('message') }}</p>
        </div>
    @endif

    <x-form.container title="Service Type Information" subtitle="Fill in the details below to create a new dental service type"
        wire:submit="save">
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
            <x-utils.submit-button buttonText="Create Service Type" wireTarget="save" />
        </div>
    </x-form.container>
</div>