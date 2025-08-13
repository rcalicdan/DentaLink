<aside
    class="sidebar sidebar-gradient text-white fixed h-full z-50 transform -translate-x-full md:translate-x-0 flex flex-col"
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
        {{-- Removed the old toggle button from here --}}
    </div>

    <!-- Navigation -->
    <nav class="flex-1 p-3 overflow-y-auto">
        <ul class="space-y-2">
            @can('view-dashboard')
            <x-partials.nav-item href="{{ route('dashboard.index') }}" icon="fas fa-home" :active="request()->routeIs('dashboard.index')">
                Dashboard
            </x-partials.nav-item>
            @endcan

            @can('viewAny', App\Models\Branch::class)
                <x-partials.nav-item href="{{ route('branches.index') }}" icon="fas fa-building" :active="request()->routeIs('branches.*')">
                    Branches
                </x-partials.nav-item>
            @endcan

            @can('viewAny', App\Models\User::class)
                <x-partials.nav-item href="{{ route('users.index') }}" icon="fas fa-users" :active="request()->routeIs('users.*')">
                    Users
                </x-partials.nav-item>
            @endcan

            @can('viewAny', App\Models\DentalServiceType::class)
                <x-partials.nav-item href="{{ route('dental-service-types.index') }}" icon="fas fa-tooth" :active="request()->routeIs('dental-service-types.*')">
                    Service Types
                </x-partials.nav-item>
            @endcan

            @can('viewAny', App\Models\DentalService::class)
                <x-partials.nav-item href="{{ route('dental-services.index') }}" icon="fas fa-procedures" :active="request()->routeIs('dental-services.*')">
                    Dental Services
                </x-partials.nav-item>
            @endcan

            @can('viewAny', App\Models\Patient::class)
                <x-partials.nav-item href="{{ route('patients.index') }}" icon="fas fa-users" :active="request()->routeIs('patients.*')">
                    Patients
                </x-partials.nav-item>
            @endcan

            @can('viewAny', App\Models\Appointment::class)
                <x-partials.nav-item href="{{ route('appointments.index') }}" icon="fas fa-calendar-check"
                    :active="request()->routeIs('appointments.*')">
                    Appointments
                </x-partials.nav-item>
            @endcan

            @can('viewAny', App\Models\PatientVisit::class)
                <x-partials.nav-item href="{{ route('patient-visits.index') }}" icon="fas fa-calendar-check"
                    :active="request()->routeIs('patient-visits.*')">
                    Patient Visits
                </x-partials.nav-item>
            @endcan

            @can('viewAny', App\Models\Inventory::class)
                <x-partials.nav-item href="{{ route('inventory.index') }}" icon="fas fa-boxes" :active="request()->routeIs('inventory.*')">
                    Inventory
                </x-partials.nav-item>
            @endcan

            @can('viewAny', App\Models\AuditLog::class)
                <x-partials.nav-item href="{{ route('audit-logs.index') }}" icon="fas fa-history" :active="request()->routeIs('audit-logs.*')">
                    Audit Logs
                </x-partials.nav-item>
            @endcan
        </ul>
    </nav>

    <!-- Sidebar Toggle Footer -->
    <div class="p-3 border-t border-blue-800/50 shrink-0">
        <button @click="toggleSidebarDesktop()"
            class="w-full flex items-center justify-center p-2 rounded-md hover:bg-blue-700/50 transition-colors duration-200">
            <i class="fas transition-transform duration-300"
                :class="{ 'fa-chevron-left': !sidebarCollapsed, 'fa-chevron-right': sidebarCollapsed }"></i>
            <span class="ml-3 font-semibold sidebar-text" x-show="!sidebarCollapsed">Collapse</span>
        </button>
    </div>
</aside>
