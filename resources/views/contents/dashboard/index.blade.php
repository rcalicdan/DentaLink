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


    {{-- AI Chat Floating Button --}}
    @can('view-ai-assistant')
        <div x-data="aiChatModal()" class="fixed bottom-6 right-6 z-50">
            <!-- Floating AI Button -->
            <button @click="toggleModal()"
                class="group relative bg-gradient-to-r from-purple-600 via-blue-600 to-cyan-500 hover:from-purple-700 hover:via-blue-700 hover:to-cyan-600 text-white rounded-full p-4 shadow-2xl hover:shadow-purple-500/25 transition-all duration-300 transform hover:scale-110 focus:outline-none focus:ring-4 focus:ring-purple-500/50">

                <!-- Animated Background Glow -->
                <div
                    class="absolute inset-0 rounded-full bg-gradient-to-r from-purple-400 to-cyan-400 opacity-75 blur-md group-hover:opacity-100 transition-opacity duration-300 animate-pulse">
                </div>

                <!-- AI Icon with Animation -->
                <div class="relative z-10 flex items-center justify-center w-6 h-6">
                    <svg class="w-6 h-6 transform group-hover:rotate-12 transition-transform duration-300"
                        fill="currentColor" viewBox="0 0 24 24">
                        <path
                            d="M12 2C6.48 2 2 6.48 2 12s4.48 10 10 10 10-4.48 10-10S17.52 2 12 2zm-1 17.93c-3.94-.49-7-3.85-7-7.93 0-.62.08-1.21.21-1.79L9 15v1c0 1.1.9 2 2 2v1.93zm6.9-2.54c-.26-.81-1-1.39-1.9-1.39h-1v-3c0-.55-.45-1-1-1H8v-2h2c.55 0 1-.45 1-1V7h2c1.1 0 2-.9 2-2v-.41c2.93 1.19 5 4.06 5 7.41 0 2.08-.8 3.97-2.1 5.39z" />
                    </svg>
                </div>

                <!-- Notification Badge -->
                <div
                    class="absolute -top-1 -right-1 bg-red-500 text-white text-xs rounded-full w-5 h-5 flex items-center justify-center animate-bounce">
                    <span class="text-xs font-bold">AI</span>
                </div>

                <!-- Tooltip -->
                <div
                    class="absolute bottom-full right-0 mb-2 px-3 py-1 bg-slate-900 dark:bg-slate-700 text-white text-sm rounded-lg opacity-0 group-hover:opacity-100 transition-opacity duration-300 whitespace-nowrap">
                    Ask AI Assistant
                    <div
                        class="absolute top-full right-4 w-0 h-0 border-l-4 border-r-4 border-t-4 border-transparent border-t-slate-900 dark:border-t-slate-700">
                    </div>
                </div>
            </button>

            <!-- AI Chat Modal -->
            <div x-show="isOpen" x-transition:enter="transition ease-out duration-300"
                x-transition:enter-start="opacity-0 transform scale-95"
                x-transition:enter-end="opacity-100 transform scale-100"
                x-transition:leave="transition ease-in duration-200"
                x-transition:leave-start="opacity-100 transform scale-100"
                x-transition:leave-end="opacity-0 transform scale-95" class="fixed inset-0 z-50 overflow-y-auto" x-cloak>

                <!-- Backdrop -->
                <div class="fixed inset-0 bg-black/50 backdrop-blur-sm"></div>

                <!-- Modal Container -->
                <div class="flex items-center justify-center min-h-screen p-4">
                    <div
                        class="relative bg-white dark:bg-slate-800 rounded-2xl shadow-2xl w-full max-w-2xl mx-4 overflow-hidden border border-slate-200 dark:border-slate-700">

                        <!-- Header -->
                        <div class="bg-gradient-to-r from-purple-600 via-blue-600 to-cyan-500 px-6 py-4">
                            <div class="flex items-center justify-between">
                                <div class="flex items-center space-x-3">
                                    <div class="bg-white/20 rounded-full p-2">
                                        <svg class="w-6 h-6 text-white animate-spin" fill="currentColor"
                                            viewBox="0 0 24 24">
                                            <path
                                                d="M12 2C6.48 2 2 6.48 2 12s4.48 10 10 10 10-4.48 10-10S17.52 2 12 2zm-1 17.93c-3.94-.49-7-3.85-7-7.93 0-.62.08-1.21.21-1.79L9 15v1c0 1.1.9 2 2 2v1.93zm6.9-2.54c-.26-.81-1-1.39-1.9-1.39h-1v-3c0-.55-.45-1-1-1H8v-2h2c.55 0 1-.45 1-1V7h2c1.1 0 2-.9 2-2v-.41c2.93 1.19 5 4.06 5 7.41 0 2.08-.8 3.97-2.1 5.39z" />
                                        </svg>
                                    </div>
                                    <div>
                                        <h3 class="text-xl font-bold text-white">AI Assistant</h3>
                                        <p class="text-white/80 text-sm">Powered by Gemini AI</p>
                                    </div>
                                </div>
                                <button @click="toggleModal()"
                                    class="text-white/80 hover:text-white transition-colors duration-200">
                                    <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                            d="M6 18L18 6M6 6l12 12" />
                                    </svg>
                                </button>
                            </div>
                        </div>

                        <!-- Chat Container -->
                        <div class="h-96 flex flex-col">
                            <!-- Messages Area -->
                            <div class="flex-1 overflow-y-auto p-6 space-y-4">
                                <!-- AI Welcome Message -->
                                <div class="flex items-start space-x-3">
                                    <div
                                        class="bg-gradient-to-r from-purple-500 to-blue-500 rounded-full p-2 flex-shrink-0">
                                        <svg class="w-4 h-4 text-white" fill="currentColor" viewBox="0 0 24 24">
                                            <path
                                                d="M12 2C6.48 2 2 6.48 2 12s4.48 10 10 10 10-4.48 10-10S17.52 2 12 2zm-1 17.93c-3.94-.49-7-3.85-7-7.93 0-.62.08-1.21.21-1.79L9 15v1c0 1.1.9 2 2 2v1.93zm6.9-2.54c-.26-.81-1-1.39-1.9-1.39h-1v-3c0-.55-.45-1-1-1H8v-2h2c.55 0 1-.45 1-1V7h2c1.1 0 2-.9 2-2v-.41c2.93 1.19 5 4.06 5 7.41 0 2.08-.8 3.97-2.1 5.39z" />
                                        </svg>
                                    </div>
                                    <div class="bg-slate-100 dark:bg-slate-700 rounded-lg p-3 max-w-sm">
                                        <p class="text-slate-800 dark:text-slate-200 text-sm">
                                            Hello! I'm your AI assistant. How can I help you with your dental clinic today?
                                        </p>
                                        <span class="text-xs text-slate-500 dark:text-slate-400">Just now</span>
                                    </div>
                                </div>

                                <!-- Sample User Message -->
                                <div class="flex items-start space-x-3 justify-end">
                                    <div class="bg-gradient-to-r from-blue-500 to-purple-500 rounded-lg p-3 max-w-sm">
                                        <p class="text-white text-sm">
                                            Show me today's appointment summary
                                        </p>
                                        <span class="text-xs text-white/70">2 min ago</span>
                                    </div>
                                    <div
                                        class="bg-gradient-to-r from-blue-500 to-purple-500 rounded-full p-2 flex-shrink-0">
                                        <i class="fas fa-user w-4 h-4 text-white"></i>
                                    </div>
                                </div>

                                <!-- AI Response -->
                                <div class="flex items-start space-x-3">
                                    <div
                                        class="bg-gradient-to-r from-purple-500 to-blue-500 rounded-full p-2 flex-shrink-0">
                                        <svg class="w-4 h-4 text-white" fill="currentColor" viewBox="0 0 24 24">
                                            <path
                                                d="M12 2C6.48 2 2 6.48 2 12s4.48 10 10 10 10-4.48 10-10S17.52 2 12 2zm-1 17.93c-3.94-.49-7-3.85-7-7.93 0-.62.08-1.21.21-1.79L9 15v1c0 1.1.9 2 2 2v1.93zm6.9-2.54c-.26-.81-1-1.39-1.9-1.39h-1v-3c0-.55-.45-1-1-1H8v-2h2c.55 0 1-.45 1-1V7h2c1.1 0 2-.9 2-2v-.41c2.93 1.19 5 4.06 5 7.41 0 2.08-.8 3.97-2.1 5.39z" />
                                        </svg>
                                    </div>
                                    <div class="bg-slate-100 dark:bg-slate-700 rounded-lg p-3 max-w-sm">
                                        <p class="text-slate-800 dark:text-slate-200 text-sm">
                                            You have 42 appointments today: 30 completed, 12 upcoming. Revenue so far:
                                            $1,850. Would you like me to show you the details for specific appointments?
                                        </p>
                                        <span class="text-xs text-slate-500 dark:text-slate-400">1 min ago</span>
                                    </div>
                                </div>
                            </div>

                            <!-- Input Area -->
                            <div class="border-t border-slate-200 dark:border-slate-600 p-4">
                                <div class="flex items-center space-x-3">
                                    <div class="flex-1 relative">
                                        <input type="text" placeholder="Ask me anything about your clinic..."
                                            class="w-full px-4 py-3 bg-slate-50 dark:bg-slate-700 border border-slate-200 dark:border-slate-600 rounded-full focus:outline-none focus:ring-2 focus:ring-purple-500 focus:border-transparent text-slate-800 dark:text-slate-200 placeholder-slate-500 dark:placeholder-slate-400">
                                    </div>
                                    <button
                                        class="bg-gradient-to-r from-purple-500 to-blue-500 hover:from-purple-600 hover:to-blue-600 text-white rounded-full p-3 transition-all duration-200 transform hover:scale-105 focus:outline-none focus:ring-4 focus:ring-purple-500/50">
                                        <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                                d="M12 19l9 2-9-18-9 18 9-2zm0 0v-8" />
                                        </svg>
                                    </button>
                                </div>

                                <!-- Actions Footer -->
                                <div class="flex items-center justify-between mt-3">
                                    <!-- Quick Actions -->
                                    <div class="flex flex-wrap gap-2">
                                        <button
                                            class="px-3 py-1 bg-purple-100 dark:bg-purple-900/30 text-purple-600 dark:text-purple-400 rounded-full text-xs hover:bg-purple-200 dark:hover:bg-purple-900/50 transition-colors duration-200">
                                            📊 Today's Stats
                                        </button>
                                        <button
                                            class="px-3 py-1 bg-blue-100 dark:bg-blue-900/30 text-blue-600 dark:text-blue-400 rounded-full text-xs hover:bg-blue-200 dark:hover:bg-blue-900/50 transition-colors duration-200">
                                            📅 Schedule Overview
                                        </button>
                                        <button
                                            class="px-3 py-1 bg-green-100 dark:bg-green-900/30 text-green-600 dark:text-green-400 rounded-full text-xs hover:bg-green-200 dark:hover:bg-green-900/50 transition-colors duration-200">
                                            💰 Revenue Report
                                        </button>
                                    </div>

                                    <!-- Clear Chat Button -->
                                    <div>
                                        <button
                                            class="inline-flex items-center px-3 py-1 bg-slate-100 dark:bg-slate-700 text-slate-500 dark:text-slate-400 rounded-full text-xs hover:bg-slate-200 dark:hover:bg-slate-600 hover:text-slate-700 dark:hover:text-slate-300 transition-colors duration-200">
                                            <i class="fas fa-eraser mr-1.5"></i>
                                            Clear Chat
                                        </button>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    @endcan

    @push('scripts')
        {{-- Chart.js CDN --}}
        <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>

        <script>
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
                                maintainAspectRatio: false,
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
                                maintainAspectRatio: false,
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

            function aiChatModal() {
                return {
                    isOpen: false,
                    toggleModal() {
                        this.isOpen = !this.isOpen;
                        if (this.isOpen) {
                            document.body.style.overflow = 'hidden';
                        } else {
                            document.body.style.overflow = 'auto';
                        }
                    }
                }
            }
        </script>
    @endpush

</x-layouts.app>
