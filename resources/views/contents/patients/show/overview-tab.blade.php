<div class="grid grid-cols-1 xl:grid-cols-3 gap-8">
    {{-- Patient Information Card --}}
    <div class="xl:col-span-2">
        <div class="bg-white shadow-xl rounded-2xl border border-gray-100 overflow-hidden">
            <div class="bg-gradient-to-r from-gray-50 to-white px-6 sm:px-8 py-4 sm:py-6 border-b border-gray-100">
                <div class="flex items-center">
                    <div class="flex-shrink-0">
                        <div
                            class="w-10 h-10 sm:w-12 sm:h-12 bg-gradient-to-r from-blue-500 to-indigo-600 rounded-xl flex items-center justify-center shadow-lg">
                            <svg class="w-5 h-5 sm:w-6 sm:h-6 text-white" fill="none" stroke="currentColor"
                                viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                    d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z" />
                            </svg>
                        </div>
                    </div>
                    <div class="ml-3 sm:ml-4">
                        <h3 class="text-lg sm:text-xl font-semibold text-gray-900">Patient Information</h3>
                        <p class="text-gray-600 text-sm">Personal details and contact information</p>
                    </div>
                </div>
            </div>
            <div class="px-6 sm:px-8 py-6">
                <dl class="grid grid-cols-1 md:grid-cols-2 gap-y-6 gap-x-4">
                    <div class="group">
                        <dt class="text-sm font-medium text-gray-500 mb-1 flex items-center">
                            <svg class="w-4 h-4 mr-2 text-gray-400" fill="none" stroke="currentColor"
                                viewBox="0 0 24 24">
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
                            <svg class="w-4 h-4 mr-2 text-gray-400" fill="none" stroke="currentColor"
                                viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                    d="M3 5a2 2 0 012-2h3.28a1 1 0 01.948.684l1.498 4.493a1 1 0 01-.502 1.21l-2.257 1.13a11.042 11.042 0 005.516 5.516l1.13-2.257a1 1 0 011.21-.502l4.493 1.498a1 1 0 01.684.949V19a2 2 0 01-2 2h-1C9.716 21 3 14.284 3 6V5z" />
                            </svg>
                            Phone
                        </dt>
                        <dd
                            class="text-base sm:text-lg font-semibold text-gray-900 group-hover:text-blue-600 transition-colors">
                            <a href="tel:{{ $patient->phone }}" class="hover:underline">{{ $patient->phone }}</a>
                        </dd>
                    </div>

                    <div class="group">
                        <dt class="text-sm font-medium text-gray-500 mb-1 flex items-center">
                            <svg class="w-4 h-4 mr-2 text-gray-400" fill="none" stroke="currentColor"
                                viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                    d="M3 8l7.89 5.26a2 2 0 002.22 0L21 8M5 19h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v10a2 2 0 002 2z" />
                            </svg>
                            Email
                        </dt>
                        <dd
                            class="text-base sm:text-lg font-semibold text-gray-900 group-hover:text-blue-600 transition-colors">
                            @if ($patient->email)
                                <a href="mailto:{{ $patient->email }}" class="hover:underline">{{ $patient->email }}</a>
                            @else
                                <span class="text-gray-400 italic">Not provided</span>
                            @endif
                        </dd>
                    </div>

                    <div class="group">
                        <dt class="text-sm font-medium text-gray-500 mb-1 flex items-center">
                            <svg class="w-4 h-4 mr-2 text-gray-400" fill="none" stroke="currentColor"
                                viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                    d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z" />
                            </svg>
                            Age
                        </dt>
                        <dd
                            class="text-base sm:text-lg font-semibold text-gray-900 group-hover:text-blue-600 transition-colors">
                            @if ($patient->age)
                                {{ $patient->age }} years old
                            @else
                                <span class="text-gray-400 italic">Not provided</span>
                            @endif
                        </dd>
                    </div>

                    <div class="group md:col-span-2">
                        <dt class="text-sm font-medium text-gray-500 mb-1 flex items-center">
                            <svg class="w-4 h-4 mr-2 text-gray-400" fill="none" stroke="currentColor"
                                viewBox="0 0 24 24">
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
                            <svg class="w-4 h-4 mr-2 text-gray-400" fill="none" stroke="currentColor"
                                viewBox="0 0 24 24">
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
                            <svg class="w-4 h-4 mr-2 text-gray-400" fill="none" stroke="currentColor"
                                viewBox="0 0 24 24">
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
                                                <svg class="w-4 h-4 sm:w-5 sm:h-5 text-blue-600" fill="none"
                                                    stroke="currentColor" viewBox="0 0 24 24">
                                                    <path stroke-linecap="round" stroke-linejoin="round"
                                                        stroke-width="2"
                                                        d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z" />
                                                </svg>
                                            </div>
                                        @else
                                            <div
                                                class="w-9 h-9 sm:w-10 sm:h-10 rounded-full bg-gradient-to-r from-green-100 to-green-200 flex items-center justify-center">
                                                <svg class="w-4 h-4 sm:w-5 sm:h-5 text-green-600" fill="none"
                                                    stroke="currentColor" viewBox="0 0 24 24">
                                                    <path stroke-linecap="round" stroke-linejoin="round"
                                                        stroke-width="2"
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
                                                    <p class="text-sm font-medium text-gray-900">Appointment scheduled
                                                    </p>
                                                    <p class="text-xs text-gray-600">
                                                        {{ $activity->appointment_date->format('M d, Y') }} - Queue
                                                        #{{ $activity->queue_number }}
                                                    </p>
                                                @else
                                                    <p class="text-sm font-medium text-gray-900">Visit completed</p>
                                                    <p class="text-xs text-gray-600">
                                                        {{ $activity->visit_date->format('M d, Y \a\t g:i A') }} -
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
                                <svg class="mx-auto h-12 w-12 text-gray-300" fill="none" stroke="currentColor"
                                    viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                        d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z" />
                                </svg>
                                <h3 class="mt-2 text-sm font-medium text-gray-900">No recent activity</h3>
                                <p class="mt-1 text-sm text-gray-500">No appointments or visits recorded yet.</p>
                            </li>
                        @endforelse
                    </ul>
                </div>
            </div>
        </div>
    </div>
</div>
