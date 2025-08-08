<div
    class="min-h-screen bg-gradient-to-br from-slate-50 via-blue-50 to-indigo-50 dark:from-slate-900 dark:via-slate-800 dark:to-slate-900">
    <!-- Header Section with Floating Design -->
    <div class="relative overflow-hidden">
        <!-- Background Pattern -->
        <div
            class="absolute inset-0 bg-gradient-to-r from-blue-600/5 to-indigo-600/5 dark:from-blue-400/5 dark:to-indigo-400/5">
        </div>
        <div class="absolute inset-0"
            style="background-image: radial-gradient(circle at 1px 1px, rgba(99,102,241,0.1) 1px, transparent 0); background-size: 20px 20px;">
        </div>

        <div class="relative container mx-auto px-6 py-8">
            <div class="flex flex-col lg:flex-row lg:justify-between lg:items-start mb-8 space-y-4 lg:space-y-0">
                <div class="space-y-2">
                    <div class="flex items-center space-x-3">
                        <div class="bg-gradient-to-r from-blue-600 to-indigo-600 p-3 rounded-xl shadow-lg">
                            <i class="fas fa-calendar-check text-white text-xl"></i>
                        </div>
                        <div>
                            <h1
                                class="text-3xl font-bold bg-gradient-to-r from-slate-800 to-slate-600 dark:from-slate-200 dark:to-slate-400 bg-clip-text text-transparent">
                                Appointment Details
                            </h1>
                            <p class="text-slate-600 dark:text-slate-400 font-medium">
                                View appointment information for
                                <span
                                    class="text-blue-600 dark:text-blue-400 font-semibold">{{ $appointment->patient->full_name }}</span>
                            </p>
                        </div>
                    </div>
                </div>

                <div class="flex flex-wrap gap-3">
                    @can('update', $appointment)
                        <a href="{{ route('appointments.edit', $appointment) }}"
                            class="group px-6 py-3 bg-gradient-to-r from-blue-600 to-blue-700 hover:from-blue-700 hover:to-blue-800 
                                   text-white font-semibold rounded-xl shadow-lg hover:shadow-xl 
                                   transform hover:-translate-y-1 transition-all duration-200 flex items-center space-x-2">
                            <i class="fas fa-edit group-hover:scale-110 transition-transform duration-200"></i>
                            <span>Edit Appointment</span>
                        </a>
                    @endcan
                    <a href="{{ route('appointments.index') }}"
                        class="group px-6 py-3 bg-white dark:bg-slate-800 hover:bg-slate-50 dark:hover:bg-slate-700 
                               text-slate-700 dark:text-slate-300 font-semibold rounded-xl shadow-lg hover:shadow-xl 
                               border border-slate-200 dark:border-slate-600 transform hover:-translate-y-1 
                               transition-all duration-200 flex items-center space-x-2">
                        <i class="fas fa-arrow-left group-hover:-translate-x-1 transition-transform duration-200"></i>
                        <span>Back to List</span>
                    </a>
                </div>
            </div>

            <x-flash-message/>
        </div>
    </div>

    <!-- Main Content Grid -->
    <div class="container mx-auto px-6 pb-12">
        <div class="grid grid-cols-1 xl:grid-cols-3 gap-8">
            {{-- Main Information --}}
            <div class="xl:col-span-2 space-y-8">
                <!-- Appointment Information Card -->
                <div
                    class="bg-white/70 dark:bg-slate-800/70 backdrop-blur-sm rounded-2xl shadow-xl border border-white/20 dark:border-slate-700/50 overflow-hidden">
                    <!-- Card Header -->
                    <div
                        class="bg-gradient-to-r from-slate-800 to-slate-700 dark:from-slate-700 dark:to-slate-600 px-8 py-6">
                        <div class="flex items-center">
                            <div class="bg-white/20 p-2 rounded-lg mr-3">
                                <i class="fas fa-info-circle text-white text-lg"></i>
                            </div>
                            <h3 class="text-xl font-semibold text-white">Appointment Information</h3>
                        </div>
                    </div>

                    <!-- Card Content -->
                    <div class="p-8 space-y-8">
                        <!-- ID and Queue Number -->
                        <div class="grid grid-cols-1 md:grid-cols-2 gap-8">
                            <div class="group">
                                <label
                                    class="flex items-center text-sm font-semibold text-slate-700 dark:text-slate-300 mb-3">
                                    <div class="bg-blue-100 dark:bg-blue-900/50 p-1.5 rounded-lg mr-2">
                                        <i class="fas fa-hashtag text-blue-600 dark:text-blue-400 text-xs"></i>
                                    </div>
                                    Appointment ID
                                </label>
                                <div
                                    class="bg-slate-50 dark:bg-slate-700/50 p-4 rounded-xl border border-slate-200 dark:border-slate-600">
                                    <p class="text-lg font-bold text-slate-900 dark:text-slate-100">
                                        #{{ $appointment->id }}</p>
                                </div>
                            </div>
                            <div class="group">
                                <label
                                    class="flex items-center text-sm font-semibold text-slate-700 dark:text-slate-300 mb-3">
                                    <div class="bg-purple-100 dark:bg-purple-900/50 p-1.5 rounded-lg mr-2">
                                        <i
                                            class="fas fa-sort-numeric-down text-purple-600 dark:text-purple-400 text-xs"></i>
                                    </div>
                                    Queue Number
                                </label>
                                <div
                                    class="bg-slate-50 dark:bg-slate-700/50 p-4 rounded-xl border border-slate-200 dark:border-slate-600">
                                    <p class="text-lg font-bold text-slate-900 dark:text-slate-100">
                                        #{{ $appointment->queue_number }}</p>
                                </div>
                            </div>
                        </div>

                        <!-- Patient Information -->
                        <div>
                            <label
                                class="flex items-center text-sm font-semibold text-slate-700 dark:text-slate-300 mb-4">
                                <div class="bg-green-100 dark:bg-green-900/50 p-1.5 rounded-lg mr-2">
                                    <i class="fas fa-user text-green-600 dark:text-green-400 text-xs"></i>
                                </div>
                                Patient Information
                            </label>
                            <div
                                class="bg-gradient-to-r from-blue-50 to-indigo-50 dark:from-slate-700/50 dark:to-slate-600/50 p-6 rounded-xl border border-blue-200 dark:border-slate-600">
                                <div class="flex items-center">
                                    <div class="bg-gradient-to-r from-blue-600 to-indigo-600 p-4 rounded-xl shadow-lg">
                                        <i class="fas fa-user text-white text-xl"></i>
                                    </div>
                                    <div class="ml-6">
                                        <p class="text-xl font-bold text-slate-900 dark:text-slate-100">
                                            {{ $appointment->patient->full_name }}
                                        </p>
                                        <div
                                            class="flex flex-col sm:flex-row sm:items-center sm:space-x-4 mt-2 space-y-1 sm:space-y-0">
                                            <p class="flex items-center text-sm text-slate-600 dark:text-slate-400">
                                                <i class="fas fa-envelope mr-2 text-blue-500"></i>
                                                {{ $appointment->patient->email }}
                                            </p>
                                            <p class="flex items-center text-sm text-slate-600 dark:text-slate-400">
                                                <i class="fas fa-phone mr-2 text-green-500"></i>
                                                {{ $appointment->patient->phone }}
                                            </p>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>

                        <!-- Date and Branch -->
                        <div class="grid grid-cols-1 md:grid-cols-2 gap-8">
                            <div>
                                <label
                                    class="flex items-center text-sm font-semibold text-slate-700 dark:text-slate-300 mb-3">
                                    <div class="bg-orange-100 dark:bg-orange-900/50 p-1.5 rounded-lg mr-2">
                                        <i class="fas fa-calendar-day text-orange-600 dark:text-orange-400 text-xs"></i>
                                    </div>
                                    Appointment Date
                                </label>
                                <div
                                    class="bg-slate-50 dark:bg-slate-700/50 p-4 rounded-xl border border-slate-200 dark:border-slate-600">
                                    <p class="text-lg font-semibold text-slate-900 dark:text-slate-100">
                                        {{ $appointment->appointment_date->format('F d, Y') }}
                                    </p>
                                </div>
                            </div>
                            <div>
                                <label
                                    class="flex items-center text-sm font-semibold text-slate-700 dark:text-slate-300 mb-3">
                                    <div class="bg-teal-100 dark:bg-teal-900/50 p-1.5 rounded-lg mr-2">
                                        <i class="fas fa-building text-teal-600 dark:text-teal-400 text-xs"></i>
                                    </div>
                                    Branch Location
                                </label>
                                <div
                                    class="bg-slate-50 dark:bg-slate-700/50 p-4 rounded-xl border border-slate-200 dark:border-slate-600">
                                    <p class="text-lg font-semibold text-slate-900 dark:text-slate-100">
                                        {{ $appointment->branch->name }}</p>
                                </div>
                            </div>
                        </div>

                        <!-- Reason for Visit -->
                        <div>
                            <label
                                class="flex items-center text-sm font-semibold text-slate-700 dark:text-slate-300 mb-3">
                                <div class="bg-rose-100 dark:bg-rose-900/50 p-1.5 rounded-lg mr-2">
                                    <i class="fas fa-stethoscope text-rose-600 dark:text-rose-400 text-xs"></i>
                                </div>
                                Reason for Visit
                            </label>
                            <div
                                class="bg-slate-50 dark:bg-slate-700/50 p-6 rounded-xl border border-slate-200 dark:border-slate-600">
                                <p class="text-slate-900 dark:text-slate-100 leading-relaxed">
                                    {{ $appointment->reason }}</p>
                            </div>
                        </div>

                        @if ($appointment->notes)
                            <!-- Notes -->
                            <div>
                                <label
                                    class="flex items-center text-sm font-semibold text-slate-700 dark:text-slate-300 mb-3">
                                    <div class="bg-amber-100 dark:bg-amber-900/50 p-1.5 rounded-lg mr-2">
                                        <i class="fas fa-sticky-note text-amber-600 dark:text-amber-400 text-xs"></i>
                                    </div>
                                    Additional Notes
                                </label>
                                <div
                                    class="bg-gradient-to-r from-amber-50 to-yellow-50 dark:from-slate-700/50 dark:to-slate-600/50 p-6 rounded-xl border border-amber-200 dark:border-slate-600">
                                    <p class="text-slate-900 dark:text-slate-100 leading-relaxed">
                                        {{ $appointment->notes }}</p>
                                </div>
                            </div>
                        @endif

                        <!-- Created By -->
                        <div>
                            <label
                                class="flex items-center text-sm font-semibold text-slate-700 dark:text-slate-300 mb-3">
                                <div class="bg-indigo-100 dark:bg-indigo-900/50 p-1.5 rounded-lg mr-2">
                                    <i class="fas fa-user-tie text-indigo-600 dark:text-indigo-400 text-xs"></i>
                                </div>
                                Created By
                            </label>
                            <div
                                class="bg-slate-50 dark:bg-slate-700/50 p-6 rounded-xl border border-slate-200 dark:border-slate-600">
                                <p class="text-lg font-semibold text-slate-900 dark:text-slate-100">
                                    {{ $appointment->creator->first_name }} {{ $appointment->creator->last_name }}
                                </p>
                                <p class="text-sm text-slate-500 dark:text-slate-400 mt-1">
                                    <i class="fas fa-clock mr-1"></i>
                                    {{ $appointment->created_at->format('M d, Y \a\t g:i A') }}
                                </p>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            {{-- Status and Actions Sidebar --}}
            <div class="space-y-6">
                {{-- Current Status --}}
                <div
                    class="bg-white/70 dark:bg-slate-800/70 backdrop-blur-sm rounded-2xl shadow-xl border border-white/20 dark:border-slate-700/50 overflow-hidden">
                    <div class="bg-gradient-to-r from-green-600 to-emerald-600 px-6 py-4">
                        <div class="flex items-center">
                            <div class="bg-white/20 p-2 rounded-lg mr-3">
                                <i class="fas fa-check-circle text-white"></i>
                            </div>
                            <h3 class="text-lg font-semibold text-white">Current Status</h3>
                        </div>
                    </div>
                    <div class="p-6">
                        <div class="text-center mb-6">
                            <div
                                class="inline-flex items-center px-6 py-3 rounded-2xl text-base font-semibold shadow-lg {{ $appointment->status->getBadgeClass() }}">
                                <i class="fas fa-{{ $appointment->status->getIcon() }} mr-3 text-lg"></i>
                                {{ $appointment->status->getDisplayName() }}
                            </div>
                        </div>

                        @if (!empty($availableTransitions) && auth()->user()->can('update', $appointment))
                            <div class="space-y-3">
                                <label class="block text-sm font-semibold text-slate-700 dark:text-slate-300 mb-3">
                                    <i class="fas fa-exchange-alt mr-2"></i>Quick Status Change
                                </label>
                                @foreach ($availableTransitions as $transition)
                                    <button wire:click="updateStatus('{{ $transition->value }}')"
                                        class="w-full px-4 py-3 text-sm font-medium bg-slate-50 hover:bg-blue-50 
                                               dark:bg-slate-700 dark:hover:bg-blue-900/50 
                                               border border-slate-200 dark:border-slate-600 
                                               hover:border-blue-300 dark:hover:border-blue-500
                                               rounded-xl transition-all duration-200 
                                               transform hover:scale-105 hover:shadow-md
                                               flex items-center justify-center space-x-2 group">
                                        <i
                                            class="fas fa-arrow-right text-blue-500 group-hover:translate-x-1 transition-transform duration-200"></i>
                                        <span>Change to {{ $transition->getDisplayName() }}</span>
                                    </button>
                                @endforeach
                            </div>
                        @endif
                    </div>
                </div>

                {{-- Visit Information --}}
                <div
                    class="bg-white/70 dark:bg-slate-800/70 backdrop-blur-sm rounded-2xl shadow-xl border border-white/20 dark:border-slate-700/50 overflow-hidden">
                    <div class="bg-gradient-to-r from-purple-600 to-pink-600 px-6 py-4">
                        <div class="flex items-center">
                            <div class="bg-white/20 p-2 rounded-lg mr-3">
                                <i class="fas fa-clipboard-check text-white"></i>
                            </div>
                            <h3 class="text-lg font-semibold text-white">Visit Status</h3>
                        </div>
                    </div>
                    <div class="p-6">
                        <div class="text-center">
                            @if ($appointment->has_visit)
                                <div
                                    class="bg-gradient-to-r from-green-50 to-emerald-50 dark:from-green-900/20 dark:to-emerald-900/20 p-6 rounded-xl border border-green-200 dark:border-green-800">
                                    <div
                                        class="bg-gradient-to-r from-green-600 to-emerald-600 p-3 rounded-full w-fit mx-auto mb-4">
                                        <i class="fas fa-check-circle text-white text-2xl"></i>
                                    </div>
                                    <p class="text-green-800 dark:text-green-200 font-semibold text-lg">Visit Completed
                                    </p>
                                    <p class="text-green-600 dark:text-green-400 text-sm mt-1">Patient has been seen
                                    </p>
                                </div>
                            @else
                                <div
                                    class="bg-gradient-to-r from-slate-50 to-gray-50 dark:from-slate-700/50 dark:to-slate-600/50 p-6 rounded-xl border border-slate-200 dark:border-slate-600">
                                    <div class="bg-slate-400 p-3 rounded-full w-fit mx-auto mb-4">
                                        <i class="fas fa-clock text-white text-2xl"></i>
                                    </div>
                                    <p class="text-slate-800 dark:text-slate-200 font-semibold text-lg">Pending Visit
                                    </p>
                                    <p class="text-slate-600 dark:text-slate-400 text-sm mt-1">No visit record yet</p>
                                </div>
                            @endif
                        </div>
                    </div>
                </div>

                <!-- Appointment Timeline (if you want to add this feature) -->
                <div
                    class="bg-white/70 dark:bg-slate-800/70 backdrop-blur-sm rounded-2xl shadow-xl border border-white/20 dark:border-slate-700/50 overflow-hidden">
                    <div class="bg-gradient-to-r from-orange-600 to-red-600 px-6 py-4">
                        <div class="flex items-center">
                            <div class="bg-white/20 p-2 rounded-lg mr-3">
                                <i class="fas fa-history text-white"></i>
                            </div>
                            <h3 class="text-lg font-semibold text-white">Timeline</h3>
                        </div>
                    </div>
                    <div class="p-6">
                        <div class="space-y-4">
                            <!-- Timeline Item -->
                            <div class="flex items-start space-x-3">
                                <div class="bg-blue-100 dark:bg-blue-900/50 p-2 rounded-full">
                                    <i class="fas fa-plus text-blue-600 dark:text-blue-400 text-sm"></i>
                                </div>
                                <div class="flex-1">
                                    <p class="text-sm font-medium text-slate-900 dark:text-slate-100">Appointment
                                        Created</p>
                                    <p class="text-xs text-slate-500 dark:text-slate-400">
                                        {{ $appointment->created_at->format('M d, Y g:i A') }}
                                    </p>
                                </div>
                            </div>

                            <!-- Current Status Timeline Item -->
                            <div class="flex items-start space-x-3">
                                <div class="bg-green-100 dark:bg-green-900/50 p-2 rounded-full">
                                    <i
                                        class="fas fa-{{ $appointment->status->getIcon() }} text-green-600 dark:text-green-400 text-sm"></i>
                                </div>
                                <div class="flex-1">
                                    <p class="text-sm font-medium text-slate-900 dark:text-slate-100">Status:
                                        {{ $appointment->status->getDisplayName() }}</p>
                                    <p class="text-xs text-slate-500 dark:text-slate-400">
                                        {{ $appointment->updated_at->format('M d, Y g:i A') }}
                                    </p>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Floating Action Button for Mobile -->
    <div class="fixed bottom-6 right-6 lg:hidden z-50">
        @can('update', $appointment)
            <a href="{{ route('appointments.edit', $appointment) }}"
                class="bg-gradient-to-r from-blue-600 to-indigo-600 hover:from-blue-700 hover:to-indigo-700 
                      text-white p-4 rounded-full shadow-2xl hover:shadow-3xl 
                      transform hover:scale-110 transition-all duration-300
                      flex items-center justify-center group">
                <i class="fas fa-edit text-xl group-hover:rotate-12 transition-transform duration-300"></i>
            </a>
        @endcan
    </div>
</div>

@push('styles')
    <style>
        @keyframes float {

            0%,
            100% {
                transform: translateY(0px);
            }

            50% {
                transform: translateY(-10px);
            }
        }

        .floating-animation {
            animation: float 6s ease-in-out infinite;
        }

        .hover-lift:hover {
            transform: translateY(-5px) scale(1.02);
        }

        .glass-effect {
            background: rgba(255, 255, 255, 0.1);
            backdrop-filter: blur(10px);
            border: 1px solid rgba(255, 255, 255, 0.2);
        }

        .gradient-border {
            background: linear-gradient(45deg, #3b82f6, #8b5cf6, #ef4444, #f59e0b);
            background-size: 400% 400%;
            animation: gradientBG 15s ease infinite;
        }

        @keyframes gradientBG {
            0% {
                background-position: 0% 50%;
            }

            50% {
                background-position: 100% 50%;
            }

            100% {
                background-position: 0% 50%;
            }
        }
    </style>
@endpush
