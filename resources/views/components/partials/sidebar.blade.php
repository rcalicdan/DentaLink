<aside class="sidebar sidebar-gradient text-white fixed h-full z-50 transform -translate-x-full md:translate-x-0 flex flex-col"
    :class="{
        'collapsed': sidebarCollapsed,
        '-translate-x-full md:translate-x-0': !sidebarOpen
    }">
    <!-- Sidebar Header -->
    <div class="p-4 flex items-center justify-between border-b border-blue-800/50 h-16 shrink-0">
        <div class="flex items-center">
            <i class="fas fa-tooth text-2xl text-white"></i>
            <h1 class="text-xl font-bold ml-3 sidebar-text" x-show="!sidebarCollapsed">Nice Smile</h1>
        </div>
        <button @click="toggleSidebarDesktop()" class="hidden md:block p-2 rounded-sm hover:bg-blue-700/50">
            <i class="fas fa-bars"></i>
        </button>
    </div>

    <!-- Navigation -->
    <nav class="flex-1 p-3 overflow-y-auto">
        <ul class="space-y-2">
            <x-partials.nav-item href="#" icon="fas fa-home" :active="true">
                Dashboard
            </x-partials.nav-item>

            <x-partials.nav-item href="#" icon="fas fa-calendar-check" :active="false">
                Appointments
            </x-partials.nav-item>

            <x-partials.nav-item href="#" icon="fas fa-users" :active="false">
                Patients
            </x-partials.nav-item>

            <x-partials.nav-item href="#" icon="fas fa-building" :active="false">
                Branches
            </x-partials.nav-item>

            <x-partials.nav-item href="#" icon="fas fa-chart-line" :active="false">
                Reports
            </x-partials.nav-item>
        </ul>
    </nav>

    <!-- Profile Section -->
    <div class="p-4 border-t border-blue-800/50 shrink-0">
        <div class="flex items-center">
            <div
                class="w-10 h-10 rounded-full bg-blue-500 flex items-center justify-center font-bold shadow-sm ring-2 ring-white/50">
                DQ
            </div>
            <div class="ml-3 sidebar-profile-text" x-show="!sidebarCollapsed">
                <p class="text-sm font-semibold">Dr. Quack</p>
                <p class="text-xs text-blue-200">Administrator</p>
            </div>
        </div>
    </div>
</aside>
