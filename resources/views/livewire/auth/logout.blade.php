<div x-data="logoutComponent()" @open-logout-modal.window="confirmLogout()">
    <!-- Confirmation Modal -->
    <div x-show="showConfirmation" 
         x-transition:enter="transition ease-out duration-300"
         x-transition:enter-start="opacity-0" 
         x-transition:enter-end="opacity-100"
         x-transition:leave="transition ease-in duration-200" 
         x-transition:leave-start="opacity-100"
         x-transition:leave-end="opacity-0" 
         class="fixed inset-0 z-[9999] flex items-center justify-center p-4"
         style="display: none;">

        <!-- Backdrop -->
        <div @click="cancelLogout()" class="absolute inset-0 bg-black/50 backdrop-blur-sm"></div>

        <!-- Modal Container -->
        <div class="relative w-full max-w-md mx-auto">
            <!-- Modal Content -->
            <div @click.stop 
                 x-transition:enter="transition ease-out duration-300"
                 x-transition:enter-start="opacity-0 scale-95 translate-y-4"
                 x-transition:enter-end="opacity-100 scale-100 translate-y-0"
                 x-transition:leave="transition ease-in duration-200"
                 x-transition:leave-start="opacity-100 scale-100 translate-y-0"
                 x-transition:leave-end="opacity-0 scale-95 translate-y-4"
                 class="relative bg-white dark:bg-slate-800 rounded-lg shadow-2xl border border-slate-200 dark:border-slate-700 overflow-hidden">

                <!-- Modal Body -->
                <div class="p-6">
                    <div class="flex items-start space-x-4">
                        <!-- Icon -->
                        <div class="flex-shrink-0">
                            <div class="flex items-center justify-center w-10 h-10 rounded-full bg-red-100 dark:bg-red-900/20">
                                <svg class="w-5 h-5 text-red-600 dark:text-red-400" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor">
                                    <path stroke-linecap="round" stroke-linejoin="round" d="M12 9v3.75m-9.303 3.376c-.866 1.5.217 3.374 1.948 3.374h14.71c1.73 0 2.813-1.874 1.948-3.374L13.949 3.378c-.866-1.5-3.032-1.5-3.898 0L2.697 16.126ZM12 15.75h.007v.008H12v-.008Z" />
                                </svg>
                            </div>
                        </div>

                        <!-- Text Content -->
                        <div class="flex-1 min-w-0">
                            <h3 class="text-lg font-semibold text-slate-900 dark:text-slate-100 mb-2">
                                {{ __('Confirm Logout') }}
                            </h3>
                            <p class="text-sm text-slate-600 dark:text-slate-400">
                                {{ __('Are you sure you want to log out? You will need to sign in again to access your account.') }}
                            </p>
                        </div>
                    </div>
                </div>

                <!-- Modal Footer -->
                <div class="px-6 py-4 bg-slate-50 dark:bg-slate-900/50 border-t border-slate-200 dark:border-slate-700 flex flex-col-reverse sm:flex-row sm:justify-end sm:space-x-3 space-y-3 space-y-reverse sm:space-y-0">
                    <!-- Cancel Button -->
                    <button @click="cancelLogout()" type="button" :disabled="isLoggingOut"
                            :class="{ 'opacity-50 cursor-not-allowed': isLoggingOut }"
                            class="inline-flex justify-center items-center px-4 py-2 text-sm font-medium text-slate-700 dark:text-slate-300 bg-white dark:bg-slate-700 border border-slate-300 dark:border-slate-600 rounded-md hover:bg-slate-50 dark:hover:bg-slate-600 focus:outline-none focus:ring-2 focus:ring-blue-500 focus:ring-offset-2 dark:focus:ring-offset-slate-800 transition-colors duration-200">
                        {{ __('Cancel') }}
                    </button>

                    <!-- Logout Button -->
                    <button @click="logout()" type="button" :disabled="isLoggingOut"
                            class="inline-flex justify-center items-center px-4 py-2 text-sm font-medium text-white bg-red-600 border border-transparent rounded-md hover:bg-red-700 focus:outline-none focus:ring-2 focus:ring-red-500 focus:ring-offset-2 dark:focus:ring-offset-slate-800 disabled:opacity-50 disabled:cursor-not-allowed transition-colors duration-200">
                        <span x-show="isLoggingOut" class="flex items-center">
                            <svg class="animate-spin -ml-1 mr-2 h-4 w-4" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24">
                                <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
                                <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path>
                            </svg>
                            {{ __('Logging out...') }}
                        </span>
                        <span x-show="!isLoggingOut">
                            {{ __('Yes, Logout') }}
                        </span>
                    </button>
                </div>
            </div>
        </div>
    </div>
</div>

@script
<script>
    Alpine.data('logoutComponent', () => ({
        showConfirmation: false,
        isLoggingOut: false,

        confirmLogout() {
            this.isLoggingOut = false;
            this.showConfirmation = true;
        },

        cancelLogout() {
            if (!this.isLoggingOut) {
                this.showConfirmation = false;
            }
        },

        logout() {
            this.isLoggingOut = true;
            this.$wire.logout().then(() => {
                window.location.reload();
            }).catch(() => {
                this.isLoggingOut = false;
                this.showConfirmation = false;
                alert('Could not log out. Please try again.');
            });
        }
    }));
</script>
@endscript