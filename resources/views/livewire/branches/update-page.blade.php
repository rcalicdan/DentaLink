<div class="container mx-auto px-6 py-8">
    <div class="flex justify-between items-center mb-6">
        <div>
            <h1 class="text-2xl font-bold text-slate-800 dark:text-slate-200">Update Branch</h1>
            <p class="text-slate-600 dark:text-slate-400 mt-1">Edit branch information for {{ $branch->name }}</p>
        </div>
    </div>

    @if (session()->has('message'))
        <div class="mb-6 p-4 bg-green-50 border border-green-200 rounded-lg">
            <p class="text-green-800">{{ session('message') }}</p>
        </div>
    @endif

    <x-form.container title="Branch Information" subtitle="Update the details below to modify the branch"
        wire:submit="update">
        <div class="grid grid-cols-1 gap-6">
            <x-form.field label="Branch Name" name="name" type="text" placeholder="Enter branch name"
                wire:model="name" required icon="fas fa-building" />
        </div>

        <div class="grid grid-cols-1 gap-6">
            <x-form.field label="Address" name="address" type="textarea" placeholder="Enter branch address"
                wire:model="address" icon="fas fa-map-marker-alt" rows="3" />
        </div>

        <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
            <x-form.field label="Phone Number" name="phone" type="tel" placeholder="Enter phone number"
                wire:model="phone" icon="fas fa-phone" />

            <x-form.field label="Email Address" name="email" type="email" placeholder="Enter email address"
                wire:model="email" icon="fas fa-envelope" />
        </div>

        <div class="flex justify-end space-x-3 pt-6">
            <x-utils.link-button href="{{ route('branches.index') }}" buttonText="Cancel" />
            <x-utils.submit-button buttonText="Update Branch" wireTarget="update" />
        </div>
    </x-form.container>
</div>