{{-- resources/views/forms/service-type-form.blade.php --}}
<x-layouts.app title="Service Type Management" page-title="Add/Edit Service Type">
    <x-form.container title="Service Type Information" subtitle="Manage dental service categories" action="#"
        method="POST">

        <x-form.field label="Type Name" name="type_name" type="text" placeholder="Enter service type name"
            required="true" icon="fas fa-tags" />

        <x-form.field label="Description" name="description" type="textarea"
            placeholder="Enter description for this service type" rows="4" icon="fas fa-align-left" />

        <div class="flex gap-4 pt-4">
            <x-form.button type="submit" variant="primary" size="md" icon="fas fa-save">
                Save Service Type
            </x-form.button>

            <x-form.button href="#" variant="secondary" size="md" icon="fas fa-arrow-left">
                Back to List
            </x-form.button>
        </div>
    </x-form.container>
</x-layouts.app>
