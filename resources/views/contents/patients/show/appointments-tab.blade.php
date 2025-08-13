<div class="bg-white shadow-xl rounded-2xl border border-gray-100 overflow-hidden">
    <!-- Header Section -->
    <div class="bg-gradient-to-r from-blue-50 to-indigo-50 px-4 sm:px-6 lg:px-8 py-4 sm:py-6 border-b border-gray-100">
        <div class="flex flex-col sm:flex-row justify-between items-start sm:items-center space-y-3 sm:space-y-0">
            <div>
                <h3 class="text-xl sm:text-2xl font-semibold text-gray-900">Appointments History</h3>
                <p class="text-gray-600 text-sm mt-1">Complete record of all scheduled appointments</p>
            </div>
            <a wire:navigate href="{{ route('appointments.create') }}?patient_id={{ $patient->id }}"
                class="w-full sm:w-auto inline-flex items-center justify-center px-5 py-2.5 border border-transparent rounded-xl text-sm font-semibold text-white bg-gradient-to-r from-blue-600 to-indigo-600 hover:from-blue-700 hover:to-indigo-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-blue-500 shadow-lg hover:shadow-xl transition-all duration-200 transform hover:translate-y-[-1px] hover:scale-[1.02]">
                <svg class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                        d="M12 6v6m0 0v6m0-6h6m-6 0H6" />
                </svg>
                New Appointment
            </a>
        </div>
    </div>

    @if ($appointments->count() > 0)
        <!-- Desktop Table View (hidden on mobile) -->
        <div class="hidden lg:block overflow-x-auto">
            <table class="min-w-full divide-y-2 divide-gray-200">
                <thead class="bg-gradient-to-r from-gray-50 to-gray-100">
                    <tr>
                        <th
                            class="px-6 py-4 text-left text-xs font-bold text-gray-600 uppercase tracking-wider border-r border-gray-200">
                            Date & Queue
                        </th>
                        <th
                            class="px-6 py-4 text-left text-xs font-bold text-gray-600 uppercase tracking-wider border-r border-gray-200">
                            Status
                        </th>
                        <th
                            class="px-6 py-4 text-left text-xs font-bold text-gray-600 uppercase tracking-wider border-r border-gray-200">
                            Branch
                        </th>
                        <th
                            class="px-6 py-4 text-left text-xs font-bold text-gray-600 uppercase tracking-wider border-r border-gray-200">
                            Reason
                        </th>
                        <th
                            class="px-6 py-4 text-left text-xs font-bold text-gray-600 uppercase tracking-wider border-r border-gray-200">
                            Created By
                        </th>
                        <th class="px-6 py-4 text-center text-xs font-bold text-gray-600 uppercase tracking-wider">
                            Actions
                        </th>
                    </tr>
                </thead>
                <tbody class="bg-white divide-y-2 divide-gray-100">
                    @foreach ($appointments as $appointment)
                        <tr class="hover:bg-blue-50/50 transition-all duration-200 group">
                            <td class="px-6 py-5 whitespace-nowrap border-r border-gray-100">
                                <div class="flex items-center">
                                    <div class="flex-shrink-0">
                                        <div
                                            class="w-12 h-12 rounded-full bg-gradient-to-br from-blue-100 to-indigo-200 flex items-center justify-center shadow-sm border border-blue-200">
                                            <span
                                                class="text-sm font-bold text-blue-700">#{{ $appointment->queue_number }}</span>
                                        </div>
                                    </div>
                                    <div class="ml-4">
                                        <div class="text-sm font-semibold text-gray-900">
                                            {{ $appointment->appointment_date->format('M d, Y') }}
                                        </div>
                                        <div class="text-xs text-gray-500 font-medium">
                                            {{ $appointment->appointment_date->format('l') }}
                                        </div>
                                    </div>
                                </div>
                            </td>
                            <td class="px-6 py-5 whitespace-nowrap border-r border-gray-100">
                                <span
                                    class="inline-flex items-center px-3 py-1.5 rounded-full text-xs font-semibold shadow-sm {{ $appointment->status_badge }}">
                                    <svg class="w-2 h-2 mr-1.5" fill="currentColor" viewBox="0 0 8 8">
                                        <circle cx="4" cy="4" r="3" />
                                    </svg>
                                    {{ ucwords(str_replace('_', ' ', $appointment->status->value)) }}
                                </span>
                            </td>
                            <td class="px-6 py-5 whitespace-nowrap text-sm text-gray-900 border-r border-gray-100">
                                <div class="flex items-center">
                                    <svg class="w-4 h-4 mr-2 text-gray-400" fill="none" stroke="currentColor"
                                        viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                            d="M19 21V5a2 2 0 00-2-2H7a2 2 0 00-2 2v16m14 0h2m-2 0h-2m-2 0H7m5 0H7" />
                                    </svg>
                                    <span class="font-medium">{{ $appointment->branch->name }}</span>
                                </div>
                            </td>
                            <td class="px-6 py-5 text-sm text-gray-700 max-w-xs border-r border-gray-100">
                                <div class="truncate font-medium">{{ $appointment->reason ?: 'Not specified' }}</div>
                            </td>
                            <td class="px-6 py-5 whitespace-nowrap text-sm text-gray-500 border-r border-gray-100">
                                <span class="font-medium">{{ $appointment->creator->name ?? 'Unknown' }}</span>
                            </td>
                            <td class="px-6 py-5 whitespace-nowrap text-center">
                                <div class="flex items-center justify-center space-x-3">
                                    <a wire:navigate href="{{ route('appointments.view', $appointment) }}"
                                        class="inline-flex items-center px-3 py-2 border border-blue-300 rounded-lg text-xs font-semibold text-blue-700 bg-blue-50 hover:bg-blue-100 hover:border-blue-400 focus:outline-none focus:ring-2 focus:ring-offset-1 focus:ring-blue-500 transition-all duration-200 shadow-sm hover:shadow">
                                        <svg class="w-3 h-3 mr-1.5" fill="none" stroke="currentColor"
                                            viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                                d="M15 12a3 3 0 11-6 0 3 3 0 016 0z" />
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                                d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z" />
                                        </svg>
                                        View
                                    </a>
                                    @if ($appointment->canBeModified())
                                        <a wire:navigate href="{{ route('appointments.edit', $appointment) }}"
                                            class="inline-flex items-center px-3 py-2 border border-indigo-300 rounded-lg text-xs font-semibold text-indigo-700 bg-indigo-50 hover:bg-indigo-100 hover:border-indigo-400 focus:outline-none focus:ring-2 focus:ring-offset-1 focus:ring-indigo-500 transition-all duration-200 shadow-sm hover:shadow">
                                            <svg class="w-3 h-3 mr-1.5" fill="none" stroke="currentColor"
                                                viewBox="0 0 24 24">
                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                                    d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z" />
                                            </svg>
                                            Edit
                                        </a>
                                    @endif
                                </div>
                            </td>
                        </tr>
                    @endforeach
                </tbody>
            </table>
        </div>

        <!-- Mobile Card View (visible on mobile and tablet) -->
        <div class="lg:hidden bg-gray-50">
            @foreach ($appointments as $appointment)
                <div
                    class="bg-white mx-4 my-4 rounded-2xl shadow-lg border border-gray-200 hover:shadow-xl transition-all duration-300 hover:border-gray-300 overflow-hidden">
                    <!-- Card Header -->
                    <div class="bg-gradient-to-r from-blue-50 to-indigo-50 px-5 py-4 border-b border-gray-200">
                        <div class="flex items-center justify-between">
                            <div class="flex items-center space-x-3">
                                <div
                                    class="w-12 h-12 rounded-full bg-gradient-to-br from-blue-100 to-indigo-200 flex items-center justify-center shadow-sm border border-blue-200">
                                    <span
                                        class="text-sm font-bold text-blue-700">#{{ $appointment->queue_number }}</span>
                                </div>
                                <div>
                                    <h4 class="text-lg font-bold text-gray-900">
                                        {{ $appointment->appointment_date->format('M d, Y') }}
                                    </h4>
                                    <p class="text-sm text-gray-600 font-medium">
                                        {{ $appointment->appointment_date->format('l') }}
                                    </p>
                                </div>
                            </div>
                            <span
                                class="inline-flex items-center px-3 py-1.5 rounded-full text-xs font-semibold shadow-sm {{ $appointment->status_badge }}">
                                <svg class="w-2 h-2 mr-1.5" fill="currentColor" viewBox="0 0 8 8">
                                    <circle cx="4" cy="4" r="3" />
                                </svg>
                                {{ ucwords(str_replace('_', ' ', $appointment->status->value)) }}
                            </span>
                        </div>
                    </div>

                    <!-- Card Content -->
                    <div class="px-5 py-4 space-y-4">
                        <!-- Branch -->
                        <div class="flex items-center">
                            <div class="w-8 h-8 bg-gray-100 rounded-lg flex items-center justify-center mr-3">
                                <svg class="w-4 h-4 text-gray-500" fill="none" stroke="currentColor"
                                    viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                        d="M19 21V5a2 2 0 00-2-2H7a2 2 0 00-2 2v16m14 0h2m-2 0h-2m-2 0H7m5 0H7" />
                                </svg>
                            </div>
                            <div>
                                <p class="text-xs text-gray-500 font-medium uppercase tracking-wide">Branch</p>
                                <p class="text-sm font-semibold text-gray-900">{{ $appointment->branch->name }}</p>
                            </div>
                        </div>

                        <!-- Reason -->
                        <div class="flex items-start">
                            <div class="w-8 h-8 bg-gray-100 rounded-lg flex items-center justify-center mr-3 mt-1">
                                <svg class="w-4 h-4 text-gray-500" fill="none" stroke="currentColor"
                                    viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                        d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z" />
                                </svg>
                            </div>
                            <div class="flex-1">
                                <p class="text-xs text-gray-500 font-medium uppercase tracking-wide">Reason</p>
                                <p class="text-sm font-medium text-gray-900 leading-relaxed">
                                    {{ $appointment->reason ?: 'Not specified' }}</p>
                            </div>
                        </div>

                        <!-- Created By -->
                        <div class="flex items-center">
                            <div class="w-8 h-8 bg-gray-100 rounded-lg flex items-center justify-center mr-3">
                                <svg class="w-4 h-4 text-gray-500" fill="none" stroke="currentColor"
                                    viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                        d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z" />
                                </svg>
                            </div>
                            <div>
                                <p class="text-xs text-gray-500 font-medium uppercase tracking-wide">Created By</p>
                                <p class="text-sm font-semibold text-gray-900">
                                    {{ $appointment->creator->name ?? 'Unknown' }}</p>
                            </div>
                        </div>
                    </div>

                    <!-- Card Actions -->
                    <div class="bg-gray-50 px-5 py-4 border-t border-gray-200">
                        <div class="flex flex-col sm:flex-row gap-3">
                            <a wire:navigate href="{{ route('appointments.view', $appointment) }}"
                                class="flex-1 inline-flex items-center justify-center px-4 py-3 border border-blue-300 rounded-xl text-sm font-semibold text-blue-700 bg-blue-50 hover:bg-blue-100 hover:border-blue-400 focus:outline-none focus:ring-2 focus:ring-offset-1 focus:ring-blue-500 transition-all duration-200 shadow-sm hover:shadow-md">
                                <svg class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                        d="M15 12a3 3 0 11-6 0 3 3 0 016 0z" />
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                        d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z" />
                                </svg>
                                View Details
                            </a>
                            @if ($appointment->canBeModified())
                                <a wire:navigate href="{{ route('appointments.edit', $appointment) }}"
                                    class="flex-1 inline-flex items-center justify-center px-4 py-3 border border-indigo-300 rounded-xl text-sm font-semibold text-indigo-700 bg-indigo-50 hover:bg-indigo-100 hover:border-indigo-400 focus:outline-none focus:ring-2 focus:ring-offset-1 focus:ring-indigo-500 transition-all duration-200 shadow-sm hover:shadow-md">
                                    <svg class="w-4 h-4 mr-2" fill="none" stroke="currentColor"
                                        viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                            d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z" />
                                    </svg>
                                    Edit Appointment
                                </a>
                            @endif
                        </div>
                    </div>
                </div>
            @endforeach
        </div>

        <!-- Pagination -->
        <div class="px-4 sm:px-6 lg:px-8 py-6 bg-white lg:bg-gray-50 border-t-2 border-gray-200">
            <div class="lg:bg-white lg:rounded-2xl lg:shadow-lg lg:border lg:border-gray-200 lg:p-6">
                {{ $appointments->links() }}
            </div>
        </div>
    @else
        <!-- Empty State -->
        <div class="px-4 sm:px-6 lg:px-8 py-16 text-center bg-gray-50">
            <div class="bg-white rounded-2xl shadow-lg border border-gray-200 py-16 px-8">
                <div
                    class="mx-auto w-20 h-20 bg-gradient-to-r from-blue-100 to-indigo-200 rounded-full flex items-center justify-center mb-6 shadow-sm border border-blue-200">
                    <svg class="w-10 h-10 text-blue-500" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5"
                            d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z" />
                    </svg>
                </div>
                <h3 class="text-xl font-semibold text-gray-900 mb-2">No appointments scheduled</h3>
                <p class="text-gray-500 mb-8 max-w-sm mx-auto leading-relaxed">This patient hasn't scheduled any
                    appointments yet.</p>
                <a wire:navigate href="{{ route('appointments.create') }}?patient_id={{ $patient->id }}"
                    class="inline-flex items-center px-6 py-3 border border-transparent rounded-xl text-sm font-semibold text-white bg-gradient-to-r from-blue-600 to-indigo-600 hover:from-blue-700 hover:to-indigo-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-blue-500 shadow-lg hover:shadow-xl transition-all duration-200 transform hover:translate-y-[-1px] hover:scale-[1.02]">
                    <svg class="w-5 h-5 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                            d="M12 6v6m0 0v6m0-6h6m-6 0H6" />
                    </svg>
                    Schedule First Appointment
                </a>
            </div>
        </div>
    @endif
</div>
