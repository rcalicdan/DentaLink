<div class="container mx-auto px-6 py-8">
    <div class="mb-6">
        <h1 class="text-2xl font-bold text-slate-800 dark:text-slate-200">My Profile</h1>
        <p class="text-slate-600 dark:text-slate-400 mt-1">Manage your personal information and password.</p>
    </div>

    <x-flash-message />

    <div class="grid grid-cols-1 lg:grid-cols-3 gap-8">
        <!-- Right Column: Profile Summary Card (Moved first in source for mobile-first view) -->
        <div class="lg:col-span-1 lg:order-2">
            <div
                class="bg-white dark:bg-slate-800 rounded-xl shadow-lg border border-slate-200 dark:border-slate-700 p-6 text-center">
                <div class="mb-4">
                    <div
                        class="w-32 h-32 rounded-full mx-auto bg-gradient-to-br from-blue-500 to-indigo-600 flex items-center justify-center ring-4 ring-white dark:ring-slate-900 shadow-md">
                        <span class="text-5xl font-bold text-white">{{ $user->name_initials }}</span>
                    </div>
                </div>
                <h2 class="text-2xl font-bold text-slate-900 dark:text-slate-100">{{ $user->full_name }}</h2>
                <p class="text-slate-500 dark:text-slate-400 mt-1">{{ $user->email }}</p>
                <div class="mt-4 flex items-center justify-center space-x-4">
                    <span
                        class="inline-flex items-center px-3 py-1 rounded-full text-sm font-medium bg-blue-100 text-blue-800 dark:bg-blue-900 dark:text-blue-200">
                        <i class="fas fa-user-tag mr-2"></i>{{ Str::title($user->role) }}
                    </span>
                    <span
                        class="inline-flex items-center px-3 py-1 rounded-full text-sm font-medium bg-green-100 text-green-800 dark:bg-green-900 dark:text-green-200">
                        <i class="fas fa-building mr-2"></i>{{ $user->branch_name }}
                    </span>
                </div>
            </div>
        </div>

        <!-- Left Column: Forms (Moved second in source, ordered first on desktop) -->
        <div class="lg:col-span-2 lg:order-1 space-y-8">

            <!-- Personal Information Form -->
            <x-form.container title="Personal Information" subtitle="Update your name, email, and phone number."
                wire:submit="updateProfile">
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

                <div class="flex justify-end pt-6">
                    <x-utils.submit-button buttonText="Save Changes" wireTarget="updateProfile" />
                </div>
            </x-form.container>

            <!-- Change Password Form -->
            <x-form.container title="Change Password"
                subtitle="For your security, you must provide your current password to change it."
                wire:submit="updatePassword">

                <x-form.field label="Current Password" name="current_password" :type="$showPassword ? 'text' : 'password'"
                    placeholder="Enter your current password" wire:model="current_password" required
                    icon="fas fa-key" />

                <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                    <div class="relative">
                        <x-form.field label="New Password" name="password" :type="$showPassword ? 'text' : 'password'"
                            placeholder="Enter new password" wire:model="password" required icon="fas fa-lock"
                            help="Must be at least 8 characters long." />
                        <button type="button" wire:click="togglePasswordVisibility"
                            class="absolute right-3 top-9 text-slate-400 hover:text-slate-600 dark:hover:text-slate-300 transition-colors">
                            <i class="fas {{ $showPassword ? 'fa-eye-slash' : 'fa-eye' }}"></i>
                        </button>
                    </div>

                    <x-form.field label="Confirm New Password" name="password_confirmation" :type="$showPassword ? 'text' : 'password'"
                        placeholder="Confirm new password" wire:model="password_confirmation" required
                        icon="fas fa-lock" />
                </div>

                <div class="flex justify-end pt-6">
                    <x-utils.submit-button buttonText="Change Password" wireTarget="updatePassword" />
                </div>
            </x-form.container>
        </div>

    </div>
</div>
