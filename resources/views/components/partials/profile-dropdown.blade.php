<div class="relative" x-data="{ open: false }" @click.outside="open = false">
    <button @click="open = !open"
        class="flex items-center space-x-2 p-1 hover:bg-slate-200 dark:hover:bg-slate-700 rounded-lg transition-colors">
        <div
            class="w-8 h-8 rounded-full bg-blue-500 flex items-center justify-center font-bold text-white shadow-sm ring-2 ring-white/50 dark:ring-slate-400/50 text-sm">
            {{ auth()->user()->name_initials }}
        </div>
        <span
            class="text-sm font-medium text-slate-700 dark:text-slate-300 hidden sm:inline">{{ auth()->user()->full_name }}</span>
        <i class="fas fa-chevron-down text-slate-500 dark:text-slate-400 text-xs transition-transform"
            :class="{ 'rotate-180': open }"></i>
    </button>

    <div x-show="open" 
         style="display: none" 
         x-transition:enter="transition ease-out duration-200"
         x-transition:enter-start="opacity-0 transform scale-95" 
         x-transition:enter-end="opacity-100 transform scale-100"
         x-transition:leave="transition ease-in duration-150" 
         x-transition:leave-start="opacity-100 transform scale-100"
         x-transition:leave-end="opacity-0 transform scale-95"
         class="absolute right-0 mt-2 w-56 bg-white dark:bg-slate-800 rounded-lg shadow-xl z-20 border border-slate-200 dark:border-slate-700">

        <!-- Profile Info -->
        <div class="p-4 border-b border-slate-200 dark:border-slate-700">
            <p class="font-semibold text-slate-800 dark:text-slate-200">{{ auth()->user()->full_name }}</p>
            <p class="text-xs text-slate-500 dark:text-slate-400">{{ ucfirst(auth()->user()->role) }}</p>
        </div>

        <!-- Menu Items -->
        <div class="py-1">
            <a href="#"
                class="flex items-center px-4 py-2 text-sm text-slate-700 dark:text-slate-300 hover:bg-blue-50 dark:hover:bg-slate-700/50">
                <i class="fas fa-user-cog mr-3 w-4 text-center text-slate-500 dark:text-slate-400"></i>
                Settings
            </a>
            
            <button @click="$dispatch('open-logout-modal')"
                class="flex items-center px-4 py-2 text-sm text-slate-700 dark:text-slate-300 hover:bg-blue-50 dark:hover:bg-slate-700/50 w-full text-left transition-colors duration-200">
                <i class="fas fa-sign-out-alt mr-3 w-4 text-center text-slate-500 dark:text-slate-400"></i>
                <span>{{ __('Logout') }}</span>
            </button>
        </div>
    </div>
</div>