<div class="container mx-auto px-6 py-8">
    <div class="flex justify-between items-center mb-6">
        <div>
            <h1 class="text-2xl font-bold text-slate-800 dark:text-slate-200">Audit Log Details</h1>
            <p class="text-slate-600 dark:text-slate-400 mt-1">Viewing details for log entry #{{ $auditLog->id }}</p>
        </div>
        <div>
            <x-utils.link-button href="{{ route('audit-logs.index') }}" buttonText="Back to Logs" />
        </div>
    </div>

    <div class="grid grid-cols-1 lg:grid-cols-3 gap-8">
        <!-- Right Column: Metadata (Placed first for mobile-first) -->
        <div class="lg:col-span-1 lg:order-2">
            <div class="bg-white dark:bg-slate-800 rounded-xl shadow-lg border border-slate-200 dark:border-slate-700">
                <div class="p-4 border-b border-slate-200 dark:border-slate-700">
                    <h3 class="text-lg font-semibold text-slate-900 dark:text-slate-100">Log Details</h3>
                </div>
                <div class="p-4 space-y-4">
                    <div class="flex items-start">
                        <i class="fas fa-info-circle w-5 text-center text-slate-400 mt-1"></i>
                        <div class="ml-3">
                            <p class="text-sm font-semibold text-slate-700 dark:text-slate-300">Event</p>
                            <p class="text-sm text-slate-500 dark:text-slate-400">{{ Str::title($auditLog->event) }}</p>
                        </div>
                    </div>
                    <div class="flex items-start">
                        <i class="fas fa-database w-5 text-center text-slate-400 mt-1"></i>
                        <div class="ml-3">
                            <p class="text-sm font-semibold text-slate-700 dark:text-slate-300">Auditable Record</p>
                            <p class="text-sm text-slate-500 dark:text-slate-400">
                                {{ class_basename($auditLog->auditable_type) }} #{{ $auditLog->auditable_id }}</p>
                        </div>
                    </div>
                    <div class="flex items-start">
                        <i class="fas fa-user w-5 text-center text-slate-400 mt-1"></i>
                        <div class="ml-3">
                            <p class="text-sm font-semibold text-slate-700 dark:text-slate-300">Performed By</p>
                            <p class="text-sm text-slate-500 dark:text-slate-400">
                                {{ $auditLog->user?->full_name ?? 'System' }}</p>
                        </div>
                    </div>
                    <div class="flex items-start">
                        <i class="fas fa-clock w-5 text-center text-slate-400 mt-1"></i>
                        <div class="ml-3">
                            <p class="text-sm font-semibold text-slate-700 dark:text-slate-300">Timestamp</p>
                            <p class="text-sm text-slate-500 dark:text-slate-400">
                                {{ $auditLog->created_at->format('M j, Y, g:i A') }}</p>
                        </div>
                    </div>
                    <div class="flex items-start">
                        <i class="fas fa-network-wired w-5 text-center text-slate-400 mt-1"></i>
                        <div class="ml-3">
                            <p class="text-sm font-semibold text-slate-700 dark:text-slate-300">IP Address</p>
                            <p class="text-sm text-slate-500 dark:text-slate-400">{{ $auditLog->ip_address }}</p>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- Left Column: Changes -->
        <div class="lg:col-span-2 lg:order-1">
            <div class="bg-white dark:bg-slate-800 rounded-xl shadow-lg border border-slate-200 dark:border-slate-700">
                <div class="p-4 border-b border-slate-200 dark:border-slate-700">
                    <h3 class="text-lg font-semibold text-slate-900 dark:text-slate-100">Changes</h3>
                    <p class="text-sm text-slate-500 dark:text-slate-400 mt-1">{{ $auditLog->message }}</p>
                </div>
                <div class="p-4">
                    @if (empty($this->changes))
                        <div class="text-center py-8 text-slate-500 dark:text-slate-400">
                            <i class="fas fa-check-circle text-2xl mb-2"></i>
                            <p>No field values were changed, or this was a creation/deletion event.</p>
                        </div>
                    @else
                        <!-- Mobile Card Layout (hidden on desktop) -->
                        <div class="block md:hidden space-y-4">
                            @foreach ($this->changes as $change)
                                <div
                                    class="bg-slate-50 dark:bg-slate-700/30 rounded-lg border border-slate-200 dark:border-slate-600 p-4">
                                    <!-- Field Name Header -->
                                    <div
                                        class="flex items-center mb-3 pb-2 border-b border-slate-200 dark:border-slate-600">
                                        <i class="fas fa-edit text-slate-400 mr-2"></i>
                                        <h4 class="font-semibold text-slate-800 dark:text-slate-200">
                                            {{ $change['field'] }}</h4>
                                    </div>

                                    <!-- Old Value -->
                                    <div class="mb-4">
                                        <div class="flex items-center mb-2">
                                            <i class="fas fa-arrow-left text-red-500 mr-2 text-sm"></i>
                                            <span
                                                class="text-xs font-medium text-red-600 dark:text-red-400 uppercase tracking-wide">Old
                                                Value</span>
                                        </div>
                                        <div
                                            class="bg-red-50 dark:bg-red-900/20 border border-red-200 dark:border-red-800 rounded-md p-3">
                                            <pre class="text-sm text-red-700 dark:text-red-300 whitespace-pre-wrap break-all font-mono">{{ is_array($change['old']) ? json_encode($change['old'], JSON_PRETTY_PRINT) : $change['old'] ?? '—' }}</pre>
                                        </div>
                                    </div>

                                    <!-- New Value -->
                                    <div>
                                        <div class="flex items-center mb-2">
                                            <i class="fas fa-arrow-right text-green-500 mr-2 text-sm"></i>
                                            <span
                                                class="text-xs font-medium text-green-600 dark:text-green-400 uppercase tracking-wide">New
                                                Value</span>
                                        </div>
                                        <div
                                            class="bg-green-50 dark:bg-green-900/20 border border-green-200 dark:border-green-800 rounded-md p-3">
                                            <pre class="text-sm text-green-700 dark:text-green-300 whitespace-pre-wrap break-all font-mono">{{ is_array($change['new']) ? json_encode($change['new'], JSON_PRETTY_PRINT) : $change['new'] ?? '—' }}</pre>
                                        </div>
                                    </div>
                                </div>
                            @endforeach
                        </div>

                        <!-- Desktop Table Layout (hidden on mobile) -->
                        <div class="hidden md:block overflow-x-auto">
                            <table class="min-w-full divide-y divide-slate-200 dark:divide-slate-700">
                                <thead class="bg-slate-50 dark:bg-slate-700/50">
                                    <tr>
                                        <th
                                            class="px-4 py-3 text-left text-xs font-semibold text-slate-600 dark:text-slate-300 uppercase tracking-wider">
                                            Field
                                        </th>
                                        <th
                                            class="px-4 py-3 text-left text-xs font-semibold text-slate-600 dark:text-slate-300 uppercase tracking-wider">
                                            <i class="fas fa-arrow-left text-red-500 mr-1"></i>
                                            Old Value
                                        </th>
                                        <th
                                            class="px-4 py-3 text-left text-xs font-semibold text-slate-600 dark:text-slate-300 uppercase tracking-wider">
                                            <i class="fas fa-arrow-right text-green-500 mr-1"></i>
                                            New Value
                                        </th>
                                    </tr>
                                </thead>
                                <tbody
                                    class="bg-white dark:bg-slate-800 divide-y divide-slate-200 dark:divide-slate-700">
                                    @foreach ($this->changes as $change)
                                        <tr
                                            class="hover:bg-slate-50 dark:hover:bg-slate-700/50 transition-colors duration-150">
                                            <td
                                                class="px-4 py-4 whitespace-nowrap text-sm font-medium text-slate-800 dark:text-slate-200">
                                                <div class="flex items-center">
                                                    <i class="fas fa-edit text-slate-400 mr-2"></i>
                                                    {{ $change['field'] }}
                                                </div>
                                            </td>
                                            <td class="px-4 py-4 text-sm">
                                                <div
                                                    class="bg-red-50 dark:bg-red-900/20 border border-red-200 dark:border-red-800 rounded-md p-2 max-w-xs">
                                                    <pre class="whitespace-pre-wrap text-red-700 dark:text-red-300 font-mono text-xs break-all">{{ is_array($change['old']) ? json_encode($change['old'], JSON_PRETTY_PRINT) : $change['old'] ?? '—' }}</pre>
                                                </div>
                                            </td>
                                            <td class="px-4 py-4 text-sm">
                                                <div
                                                    class="bg-green-50 dark:bg-green-900/20 border border-green-200 dark:border-green-800 rounded-md p-2 max-w-xs">
                                                    <pre class="whitespace-pre-wrap text-green-700 dark:text-green-300 font-mono text-xs break-all">{{ is_array($change['new']) ? json_encode($change['new'], JSON_PRETTY_PRINT) : $change['new'] ?? '—' }}</pre>
                                                </div>
                                            </td>
                                        </tr>
                                    @endforeach
                                </tbody>
                            </table>
                        </div>
                    @endif
                </div>
            </div>
        </div>
    </div>
</div>
