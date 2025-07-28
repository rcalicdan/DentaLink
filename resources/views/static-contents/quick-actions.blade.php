{{-- resources/views/forms/quick-actions.blade.php --}}
<x-layouts.app title="Quick Actions" page-title="Quick Patient Actions">
    <div class="grid grid-cols-1 lg:grid-cols-2 gap-8">
        <!-- Quick Patient Search -->
        <x-form.container title="Patient Search" subtitle="Find patient quickly" action="#" method="GET">

            <x-form.field label="Search Patient" name="search" type="text" placeholder="Enter name or phone number"
                icon="fas fa-search" />

            <x-form.button type="submit" variant="primary" size="sm" icon="fas fa-search">
                Search
            </x-form.button>
        </x-form.container>

        <!-- Quick Appointment -->
        <x-form.container title="Quick Appointment" subtitle="Schedule walk-in appointment" action="#"
            method="POST">

            <x-form.field label="Patient Phone" name="patient_phone" type="tel" placeholder="+63 xxx xxx xxxx"
                required="true" icon="fas fa-phone" />

            <x-form.field label="Service" name="service_id" type="select" required="true" :options="[
                '' => 'Select Service',
                '1' => 'Consultation - ₱300.00',
                '2' => 'Dental Cleaning - ₱800.00',
                '3' => 'Emergency Treatment - ₱1,000.00',
            ]" />

            <x-form.button type="submit" variant="success" size="sm" icon="fas fa-plus">
                Add to Queue
            </x-form.button>
        </x-form.container>
    </div>
</x-layouts.app>
