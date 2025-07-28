{{-- resources/views/forms/service-form.blade.php --}}
<x-layouts.app title="Service Management" page-title="Add/Edit Dental Service">
    <x-form.container title="Dental Service Information" subtitle="Manage dental services and pricing" action="#"
        method="POST">

        <x-form.field label="Service Name" name="service_name" type="text" placeholder="Enter service name"
            required="true" icon="fas fa-tooth" />

        <x-form.field label="Service Type" name="service_type_id" type="select" required="true" :options="[
            '' => 'Select Service Type',
            '1' => 'Preventive Care',
            '2' => 'Restorative Dentistry',
            '3' => 'Cosmetic Dentistry',
            '4' => 'Oral Surgery',
            '5' => 'Orthodontics',
        ]" />

        <x-form.field label="Price" name="price" type="number" placeholder="0.00" required="true" prefix="₱"
            step="0.01" min="0" />

        <div class="flex gap-4 pt-4">
            <x-form.button type="submit" variant="primary" size="md" icon="fas fa-save">
                Save Service
            </x-form.button>

            <x-form.button href="#" variant="secondary" size="md" icon="fas fa-arrow-left">
                Back to List
            </x-form.button>
        </div>
    </x-form.container>
</x-layouts.app>
