{{-- resources/views/forms/visit-form.blade.php --}}
<x-layouts.app title="Patient Visit" page-title="Record Patient Visit">
    <x-form.container title="Visit Information" subtitle="Record patient visit and treatment" action="#" method="POST">

        <x-form.field label="Patient" name="patient_id" type="select" required="true" :options="[
            '' => 'Select Patient',
            '1' => 'Juan Dela Cruz - +63 912 345 6789',
            '2' => 'Maria Santos - +63 923 456 7890',
            '3' => 'Pedro Garcia - +63 934 567 8901',
        ]"
            icon="fas fa-user" />

        <x-form.field label="Branch" name="branch_id" type="select" required="true" :options="[
            '' => 'Select Branch',
            '1' => 'Main Branch - Tacloban',
            '2' => 'Branch 2 - Ormoc',
            '3' => 'Branch 3 - Baybay',
        ]"
            icon="fas fa-building" />

        <x-form.field label="Service Provided" name="service_id" type="select" required="true" :options="[
            '' => 'Select Service',
            '1' => 'Dental Cleaning - ₱800.00',
            '2' => 'Tooth Extraction - ₱1,500.00',
            '3' => 'Dental Filling - ₱1,200.00',
            '4' => 'Root Canal - ₱3,500.00',
        ]"
            icon="fas fa-tooth" />

        <x-form.field label="Visit Date & Time" name="visit_date" type="datetime-local" required="true"
            icon="fas fa-calendar-alt" />

        <x-form.field label="Amount Paid" name="amount_paid" type="number" placeholder="0.00" required="true"
            prefix="₱" step="0.01" min="0" icon="fas fa-peso-sign" />

        <x-form.field label="Treatment Notes" name="notes" type="textarea"
            placeholder="Record treatment details, observations, and recommendations" rows="4"
            icon="fas fa-notes-medical" />

        <div class="flex gap-4 pt-4">
            <x-form.button type="submit" variant="success" size="md" icon="fas fa-save">
                Record Visit
            </x-form.button>

            <x-form.button href="#" variant="secondary" size="md" icon="fas fa-arrow-left">
                Back to List
            </x-form.button>
        </div>
    </x-form.container>
</x-layouts.app>
