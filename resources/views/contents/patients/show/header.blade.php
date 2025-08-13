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
            <div class="flex flex-col sm:flex-row space-y-2 sm:space-y-0 sm:space-x-4 w-full sm:w-auto mt-4 sm:mt-0">
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
                    Back to Patients
                </a>
            </div>
        </div>
    </div>
</div>