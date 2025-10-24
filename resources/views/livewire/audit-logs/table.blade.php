<div>
    <x-flash-session />
    <x-flash-message />

    <div
        class="bg-white dark:bg-slate-800 rounded-lg shadow-md border border-slate-200 dark:border-slate-700 mb-6 overflow-hidden">
        <div class="bg-gradient-to-r from-slate-600 to-slate-700 px-6 py-4">
            <h3 class="text-lg font-semibold text-white flex items-center">
                <i class="fas fa-filter text-white/70 mr-3"></i>
                Filter Audit Logs
            </h3>
        </div>
        <div class="p-6 bg-slate-50/50 dark:bg-slate-800/50">
            <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-5 gap-6">
                <!-- Search Filter -->
                <div class="space-y-2">
                    <label class="block text-sm font-medium text-slate-700 dark:text-slate-300">
                        <i class="fas fa-search text-slate-400 mr-2"></i>
                        Search
                    </label>
                    <div class="relative">
                        <input type="text" wire:model.live.debounce.300ms="search"
                            placeholder="Search descriptions..."
                            class="w-full pl-10 pr-4 py-2.5 text-sm bg-white dark:bg-slate-700 border border-slate-300 dark:border-slate-600 rounded-lg shadow-sm focus:ring-2 focus:ring-blue-500 focus:border-blue-500 dark:text-slate-300 transition-all duration-200">
                        <div class="absolute inset-y-0 left-0 pl-3 flex items-center pointer-events-none">
                            <i class="fas fa-search text-slate-400 text-sm"></i>
                        </div>
                    </div>
                </div>

                <!-- Date Filter -->
                <div class="space-y-2">
                    <label class="block text-sm font-medium text-slate-700 dark:text-slate-300">
                        <i class="fas fa-calendar-alt text-slate-400 mr-2"></i>
                        Date
                    </label>
                    <div class="relative">
                        <input type="date" wire:model.live="searchDate"
                            class="w-full pl-10 pr-4 py-2.5 text-sm bg-white dark:bg-slate-700 border border-slate-300 dark:border-slate-600 rounded-lg shadow-sm focus:ring-2 focus:ring-blue-500 focus:border-blue-500 dark:text-slate-300 transition-all duration-200">
                        <div class="absolute inset-y-0 left-0 pl-3 flex items-center pointer-events-none">
                            <i class="fas fa-calendar-alt text-slate-400 text-sm"></i>
                        </div>
                    </div>
                </div>

                <!-- Event Type Filter -->
                <div class="space-y-2">
                    <label class="block text-sm font-medium text-slate-700 dark:text-slate-300">
                        <i class="fas fa-tag text-slate-400 mr-2"></i>
                        Event Type
                    </label>
                    <div class="relative">
                        <select wire:model.live="searchEvent"
                            class="w-full pl-10 pr-8 py-2.5 text-sm bg-white dark:bg-slate-700 border border-slate-300 dark:border-slate-600 rounded-lg shadow-sm focus:ring-2 focus:ring-blue-500 focus:border-blue-500 dark:text-slate-300 transition-all duration-200 appearance-none">
                            <option value="">All Events</option>
                            @foreach ($eventTypes as $event)
                                <option value="{{ $event }}">{{ ucfirst($event) }}</option>
                            @endforeach
                        </select>
                        <div class="absolute inset-y-0 left-0 pl-3 flex items-center pointer-events-none">
                            <i class="fas fa-tag text-slate-400 text-sm"></i>
                        </div>
                        <div class="absolute inset-y-0 right-0 pr-3 flex items-center pointer-events-none">
                            <i class="fas fa-chevron-down text-slate-400 text-sm"></i>
                        </div>
                    </div>
                </div>

                <!-- User Filter -->
                <div class="space-y-2">
                    <label class="block text-sm font-medium text-slate-700 dark:text-slate-300">
                        <i class="fas fa-user text-slate-400 mr-2"></i>
                        Performed By
                    </label>
                    <div class="relative">
                        <select wire:model.live="searchUser"
                            class="w-full pl-10 pr-8 py-2.5 text-sm bg-white dark:bg-slate-700 border border-slate-300 dark:border-slate-600 rounded-lg shadow-sm focus:ring-2 focus:ring-blue-500 focus:border-blue-500 dark:text-slate-300 transition-all duration-200 appearance-none">
                            <option value="">All Users</option>
                            @foreach ($users as $user)
                                <option value="{{ $user->id }}">{{ $user->full_name }}</option>
                            @endforeach
                        </select>
                        <div class="absolute inset-y-0 left-0 pl-3 flex items-center pointer-events-none">
                            <i class="fas fa-user text-slate-400 text-sm"></i>
                        </div>
                        <div class="absolute inset-y-0 right-0 pr-3 flex items-center pointer-events-none">
                            <i class="fas fa-chevron-down text-slate-400 text-sm"></i>
                        </div>
                    </div>
                </div>

                <!-- Branch Filter -->
                <div class="space-y-2">
                    <label class="block text-sm font-medium text-slate-700 dark:text-slate-300">
                        <i class="fas fa-building text-slate-400 mr-2"></i>
                        Branch
                    </label>
                    <div class="relative">
                        <select wire:model.live="searchBranch"
                            class="w-full pl-10 pr-8 py-2.5 text-sm bg-white dark:bg-slate-700 border border-slate-300 dark:border-slate-600 rounded-lg shadow-sm focus:ring-2 focus:ring-blue-500 focus:border-blue-500 dark:text-slate-300 transition-all duration-200 appearance-none">
                            <option value="">All Branches</option>
                            @foreach ($branches as $branch)
                                <option value="{{ $branch->id }}">{{ $branch->name }}</option>
                            @endforeach
                        </select>
                        <div class="absolute inset-y-0 left-0 pl-3 flex items-center pointer-events-none">
                            <i class="fas fa-building text-slate-400 text-sm"></i>
                        </div>
                        <div class="absolute inset-y-0 right-0 pr-3 flex items-center pointer-events-none">
                            <i class="fas fa-chevron-down text-slate-400 text-sm"></i>
                        </div>
                    </div>
                </div>
            </div>

            @if ($search || $searchDate || $searchEvent || $searchUser || $searchBranch)
                <div class="mt-6 pt-4 border-t border-slate-200 dark:border-slate-600">
                    <button wire:click="clearFilters" type="button"
                        class="inline-flex items-center px-4 py-2 text-sm font-medium text-slate-600 bg-white hover:bg-slate-50 dark:bg-slate-700 dark:text-slate-300 dark:hover:bg-slate-600 border border-slate-300 dark:border-slate-600 rounded-lg shadow-sm transition-all duration-200 hover:shadow-md">
                        <i class="fas fa-times mr-2"></i>
                        Clear All Filters
                    </button>
                </div>
            @endif
        </div>
    </div>

    <x-partials.table-header title="Audit Logs" />

    <!-- Active Filter Indicators -->
    <div class="flex flex-wrap items-center gap-2 mb-4">
        @if ($searchDate)
            <span
                class="inline-flex items-center px-2.5 py-1 rounded-full text-xs font-medium bg-blue-100 text-blue-800 dark:bg-blue-900 dark:text-blue-200">
                <i class="fas fa-calendar-alt mr-1.5"></i>
                Date: {{ \Carbon\Carbon::parse($searchDate)->format('M j, Y') }}
                <button wire:click="$set('searchDate', '')" class="ml-1.5 text-blue-500 hover:text-blue-700">
                    <i class="fas fa-times-circle"></i>
                </button>
            </span>
        @endif
        @if ($searchEvent)
            <span
                class="inline-flex items-center px-2.5 py-1 rounded-full text-xs font-medium bg-purple-100 text-purple-800 dark:bg-purple-900 dark:text-purple-200">
                <i class="fas fa-tag mr-1.5"></i>
                Event: {{ ucfirst($searchEvent) }}
                <button wire:click="$set('searchEvent', '')" class="ml-1.5 text-purple-500 hover:text-purple-700">
                    <i class="fas fa-times-circle"></i>
                </button>
            </span>
        @endif
        @if ($searchUser)
            @php $user = \App\Models\User::find($searchUser); @endphp
            @if ($user)
                <span
                    class="inline-flex items-center px-2.5 py-1 rounded-full text-xs font-medium bg-green-100 text-green-800 dark:bg-green-900 dark:text-green-200">
                    <i class="fas fa-user mr-1.5"></i>
                    User: {{ $user->full_name }}
                    <button wire:click="$set('searchUser', '')" class="ml-1.5 text-green-500 hover:text-green-700">
                        <i class="fas fa-times-circle"></i>
                    </button>
                </span>
            @endif
        @endif
        @if ($searchBranch)
            @php $branch = \App\Models\Branch::find($searchBranch); @endphp
            @if ($branch)
                <span
                    class="inline-flex items-center px-2.5 py-1 rounded-full text-xs font-medium bg-amber-100 text-amber-800 dark:bg-amber-900 dark:text-amber-200">
                    <i class="fas fa-building mr-1.5"></i>
                    Branch: {{ $branch->name }}
                    <button wire:click="$set('searchBranch', '')" class="ml-1.5 text-amber-500 hover:text-amber-700">
                        <i class="fas fa-times-circle"></i>
                    </button>
                </span>
            @endif
        @endif
    </div>

    <!-- Mobile Card Layout (hidden on desktop) -->
    <div class="block md:hidden space-y-4">
        @forelse ($auditLogs as $auditLog)
            <div
                class="bg-white dark:bg-slate-800 rounded-lg border border-slate-200 dark:border-slate-700 shadow-sm p-4">
                <!-- Header with Event Badge and Action -->
                <div class="flex items-center justify-between mb-3">
                    <span
                        class="px-2 py-1 text-xs font-semibold rounded-full {{ $this->getEventBadgeClass($auditLog->event) }}">
                        {{ ucfirst($auditLog->event) }}
                    </span>
                    @can('view', $auditLog)
                        <x-utils.view-button :route="route('audit-logs.view', $auditLog->id)" />
                    @endcan
                </div>

                <!-- Content Grid -->
                <div class="space-y-2">
                    <!-- Model & ID -->
                    <div class="flex justify-between items-center py-1">
                        <span class="text-xs font-medium text-slate-500 dark:text-slate-400">Model & ID:</span>
                        <div class="text-right">
                            <div class="text-sm text-slate-700 dark:text-slate-300">
                                {{ class_basename($auditLog->auditable_type) }}</div>
                            <div class="text-xs text-slate-500 dark:text-slate-400">ID: {{ $auditLog->auditable_id }}
                            </div>
                        </div>
                    </div>

                    <!-- User -->
                    <div class="flex justify-between items-center py-1">
                        <span class="text-xs font-medium text-slate-500 dark:text-slate-400">User:</span>
                        <span class="text-sm text-slate-700 dark:text-slate-300">
                            @if ($auditLog->user)
                                {{ $auditLog->user->full_name }}
                            @else
                                <span class="text-slate-500 italic">System</span>
                            @endif
                        </span>
                    </div>

                    <!-- Branch -->
                    <div class="flex justify-between items-center py-1">
                        <span class="text-xs font-medium text-slate-500 dark:text-slate-400">Branch:</span>
                        <span class="text-sm text-slate-700 dark:text-slate-300">
                            {{ $auditLog->branch?->name ?? 'N/A' }}
                        </span>
                    </div>

                    <!-- IP Address -->
                    <div class="flex justify-between items-center py-1">
                        <span class="text-xs font-medium text-slate-500 dark:text-slate-400">IP Address:</span>
                        <span
                            class="text-sm text-slate-700 dark:text-slate-300">{{ $auditLog->ip_address ?? 'N/A' }}</span>
                    </div>

                    <!-- Date & Time -->
                    <div class="flex justify-between items-center py-1">
                        <span class="text-xs font-medium text-slate-500 dark:text-slate-400">Date & Time:</span>
                        <span
                            class="text-sm text-slate-700 dark:text-slate-300">{{ $auditLog->created_at->format('M d, Y, h:i A') }}</span>
                    </div>
                </div>
            </div>
        @empty
            <div
                class="bg-white dark:bg-slate-800 rounded-lg border border-slate-200 dark:border-slate-700 p-8 text-center">
                <div class="text-slate-500 dark:text-slate-400">
                    <i class="fas fa-box-open text-4xl mb-3"></i>
                    <p>No audit logs found for the selected filters.</p>
                </div>
            </div>
        @endforelse
    </div>

    <!-- Desktop Table Layout (hidden on mobile) -->
    <div
        class="hidden md:block overflow-x-auto bg-white dark:bg-slate-800 shadow-md rounded-lg border border-slate-200 dark:border-slate-700">
        <table class="min-w-full divide-y divide-slate-200 dark:divide-slate-700">
            <thead class="bg-slate-50 dark:bg-slate-700/50">
                <tr>
                    <th scope="col"
                        class="px-6 py-3 text-left text-xs font-medium text-slate-500 dark:text-slate-400 uppercase tracking-wider cursor-pointer"
                        wire:click="sortBy('event')">
                        <div class="flex items-center">
                            Event
                            @if ($sortColumn === 'event')
                                <i class="fas fa-sort-{{ $sortDirection === 'asc' ? 'up' : 'down' }} ml-2"></i>
                            @endif
                        </div>
                    </th>
                    <th scope="col"
                        class="px-6 py-3 text-left text-xs font-medium text-slate-500 dark:text-slate-400 uppercase tracking-wider">
                        Model & Record ID
                    </th>
                    <th scope="col"
                        class="px-6 py-3 text-left text-xs font-medium text-slate-500 dark:text-slate-400 uppercase tracking-wider cursor-pointer"
                        wire:click="sortBy('user_id')">
                        <div class="flex items-center">
                            User
                            @if ($sortColumn === 'user_id')
                                <i class="fas fa-sort-{{ $sortDirection === 'asc' ? 'up' : 'down' }} ml-2"></i>
                            @endif
                        </div>
                    </th>
                    <th scope="col"
                        class="px-6 py-3 text-left text-xs font-medium text-slate-500 dark:text-slate-400 uppercase tracking-wider cursor-pointer"
                        wire:click="sortBy('branch_id')">
                        <div class="flex items-center">
                            Branch
                            @if ($sortColumn === 'branch_id')
                                <i class="fas fa-sort-{{ $sortDirection === 'asc' ? 'up' : 'down' }} ml-2"></i>
                            @endif
                        </div>
                    </th>
                    <th scope="col"
                        class="px-6 py-3 text-left text-xs font-medium text-slate-500 dark:text-slate-400 uppercase tracking-wider cursor-pointer"
                        wire:click="sortBy('ip_address')">
                        <div class="flex items-center">
                            IP Address
                            @if ($sortColumn === 'ip_address')
                                <i class="fas fa-sort-{{ $sortDirection === 'asc' ? 'up' : 'down' }} ml-2"></i>
                            @endif
                        </div>
                    </th>
                    <th scope="col"
                        class="px-6 py-3 text-left text-xs font-medium text-slate-500 dark:text-slate-400 uppercase tracking-wider cursor-pointer"
                        wire:click="sortBy('created_at')">
                        <div class="flex items-center">
                            Date & Time
                            @if ($sortColumn === 'created_at')
                                <i class="fas fa-sort-{{ $sortDirection === 'asc' ? 'up' : 'down' }} ml-2"></i>
                            @endif
                        </div>
                    </th>
                    <th scope="col" class="relative px-6 py-3">
                        <span class="sr-only">Actions</span>
                    </th>
                </tr>
            </thead>
            <tbody class="bg-white dark:bg-slate-800 divide-y divide-slate-200 dark:divide-slate-700">
                @forelse ($auditLogs as $auditLog)
                    <tr
                        class="hover:bg-slate-50 dark:hover:bg-slate-700/50 transition-colors duration-150 ease-in-out">
                        <td class="px-6 py-4 whitespace-nowrap">
                            <span
                                class="px-2 inline-flex text-xs leading-5 font-semibold rounded-full {{ $this->getEventBadgeClass($auditLog->event) }}">
                                {{ ucfirst($auditLog->event) }}
                            </span>
                        </td>
                        <td class="px-6 py-4 whitespace-nowrap text-sm text-slate-700 dark:text-slate-300">
                            {{ class_basename($auditLog->auditable_type) }}
                            <div class="text-xs text-slate-500 dark:text-slate-400">ID: {{ $auditLog->auditable_id }}
                            </div>
                        </td>
                        <td class="px-6 py-4 whitespace-nowrap text-sm text-slate-700 dark:text-slate-300">
                            @if ($auditLog->user)
                                {{ $auditLog->user->full_name }}
                            @else
                                <span class="text-slate-500 italic">System</span>
                            @endif
                        </td>
                        <td class="px-6 py-4 whitespace-nowrap text-sm text-slate-700 dark:text-slate-300">
                            {{ $auditLog->branch?->name ?? 'N/A' }}
                        </td>
                        <td class="px-6 py-4 whitespace-nowrap text-sm text-slate-700 dark:text-slate-300">
                            {{ $auditLog->ip_address ?? 'N/A' }}
                        </td>
                        <td class="px-6 py-4 whitespace-nowrap text-sm text-slate-700 dark:text-slate-300">
                            {{ $auditLog->created_at->format('M d, Y, h:i A') }}
                        </td>
                        <td class="px-6 py-4 whitespace-nowrap text-right text-sm font-medium">
                            @can('view', $auditLog)
                                <x-utils.view-button :route="route('audit-logs.view', $auditLog->id)" />
                            @endcan
                        </td>
                    </tr>
                @empty
                    <tr>
                        <td colspan="7" class="px-6 py-12 text-center">
                            <div class="text-slate-500 dark:text-slate-400">
                                <i class="fas fa-box-open text-4xl mb-3"></i>
                                <p>No audit logs found for the selected filters.</p>
                            </div>
                        </td>
                    </tr>
                @endforelse
            </tbody>
        </table>
    </div>

    <!-- Pagination -->
    <div class="mt-6">
        {{ $auditLogs->links() }}
    </div>
</div>