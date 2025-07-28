<x-layouts.app title="Branch Management" page-title="Add/Edit Branch">
    <x-form.container title="Branch Information" subtitle="Manage clinic branch details" action="#" method="POST">

        <x-form.field label="Branch Name" name="branch_name" type="text" placeholder="Enter branch name" required="true"
            icon="fas fa-building" />

        <x-form.field label="Address" name="address" type="textarea" placeholder="Enter complete address" rows="3"
            icon="fas fa-map-marker-alt" />

        <x-form.field label="Phone Number" name="phone" type="tel" placeholder="+63 xxx xxx xxxx"
            icon="fas fa-phone" />

        <x-form.field label="Email" name="email" type="email" placeholder="branch@nicesmile.com"
            icon="fas fa-envelope" />

        <div class="flex gap-4 pt-4">
            <x-form.button type="submit" variant="primary" size="md" icon="fas fa-save">
                Save Branch
            </x-form.button>

            <x-form.button href="#" variant="secondary" size="md" icon="fas fa-arrow-left">
                Back to List
            </x-form.button>
        </div>
    </x-form.container>
</x-layouts.app>
