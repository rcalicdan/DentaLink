<x-layouts.app>
    <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-6">

        <div class="bg-white dark:bg-slate-800 rounded-lg shadow p-6 flex items-center justify-between">
            <div>
                <p class="text-sm font-medium text-slate-500 dark:text-slate-400">Total Patients</p>
                <p class="text-3xl font-bold text-slate-900 dark:text-slate-100">{{ $totalPatients }}</p>
                <p class="text-xs text-green-500 flex items-center mt-1">
                    <i class="fas fa-arrow-up mr-1"></i>
                    <span>{{ $patientGrowth }}% this month</span>
                </p>
            </div>
            <div
                class="bg-blue-100 dark:bg-blue-500/20 text-blue-500 dark:text-blue-400 rounded-full w-12 h-12 flex items-center justify-center">
                <i class="fas fa-users fa-lg"></i>
            </div>
        </div>

        <div class="bg-white dark:bg-slate-800 rounded-lg shadow p-6 flex items-center justify-between">
            <div>
                <p class="text-sm font-medium text-slate-500 dark:text-slate-400">Appointments Today</p>
                <p class="text-3xl font-bold text-slate-900 dark:text-slate-100">{{ $appointmentsToday }}</p>
                <p class="text-xs text-slate-500 dark:text-slate-400 flex items-center mt-1">
                    <i class="fas fa-calendar-check mr-1"></i>
                    <span>{{ $upcomingAppointments }} upcoming</span>
                </p>
            </div>
            <div
                class="bg-green-100 dark:bg-green-500/20 text-green-500 dark:text-green-400 rounded-full w-12 h-12 flex items-center justify-center">
                <i class="fas fa-calendar-check fa-lg"></i>
            </div>
        </div>

        <div class="bg-white dark:bg-slate-800 rounded-lg shadow p-6 flex items-center justify-between">
            <div>
                <p class="text-sm font-medium text-slate-500 dark:text-slate-400">Revenue (This Month)</p>
                <p class="text-3xl font-bold text-slate-900 dark:text-slate-100">
                    ₱{{ number_format($monthlyRevenue, 2) }}</p>
                <p class="text-xs text-green-500 flex items-center mt-1">
                    <i class="fas fa-arrow-up mr-1"></i>
                    <span>{{ $revenueGrowth }}% vs last month</span>
                </p>
            </div>
            <div
                class="bg-indigo-100 dark:bg-indigo-500/20 text-indigo-500 dark:text-indigo-400 rounded-full w-12 h-12 flex items-center justify-center">
                <i class="fas fa-peso-sign fa-lg"></i>
            </div>
        </div>
    </div>

    {{-- Charts Section --}}
    <div class="grid grid-cols-1 lg:grid-cols-5 gap-6 mt-6">
        <div class="lg:col-span-3 bg-white dark:bg-slate-800 rounded-lg shadow p-6" x-data="appointmentsChart()">
            <h3 class="text-lg font-semibold text-slate-800 dark:text-slate-200 mb-4">Weekly Appointments</h3>

            @if (empty($weeklyAppointments['labels']) ||
                    empty($weeklyAppointments['data']) ||
                    array_sum($weeklyAppointments['data']) === 0)
                <div class="flex items-center justify-center h-80 text-slate-500 dark:text-slate-400">
                    <div class="text-center">
                        <i class="fas fa-chart-bar text-4xl mb-2 opacity-30"></i>
                        <p>No appointment data available</p>
                    </div>
                </div>
            @else
                <div class="relative h-80">
                    <canvas id="appointmentsChart" x-ref="chart"></canvas>
                </div>
            @endif
        </div>

        <div class="lg:col-span-2 bg-white dark:bg-slate-800 rounded-lg shadow p-6" x-data="servicesChart()">
            <h3 class="text-lg font-semibold text-slate-800 dark:text-slate-200 mb-4">
                Services Breakdown
                @if (!empty($servicesBreakdown['labels']))
                    <span class="text-xs font-normal text-slate-500 dark:text-slate-400">(Last 3 Months)</span>
                @endif
            </h3>

            @if (empty($servicesBreakdown['labels']) || empty($servicesBreakdown['data']))
                <div class="flex items-center justify-center h-80 text-slate-500 dark:text-slate-400">
                    <div class="text-center">
                        <i class="fas fa-chart-pie text-4xl mb-2 opacity-30"></i>
                        <p>No services data available</p>
                    </div>
                </div>
            @else
                <div class="relative h-80">
                    <canvas id="servicesChart" x-ref="chart"></canvas>
                </div>
            @endif
        </div>
    </div>

    {{-- AI Forecast Section --}}
    @can('view-ai-assistant')
        @livewire('dashboard.ai-forecast')
    @endcan


    <div class="mt-8">
        <div class="bg-white dark:bg-slate-800 rounded-lg shadow overflow-hidden">
            <div class="p-6">
                <h3 class="text-lg font-semibold text-slate-800 dark:text-slate-200">Upcoming Appointments</h3>
            </div>
            <div class="overflow-x-auto">
                <table class="w-full text-sm text-left text-slate-500 dark:text-slate-400">
                    <thead class="text-xs text-slate-700 uppercase bg-slate-50 dark:bg-slate-700 dark:text-slate-300">
                        <tr>
                            <th scope="col" class="px-6 py-3">Patient Name</th>
                            <th scope="col" class="px-6 py-3">Date</th>
                            <th scope="col" class="px-6 py-3">Time</th>
                            <th scope="col" class="px-6 py-3">Branch</th>
                            <th scope="col" class="px-6 py-3 text-center">Status</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse ($upcomingAppointmentsList as $appt)
                            <tr
                                class="bg-white dark:bg-slate-800 border-b dark:border-slate-700 hover:bg-slate-50 dark:hover:bg-slate-600">
                                <td class="px-6 py-4 font-medium text-slate-900 dark:text-white whitespace-nowrap">
                                    {{ $appt->patient_name }}
                                </td>
                                <td class="px-6 py-4">{{ $appt->formatted_date }}</td>
                                <td class="px-6 py-4">{{ $appt->formatted_time }}</td>
                                <td class="px-6 py-4">{{ $appt->branch_name }}</td>
                                <td class="px-6 py-4 text-center">
                                    <span class="px-2 py-1 text-xs font-medium rounded-full {{ $appt->status_class }}">
                                        {{ $appt->status_label }}
                                    </span>
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="5" class="px-6 py-4 text-center text-slate-500 dark:text-slate-400">
                                    No upcoming appointments
                                </td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>
    </div>

    @can('view-ai-assistant')
        @include('contents.dashboard.ai-chat-modal')
    @endcan

    @include('contents.dashboard.charts-js')
</x-layouts.app>
