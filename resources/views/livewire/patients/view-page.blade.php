<div class="min-h-screen bg-gray-50">
    {{-- Enhanced Header with Gradient Background --}}
    <div class="bg-gradient-to-r from-blue-600 to-indigo-700 shadow-lg">
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-6 sm:py-8">
            <div class="flex flex-col sm:flex-row items-start sm:items-center justify-between space-y-4 sm:space-y-0">
                <div class="flex items-center">
                    <div>
                        <h1 class="text-2xl sm:text-3xl font-bold text-white">{{ $patient->full_name }}</h1>
                        <p class="text-blue-100 text-base sm:text-lg">Patient ID:
                            #{{ str_pad($patient->id, 6, '0', STR_PAD_LEFT) }}</p>
                        <div class="flex flex-wrap items-center mt-2 space-x-4 text-blue-100 text-sm">
                            <span class="flex items-center mt-1 sm:mt-0">
                                <svg class="w-4 h-4 mr-1" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                        d="M19 21V5a2 2 0 00-2-2H7a2 2 0 00-2 2v16m14 0h2m-2 0h-2m-2 0H7m5 0H7" />
                                </svg>
                                {{ $patient->registration_branch_name }}
                            </span>
                            <span class="flex items-center mt-1 sm:mt-0">
                                <svg class="w-4 h-4 mr-1" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                        d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z" />
                                </svg>
                                Registered {{ $patient->created_at->diffForHumans() }}
                            </span>
                        </div>
                    </div>
                </div>
                <div
                    class="flex flex-col sm:flex-row space-y-2 sm:space-y-0 sm:space-x-4 w-full sm:w-auto mt-4 sm:mt-0">
                    <a wire:navigate href="{{ route('appointments.create') }}?patient_id={{ $patient->id }}"
                        class="w-full sm:w-auto sm:min-w-[140px] inline-flex items-center justify-center px-4 py-2 border border-transparent rounded-lg shadow-lg text-sm font-medium text-white bg-gradient-to-r from-blue-600 to-indigo-600 hover:from-blue-700 hover:to-indigo-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-blue-500 transition-all duration-200 hover:shadow-xl transform hover:scale-103">
                        <svg class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z" />
                        </svg>
                        Book Appointment
                    </a>
                    <a wire:navigate href="{{ route('patients.edit', $patient) }}"
                        class="w-full sm:w-auto sm:min-w-[140px] inline-flex items-center justify-center px-4 py-2 border border-transparent rounded-lg shadow-lg text-sm font-medium text-white bg-gradient-to-r from-purple-600 to-indigo-700 hover:from-purple-700 hover:to-indigo-800 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-purple-500 transition-all duration-200 hover:shadow-xl transform hover:scale-103">
                        <svg class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z" />
                        </svg>
                        Edit Patient
                    </a>
                    <a wire:navigate href="{{ route('patients.index') }}"
                        class="w-full sm:w-auto sm:min-w-[140px] inline-flex items-center justify-center px-4 py-2 bg-white rounded-lg shadow-sm text-sm font-medium text-gray-700 hover:bg-gray-100 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-blue-500 transition-all duration-200 hover:scale-103">
                        {{-- Changed bg, text-color, removed border and icon --}}
                        Back to Patients
                    </a>
                </div>
            </div>
        </div>
    </div>

    <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-8">
        {{-- Enhanced Statistics Cards --}}
        <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 xl:grid-cols-6 gap-6 mb-8">
            {{-- Total Appointments --}}
            <div
                class="bg-white overflow-hidden shadow-lg rounded-xl border border-gray-100 hover:shadow-xl transition-all duration-300 transform hover:translate-y-[-2px] hover:scale-103 animate-fade-in">
                <div class="p-5 sm:p-6 text-center">
                    <dl>
                        <dt class="text-xs sm:text-sm font-medium text-gray-500 truncate mb-1">Total Appointments</dt>
                        <dd class="text-xl sm:text-2xl font-bold text-gray-900">
                            {{ $patientStats['total_appointments'] }}</dd>
                    </dl>
                </div>
            </div>

            {{-- Completed Appointments --}}
            <div
                class="bg-white overflow-hidden shadow-lg rounded-xl border border-gray-100 hover:shadow-xl transition-all duration-300 transform hover:translate-y-[-2px] hover:scale-103 animate-fade-in">
                <div class="p-5 sm:p-6 text-center">
                    <dl>
                        <dt class="text-xs sm:text-sm font-medium text-gray-500 truncate mb-1">Completed</dt>
                        <dd class="text-xl sm:text-2xl font-bold text-gray-900">
                            {{ $patientStats['completed_appointments'] }}</dd>
                    </dl>
                </div>
            </div>

            {{-- Pending Appointments --}}
            <div
                class="bg-white overflow-hidden shadow-lg rounded-xl border border-gray-100 hover:shadow-xl transition-all duration-300 transform hover:translate-y-[-2px] hover:scale-103 animate-fade-in">
                <div class="p-5 sm:p-6 text-center">
                    <dl>
                        <dt class="text-xs sm:text-sm font-medium text-gray-500 truncate mb-1">Pending</dt>
                        <dd class="text-xl sm:text-2xl font-bold text-gray-900">
                            {{ $patientStats['pending_appointments'] }}</dd>
                    </dl>
                </div>
            </div>

            {{-- Total Visits --}}
            <div
                class="bg-white overflow-hidden shadow-lg rounded-xl border border-gray-100 hover:shadow-xl transition-all duration-300 transform hover:translate-y-[-2px] hover:scale-103 animate-fade-in">
                <div class="p-5 sm:p-6 text-center">
                    <dl>
                        <dt class="text-xs sm:text-sm font-medium text-gray-500 truncate mb-1">Total Visits</dt>
                        <dd class="text-xl sm:text-2xl font-bold text-gray-900">{{ $patientStats['total_visits'] }}
                        </dd>
                    </dl>
                </div>
            </div>

            {{-- Total Spent --}}
            <div
                class="bg-white overflow-hidden shadow-lg rounded-xl border border-gray-100 hover:shadow-xl transition-all duration-300 transform hover:translate-y-[-2px] hover:scale-103 animate-fade-in">
                <div class="p-5 sm:p-6 text-center">
                    <dl>
                        <dt class="text-xs sm:text-sm font-medium text-gray-500 truncate mb-1">Total Spent</dt>
                        <dd class="text-xl sm:text-2xl font-bold text-gray-900">
                            ₱{{ number_format($patientStats['total_spent'], 2) }}</dd>
                    </dl>
                </div>
            </div>

            {{-- Last Visit --}}
            <div
                class="bg-white overflow-hidden shadow-lg rounded-xl border border-gray-100 hover:shadow-xl transition-all duration-300 transform hover:translate-y-[-2px] hover:scale-103 animate-fade-in">
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

        {{-- Next Appointment Alert --}}
        @if ($patientStats['next_appointment'])
            <div class="mb-8">
                <div
                    class="bg-gradient-to-r from-blue-50 to-indigo-50 border-l-4 border-blue-400 p-4 sm:p-6 rounded-r-xl shadow-sm">
                    <div class="flex items-center">
                        <div class="flex-shrink-0">
                            <div
                                class="w-8 h-8 sm:w-10 sm:h-10 bg-blue-100 rounded-full flex items-center justify-center">
                                <svg class="w-4 h-4 sm:w-5 sm:h-5 text-blue-600" fill="none" stroke="currentColor"
                                    viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                        d="M13 16h-1v-4h-1m1-4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z" />
                                </svg>
                            </div>
                        </div>
                        <div class="ml-3 sm:ml-4">
                            <h3 class="text-base sm:text-lg font-medium text-blue-900">Upcoming Appointment</h3>
                            <p class="text-blue-700 text-sm sm:text-base">
                                Next appointment scheduled for <span
                                    class="font-semibold">{{ $patientStats['next_appointment']->appointment_date->format('l, F d, Y') }}</span>
                                at Queue #{{ $patientStats['next_appointment']->queue_number }}
                            </p>
                        </div>
                    </div>
                </div>
            </div>
        @endif

        {{-- Enhanced Tabs Navigation --}}
        <div class="mb-8">
            <div class="border-b border-gray-200 bg-white rounded-t-xl shadow-sm">
                <nav class="-mb-px flex flex-wrap sm:space-x-8 space-x-4 px-4 sm:px-6">
                    <button wire:click="setActiveTab('overview')"
                        class="border-transparent text-gray-500 hover:text-gray-700 hover:border-gray-300 whitespace-nowrap py-3 sm:py-4 px-1 border-b-2 font-medium text-sm transition-colors duration-200 {{ $activeTab === 'overview' ? 'border-blue-500 text-blue-600' : '' }}">
                        <div class="flex items-center">
                            <svg class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                    d="M9 19v-6a2 2 0 00-2-2H5a2 2 0 00-2 2v6a2 2 0 002 2h2a2 2 0 002-2zm0 0V9a2 2 0 012-2h2a2 2 0 012 2v10m-6 0a2 2 0 002 2h2a2 2 0 002-2m0 0V5a2 2 0 012-2h2a2 2 0 012 2v14a2 2 0 01-2 2h-2a2 2 0 01-2-2z" />
                            </svg>
                            Overview
                        </div>
                    </button>
                    <button wire:click="setActiveTab('appointments')"
                        class="border-transparent text-gray-500 hover:text-gray-700 hover:border-gray-300 whitespace-nowrap py-3 sm:py-4 px-1 border-b-2 font-medium text-sm transition-colors duration-200 {{ $activeTab === 'appointments' ? 'border-blue-500 text-blue-600' : '' }}">
                        <div class="flex items-center">
                            <svg class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                    d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z" />
                            </svg>
                            Appointments
                            <span
                                class="ml-2 inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-blue-100 text-blue-800">
                                {{ $patientStats['total_appointments'] }}
                            </span>
                        </div>
                    </button>
                    <button wire:click="setActiveTab('visits')"
                        class="border-transparent text-gray-500 hover:text-gray-700 hover:border-gray-300 whitespace-nowrap py-3 sm:py-4 px-1 border-b-2 font-medium text-sm transition-colors duration-200 {{ $activeTab === 'visits' ? 'border-blue-500 text-blue-600' : '' }}">
                        <div class="flex items-center">
                            <svg class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                    d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z" />
                            </svg>
                            Visits
                            <span
                                class="ml-2 inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-green-100 text-green-800">
                                {{ $patientStats['total_visits'] }}
                            </span>
                        </div>
                    </button>
                </nav>
            </div>
        </div>

        {{-- Tab Content --}}
        <div class="tab-content">
            {{-- Overview Tab --}}
            @if ($activeTab === 'overview')
                <div class="grid grid-cols-1 xl:grid-cols-3 gap-8">
                    {{-- Patient Information Card --}}
                    <div class="xl:col-span-2">
                        <div class="bg-white shadow-xl rounded-2xl border border-gray-100 overflow-hidden">
                            <div
                                class="bg-gradient-to-r from-gray-50 to-white px-6 sm:px-8 py-4 sm:py-6 border-b border-gray-100">
                                <div class="flex items-center">
                                    <div class="flex-shrink-0">
                                        <div
                                            class="w-10 h-10 sm:w-12 sm:h-12 bg-gradient-to-r from-blue-500 to-indigo-600 rounded-xl flex items-center justify-center shadow-lg">
                                            <svg class="w-5 h-5 sm:w-6 sm:h-6 text-white" fill="none"
                                                stroke="currentColor" viewBox="0 0 24 24">
                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                                    d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z" />
                                            </svg>
                                        </div>
                                    </div>
                                    <div class="ml-3 sm:ml-4">
                                        <h3 class="text-lg sm:text-xl font-semibold text-gray-900">Patient Information
                                        </h3>
                                        <p class="text-gray-600 text-sm">Personal details and contact information</p>
                                    </div>
                                </div>
                            </div>
                            <div class="px-6 sm:px-8 py-6">
                                <dl class="grid grid-cols-1 md:grid-cols-2 gap-y-6 gap-x-4">
                                    <div class="group">
                                        <dt class="text-sm font-medium text-gray-500 mb-1 flex items-center">
                                            <svg class="w-4 h-4 mr-2 text-gray-400" fill="none"
                                                stroke="currentColor" viewBox="0 0 24 24">
                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                                    d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z" />
                                            </svg>
                                            Full Name
                                        </dt>
                                        <dd
                                            class="text-base sm:text-lg font-semibold text-gray-900 group-hover:text-blue-600 transition-colors">
                                            {{ $patient->full_name }}</dd>
                                    </div>

                                    <div class="group">
                                        <dt class="text-sm font-medium text-gray-500 mb-1 flex items-center">
                                            <svg class="w-4 h-4 mr-2 text-gray-400" fill="none"
                                                stroke="currentColor" viewBox="0 0 24 24">
                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                                    d="M3 5a2 2 0 012-2h3.28a1 1 0 01.948.684l1.498 4.493a1 1 0 01-.502 1.21l-2.257 1.13a11.042 11.042 0 005.516 5.516l1.13-2.257a1 1 0 011.21-.502l4.493 1.498a1 1 0 01.684.949V19a2 2 0 01-2 2h-1C9.716 21 3 14.284 3 6V5z" />
                                            </svg>
                                            Phone
                                        </dt>
                                        <dd
                                            class="text-base sm:text-lg font-semibold text-gray-900 group-hover:text-blue-600 transition-colors">
                                            <a href="tel:{{ $patient->phone }}"
                                                class="hover:underline">{{ $patient->phone }}</a>
                                        </dd>
                                    </div>

                                    <div class="group">
                                        <dt class="text-sm font-medium text-gray-500 mb-1 flex items-center">
                                            <svg class="w-4 h-4 mr-2 text-gray-400" fill="none"
                                                stroke="currentColor" viewBox="0 0 24 24">
                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                                    d="M3 8l7.89 5.26a2 2 0 002.22 0L21 8M5 19h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v10a2 2 0 002 2z" />
                                            </svg>
                                            Email
                                        </dt>
                                        <dd
                                            class="text-base sm:text-lg font-semibold text-gray-900 group-hover:text-blue-600 transition-colors">
                                            @if ($patient->email)
                                                <a href="mailto:{{ $patient->email }}"
                                                    class="hover:underline">{{ $patient->email }}</a>
                                            @else
                                                <span class="text-gray-400 italic">Not provided</span>
                                            @endif
                                        </dd>
                                    </div>

                                    <div class="group">
                                        <dt class="text-sm font-medium text-gray-500 mb-1 flex items-center">
                                            <svg class="w-4 h-4 mr-2 text-gray-400" fill="none"
                                                stroke="currentColor" viewBox="0 0 24 24">
                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                                    d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z" />
                                            </svg>
                                            Date of Birth
                                        </dt>
                                        <dd
                                            class="text-base sm:text-lg font-semibold text-gray-900 group-hover:text-blue-600 transition-colors">
                                            @if ($patient->date_of_birth)
                                                {{ $patient->date_of_birth->format('M d, Y') }}
                                                <span
                                                    class="text-sm text-gray-500 ml-2">({{ $patient->date_of_birth->age }}
                                                    years old)</span>
                                            @else
                                                <span class="text-gray-400 italic">Not provided</span>
                                            @endif
                                        </dd>
                                    </div>

                                    <div class="group md:col-span-2">
                                        <dt class="text-sm font-medium text-gray-500 mb-1 flex items-center">
                                            <svg class="w-4 h-4 mr-2 text-gray-400" fill="none"
                                                stroke="currentColor" viewBox="0 0 24 24">
                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                                    d="M17.657 16.657L13.414 20.9a1.998 1.998 0 01-2.827 0l-4.244-4.243a8 8 0 1111.314 0z" />
                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                                    d="M15 11a3 3 0 11-6 0 3 3 0 016 0z" />
                                            </svg>
                                            Address
                                        </dt>
                                        <dd
                                            class="text-base sm:text-lg font-semibold text-gray-900 group-hover:text-blue-600 transition-colors">
                                            {{ $patient->address ?: 'Not provided' }}
                                        </dd>
                                    </div>

                                    <div class="group">
                                        <dt class="text-sm font-medium text-gray-500 mb-1 flex items-center">
                                            <svg class="w-4 h-4 mr-2 text-gray-400" fill="none"
                                                stroke="currentColor" viewBox="0 0 24 24">
                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                                    d="M19 21V5a2 2 0 00-2-2H7a2 2 0 00-2 2v16m14 0h2m-2 0h-2m-2 0H7m5 0H7" />
                                            </svg>
                                            Registration Branch
                                        </dt>
                                        <dd
                                            class="text-base sm:text-lg font-semibold text-gray-900 group-hover:text-blue-600 transition-colors">
                                            {{ $patient->registration_branch_name }}</dd>
                                    </div>

                                    <div class="group">
                                        <dt class="text-sm font-medium text-gray-500 mb-1 flex items-center">
                                            <svg class="w-4 h-4 mr-2 text-gray-400" fill="none"
                                                stroke="currentColor" viewBox="0 0 24 24">
                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                                    d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z" />
                                            </svg>
                                            Registered
                                        </dt>
                                        <dd
                                            class="text-base sm:text-lg font-semibold text-gray-900 group-hover:text-blue-600 transition-colors">
                                            {{ $patient->created_at->format('M d, Y') }}
                                            <span
                                                class="text-sm text-gray-500 ml-2">({{ $patient->created_at->diffForHumans() }})</span>
                                        </dd>
                                    </div>
                                </dl>
                            </div>
                        </div>
                    </div>

                    {{-- Recent Activity & Quick Actions --}}
                    <div class="space-y-6 sm:space-y-8">
                        {{-- Recent Activity --}}
                        <div class="bg-white shadow-xl rounded-2xl border border-gray-100 overflow-hidden">
                            <div class="bg-gradient-to-r from-gray-50 to-white px-6 py-4 border-b border-gray-100">
                                <h3 class="text-lg font-semibold text-gray-900">Recent Activity</h3>
                                <p class="text-sm text-gray-600 mt-1">Latest appointments and visits</p>
                            </div>
                            <div class="p-4 sm:p-6">
                                <div class="flow-root">
                                    <ul role="list" class="space-y-4">
                                        @php
                                            $recentAppointments = $patient
                                                ->appointments()
                                                ->with('branch')
                                                ->orderBy('appointment_date', 'desc')
                                                ->take(3)
                                                ->get();
                                            $recentVisits = $patient
                                                ->patientVisits()
                                                ->with('branch')
                                                ->orderBy('visit_date', 'desc')
                                                ->take(3)
                                                ->get();
                                            $recentActivity = collect([$recentAppointments, $recentVisits])
                                                ->flatten()
                                                ->sortByDesc('created_at')
                                                ->take(5);
                                        @endphp

                                        @forelse($recentActivity as $activity)
                                            <li class="relative">
                                                <div
                                                    class="flex space-x-3 hover:bg-gray-50 p-3 rounded-lg transition-colors duration-200">
                                                    <div class="flex-shrink-0">
                                                        @if ($activity instanceof App\Models\Appointment)
                                                            <div
                                                                class="w-9 h-9 sm:w-10 sm:h-10 rounded-full bg-gradient-to-r from-blue-100 to-blue-200 flex items-center justify-center">
                                                                <svg class="w-4 h-4 sm:w-5 sm:h-5 text-blue-600"
                                                                    fill="none" stroke="currentColor"
                                                                    viewBox="0 0 24 24">
                                                                    <path stroke-linecap="round"
                                                                        stroke-linejoin="round" stroke-width="2"
                                                                        d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z" />
                                                                </svg>
                                                            </div>
                                                        @else
                                                            <div
                                                                class="w-9 h-9 sm:w-10 sm:h-10 rounded-full bg-gradient-to-r from-green-100 to-green-200 flex items-center justify-center">
                                                                <svg class="w-4 h-4 sm:w-5 sm:h-5 text-green-600"
                                                                    fill="none" stroke="currentColor"
                                                                    viewBox="0 0 24 24">
                                                                    <path stroke-linecap="round"
                                                                        stroke-linejoin="round" stroke-width="2"
                                                                        d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z" />
                                                                </svg>
                                                            </div>
                                                        @endif
                                                    </div>
                                                    <div class="flex-1 min-w-0">
                                                        <div
                                                            class="flex flex-col sm:flex-row items-start sm:items-center justify-between">
                                                            <div>
                                                                @if ($activity instanceof App\Models\Appointment)
                                                                    <p class="text-sm font-medium text-gray-900">
                                                                        Appointment scheduled
                                                                    </p>
                                                                    <p class="text-xs text-gray-600">
                                                                        {{ $activity->appointment_date->format('M d, Y') }}
                                                                        - Queue #{{ $activity->queue_number }}
                                                                    </p>
                                                                @else
                                                                    <p class="text-sm font-medium text-gray-900">
                                                                        Visit completed
                                                                    </p>
                                                                    <p class="text-xs text-gray-600">
                                                                        {{ $activity->visit_date->format('M d, Y \a\t g:i A') }}
                                                                        -
                                                                        ₱{{ number_format($activity->total_amount_paid, 2) }}
                                                                    </p>
                                                                @endif
                                                            </div>
                                                            <div class="text-left sm:text-right mt-1 sm:mt-0">
                                                                <time class="text-xs text-gray-500">
                                                                    {{ $activity->created_at->diffForHumans() }}
                                                                </time>
                                                            </div>
                                                        </div>
                                                    </div>
                                                </div>
                                            </li>
                                        @empty
                                            <li class="text-center py-8">
                                                <svg class="mx-auto h-12 w-12 text-gray-300" fill="none"
                                                    stroke="currentColor" viewBox="0 0 24 24">
                                                    <path stroke-linecap="round" stroke-linejoin="round"
                                                        stroke-width="2"
                                                        d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z" />
                                                </svg>
                                                <h3 class="mt-2 text-sm font-medium text-gray-900">No recent activity
                                                </h3>
                                                <p class="mt-1 text-sm text-gray-500">No appointments or visits
                                                    recorded yet.</p>
                                            </li>
                                        @endforelse
                                    </ul>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            @endif

            {{-- Appointments Tab --}}
            @if ($activeTab === 'appointments')
                <div class="bg-white shadow-xl rounded-2xl border border-gray-100 overflow-hidden">
                    <div
                        class="bg-gradient-to-r from-blue-50 to-indigo-50 px-6 sm:px-8 py-4 sm:py-6 border-b border-gray-100">
                        <div
                            class="flex flex-col sm:flex-row justify-between items-start sm:items-center space-y-3 sm:space-y-0">
                            <div>
                                <h3 class="text-lg sm:text-xl font-semibold text-gray-900">Appointments History</h3>
                                <p class="text-gray-600 text-sm mt-1">Complete record of all scheduled appointments</p>
                            </div>
                            <a wire:navigate href="{{ route('appointments.create') }}?patient_id={{ $patient->id }}"
                                class="w-full sm:w-auto inline-flex items-center justify-center px-4 py-2 border border-transparent rounded-xl text-sm font-medium text-white bg-gradient-to-r from-blue-600 to-indigo-600 hover:from-blue-700 hover:to-indigo-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-blue-500 shadow-lg hover:shadow-xl transition-all duration-200 transform hover:translate-y-[-2px]">
                                <svg class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                        d="M12 6v6m0 0v6m0-6h6m-6 0H6" />
                                </svg>
                                New Appointment
                            </a>
                        </div>
                    </div>
                    <div class="overflow-x-auto">
                        @if ($appointments->count() > 0)
                            <table class="min-w-full divide-y divide-gray-200">
                                <thead class="bg-gray-50">
                                    <tr>
                                        <th
                                            class="px-4 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                                            Date & Queue</th>
                                        <th
                                            class="px-4 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                                            Status</th>
                                        <th
                                            class="px-4 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                                            Branch</th>
                                        <th
                                            class="px-4 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                                            Reason</th>
                                        <th
                                            class="px-4 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                                            Created By</th>
                                        <th
                                            class="px-4 py-3 text-right text-xs font-medium text-gray-500 uppercase tracking-wider">
                                            Actions</th>
                                    </tr>
                                </thead>
                                <tbody class="bg-white divide-y divide-gray-200">
                                    @foreach ($appointments as $appointment)
                                        <tr class="hover:bg-gray-50 transition-colors duration-200">
                                            <td class="px-4 py-4 whitespace-nowrap">
                                                <div class="flex items-center">
                                                    <div class="flex-shrink-0 w-9 h-9 sm:w-10 sm:h-10">
                                                        <div
                                                            class="w-9 h-9 sm:w-10 sm:h-10 rounded-full bg-gradient-to-r from-blue-100 to-blue-200 flex items-center justify-center">
                                                            <span
                                                                class="text-xs sm:text-sm font-semibold text-blue-700">#{{ $appointment->queue_number }}</span>
                                                        </div>
                                                    </div>
                                                    <div class="ml-3 sm:ml-4">
                                                        <div class="text-sm font-medium text-gray-900">
                                                            {{ $appointment->appointment_date->format('M d, Y') }}
                                                        </div>
                                                        <div class="text-xs text-gray-500">
                                                            {{ $appointment->appointment_date->format('l') }}
                                                        </div>
                                                    </div>
                                                </div>
                                            </td>
                                            <td class="px-4 py-4 whitespace-nowrap">
                                                <span
                                                    class="inline-flex items-center px-2 py-0.5 sm:px-3 sm:py-1 rounded-full text-xs font-medium {{ $appointment->status_badge }}">
                                                    <svg class="w-2 h-2 mr-1" fill="currentColor" viewBox="0 0 8 8">
                                                        <circle cx="4" cy="4" r="3" />
                                                    </svg>
                                                    {{ ucwords(str_replace('_', ' ', $appointment->status->value)) }}
                                                </span>
                                            </td>
                                            <td class="px-4 py-4 whitespace-nowrap text-sm text-gray-900">
                                                <div class="flex items-center">
                                                    <svg class="w-4 h-4 mr-2 text-gray-400" fill="none"
                                                        stroke="currentColor" viewBox="0 0 24 24">
                                                        <path stroke-linecap="round" stroke-linejoin="round"
                                                            stroke-width="2"
                                                            d="M19 21V5a2 2 0 00-2-2H7a2 2 0 00-2 2v16m14 0h2m-2 0h-2m-2 0H7m5 0H7" />
                                                    </svg>
                                                    {{ $appointment->branch->name }}
                                                </div>
                                            </td>
                                            <td class="px-4 py-4 text-sm text-gray-900 max-w-xs truncate">
                                                {{ $appointment->reason ?: 'Not specified' }}
                                            </td>
                                            <td class="px-4 py-4 whitespace-nowrap text-sm text-gray-500">
                                                {{ $appointment->creator->name ?? 'Unknown' }}
                                            </td>
                                            <td class="px-4 py-4 whitespace-nowrap text-right text-sm font-medium">
                                                <div class="flex items-center justify-end space-x-2">
                                                    <a wire:navigate
                                                        href="{{ route('appointments.view', $appointment) }}"
                                                        class="text-blue-600 hover:text-blue-900 font-medium hover:underline">
                                                        View
                                                    </a>
                                                    @if ($appointment->canBeModified())
                                                        <a wire:navigate
                                                            href="{{ route('appointments.edit', $appointment) }}"
                                                            class="text-indigo-600 hover:text-indigo-900 font-medium hover:underline">
                                                            Edit
                                                        </a>
                                                    @endif
                                                </div>
                                            </td>
                                        </tr>
                                    @endforeach
                                </tbody>
                            </table>
                            <div class="px-4 sm:px-6 py-4 bg-gray-50 border-t border-gray-200">
                                {{ $appointments->links() }}
                            </div>
                        @else
                            <div class="px-4 sm:px-6 py-10 sm:py-12 text-center">
                                <svg class="mx-auto h-14 w-14 sm:h-16 sm:w-16 text-gray-300" fill="none"
                                    stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1"
                                        d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z" />
                                </svg>
                                <h3 class="mt-3 sm:mt-4 text-base sm:text-lg font-medium text-gray-900">No appointments
                                    scheduled</h3>
                                <p class="mt-1 sm:mt-2 text-sm text-gray-500">This patient hasn't scheduled any
                                    appointments
                                    yet.</p>
                                <div class="mt-5 sm:mt-6">
                                    <a wire:navigate
                                        href="{{ route('appointments.create') }}?patient_id={{ $patient->id }}"
                                        class="inline-flex items-center px-4 py-2 border border-transparent rounded-xl text-sm font-medium text-white bg-gradient-to-r from-blue-600 to-indigo-600 hover:from-blue-700 hover:to-indigo-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-blue-500 shadow-lg hover:shadow-xl transition-all duration-200 transform hover:translate-y-[-2px]">
                                        <svg class="w-4 h-4 mr-2" fill="none" stroke="currentColor"
                                            viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                                d="M12 6v6m0 0v6m0-6h6m-6 0H6" />
                                        </svg>
                                        Schedule First Appointment
                                    </a>
                                </div>
                            </div>
                        @endif
                    </div>
                </div>
            @endif

            {{-- Visits Tab --}}
            @if ($activeTab === 'visits')
                <div class="bg-white shadow-xl rounded-2xl border border-gray-100 overflow-hidden">
                    <div
                        class="bg-gradient-to-r from-green-50 to-emerald-50 px-6 sm:px-8 py-4 sm:py-6 border-b border-gray-100">
                        <div
                            class="flex flex-col sm:flex-row justify-between items-start sm:items-center space-y-3 sm:space-y-0">
                            <div>
                                <h3 class="text-lg sm:text-xl font-semibold text-gray-900">Visits History</h3>
                                <p class="text-gray-600 text-sm mt-1">Complete record of all patient visits and
                                    treatments</p>
                            </div>
                            <a wire:navigate
                                href="{{ route('patient-visits.create') }}?patient_id={{ $patient->id }}"
                                class="w-full sm:w-auto inline-flex items-center justify-center px-4 py-2 border border-transparent rounded-xl text-sm font-medium text-white bg-gradient-to-r from-green-600 to-emerald-600 hover:from-green-700 hover:to-emerald-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-green-500 shadow-lg hover:shadow-xl transition-all duration-200 transform hover:translate-y-[-2px]">
                                <svg class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                        d="M12 6v6m0 0v6m0-6h6m-6 0H6" />
                                </svg>
                                New Visit
                            </a>
                        </div>
                    </div>
                    <div class="divide-y divide-gray-200">
                        @if ($visits->count() > 0)
                            @foreach ($visits as $visit)
                                <div class="p-6 sm:p-8 hover:bg-gray-50 transition-colors duration-200">
                                    {{-- Visit Header --}}
                                    <div
                                        class="flex flex-col sm:flex-row justify-between items-start sm:items-center mb-4 sm:mb-6 space-y-3 sm:space-y-0">
                                        <div class="flex items-start space-x-3 sm:space-x-4">
                                            <div class="flex-shrink-0">
                                                <div
                                                    class="w-10 h-10 sm:w-12 sm:h-12 bg-gradient-to-r from-green-100 to-emerald-200 rounded-xl flex items-center justify-center">
                                                    <svg class="w-5 h-5 sm:w-6 sm:h-6 text-green-600"
                                                        fill="currentColor" viewBox="0 0 24 24">
                                                        <path stroke-linecap="round" stroke-linejoin="round"
                                                            stroke-width="2"
                                                            d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z" />
                                                    </svg>
                                                </div>
                                            </div>
                                            <div>
                                                <h4 class="text-lg sm:text-xl font-semibold text-gray-900">
                                                    {{ $visit->visit_date->format('F d, Y') }}
                                                </h4>
                                                <p class="text-sm text-gray-600 mt-1">
                                                    {{ $visit->visit_date->format('g:i A') }} •
                                                    {{ $visit->branch->name }} •
                                                    <span
                                                        class="inline-flex items-center px-2 py-0.5 rounded-full text-xs font-medium
                                                       {{ $visit->visit_type === 'appointment' ? 'bg-blue-100 text-blue-800' : 'bg-orange-100 text-orange-800' }}">
                                                        {{ ucfirst($visit->visit_type) }}
                                                    </span>
                                                    @if ($visit->appointment_id)
                                                        • Appointment:
                                                        {{ $visit->appointment->appointment_date->format('M d, Y') }}
                                                    @endif
                                                </p>
                                            </div>
                                        </div>
                                        <div class="text-left sm:text-right w-full sm:w-auto">
                                            <div class="text-xl sm:text-2xl font-bold text-gray-900">
                                                ₱{{ number_format($visit->total_amount_paid, 2) }}</div>
                                            <p class="text-sm text-gray-500">Total Paid</p>
                                        </div>
                                    </div>

                                    {{-- Services Grid --}}
                                    @if ($visit->patientVisitServices->count() > 0)
                                        <div class="mb-6">
                                            <h5
                                                class="text-base sm:text-lg font-medium text-gray-900 mb-3 sm:mb-4 flex items-center">
                                                <svg class="w-4 h-4 sm:w-5 sm:h-5 mr-2 text-gray-600"
                                                    fill="currentColor" viewBox="0 0 24 24">
                                                    <path stroke-linecap="round" stroke-linejoin="round"
                                                        stroke-width="2"
                                                        d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2" />
                                                </svg>
                                                Services Provided
                                            </h5>
                                            <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-4">
                                                @foreach ($visit->patientVisitServices as $service)
                                                    <div
                                                        class="bg-gradient-to-r from-gray-50 to-white border border-gray-200 rounded-xl p-4 shadow-sm hover:shadow-md transition-shadow duration-200">
                                                        <div class="flex justify-between items-start mb-2">
                                                            <div class="flex-1">
                                                                <h6 class="text-sm font-semibold text-gray-900 mb-1">
                                                                    {{ $service->dentalService->name }}
                                                                </h6>
                                                                <div
                                                                    class="flex flex-wrap items-center text-xs text-gray-600 space-x-2">
                                                                    <span
                                                                        class="bg-blue-100 text-blue-700 px-2 py-1 rounded-full mt-1">
                                                                        Qty: {{ $service->quantity }}
                                                                    </span>
                                                                    <span class="mt-1">×</span>
                                                                    <span
                                                                        class="bg-green-100 text-green-700 px-2 py-1 rounded-full mt-1">
                                                                        ₱{{ number_format($service->service_price, 2) }}
                                                                    </span>
                                                                </div>
                                                                @if ($service->service_notes)
                                                                    <p class="text-xs text-gray-500 mt-2 italic">
                                                                        {{ $service->service_notes }}</p>
                                                                @endif
                                                            </div>
                                                            <div class="text-right ml-4">
                                                                <span
                                                                    class="text-base sm:text-lg font-bold text-gray-900">
                                                                    ₱{{ number_format($service->total_price, 2) }}
                                                                </span>
                                                            </div>
                                                        </div>
                                                    </div>
                                                @endforeach
                                            </div>
                                        </div>
                                    @endif

                                    {{-- Visit Notes --}}
                                    @if ($visit->notes)
                                        <div class="mb-6">
                                            <h5
                                                class="text-base sm:text-lg font-medium text-gray-900 mb-2 sm:mb-3 flex items-center">
                                                <svg class="w-4 h-4 sm:w-5 sm:h-5 mr-2 text-gray-600"
                                                    fill="currentColor" viewBox="0 0 24 24">
                                                    <path stroke-linecap="round" stroke-linejoin="round"
                                                        stroke-width="2"
                                                        d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z" />
                                                </svg>
                                                Visit Notes
                                            </h5>
                                            <div
                                                class="bg-gradient-to-r from-blue-50 to-indigo-50 border border-blue-200 rounded-xl p-4">
                                                <p class="text-sm text-gray-700 leading-relaxed">{{ $visit->notes }}
                                                </p>
                                            </div>
                                        </div>
                                    @endif

                                    {{-- Visit Footer --}}
                                    <div
                                        class="flex flex-col sm:flex-row justify-between items-start sm:items-center pt-3 sm:pt-4 border-t border-gray-200 space-y-3 sm:space-y-0">
                                        <div
                                            class="flex flex-wrap items-center text-xs sm:text-sm text-gray-500 space-x-0 sm:space-x-4 space-y-1 sm:space-y-0">
                                            <span class="flex items-center">
                                                <svg class="w-3 h-3 sm:w-4 sm:h-4 mr-1" fill="currentColor"
                                                    viewBox="0 0 24 24">
                                                    <path stroke-linecap="round" stroke-linejoin="round"
                                                        stroke-width="2"
                                                        d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z" />
                                                </svg>
                                                Created by: {{ $visit->creator->name ?? 'Unknown' }}
                                            </span>
                                            <span class="flex items-center">
                                                <svg class="w-3 h-3 sm:w-4 sm:h-4 mr-1" fill="currentColor"
                                                    viewBox="0 0 24 24">
                                                    <path stroke-linecap="round" stroke-linejoin="round"
                                                        stroke-width="2"
                                                        d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z" />
                                                </svg>
                                                {{ $visit->created_at->diffForHumans() }}
                                            </span>
                                        </div>
                                        <div class="flex items-center space-x-3 mt-3 sm:mt-0">
                                            <a wire:navigate href="{{ route('patient-visits.edit', $visit) }}"
                                                class="inline-flex items-center px-3 py-1.5 border border-gray-300 rounded-lg text-xs font-medium text-gray-700 bg-white hover:bg-gray-50 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-indigo-500 transition-all duration-200">
                                                <svg class="w-3 h-3 mr-1" fill="currentColor" viewBox="0 0 24 24">
                                                    <path stroke-linecap="round" stroke-linejoin="round"
                                                        stroke-width="2"
                                                        d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z" />
                                                </svg>
                                                Edit Visit
                                            </a>
                                        </div>
                                    </div>
                                </div>
                            @endforeach

                            {{-- Pagination --}}
                            <div class="px-6 sm:px-8 py-4 sm:py-6 bg-gray-50 border-t border-gray-200">
                                {{ $visits->links() }}
                            </div>
                        @else
                            <div class="px-4 sm:px-8 py-14 sm:py-16 text-center">
                                <div
                                    class="mx-auto w-20 h-20 sm:w-24 sm:h-24 bg-gradient-to-r from-gray-100 to-gray-200 rounded-full flex items-center justify-center mb-4 sm:mb-6">
                                    <svg class="w-10 h-10 sm:w-12 sm:h-12 text-gray-400" fill="currentColor"
                                        viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1"
                                            d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z" />
                                    </svg>
                                </div>
                                <h3 class="text-lg sm:text-xl font-semibold text-gray-900 mb-2">No visits recorded</h3>
                                <p class="text-gray-500 mb-6 sm:mb-8 max-w-sm mx-auto">This patient hasn't had any
                                    visits yet.
                                    Record their first visit to start tracking their treatment history.</p>
                                <a wire:navigate
                                    href="{{ route('patient-visits.create') }}?patient_id={{ $patient->id }}"
                                    class="inline-flex items-center px-5 py-2.5 sm:px-6 sm:py-3 border border-transparent rounded-xl text-sm font-medium text-white bg-gradient-to-r from-green-600 to-emerald-600 hover:from-green-700 hover:to-emerald-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-green-500 shadow-lg hover:shadow-xl transition-all duration-200 transform hover:translate-y-[-2px]">
                                    <svg class="w-4 h-4 sm:w-5 sm:h-5 mr-2" fill="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                            d="M12 6v6m0 0v6m0-6h6m-6 0H6" />
                                    </svg>
                                    Record First Visit
                                </a>
                            </div>
                        @endif
                    </div>
                </div>
            @endif
        </div>
    </div>

    {{-- Loading State Overlay --}}
    <div wire:loading.flex
        class="fixed inset-0 z-50 items-center justify-center bg-black bg-opacity-50 backdrop-blur-sm">
        <div class="bg-white rounded-2xl p-6 shadow-2xl">
            <div class="flex items-center space-x-4">
                <div class="animate-spin rounded-full h-8 w-8 border-b-2 border-blue-600"></div>
                <span class="text-gray-900 font-medium">Loading...</span>
            </div>
        </div>
    </div>
</div>


@push('styles')
    {{-- Custom Styles --}}
    <style>
        @keyframes fadeInUp {
            from {
                opacity: 0;
                transform: translateY(30px);
            }

            to {
                opacity: 1;
                transform: translateY(0);
            }
        }

        .tab-content>div {
            animation: fadeInUp 0.4s ease-out;
        }

        /* Add a subtle fade-in animation for elements appearing after load, like the stat cards */
        @keyframes fadeIn {
            from {
                opacity: 0;
            }

            to {
                opacity: 1;
            }
        }

        .animate-fade-in {
            animation: fadeIn 0.5s ease-out forwards;
        }


        /* Custom scrollbar for better aesthetics */
        .overflow-x-auto::-webkit-scrollbar {
            height: 6px;
        }

        .overflow-x-auto::-webkit-scrollbar-track {
            background: #f1f5f9;
            border-radius: 3px;
        }

        .overflow-x-auto::-webkit-scrollbar-thumb {
            background: #cbd5e1;
            border-radius: 3px;
        }

        .overflow-x-auto::-webkit-scrollbar-thumb:hover {
            background: #94a3b8;
        }
    </style>
@endpush
