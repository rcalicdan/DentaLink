<div class="bg-white shadow-xl rounded-2xl border border-gray-100 overflow-hidden">
    <div class="bg-gradient-to-r from-blue-50 to-indigo-50 px-6 sm:px-8 py-4 sm:py-6 border-b border-gray-100">
        <div class="flex flex-col sm:flex-row justify-between items-start sm:items-center space-y-3 sm:space-y-0">
            <div>
                <h3 class="text-lg sm:text-xl font-semibold text-gray-900">Appointments History</h3>
                <p class="text-gray-600 text-sm mt-1">Complete record of all scheduled appointments</p>
            </div>
            <a wire:navigate href="{{ route('appointments.create') }}?patient_id={{ $patient->id }}"
                class="w-full sm:w-auto inline-flex items-center justify-center px-4 py-2 border border-transparent rounded-xl text-sm font-medium text-white bg-gradient-to-r from-blue-600 to-indigo-600 hover:from-blue-700 hover:to-indigo-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-blue-500 shadow-lg hover:shadow-xl transition-all duration-200 transform hover:translate-y-[-2px]">
                <svg class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 6v6m0 0v6m0-6h6m-6 0H6" />
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
                        <th class="px-4 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Date & Queue</th>
                        <th class="px-4 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Status</th>
                        <th class="px-4 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Branch</th>
                        <th class="px-4 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Reason</th>
                        <th class="px-4 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Created By</th>
                        <th class="px-4 py-3 text-right text-xs font-medium text-gray-500 uppercase tracking-wider">Actions</th>
                    </tr>
                </thead>
                <tbody class="bg-white divide-y divide-gray-200">
                    @foreach ($appointments as $appointment)
                        <tr class="hover:bg-gray-50 transition-colors duration-200">
                            <td class="px-4 py-4 whitespace-nowrap">
                                <div class="flex items-center">
                                    <div class="flex-shrink-0 w-9 h-9 sm:w-10 sm:h-10">
                                        <div class="w-9 h-9 sm:w-10 sm:h-10 rounded-full bg-gradient-to-r from-blue-100 to-blue-200 flex items-center justify-center">
                                            <span class="text-xs sm:text-sm font-semibold text-blue-700">#{{ $appointment->queue_number }}</span>
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
                                <span class="inline-flex items-center px-2 py-0.5 sm:px-3 sm:py-1 rounded-full text-xs font-medium {{ $appointment->status_badge }}">
                                    <svg class="w-2 h-2 mr-1" fill="currentColor" viewBox="0 0 8 8">
                                        <circle cx="4" cy="4" r="3" />
                                    </svg>
                                    {{ ucwords(str_replace('_', ' ', $appointment->status->value)) }}
                                </span>
                            </td>
                            <td class="px-4 py-4 whitespace-nowrap text-sm text-gray-900">
                                <div class="flex items-center">
                                    <svg class="w-4 h-4 mr-2 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
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
                                    <a wire:navigate href="{{ route('appointments.view', $appointment) }}"
                                        class="text-blue-600 hover:text-blue-900 font-medium hover:underline">
                                        View
                                    </a>
                                    @if ($appointment->canBeModified())
                                        <a wire:navigate href="{{ route('appointments.edit', $appointment) }}"
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
                <svg class="mx-auto h-14 w-14 sm:h-16 sm:w-16 text-gray-300" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1"
                        d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z" />
                </svg>
                <h3 class="mt-3 sm:mt-4 text-base sm:text-lg font-medium text-gray-900">No appointments scheduled</h3>
                <p class="mt-1 sm:mt-2 text-sm text-gray-500">This patient hasn't scheduled any appointments yet.</p>
                <div class="mt-5 sm:mt-6">
                    <a wire:navigate href="{{ route('appointments.create') }}?patient_id={{ $patient->id }}"
                        class="inline-flex items-center px-4 py-2 border border-transparent rounded-xl text-sm font-medium text-white bg-gradient-to-r from-blue-600 to-indigo-600 hover:from-blue-700 hover:to-indigo-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-blue-500 shadow-lg hover:shadow-xl transition-all duration-200 transform hover:translate-y-[-2px]">
                        <svg class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 6v6m0 0v6m0-6h6m-6 0H6" />
                        </svg>
                        Schedule First Appointment
                    </a>
                </div>
            </div>
        @endif
    </div>
</div>