{{-- resources/views/forms/appointment-form.blade.php --}}
<x-layouts.app title="Appointment Management" page-title="Schedule Appointment">
    <x-form.container title="Appointment Details" subtitle="Schedule patient appointment" action="#" method="POST">

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

        <x-form.field label="Service" name="service_id" type="select" required="true" :options="[
            '' => 'Select Service',
            '1' => 'Dental Cleaning - ₱800.00',
            '2' => 'Tooth Extraction - ₱1,500.00',
            '3' => 'Dental Filling - ₱1,200.00',
            '4' => 'Root Canal - ₱3,500.00',
        ]"
            icon="fas fa-tooth" />

        <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
            <x-form.field label="Appointment Date" name="appointment_date" type="date" required="true"
                icon="fas fa-calendar" />

            <x-form.field label="Appointment Time" name="appointment_time" type="time" required="true"
                icon="fas fa-clock" />
        </div>

        <x-form.radio-group label="Status" name="status" :options="[
            'Scheduled' => 'Scheduled',
            'In Progress' => 'In Progress',
            'Completed' => 'Completed',
            'Cancelled' => 'Cancelled',
        ]" value="Scheduled" inline="true" />

        <x-form.field label="Notes" name="notes" type="textarea"
            placeholder="Additional notes or special instructions" rows="3" icon="fas fa-sticky-note" />

        <div class="flex gap-4 pt-4">
            <x-form.button type="submit" variant="success" size="md" icon="fas fa-calendar-plus">
                Schedule Appointment
            </x-form.button>

            <x-form.button href="#" variant="secondary" size="md" icon="fas fa-arrow-left">
                Back to List
            </x-form.button>
        </div>
    </x-form.container>
</x-layouts.app>
