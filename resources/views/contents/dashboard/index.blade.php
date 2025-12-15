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
        <div class="mt-8 mb-10">
            <!-- Main Card: Deep Blue Gradient Theme -->
            <div
                class="relative overflow-hidden rounded-2xl bg-gradient-to-br from-blue-950 via-indigo-950 to-slate-900 border border-indigo-500/30 shadow-2xl shadow-indigo-900/20">

                <!-- Decorative Background Glows -->
                <div class="absolute top-0 right-0 -mr-20 -mt-20 w-96 h-96 rounded-full bg-cyan-500/20 blur-3xl opacity-40">
                </div>
                <div
                    class="absolute bottom-0 left-0 -ml-20 -mb-20 w-80 h-80 rounded-full bg-blue-600/20 blur-3xl opacity-40">
                </div>

                <!-- Header -->
                <div
                    class="relative z-10 border-b border-white/10 bg-white/5 p-6 flex flex-col md:flex-row md:items-center justify-between gap-4 backdrop-blur-sm">
                    <div class="flex items-center">
                        <div>
                            <h3 class="text-xl font-bold text-white tracking-wide drop-shadow-sm">
                                AI Business Forecast
                            </h3>
                            <div class="flex items-center gap-2 text-sm mt-1">
                                @if ($aiForecast['success'])
                                    <span
                                        class="inline-flex items-center px-2 py-0.5 rounded text-xs font-medium bg-cyan-500/10 text-cyan-300 border border-cyan-500/20">
                                        <i class="fas fa-check-circle mr-1.5"></i>Analysis Ready
                                    </span>
                                    <span class="text-indigo-300/50">•</span>
                                    <span class="text-indigo-200">{{ $aiForecast['generated_at'] }}</span>
                                @else
                                    <span class="text-red-300 font-medium bg-red-500/10 px-2 py-0.5 rounded">Generation
                                        Failed</span>
                                @endif
                            </div>
                        </div>
                    </div>

                    <!-- Refresh Button -->
                    <form action="{{ route('dashboard.refresh-forecast') }}" method="POST">
                        @csrf
                        <button type="submit"
                            class="group relative inline-flex items-center justify-center px-5 py-2 text-sm font-medium text-white transition-all duration-200 bg-white/10 border border-white/10 rounded-lg hover:bg-white/20 hover:border-white/20 hover:shadow-lg hover:shadow-indigo-500/20 focus:outline-none focus:ring-2 focus:ring-cyan-400">
                            <i class="fas fa-sync-alt mr-2 text-cyan-300 transition-transform group-hover:rotate-180"></i>
                            Update Analysis
                        </button>
                    </form>
                </div>

                <!-- Content Body -->
                <div class="relative z-10 p-6 md:p-8">
                    @if ($aiForecast['success'])
                        {{-- 
                       CUSTOM CSS STYLING (Blue Theme)
                       We use text-indigo-100 and blue-50/10 backgrounds
                    --}}
                        <div
                            class="text-indigo-100 leading-relaxed space-y-4
                        
                        {{-- Headers --}}
                        [&>h1]:text-2xl [&>h1]:font-bold [&>h1]:text-white [&>h1]:mb-6
                        [&>h2]:text-xl [&>h2]:font-bold [&>h2]:text-cyan-200 [&>h2]:mt-8 [&>h2]:mb-4
                        [&>h3]:text-lg [&>h3]:font-bold [&>h3]:text-indigo-300 [&>h3]:mt-6 [&>h3]:mb-3 [&>h3]:uppercase [&>h3]:tracking-wider
                        
                        {{-- Lists (The bullet points) converted to 'Cards' --}}
                        [&>ul]:grid [&>ul]:grid-cols-1 [&>ul]:md:grid-cols-2 [&>ul]:gap-4 [&>ul]:my-4
                        
                        {{-- List Items (Card Style) --}}
                        [&>ul>li]:relative 
                        [&>ul>li]:bg-white/5 
                        [&>ul>li]:backdrop-blur-md 
                        [&>ul>li]:p-5 
                        [&>ul>li]:rounded-xl 
                        [&>ul>li]:border 
                        [&>ul>li]:border-white/5 
                        [&>ul>li]:transition 
                        [&>ul>li]:duration-300
                        [&>ul>li]:hover:bg-white/10 
                        [&>ul>li]:hover:border-cyan-400/30
                        
                        {{-- Remove default list bullets --}}
                        [&>ul>li]:list-none
                        
                        {{-- Bold Text (Numbers/Metrics) --}}
                        [&_strong]:text-cyan-300 [&_strong]:font-bold [&_strong]:text-lg [&_strong]:drop-shadow-sm
                        
                        {{-- Paragraphs --}}
                        [&>p]:mb-4 [&>p]:text-blue-100/90
                    ">

                            {{-- PARSE MARKDOWN TO HTML --}}
                            {!! Str::markdown($aiForecast['forecast']) !!}

                        </div>
                    @else
                        <!-- Error State -->
                        <div
                            class="flex flex-col items-center justify-center py-10 text-center bg-blue-900/20 rounded-xl border border-dashed border-blue-700/50">
                            <div class="w-16 h-16 bg-blue-800/30 rounded-full flex items-center justify-center mb-4">
                                <i class="fas fa-robot text-3xl text-blue-400"></i>
                            </div>
                            <h4 class="text-lg font-medium text-white">Analysis Unavailable</h4>
                            <p class="text-blue-200 text-sm mt-1 max-w-md">{{ $aiForecast['error'] }}</p>
                        </div>
                    @endif
                </div>

                <!-- Footer -->
                <div
                    class="px-6 py-3 bg-black/20 border-t border-white/5 flex items-center justify-between text-xs text-indigo-300/70">
                    <span class="flex items-center">
                        <i class="fas fa-shield-alt mr-1.5 text-cyan-500/70"></i>
                        Secure AI Analysis
                    </span>
                    <span>
                        Powered by Gemini
                    </span>
                </div>
            </div>
        </div>
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
