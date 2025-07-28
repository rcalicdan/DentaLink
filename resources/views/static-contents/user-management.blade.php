{{-- resources/views/forms/user-form.blade.php --}}
<x-layouts.app title="User Management" page-title="Add/Edit User">
    <x-form.container title="User Account Information" subtitle="Create or update user account" action="#"
        method="POST">

        <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
            <x-form.field label="First Name" name="first_name" type="text" placeholder="Enter first name"
                required="true" icon="fas fa-user" />

            <x-form.field label="Last Name" name="last_name" type="text" placeholder="Enter last name"
                required="true" icon="fas fa-user" />
        </div>

        <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
            <x-form.field label="Username" name="username" type="text" placeholder="Enter username" required="true"
                icon="fas fa-at" />

            <x-form.field label="Email" name="email" type="email" placeholder="user@nicesmile.com" required="true"
                icon="fas fa-envelope" />
        </div>

        <x-form.field label="Phone Number" name="phone" type="tel" placeholder="+63 xxx xxx xxxx"
            icon="fas fa-phone" />

        <x-form.field label="Password" name="password" type="password" placeholder="Enter secure password"
            required="true" icon="fas fa-lock" help="Password must be at least 8 characters long" />

        <x-form.radio-group label="User Role" name="role" :options="[
            'superadmin' => 'Super Administrator',
            'admin' => 'Administrator',
            'employee' => 'Employee',
        ]" value="employee" inline="true" />

        <x-form.field label="Assigned Branch" name="branch_id" type="select" :options="[
            '' => 'Select Branch',
            '1' => 'Main Branch - Tacloban',
            '2' => 'Branch 2 - Ormoc',
            '3' => 'Branch 3 - Baybay',
        ]"
            help="Leave empty for Super Administrator" />

        <div class="flex gap-4 pt-4">
            <x-form.button type="submit" variant="primary" size="md" icon="fas fa-user-plus">
                Save User
            </x-form.button>

            <x-form.button href="#" variant="secondary" size="md" icon="fas fa-arrow-left">
                Back to List
            </x-form.button>
        </div>
    </x-form.container>
</x-layouts.app>
