{{-- resources/views/forms/inventory-form.blade.php --}}
<x-layouts.app title="Inventory Management" page-title="Add/Edit Inventory Item">
    <x-form.container title="Inventory Item Information" subtitle="Manage clinic inventory items" action="#"
        method="POST">

        <x-form.field label="Branch" name="branch_id" type="select" required="true" :options="[
            '' => 'Select Branch',
            '1' => 'Main Branch - Tacloban',
            '2' => 'Branch 2 - Ormoc',
            '3' => 'Branch 3 - Baybay',
        ]"
            icon="fas fa-building" />

        <x-form.field label="Item Name" name="item_name" type="text" placeholder="Enter item name" required="true"
            icon="fas fa-box" />

        <x-form.radio-group label="Category" name="category" :options="[
            'Consumables' => 'Consumables',
            'Instruments' => 'Instruments',
            'Materials' => 'Materials',
            'Equipment' => 'Equipment',
        ]" value="Consumables" inline="true" />

        <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
            <x-form.field label="Current Stock" name="current_stock" type="number" placeholder="0" value="0"
                min="0" icon="fas fa-boxes" />

            <x-form.field label="Minimum Stock Level" name="minimum_stock" type="number" placeholder="10"
                value="10" min="1" icon="fas fa-exclamation-triangle"
                help="Alert when stock falls below this level" />
        </div>

        <div class="flex gap-4 pt-4">
            <x-form.button type="submit" variant="primary" size="md" icon="fas fa-save">
                Save Item
            </x-form.button>

            <x-form.button href="#" variant="secondary" size="md" icon="fas fa-arrow-left">
                Back to List
            </x-form.button>
        </div>
    </x-form.container>
</x-layouts.app>
