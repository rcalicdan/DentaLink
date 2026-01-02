<div class="bg-white shadow-xl rounded-2xl border border-gray-100 overflow-hidden">
    <!-- Header Section -->
    <div class="bg-gradient-to-r from-green-50 to-emerald-50 px-4 sm:px-6 lg:px-8 py-4 sm:py-6 border-b border-gray-100">
        <div class="flex flex-col sm:flex-row justify-between items-start sm:items-center space-y-3 sm:space-y-0">
            <div>
                <h3 class="text-xl sm:text-2xl font-semibold text-gray-900">Visits History</h3>
                <p class="text-gray-600 text-sm mt-1">Complete record of all patient visits and treatments</p>
            </div>
            
            @can('create', App\Models\PatientVisit::class)
                <a wire:navigate href="{{ route('patient-visits.create') }}?patient_id={{ $patient->id }}"
                    class="w-full sm:w-auto inline-flex items-center justify-center px-4 py-2.5 border border-transparent rounded-xl text-sm font-medium text-white bg-gradient-to-r from-green-600 to-emerald-600 hover:from-green-700 hover:to-emerald-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-green-500 shadow-lg hover:shadow-xl transition-all duration-200 transform hover:translate-y-[-2px]">
                    <svg class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                            d="M12 6v6m0 0v6m0-6h6m-6 0H6" />
                    </svg>
                    <span class="sm:inline">New Visit</span>
                </a>
            @endcan
        </div>
    </div>

    <!-- Visits Container -->
    <div class="bg-gray-50">
        @if ($visits->count() > 0)
            @foreach ($visits as $visit)
                <!-- Individual Visit Card with Clear Separation -->
                <div class="relative">
                    <!-- Strong Visual Separator -->
                    @if (!$loop->first)
                        <div class="bg-gray-200 h-2"></div>
                        <div class="bg-gradient-to-r from-gray-300 via-gray-400 to-gray-300 h-0.5"></div>
                        <div class="bg-gray-100 h-4 flex items-center justify-center">
                            <div
                                class="w-8 h-8 bg-white rounded-full border-2 border-gray-300 flex items-center justify-center shadow-sm">
                                <div class="w-2 h-2 bg-gray-400 rounded-full"></div>
                            </div>
                        </div>
                    @endif

                    <!-- Visit Card -->
                    <div
                        class="bg-white mx-4 sm:mx-6 lg:mx-8 mb-6 rounded-2xl shadow-lg border border-gray-200 hover:shadow-xl transition-all duration-300 hover:border-gray-300">
                        <!-- Visit Content -->
                        <div class="px-4 sm:px-6 lg:px-8 py-6 sm:py-8">
                            <!-- Visit Header -->
                            <div class="flex flex-col space-y-4 mb-6">
                                <!-- Mobile: Stack vertically, Desktop: Side by side -->
                                <div
                                    class="flex flex-col sm:flex-row sm:justify-between sm:items-start space-y-4 sm:space-y-0">
                                    <!-- Left side: Visit info -->
                                    <div class="flex items-start space-x-4">
                                        <div class="flex-shrink-0">
                                            <div
                                                class="w-12 h-12 sm:w-14 sm:h-14 bg-gradient-to-r from-green-100 to-emerald-200 rounded-xl flex items-center justify-center shadow-sm border border-green-200">
                                                <svg class="w-6 h-6 sm:w-7 sm:h-7 text-green-600" fill="currentColor"
                                                    viewBox="0 0 24 24">
                                                    <path
                                                        d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z" />
                                                </svg>
                                            </div>
                                        </div>
                                        <div class="min-w-0 flex-1">
                                            <h4 class="text-lg sm:text-xl font-bold text-gray-900 mb-1">
                                                {{ $visit->visit_date->format('F d, Y') }}
                                            </h4>
                                            <div class="flex flex-wrap items-center gap-2 text-sm text-gray-600">
                                                <span class="flex items-center">
                                                    <svg class="w-4 h-4 mr-1 text-gray-400" fill="currentColor"
                                                        viewBox="0 0 24 24">
                                                        <path
                                                            d="M12 2C6.48 2 2 6.48 2 12s4.48 10 10 10 10-4.48 10-10S17.52 2 12 2zm-2 15l-5-5 1.41-1.41L10 14.17l7.59-7.59L19 8l-9 9z" />
                                                    </svg>
                                                    {{ $visit->visit_date->format('g:i A') }}
                                                </span>
                                                <span class="text-gray-400">•</span>
                                                <span>{{ $visit->branch->name }}</span>
                                            </div>
                                            <div class="flex flex-wrap items-center gap-2 mt-2">
                                                <span
                                                    class="inline-flex items-center px-2.5 py-1 rounded-full text-xs font-semibold 
                                                    {{ $visit->visit_type === 'appointment' ? 'bg-blue-100 text-blue-800 border border-blue-200' : 'bg-orange-100 text-orange-800 border border-orange-200' }}">
                                                    {{ ucfirst($visit->visit_type) }}
                                                </span>
                                                @if ($visit->appointment_id)
                                                    <span
                                                        class="inline-flex items-center px-2.5 py-1 rounded-full text-xs font-medium bg-purple-100 text-purple-800 border border-purple-200">
                                                        Appointment:
                                                        {{ $visit->appointment->appointment_date->format('M d, Y') }}
                                                    </span>
                                                @endif
                                            </div>
                                        </div>
                                    </div>

                                    <!-- Right side: Total amount -->
                                    <div class="flex-shrink-0 text-center sm:text-right">
                                        <div
                                            class="inline-flex flex-col items-center sm:items-end p-4 sm:p-0 bg-green-50 sm:bg-transparent rounded-xl sm:rounded-none border sm:border-0 border-green-200">
                                            <div class="text-2xl sm:text-3xl font-bold text-green-600">
                                                ₱{{ number_format($visit->total_amount_paid, 2) }}
                                            </div>
                                            <p class="text-sm text-gray-500 font-medium">Total Paid</p>
                                        </div>
                                    </div>
                                </div>
                            </div>

                            <!-- Services Section -->
                            @if ($visit->patientVisitServices->count() > 0)
                                <div class="mb-6">
                                    <div class="bg-gray-50 rounded-xl p-4 border border-gray-200">
                                        <h5 class="text-lg font-semibold text-gray-900 mb-4 flex items-center">
                                            <svg class="w-5 h-5 mr-2 text-gray-500" fill="currentColor"
                                                viewBox="0 0 24 24">
                                                <path
                                                    d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2" />
                                            </svg>
                                            Services Provided
                                        </h5>

                                        <!-- Mobile: Single column, Tablet: 2 columns, Desktop: 3 columns -->
                                        <div class="grid grid-cols-1 md:grid-cols-2 xl:grid-cols-3 gap-4">
                                            @foreach ($visit->patientVisitServices as $service)
                                                <div
                                                    class="bg-white border-2 border-gray-200 rounded-xl p-4 shadow-sm hover:shadow-md hover:border-gray-300 transition-all duration-200">
                                                    <div class="space-y-3">
                                                        <div class="flex justify-between items-start">
                                                            <h6
                                                                class="text-sm font-semibold text-gray-900 leading-tight pr-2">
                                                                {{ $service->dentalService->name }}
                                                            </h6>
                                                            <span class="text-lg font-bold text-gray-900 flex-shrink-0">
                                                                ₱{{ number_format($service->total_price, 2) }}
                                                            </span>
                                                        </div>

                                                        <div class="flex flex-wrap gap-2">
                                                            <span
                                                                class="inline-flex items-center px-2.5 py-1 rounded-full text-xs font-medium bg-blue-100 text-blue-700 border border-blue-200">
                                                                Qty: {{ $service->quantity }}
                                                            </span>
                                                            <span
                                                                class="inline-flex items-center px-2.5 py-1 rounded-full text-xs font-medium bg-green-100 text-green-700 border border-green-200">
                                                                ₱{{ number_format($service->service_price, 2) }} each
                                                            </span>
                                                        </div>

                                                        @if ($service->service_notes)
                                                            <p
                                                                class="text-xs text-gray-600 italic bg-gray-100 rounded-lg p-2 leading-relaxed border border-gray-200">
                                                                {{ $service->service_notes }}
                                                            </p>
                                                        @endif
                                                    </div>
                                                </div>
                                            @endforeach
                                        </div>
                                    </div>
                                </div>
                            @endif

                            <!-- Visit Notes Section -->
                            @if ($visit->notes)
                                <div class="mb-6">
                                    <h5 class="text-lg font-semibold text-gray-900 mb-3 flex items-center">
                                        <svg class="w-5 h-5 mr-2 text-gray-500" fill="currentColor" viewBox="0 0 24 24">
                                            <path
                                                d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z" />
                                        </svg>
                                        Visit Notes
                                    </h5>
                                    <div
                                        class="bg-gradient-to-r from-blue-50 to-indigo-50 border-l-4 border-blue-400 rounded-xl p-4 border border-blue-200">
                                        <p class="text-sm text-gray-700 leading-relaxed">{{ $visit->notes }}</p>
                                    </div>
                                </div>
                            @endif
                            <!-- Visit Footer -->
                            <div
                                class="flex flex-col sm:flex-row justify-between items-start sm:items-center pt-4 border-t-2 border-gray-200 space-y-4 sm:space-y-0">
                                <!-- Meta Information -->
                                <div
                                    class="flex flex-col sm:flex-row sm:items-center space-y-2 sm:space-y-0 sm:space-x-6 text-sm text-gray-500">
                                    <span class="flex items-center">
                                        <svg class="w-4 h-4 mr-1.5 text-gray-400" fill="currentColor"
                                            viewBox="0 0 24 24">
                                            <path
                                                d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z" />
                                        </svg>
                                        <span class="font-medium">Created
                                            by:</span>&nbsp;{{ $visit->creator->full_name ?? 'Unknown' }}
                                    </span>
                                    <span class="flex items-center">
                                        <svg class="w-4 h-4 mr-1.5 text-gray-400" fill="currentColor"
                                            viewBox="0 0 24 24">
                                            <path d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z" />
                                        </svg>
                                        {{ $visit->created_at->diffForHumans() }}
                                    </span>
                                </div>

                                <!-- Action Button -->
                                <div class="flex-shrink-0">
                                    {{-- NEW: Authorization check for updating visits --}}
                                    @can('update', $visit)
                                        <a wire:navigate href="{{ route('patient-visits.edit', $visit) }}"
                                            class="inline-flex items-center px-4 py-2 border-2 border-gray-300 rounded-lg text-sm font-medium text-gray-700 bg-white hover:bg-gray-50 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-indigo-500 transition-all duration-200 shadow-sm hover:shadow-md hover:border-gray-400">
                                            <svg class="w-4 h-4 mr-2" fill="currentColor" viewBox="0 0 24 24">
                                                <path
                                                    d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z" />
                                            </svg>
                                            Edit Visit
                                        </a>
                                    @endcan
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            @endforeach

            <!-- Pagination -->
            <div
                class="px-4 sm:px-6 lg:px-8 py-6 bg-white mx-4 sm:mx-6 lg:mx-8 rounded-2xl shadow-lg border border-gray-200 mt-6">
                {{ $visits->links() }}
            </div>
        @else
             <!-- Empty State -->
             <div class="bg-white mx-4 sm:mx-6 lg:mx-8 rounded-2xl shadow-lg border border-gray-200 py-16 text-center">
                <div class="px-4 sm:px-6 lg:px-8">
                    <div
                        class="mx-auto w-24 h-24 bg-gradient-to-r from-gray-100 to-gray-200 rounded-full flex items-center justify-center mb-6 border-2 border-gray-300">
                        <svg class="w-12 h-12 text-gray-400" fill="currentColor" viewBox="0 0 24 24">
                            <path d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z" />
                        </svg>
                    </div>
                    <h3 class="text-xl font-semibold text-gray-900 mb-2">No visits recorded</h3>
                    <p class="text-gray-500 mb-8 max-w-sm mx-auto leading-relaxed">
                        This patient hasn't had any visits yet. Record their first visit to start tracking their
                        treatment history.
                    </p>
                    <a wire:navigate href="{{ route('patient-visits.create') }}?patient_id={{ $patient->id }}"
                        class="inline-flex items-center px-6 py-3 border border-transparent rounded-xl text-sm font-medium text-white bg-gradient-to-r from-green-600 to-emerald-600 hover:from-green-700 hover:to-emerald-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-green-500 shadow-lg hover:shadow-xl transition-all duration-200 transform hover:translate-y-[-2px]">
                        <svg class="w-5 h-5 mr-2" fill="currentColor" viewBox="0 0 24 24">
                            <path d="M12 6v6m0 0v6m0-6h6m-6 0H6" />
                        </svg>
                        Record First Visit
                    </a>
                </div>
            </div>
        @endif
    </div>
</div>