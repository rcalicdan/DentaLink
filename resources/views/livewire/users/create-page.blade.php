<div class="container mx-auto px-6 py-8">
    <div class="flex justify-between items-center mb-6">
        <div>
            <h1 class="text-2xl font-bold text-slate-800 dark:text-slate-200">Create New User</h1>
            <p class="text-slate-600 dark:text-slate-400 mt-1">Add a new user to the system</p>
        </div>
        <x-utils.link-button href="{{ route('users.index') }}" buttonText="Back to Users" bgColor="bg-slate-600"
            hoverColor="hover:bg-slate-700" focusRing="focus:ring-slate-500" />
    </div>

    @if (session()->has('message'))
        <div class="mb-6 p-4 bg-green-50 border border-green-200 rounded-lg">
            <p class="text-green-800">{{ session('message') }}</p>
        </div>
    @endif

    <x-form.container title="User Information" subtitle="Fill in the details below to create a new user account"
        wire:submit="save">
        <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
            <x-form.field label="First Name" name="first_name" type="text" placeholder="Enter first name"
                wire:model="first_name" required icon="fas fa-user" />

            <x-form.field label="Last Name" name="last_name" type="text" placeholder="Enter last name"
                wire:model="last_name" required icon="fas fa-user" />
        </div>

        <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
            <x-form.field label="Email Address" name="email" type="email" placeholder="Enter email address"
                wire:model="email" required icon="fas fa-envelope" />

            <x-form.field label="Phone Number" name="phone" type="tel" placeholder="Enter phone number"
                wire:model="phone" icon="fas fa-phone" />
        </div>

        <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
            <div class="relative">
                <x-form.field label="Password" name="password" :type="$showPassword ? 'text' : 'password'" placeholder="Enter password"
                    wire:model="password" required icon="fas fa-lock"
                    help="Password must be at least 8 characters long" />
                <button type="button" wire:click="togglePasswordVisibility"
                    class="absolute right-3 top-9 text-slate-400 hover:text-slate-600 dark:hover:text-slate-300 transition-colors">
                    <i class="fas {{ $showPassword ? 'fa-eye-slash' : 'fa-eye' }}"></i>
                </button>
            </div>

            <div class="relative">
                <x-form.field label="Confirm Password" name="password_confirmation" :type="$showPassword ? 'text' : 'password'"
                    placeholder="Confirm password" wire:model="password_confirmation" required icon="fas fa-lock" />
                <button type="button" wire:click="togglePasswordVisibility"
                    class="absolute right-3 top-9 text-slate-400 hover:text-slate-600 dark:hover:text-slate-300 transition-colors">
                    <i class="fas {{ $showPassword ? 'fa-eye-slash' : 'fa-eye' }}"></i>
                </button>
            </div>
        </div>

        <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
            <x-form.field label="User Role" name="role" type="select" wire:model="role" :options="$roleOptions" required
                icon="fas fa-user-tag" help="Select the appropriate role for this user" />

            <x-form.field label="Branch" name="branch_id" type="select" wire:model="branch_id" :options="$branchOptions"
                icon="fas fa-building" help="Optional: Assign user to a specific branch" />
        </div>

        <div class="flex justify-end space-x-3 pt-6">
            <x-utils.link-button href="{{ route('users.index') }}" buttonText="Cancel" />
            <x-utils.submit-button buttonText="Create User" wireTarget="save" />
        </div>
    </x-form.container>
</div>
