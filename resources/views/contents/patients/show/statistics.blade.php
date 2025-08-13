<div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 xl:grid-cols-6 gap-6 mb-8">
    {{-- Total Appointments --}}
    <div class="bg-white overflow-hidden shadow-lg rounded-xl border border-gray-100 hover:shadow-xl transition-all duration-300 transform hover:translate-y-[-2px] hover:scale-103 animate-fade-in">
        <div class="p-5 sm:p-6 text-center">
            <dl>
                <dt class="text-xs sm:text-sm font-medium text-gray-500 truncate mb-1">Total Appointments</dt>
                <dd class="text-xl sm:text-2xl font-bold text-gray-900">
                    {{ $patientStats['total_appointments'] }}</dd>
            </dl>
        </div>
    </div>

    {{-- Completed Appointments --}}
    <div class="bg-white overflow-hidden shadow-lg rounded-xl border border-gray-100 hover:shadow-xl transition-all duration-300 transform hover:translate-y-[-2px] hover:scale-103 animate-fade-in">
        <div class="p-5 sm:p-6 text-center">
            <dl>
                <dt class="text-xs sm:text-sm font-medium text-gray-500 truncate mb-1">Completed</dt>
                <dd class="text-xl sm:text-2xl font-bold text-gray-900">
                    {{ $patientStats['completed_appointments'] }}</dd>
            </dl>
        </div>
    </div>

    {{-- Pending Appointments --}}
    <div class="bg-white overflow-hidden shadow-lg rounded-xl border border-gray-100 hover:shadow-xl transition-all duration-300 transform hover:translate-y-[-2px] hover:scale-103 animate-fade-in">
        <div class="p-5 sm:p-6 text-center">
            <dl>
                <dt class="text-xs sm:text-sm font-medium text-gray-500 truncate mb-1">Pending</dt>
                <dd class="text-xl sm:text-2xl font-bold text-gray-900">
                    {{ $patientStats['pending_appointments'] }}</dd>
            </dl>
        </div>
    </div>

    {{-- Total Visits --}}
    <div class="bg-white overflow-hidden shadow-lg rounded-xl border border-gray-100 hover:shadow-xl transition-all duration-300 transform hover:translate-y-[-2px] hover:scale-103 animate-fade-in">
        <div class="p-5 sm:p-6 text-center">
            <dl>
                <dt class="text-xs sm:text-sm font-medium text-gray-500 truncate mb-1">Total Visits</dt>
                <dd class="text-xl sm:text-2xl font-bold text-gray-900">{{ $patientStats['total_visits'] }}
                </dd>
            </dl>
        </div>
    </div>

    {{-- Total Spent --}}
    <div class="bg-white overflow-hidden shadow-lg rounded-xl border border-gray-100 hover:shadow-xl transition-all duration-300 transform hover:translate-y-[-2px] hover:scale-103 animate-fade-in">
        <div class="p-5 sm:p-6 text-center">
            <dl>
                <dt class="text-xs sm:text-sm font-medium text-gray-500 truncate mb-1">Total Spent</dt>
                <dd class="text-xl sm:text-2xl font-bold text-gray-900">
                    ₱{{ number_format($patientStats['total_spent'], 2) }}</dd>
            </dl>
        </div>
    </div>

    {{-- Last Visit --}}
    <div class="bg-white overflow-hidden shadow-lg rounded-xl border border-gray-100 hover:shadow-xl transition-all duration-300 transform hover:translate-y-[-2px] hover:scale-103 animate-fade-in">
        <div class="p-5 sm:p-6 text-center">
            <dl>
                <dt class="text-xs sm:text-sm font-medium text-gray-500 truncate mb-1">Last Visit</dt>
                <dd class="text-base sm:text-lg font-bold text-gray-900">
                    {{ $patientStats['last_visit'] ? $patientStats['last_visit']->format('M d') : 'No visits' }}
                </dd>
                @if ($patientStats['last_visit'])
                    <dd class="text-xs text-gray-500">
                        {{ $patientStats['last_visit']->diffForHumans() }}</dd>
                @endif
            </dl>
        </div>
    </div>
</div>