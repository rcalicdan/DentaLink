{{-- resources/views/livewire/appointments/calendar.blade.php --}}

<div>
    <div class="mb-6">
        <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-4">
            <div>
                <h1 class="text-2xl font-bold text-slate-800 dark:text-slate-100">
                    <i class="fas fa-calendar-alt mr-2"></i>Appointments Calendar
                </h1>
                <p class="text-slate-600 dark:text-slate-400 mt-1">View and manage appointments by date</p>
            </div>

            <div class="flex items-center gap-3">
                @if(Auth::user()->isSuperadmin())
                    <select wire:model.live="searchBranch"
                        class="px-4 py-2 border border-slate-300 dark:border-slate-600 rounded-lg bg-white dark:bg-slate-800 text-slate-900 dark:text-slate-100 focus:ring-2 focus:ring-blue-500">
                        <option value="">All Branches</option>
                        @foreach($branches as $branch)
                            <option value="{{ $branch->id }}">{{ $branch->name }}</option>
                        @endforeach
                    </select>
                @endif

                <a href="{{ route('appointments.index') }}"
                    class="px-4 py-2 bg-slate-600 hover:bg-slate-700 text-white rounded-lg transition-colors duration-200 flex items-center gap-2">
                    <i class="fas fa-list"></i>
                    <span>Table View</span>
                </a>
            </div>
        </div>
    </div>

    {{-- Calendar Navigation --}}
    <div class="bg-white dark:bg-slate-800 rounded-lg shadow-lg p-6 mb-6">
        <div class="flex items-center justify-between mb-6">
            <button wire:click="previousMonth"
                class="p-2 hover:bg-slate-100 dark:hover:bg-slate-700 rounded-lg transition-colors duration-200">
                <i class="fas fa-chevron-left text-slate-600 dark:text-slate-400"></i>
            </button>

            <div class="flex items-center gap-4">
                <h2 class="text-xl font-bold text-slate-800 dark:text-slate-100">{{ $currentMonthName }}</h2>
                <button wire:click="goToToday"
                    class="px-3 py-1 text-sm bg-blue-600 hover:bg-blue-700 text-white rounded-lg transition-colors duration-200">
                    Today
                </button>
            </div>

            <button wire:click="nextMonth"
                class="p-2 hover:bg-slate-100 dark:hover:bg-slate-700 rounded-lg transition-colors duration-200">
                <i class="fas fa-chevron-right text-slate-600 dark:text-slate-400"></i>
            </button>
        </div>

        {{-- Legend --}}
        <div class="flex flex-wrap items-center gap-4 mb-4 pb-4 border-b border-slate-200 dark:border-slate-700">
            <span class="text-sm text-slate-600 dark:text-slate-400 font-semibold">Status Legend:</span>
            @foreach($availableStatuses as $status)
                <div class="flex items-center gap-2">
                    <span class="w-3 h-3 rounded-full {{ $status->getBadgeClass() }}"></span>
                    <span class="text-sm text-slate-700 dark:text-slate-300">{{ $status->getDisplayName() }}</span>
                </div>
            @endforeach
        </div>

        {{-- Calendar Grid --}}
        <div class="grid grid-cols-7 gap-2">
            {{-- Day Headers --}}
            @foreach(['Sun', 'Mon', 'Tue', 'Wed', 'Thu', 'Fri', 'Sat'] as $day)
                <div class="text-center font-semibold text-slate-600 dark:text-slate-400 py-2 text-sm">
                    {{ $day }}
                </div>
            @endforeach

            {{-- Calendar Days --}}
            @foreach($calendarDays as $day)
                @php
                    $dateString = $day['dateString'];
                    $data = $appointmentData[$dateString] ?? null;
                    $hasAppointments = $data !== null;
                @endphp

                <div
                    class="min-h-[100px] sm:min-h-[120px] border border-slate-200 dark:border-slate-700 rounded-lg p-2 transition-all duration-200
                    {{ $day['isCurrentMonth'] ? 'bg-white dark:bg-slate-800' : 'bg-slate-50 dark:bg-slate-900' }}
                    {{ $day['isToday'] ? 'ring-2 ring-blue-500' : '' }}
                    {{ $hasAppointments ? 'hover:shadow-lg hover:scale-105 cursor-pointer' : '' }}"
                    wire:click="{{ $hasAppointments ? 'goToDate(\'' . $dateString . '\')' : '' }}">
                    
                    <div class="flex items-center justify-between mb-2">
                        <span class="text-sm font-semibold
                            {{ $day['isToday'] ? 'bg-blue-600 text-white rounded-full w-6 h-6 flex items-center justify-center' : '' }}
                            {{ $day['isCurrentMonth'] ? 'text-slate-700 dark:text-slate-300' : 'text-slate-400 dark:text-slate-600' }}">
                            {{ $day['date']->format('j') }}
                        </span>
                        
                        @if($hasAppointments)
                            <span class="text-xs font-bold bg-blue-600 text-white rounded-full px-2 py-1">
                                {{ $data['total'] }}
                            </span>
                        @endif
                    </div>

                    @if($hasAppointments)
                        <div class="space-y-1">
                            @foreach($data['statuses'] as $statusValue => $count)
                                @php
                                    $status = \App\Enums\AppointmentStatuses::from($statusValue);
                                @endphp
                                <div class="flex items-center justify-between text-xs">
                                    <div class="flex items-center gap-1">
                                        <span class="w-2 h-2 rounded-full {{ $status->getBadgeClass() }}"></span>
                                        <span class="text-slate-600 dark:text-slate-400 truncate">
                                            {{ $status->getDisplayName() }}
                                        </span>
                                    </div>
                                    <span class="font-semibold text-slate-700 dark:text-slate-300">{{ $count }}</span>
                                </div>
                            @endforeach
                        </div>
                    @endif
                </div>
            @endforeach
        </div>
    </div>

    {{-- Quick Stats --}}
    <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-4">
        @foreach($availableStatuses as $status)
            @php
                $statusCount = collect($appointmentData)->sum(function($data) use ($status) {
                    return $data['statuses'][$status->value] ?? 0;
                });
            @endphp
            <div class="bg-white dark:bg-slate-800 rounded-lg shadow p-4">
                <div class="flex items-center justify-between">
                    <div>
                        <p class="text-sm text-slate-600 dark:text-slate-400">{{ $status->getDisplayName() }}</p>
                        <p class="text-2xl font-bold text-slate-800 dark:text-slate-100">{{ $statusCount }}</p>
                    </div>
                    <div class="w-12 h-12 rounded-full {{ $status->getBadgeClass() }} flex items-center justify-center">
                        <i class="{{ $status->getIcon() }} text-white"></i>
                    </div>
                </div>
            </div>
        @endforeach
    </div>
</div>