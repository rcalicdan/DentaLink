<div x-data="loginForm()">
    <form class="space-y-6" wire:submit.prevent="login">
        <!-- Email Field -->
        <div>
            <label for="email" class="block text-sm font-semibold text-slate-700 dark:text-slate-200 mb-3">
                Email Address
            </label>
            <div class="relative group">
                <div class="absolute inset-y-0 left-0 pl-4 flex items-center pointer-events-none z-10">
                    <i
                        class="fas fa-envelope text-slate-400 group-focus-within:text-blue-500 transition-colors duration-200"></i>
                </div>
                <input id="email" name="email" type="email" autocomplete="email" wire:model="email" required
                    value="{{ old('email') }}"
                    class="block w-full pl-12 pr-4 py-4 border border-slate-200 dark:border-slate-600 rounded-xl bg-white dark:bg-slate-700 text-slate-900 dark:text-slate-100 placeholder-slate-400 dark:placeholder-slate-500 focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-blue-500 transition-all duration-200 shadow-sm hover:shadow-md disabled:opacity-50 disabled:cursor-not-allowed"
                    placeholder="your.email@example.com" wire:loading.attr="disabled" wire:target="login">
                <div
                    class="absolute inset-0 rounded-xl bg-gradient-to-r from-blue-500/5 to-cyan-500/5 opacity-0 group-focus-within:opacity-100 transition-opacity duration-200 pointer-events-none">
                </div>
            </div>
            @error('email')
                <div class="mt-2 flex items-center text-sm text-red-500 dark:text-red-400">
                    <i class="fas fa-exclamation-circle mr-2"></i>
                    {{ $message }}
                </div>
            @enderror
        </div>

        <!-- Password Field -->
        <div>
            <label for="password" class="block text-sm font-semibold text-slate-700 dark:text-slate-200 mb-3">
                Password
            </label>
            <div class="relative group">
                <div class="absolute inset-y-0 left-0 pl-4 flex items-center pointer-events-none z-10">
                    <i
                        class="fas fa-lock text-slate-400 group-focus-within:text-blue-500 transition-colors duration-200"></i>
                </div>
                <input id="password" name="password" wire:model="password" :type="showPassword ? 'text' : 'password'"
                    autocomplete="current-password" required
                    class="block w-full pl-12 pr-14 py-4 border border-slate-200 dark:border-slate-600 rounded-xl bg-white dark:bg-slate-700 text-slate-900 dark:text-slate-100 placeholder-slate-400 dark:placeholder-slate-500 focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-blue-500 transition-all duration-200 shadow-sm hover:shadow-md disabled:opacity-50 disabled:cursor-not-allowed"
                    placeholder="Enter your password" wire:loading.attr="disabled" wire:target="login">
                <button type="button" @click="togglePassword()"
                    class="absolute inset-y-0 right-0 pr-4 flex items-center text-slate-400 hover:text-blue-500 transition-colors duration-200 z-10 disabled:opacity-50"
                    wire:loading.attr="disabled" wire:target="login">
                    <i :class="showPassword ? 'fas fa-eye-slash' : 'fas fa-eye'"></i>
                </button>
                <div
                    class="absolute inset-0 rounded-xl bg-gradient-to-r from-blue-500/5 to-cyan-500/5 opacity-0 group-focus-within:opacity-100 transition-opacity duration-200 pointer-events-none">
                </div>
            </div>
            @error('password')
                <div class="mt-2 flex items-center text-sm text-red-500 dark:text-red-400">
                    <i class="fas fa-exclamation-circle mr-2"></i>
                    {{ $message }}
                </div>
            @enderror
        </div>

        <!-- Submit Button -->
        <div class="pt-2">
            <button type="submit"
                class="group relative w-full flex justify-center items-center py-4 px-6 border border-transparent text-base font-semibold rounded-xl text-white bg-gradient-to-r from-blue-600 to-cyan-600 hover:from-blue-700 hover:to-cyan-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-blue-500 transition-all duration-300 transform hover:scale-[1.02] active:scale-[0.98] shadow-lg hover:shadow-xl disabled:opacity-70 disabled:cursor-not-allowed disabled:transform-none disabled:hover:scale-100"
                wire:loading.attr="disabled" wire:target="login">

                <div
                    class="absolute inset-0 bg-gradient-to-r from-white/10 to-white/5 rounded-xl opacity-0 group-hover:opacity-100 transition-opacity duration-300">
                </div>

                <!-- Default state -->
                <span class="flex items-center relative z-10" wire:loading.remove wire:target="login">
                    <i class="fas fa-sign-in-alt mr-3 text-blue-200"></i>
                    Sign in to Dashboard
                </span>

                <!-- Loading state -->
                <span class="flex items-center relative z-10" wire:loading wire:target="login">
                    <svg class="animate-spin -ml-1 mr-3 h-5 w-5 text-white" xmlns="http://www.w3.org/2000/svg"
                        fill="none" viewBox="0 0 24 24">
                        <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor"
                            stroke-width="4"></circle>
                        <path class="opacity-75" fill="currentColor"
                            d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z">
                        </path>
                    </svg>
                </span>
            </button>
        </div>
    </form>
</div>

@push('scripts')
    <script>
        function loginForm() {
            return {
                showPassword: false,

                togglePassword() {
                    this.showPassword = !this.showPassword;
                }
            }
        }
    </script>
@endpush
