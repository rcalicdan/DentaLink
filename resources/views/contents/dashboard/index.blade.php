<x-layouts.app>

    {{-- Dashboard Grid --}}
    <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-6">

        <!-- Stat Card: Total Patients -->
        <div class="bg-white dark:bg-slate-800 rounded-lg shadow p-6 flex items-center justify-between">
            <div>
                <p class="text-sm font-medium text-slate-500 dark:text-slate-400">Total Patients</p>
                <p class="text-3xl font-bold text-slate-900 dark:text-slate-100">1,428</p>
                <p class="text-xs text-green-500 flex items-center mt-1">
                    <i class="fas fa-arrow-up mr-1"></i>
                    <span>2.5% this month</span>
                </p>
            </div>
            <div
                class="bg-blue-100 dark:bg-blue-500/20 text-blue-500 dark:text-blue-400 rounded-full w-12 h-12 flex items-center justify-center">
                <i class="fas fa-users fa-lg"></i>
            </div>
        </div>

        <!-- Stat Card: Appointments Today -->
        <div class="bg-white dark:bg-slate-800 rounded-lg shadow p-6 flex items-center justify-between">
            <div>
                <p class="text-sm font-medium text-slate-500 dark:text-slate-400">Appointments Today</p>
                <p class="text-3xl font-bold text-slate-900 dark:text-slate-100">42</p>
                <p class="text-xs text-slate-500 dark:text-slate-400 flex items-center mt-1">
                    <i class="fas fa-calendar-check mr-1"></i>
                    <span>12 upcoming</span>
                </p>
            </div>
            <div
                class="bg-green-100 dark:bg-green-500/20 text-green-500 dark:text-green-400 rounded-full w-12 h-12 flex items-center justify-center">
                <i class="fas fa-calendar-check fa-lg"></i>
            </div>
        </div>

        <!-- Stat Card: Clinic Revenue (Monthly) -->
        <div class="bg-white dark:bg-slate-800 rounded-lg shadow p-6 flex items-center justify-between">
            <div>
                <p class="text-sm font-medium text-slate-500 dark:text-slate-400">Revenue (This Month)</p>
                <p class="text-3xl font-bold text-slate-900 dark:text-slate-100">$21,850</p>
                <p class="text-xs text-green-500 flex items-center mt-1">
                    <i class="fas fa-arrow-up mr-1"></i>
                    <span>8.1% vs last month</span>
                </p>
            </div>
            <div
                class="bg-indigo-100 dark:bg-indigo-500/20 text-indigo-500 dark:text-indigo-400 rounded-full w-12 h-12 flex items-center justify-center">
                <i class="fas fa-dollar-sign fa-lg"></i>
            </div>
        </div>

        <!-- Stat Card: Pending Invoices -->
        <div class="bg-white dark:bg-slate-800 rounded-lg shadow p-6 flex items-center justify-between">
            <div>
                <p class="text-sm font-medium text-slate-500 dark:text-slate-400">Pending Invoices</p>
                <p class="text-3xl font-bold text-slate-900 dark:text-slate-100">14</p>
                <p class="text-xs text-red-500 flex items-center mt-1">
                    <i class="fas fa-exclamation-triangle mr-1"></i>
                    <span>$2,150 overdue</span>
                </p>
            </div>
            <div
                class="bg-orange-100 dark:bg-orange-500/20 text-orange-500 dark:text-orange-400 rounded-full w-12 h-12 flex items-center justify-center">
                <i class="fas fa-file-invoice-dollar fa-lg"></i>
            </div>
        </div>
    </div>


    {{-- Charts Section --}}
    <div class="grid grid-cols-1 lg:grid-cols-5 gap-6 mt-6">
        <!-- Bar Chart: Appointments over time -->
        <div class="lg:col-span-3 bg-white dark:bg-slate-800 rounded-lg shadow p-6" x-data="appointmentsChart()">
            <h3 class="text-lg font-semibold text-slate-800 dark:text-slate-200 mb-4">Weekly Appointments</h3>

            <!-- V V V THIS IS THE FIX V V V -->
            <!-- Wrapper with a defined height and relative position -->
            <div class="relative h-80">
                <canvas id="appointmentsChart" x-ref="chart"></canvas>
            </div>
            <!-- ^ ^ ^ THIS IS THE FIX ^ ^ ^ -->

        </div>

        <!-- Doughnut Chart: Services Breakdown -->
        <div class="lg:col-span-2 bg-white dark:bg-slate-800 rounded-lg shadow p-6" x-data="servicesChart()">
            <h3 class="text-lg font-semibold text-slate-800 dark:text-slate-200 mb-4">Services Breakdown</h3>

            <!-- V V V THIS IS THE FIX V V V -->
            <!-- Wrapper with a defined height and relative position -->
            <div class="relative h-80">
                <canvas id="servicesChart" x-ref="chart"></canvas>
            </div>
            <!-- ^ ^ ^ THIS IS THE FIX ^ ^ ^ -->

        </div>
    </div>


    {{-- Table Section (No changes here) --}}
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
                            <th scope="col" class="px-6 py-3">Service</th>
                            <th scope="col" class="px-6 py-3">Doctor</th>
                            <th scope="col" class="px-6 py-3">Time</th>
                            <th scope="col" class="px-6 py-3 text-center">Status</th>
                        </tr>
                    </thead>
                    <tbody>
                        @php
                            $appointments = [
                                [
                                    'name' => 'John Doe',
                                    'service' => 'Annual Check-up',
                                    'doctor' => 'Dr. Smith',
                                    'time' => '10:00 AM',
                                    'status' => 'Confirmed',
                                ],
                                [
                                    'name' => 'Jane Smith',
                                    'service' => 'Teeth Whitening',
                                    'doctor' => 'Dr. Quack',
                                    'time' => '10:30 AM',
                                    'status' => 'Confirmed',
                                ],
                                [
                                    'name' => 'Michael Johnson',
                                    'service' => 'Root Canal Therapy',
                                    'doctor' => 'Dr. Jones',
                                    'time' => '11:00 AM',
                                    'status' => 'Pending',
                                ],
                                [
                                    'name' => 'Emily Davis',
                                    'service' => 'Dental Implant',
                                    'doctor' => 'Dr. Smith',
                                    'time' => '1:00 PM',
                                    'status' => 'Confirmed',
                                ],
                                [
                                    'name' => 'Chris Wilson',
                                    'service' => 'Wisdom Tooth Removal',
                                    'doctor' => 'Dr. Jones',
                                    'time' => '2:30 PM',
                                    'status' => 'Confirmed',
                                ],
                            ];
                        @endphp

                        @foreach ($appointments as $appt)
                            <tr
                                class="bg-white dark:bg-slate-800 border-b dark:border-slate-700 hover:bg-slate-50 dark:hover:bg-slate-600">
                                <td class="px-6 py-4 font-medium text-slate-900 dark:text-white whitespace-nowrap">
                                    {{ $appt['name'] }}</td>
                                <td class="px-6 py-4">{{ $appt['service'] }}</td>
                                <td class="px-6 py-4">{{ $appt['doctor'] }}</td>
                                <td class="px-6 py-4">{{ $appt['time'] }}</td>
                                <td class="px-6 py-4 text-center">
                                    <span @class([
                                        'px-2 py-1 text-xs font-medium rounded-full',
                                        'bg-green-100 text-green-800 dark:bg-green-900 dark:text-green-300' =>
                                            $appt['status'] === 'Confirmed',
                                        'bg-yellow-100 text-yellow-800 dark:bg-yellow-900 dark:text-yellow-300' =>
                                            $appt['status'] === 'Pending',
                                    ])>
                                        {{ $appt['status'] }}
                                    </span>
                                </td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
        </div>
    </div>


    @push('scripts')
        {{-- Chart.js CDN --}}
        <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>

        <script>
            // Alpine.js component for the appointments bar chart
            function appointmentsChart() {
                return {
                    init() {
                        const ctx = this.$refs.chart.getContext('2d');
                        const isDarkMode = document.documentElement.classList.contains('dark');
                        const gridColor = isDarkMode ? 'rgba(255, 255, 255, 0.1)' : 'rgba(0, 0, 0, 0.1)';
                        const textColor = isDarkMode ? '#cbd5e1' : '#475569';

                        new Chart(ctx, {
                            type: 'bar',
                            data: {
                                labels: ['Mon', 'Tue', 'Wed', 'Thu', 'Fri', 'Sat', 'Sun'],
                                datasets: [{
                                    label: 'Appointments',
                                    data: [12, 19, 15, 21, 30, 25, 18],
                                    backgroundColor: 'rgba(59, 130, 246, 0.5)',
                                    borderColor: 'rgba(59, 130, 246, 1)',
                                    borderWidth: 2,
                                    borderRadius: 4,
                                    hoverBackgroundColor: 'rgba(59, 130, 246, 0.7)',
                                }]
                            },
                            options: {
                                responsive: true,
                                maintainAspectRatio: false, // Keep this false to fill the container
                                scales: {
                                    y: {
                                        beginAtZero: true,
                                        grid: {
                                            color: gridColor
                                        },
                                        ticks: {
                                            color: textColor
                                        }
                                    },
                                    x: {
                                        grid: {
                                            display: false
                                        },
                                        ticks: {
                                            color: textColor
                                        }
                                    }
                                },
                                plugins: {
                                    legend: {
                                        display: false
                                    }
                                }
                            }
                        });
                    }
                }
            }

            // Alpine.js component for the services doughnut chart
            function servicesChart() {
                return {
                    init() {
                        const ctx = this.$refs.chart.getContext('2d');
                        const isDarkMode = document.documentElement.classList.contains('dark');
                        const textColor = isDarkMode ? '#cbd5e1' : '#475569';

                        new Chart(ctx, {
                            type: 'doughnut',
                            data: {
                                labels: ['Check-ups', 'Whitening', 'Fillings', 'Implants', 'Braces'],
                                datasets: [{
                                    data: [300, 150, 100, 80, 40],
                                    backgroundColor: ['#3B82F6', '#10B981', '#8B5CF6', '#F97316', '#64748B'],
                                    hoverOffset: 4
                                }]
                            },
                            options: {
                                responsive: true,
                                maintainAspectRatio: false, // Keep this false to fill the container
                                plugins: {
                                    legend: {
                                        position: 'bottom',
                                        labels: {
                                            color: textColor
                                        }
                                    }
                                }
                            }
                        });
                    }
                }
            }
        </script>
    @endpush

</x-layouts.app>
