<div class="container mx-auto px-2 py-0">
    <div class="flex justify-between items-center mb-6">
        <div>
            <h1 class="text-2xl font-bold text-slate-800 dark:text-slate-200">Create Inventory Item</h1>
            <p class="text-slate-600 dark:text-slate-400 mt-1">Add a new item to the inventory</p>
        </div>
    </div>

    <x-form.container title="Item Information" subtitle="Fill in the details below to add a new inventory item"
        wire:submit="save">

        <x-form.field label="Item Name" name="name" type="text" placeholder="e.g., Dental Bibs"
            wire:model="name" required icon="fas fa-box" />

        <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
            <x-form.field label="Category" name="category" type="select" wire:model="category" :options="$categoryOptions"
                required icon="fas fa-tags" />

            @if ($isSuperadmin)
                <x-form.field label="Branch" name="branch_id" type="select" wire:model="branch_id" :options="$branchOptions"
                    required icon="fas fa-building" />
            @else
                <input type="hidden" wire:model="branch_id" />
                <x-form.field label="Branch" name="branch_id" type="select" :options="$branchOptions"
                    icon="fas fa-building" :readonly="true" />
            @endif
        </div>

        <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
            <x-form.field label="Current Stock" name="current_stock" type="number" placeholder="Enter current quantity"
                wire:model="current_stock" required icon="fas fa-boxes" />

            <x-form.field label="Minimum Stock Level" name="minimum_stock" type="number"
                placeholder="Enter reorder point" wire:model="minimum_stock" required icon="fas fa-exclamation-triangle"
                help="When current stock is at or below this level, it will be marked as low." />
        </div>

        <div class="flex justify-end space-x-3 pt-6">
            <x-utils.link-button href="{{ route('inventory.index') }}" buttonText="Cancel" />
            <x-utils.submit-button buttonText="Create Item" wireTarget="save" />
        </div>
    </x-form.container>
</div>