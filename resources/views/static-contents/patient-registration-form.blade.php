<x-layouts.app title="Patient Management" page-title="Patient Registration">
    <x-form.container title="Patient Information" subtitle="Register new patient or update existing record" action="#"
        method="POST">

        <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
            <x-form.field label="First Name" name="first_name" type="text" placeholder="Enter first name"
                required="true" icon="fas fa-user" />

            <x-form.field label="Last Name" name="last_name" type="text" placeholder="Enter last name" required="true"
                icon="fas fa-user" />
        </div>

        <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
            <x-form.field label="Phone Number" name="phone" type="tel" placeholder="+63 xxx xxx xxxx" required="true"
                icon="fas fa-phone" />

            <x-form.field label="Email" name="email" type="email" placeholder="patient@email.com"
                icon="fas fa-envelope" />
        </div>

        <x-form.field label="Date of Birth" name="date_of_birth" type="date" icon="fas fa-calendar" />

        <x-form.field label="Address" name="address" type="textarea" placeholder="Enter complete address" rows="3"
            icon="fas fa-map-marker-alt" />

        <x-form.field label="Registration Branch" name="registration_branch_id" type="select" required="true" :options="[
                '' => 'Select Branch',
                '1' => 'Main Branch - Tacloban',
                '2' => 'Branch 2 - Ormoc',
                '3' => 'Branch 3 - Baybay'
            ]" />

        <div class="flex gap-4 pt-4">
            <x-form.button type="submit" variant="success" size="md" icon="fas fa-user-plus">
                Register Patient
            </x-form.button>

            <x-form.button href="#" variant="secondary" size="md" icon="fas fa-arrow-left">
                Back to List
            </x-form.button>
        </div>
    </x-form.container>
</x-layouts.app>