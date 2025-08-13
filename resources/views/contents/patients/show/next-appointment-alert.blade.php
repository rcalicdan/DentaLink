@if ($patientStats['next_appointment'])
    <div class="mb-8">
        <div class="bg-gradient-to-r from-blue-50 to-indigo-50 border-l-4 border-blue-400 p-4 sm:p-6 rounded-r-xl shadow-sm">
            <div class="flex items-center">
                <div class="flex-shrink-0">
                    <div class="w-8 h-8 sm:w-10 sm:h-10 bg-blue-100 rounded-full flex items-center justify-center">
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