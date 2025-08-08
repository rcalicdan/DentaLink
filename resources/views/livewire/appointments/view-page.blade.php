<div class="container mx-auto px-6 py-8">
    <div class="flex justify-between items-center mb-6">
        <div>
            <h1 class="text-2xl font-bold text-slate-800 dark:text-slate-200">Appointment Details</h1>
            <p class="text-slate-600 dark:text-slate-400 mt-1">
                View appointment information for {{ $appointment->patient->full_name }}
            </p>
        </div>
        <div class="flex space-x-3">
            @can('update', $appointment)
                <a href="{{ route('appointments.edit', $appointment) }}"
                    class="px-4 py-2 bg-blue-600 hover:bg-blue-700 text-white rounded-md transition-colors">
                    <i class="fas fa-edit mr-2"></i>Edit
                </a>
            @endcan
            <a href="{{ route('appointments.index') }}"
                class="px-4 py-2 bg-slate-500 hover:bg-slate-600 text-white rounded-md transition-colors">
                <i class="fas fa-arrow-left mr-2"></i>Back to List
            </a>
        </div>
    </div>

    @if (session()->has('success'))
        <div class="mb-6 p-4 bg-green-50 border border-green-200 rounded-lg">
            <p class="text-green-800">{{ session('success') }}</p>
        </div>
    @endif

    @if (session()->has('error'))
        <div class="mb-6 p-4 bg-red-50 border border-red-200 rounded-lg">
            <p class="text-red-800">{{ session('error') }}</p>
        </div>
    @endif

    <div class="grid grid-cols-1 lg:grid-cols-3 gap-6">
        {{-- Main Information --}}
        <div class="lg:col-span-2">
            <div class="bg-white dark:bg-slate-800 rounded-lg shadow-sm border border-slate-200 dark:border-slate-700">
                <div class="px-6 py-4 border-b border-slate-200 dark:border-slate-700">
                    <h3 class="text-lg font-medium text-slate-900 dark:text-slate-100">Appointment Information</h3>
                </div>
                <div class="p-6 space-y-6">
                    <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                        <div>
                            <label class="block text-sm font-medium text-slate-700 dark:text-slate-300 mb-1">
                                Appointment ID
                            </label>
                            <p class="text-slate-900 dark:text-slate-100">#{{ $appointment->id }}</p>
                        </div>
                        <div>
                            <label class="block text-sm font-medium text-slate-700 dark:text-slate-300 mb-1">
                                Queue Number
                            </label>
                            <p class="text-slate-900 dark:text-slate-100">#{{ $appointment->queue_number }}</p>
                        </div>
                    </div>

                    <div>
                        <label class="block text-sm font-medium text-slate-700 dark:text-slate-300 mb-1">
                            Patient
                        </label>
                        <div class="flex items-center">
                            <div class="bg-blue-100 dark:bg-blue-900 p-3 rounded-lg">
                                <i class="fas fa-user text-blue-600 dark:text-blue-400"></i>
                            </div>
                            <div class="ml-4">
                                <p class="font-medium text-slate-900 dark:text-slate-100">
                                    {{ $appointment->patient->full_name }}
                                </p>
                                <p class="text-sm text-slate-500 dark:text-slate-400">
                                    {{ $appointment->patient->email }} | {{ $appointment->patient->phone }}
                                </p>
                            </div>
                        </div>
                    </div>

                    <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                        <div>
                            <label class="block text-sm font-medium text-slate-700 dark:text-slate-300 mb-1">
                                Appointment Date
                            </label>
                            <p class="text-slate-900 dark:text-slate-100">
                                {{ $appointment->appointment_date->format('F d, Y') }}
                            </p>
                        </div>
                        <div>
                            <label class="block text-sm font-medium text-slate-700 dark:text-slate-300 mb-1">
                                Branch
                            </label>
                            <p class="text-slate-900 dark:text-slate-100">{{ $appointment->branch->name }}</p>
                        </div>
                    </div>

                    <div>
                        <label class="block text-sm font-medium text-slate-700 dark:text-slate-300 mb-1">
                            Reason for Visit
                        </label>
                        <p class="text-slate-900 dark:text-slate-100">{{ $appointment->reason }}</p>
                    </div>

                    @if($appointment->notes)
                        <div>
                            <label class="block text-sm font-medium text-slate-700 dark:text-slate-300 mb-1">
                                Notes
                            </label>
                            <div class="bg-slate-50 dark:bg-slate-700 p-4 rounded-lg">
                                <p class="text-slate-900 dark:text-slate-100">{{ $appointment->notes }}</p>
                            </div>
                        </div>
                    @endif

                    <div>
                        <label class="block text-sm font-medium text-slate-700 dark:text-slate-300 mb-1">
                            Created By
                        </label>
                        <p class="text-slate-900 dark:text-slate-100">
                            {{ $appointment->creator->first_name }} {{ $appointment->creator->last_name }}
                        </p>
                        <p class="text-sm text-slate-500 dark:text-slate-400">
                            on {{ $appointment->created_at->format('M d, Y \a\t g:i A') }}
                        </p>
                    </div>
                </div>
            </div>
        </div>

        {{-- Status and Actions Sidebar --}}
        <div class="space-y-6">
            {{-- Current Status --}}
            <div class="bg-white dark:bg-slate-800 rounded-lg shadow-sm border border-slate-200 dark:border-slate-700">
                <div class="px-6 py-4 border-b border-slate-200 dark:border-slate-700">
                    <h3 class="text-lg font-medium text-slate-900 dark:text-slate-100">Status</h3>
                </div>
                <div class="p-6">
                    <div class="flex items-center justify-center mb-4">
                        <span class="px-4 py-2 text-sm font-medium rounded-full {{ $appointment->status->getBadgeClass() }}">
                            <i class="fas fa-{{ $appointment->status->getIcon() }} mr-2"></i>
                            {{ $appointment->status->getDisplayName() }}
                        </span>
                    </div>

                    @if(!empty($availableTransitions) && auth()->user()->can('update', $appointment))
                        <div class="space-y-2">
                            <label class="block text-sm font-medium text-slate-700 dark:text-slate-300">
                                Quick Status Change
                            </label>
                            @foreach($availableTransitions as $transition)
                                <button wire:click="updateStatus('{{ $transition->value }}')" 
                                    class="w-full px-3 py-2 text-sm bg-slate-100 hover:bg-slate-200 dark:bg-slate-700 dark:hover:bg-slate-600 rounded-md transition-colors">
                                    Change to {{ $transition->getDisplayName() }}
                                </button>
                            @endforeach
                        </div>
                    @endif
                </div>
            </div>

            {{-- Visit Information --}}
            <div class="bg-white dark:bg-slate-800 rounded-lg shadow-sm border border-slate-200 dark:border-slate-700">
                <div class="px-6 py-4 border-b border-slate-200 dark:border-slate-700">
                    <h3 class="text-lg font-medium text-slate-900 dark:text-slate-100">Visit Status</h3>
                </div>
                <div class="p-6">
                    <div class="flex items-center">
                        @if($appointment->has_visit)
                            <div class="flex items-center text-green-600 dark:text-green-400">
                                <i class="fas fa-check-circle mr-2"></i>
                                <span class="text-sm font-medium">Has Visit Record</span>
                            </div>
                        @else
                            <div class="flex items-center text-slate-500 dark:text-slate-400">
                                <i class="fas fa-clock mr-2"></i>
                                <span class="text-sm font-medium">No Visit Record</span>
                            </div>
                        @endif
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>