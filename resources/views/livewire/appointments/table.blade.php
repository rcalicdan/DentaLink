<div>
    <x-flash-session />
    <x-flash-message />

    <!-- Compact Filter Section -->
    <div
        class="bg-white dark:bg-slate-800 rounded-lg shadow-md border border-slate-200 dark:border-slate-700 mb-6 overflow-hidden">
        <!-- Compact Header -->
        <div class="bg-gradient-to-r from-blue-600 to-indigo-600 px-4 py-3">
            <div class="flex items-center">
                <div class="bg-white/20 p-1.5 rounded-md mr-2">
                    <i class="fas fa-filter text-white text-sm"></i>
                </div>
                <h3 class="text-lg font-semibold text-white">Filters</h3>
                <div class="ml-auto">
                    <span class="bg-white/20 px-2 py-1 rounded-md text-white text-xs font-medium">
                        Quick Filter
                    </span>
                </div>
            </div>
        </div>

        <!-- Compact Filter Controls -->
        <div class="p-4">
            <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-4">

                <!-- Date Filter -->
                <div class="group">
                    <label class="flex items-center text-xs font-semibold text-slate-600 dark:text-slate-400 mb-2">
                        <div
                            class="bg-blue-100 dark:bg-blue-900/50 p-1 rounded-md mr-1.5 group-hover:bg-blue-200 dark:group-hover:bg-blue-800/50 transition-colors">
                            <i class="fas fa-calendar-alt text-blue-600 dark:text-blue-400 text-xs"></i>
                        </div>
                        Date
                    </label>
                    <div class="relative">
                        <input type="date" wire:model.live="searchDate"
                            class="w-full pl-3 pr-8 py-2 text-sm rounded-lg border border-slate-300 dark:border-slate-600 
                       bg-white dark:bg-slate-700 text-slate-900 dark:text-slate-300
                       focus:border-blue-500 focus:ring-2 focus:ring-blue-500/20 
                       hover:border-slate-400 dark:hover:border-slate-500
                       transition-all duration-150 shadow-sm">
                        <div class="absolute right-2.5 top-1/2 transform -translate-y-1/2 pointer-events-none">
                            <i class="fas fa-calendar text-slate-400 text-xs"></i>
                        </div>
                    </div>
                </div>

                <!-- Status Filter -->
                <div class="group">
                    <label class="flex items-center text-xs font-semibold text-slate-600 dark:text-slate-400 mb-2">
                        <div
                            class="bg-green-100 dark:bg-green-900/50 p-1 rounded-md mr-1.5 group-hover:bg-green-200 dark:group-hover:bg-green-800/50 transition-colors">
                            <i class="fas fa-check-circle text-green-600 dark:text-green-400 text-xs"></i>
                        </div>
                        Status
                    </label>
                    <div class="relative">
                        <select wire:model.live="searchStatus"
                            class="w-full pl-3 pr-8 py-2 text-sm rounded-lg border border-slate-300 dark:border-slate-600 
                       bg-white dark:bg-slate-700 text-slate-900 dark:text-slate-300
                       focus:border-green-500 focus:ring-2 focus:ring-green-500/20 
                       hover:border-slate-400 dark:hover:border-slate-500
                       transition-all duration-150 shadow-sm appearance-none cursor-pointer">
                            <option value="">All Statuses</option>
                            @foreach ($availableStatuses as $status)
                                <option value="{{ $status->value }}">{{ $status->getDisplayName() }}</option>
                            @endforeach
                        </select>
                        <div class="absolute right-2.5 top-1/2 transform -translate-y-1/2 pointer-events-none">
                            <i class="fas fa-chevron-down text-slate-400 text-xs"></i>
                        </div>
                    </div>
                </div>

                <!-- Branch Filter - Now available for all users -->
                <div class="group">
                    <label class="flex items-center text-xs font-semibold text-slate-600 dark:text-slate-400 mb-2">
                        <div
                            class="bg-purple-100 dark:bg-purple-900/50 p-1 rounded-md mr-1.5 group-hover:bg-purple-200 dark:group-hover:bg-purple-800/50 transition-colors">
                            <i class="fas fa-building text-purple-600 dark:text-purple-400 text-xs"></i>
                        </div>
                        Branch
                    </label>
                    <div class="relative">
                        <select wire:model.live="searchBranch"
                            class="w-full pl-3 pr-8 py-2 text-sm rounded-lg border border-slate-300 dark:border-slate-600 
                       bg-white dark:bg-slate-700 text-slate-900 dark:text-slate-300
                       focus:border-purple-500 focus:ring-2 focus:ring-purple-500/20 
                       hover:border-slate-400 dark:hover:border-slate-500
                       transition-all duration-150 shadow-sm appearance-none cursor-pointer">
                            <option value="">All Branches</option>
                            @foreach ($branches as $branch)
                                <option value="{{ $branch->id }}">{{ $branch->name }}</option>
                            @endforeach
                        </select>
                        <div class="absolute right-2.5 top-1/2 transform -translate-y-1/2 pointer-events-none">
                            <i class="fas fa-chevron-down text-slate-400 text-xs"></i>
                        </div>
                    </div>
                </div>

                <!-- Clear Filters -->
                <div class="flex items-end">
                    <button wire:click="clearFilters" type="button"
                        class="w-full px-4 py-2 bg-gradient-to-r from-slate-500 to-slate-600 hover:from-slate-600 hover:to-slate-700 
                   text-white font-medium text-sm rounded-lg shadow-md hover:shadow-lg 
                   transform hover:-translate-y-0.5 transition-all duration-150
                   flex items-center justify-center space-x-1.5 group">
                        <i
                            class="fas fa-times-circle text-xs group-hover:rotate-90 transition-transform duration-150"></i>
                        <span>Clear</span>
                    </button>
                </div>
            </div>
        </div>
    </div>

    <!-- Dynamic Title with Indicators -->
    <div class="mb-6">
        <div class="flex items-center justify-between">
            <div>
                <h1 class="text-2xl font-bold text-slate-900 dark:text-slate-100 mb-2">
                    {{ $dynamicTitle }}
                </h1>
                <div class="flex flex-wrap gap-2">
                    @if ($searchDate)
                        <span
                            class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-blue-100 text-blue-800 dark:bg-blue-900 dark:text-blue-200">
                            <i class="fas fa-calendar-alt mr-1"></i>
                            @php
                                $date = \Carbon\Carbon::parse($searchDate);
                                if ($date->isToday()) {
                                    echo 'Today';
                                } elseif ($date->isYesterday()) {
                                    echo 'Yesterday';
                                } elseif ($date->isTomorrow()) {
                                    echo 'Tomorrow';
                                } else {
                                    echo $date->format('M j, Y');
                                }
                            @endphp
                        </span>
                    @endif
                    @if ($searchStatus)
                        <span
                            class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-green-100 text-green-800 dark:bg-green-900 dark:text-green-200">
                            <i class="fas fa-check-circle mr-1"></i>
                            {{ \App\Enums\AppointmentStatuses::from($searchStatus)->getDisplayName() }}
                        </span>
                    @endif
                    @if ($searchBranch)
                        @php $branch = \App\Models\Branch::find($searchBranch); @endphp
                        @if ($branch)
                            <span
                                class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-purple-100 text-purple-800 dark:bg-purple-900 dark:text-purple-200">
                                <i class="fas fa-building mr-1"></i>
                                {{ $branch->name }}
                            </span>
                        @endif
                    @endif
                </div>
            </div>
        </div>
    </div>

    <x-data-table :data="$this->rows" :headers="$dataTable['headers']" :showActions="$dataTable['showActions']" :showSearch="$dataTable['showSearch']" :showCreate="$dataTable['showCreate']"
        :createRoute="$dataTable['createRoute']" :createButtonName="$dataTable['createButtonName']" :editRoute="$dataTable['editRoute']" :viewRoute="$dataTable['viewRoute']" :deleteAction="$dataTable['deleteAction']" :searchPlaceholder="$dataTable['searchPlaceholder']"
        :emptyMessage="$dataTable['emptyMessage']" :searchQuery="$search" :sortColumn="$sortColumn" :sortDirection="$sortDirection" :showBulkActions="$dataTable['showBulkActions']"
        :bulkDeleteAction="$dataTable['bulkDeleteAction']" :selectedRowsCount="$selectedRowsCount" :selectAll="$selectAll" :selectPage="$selectPage" :selectedRows="$selectedRows" />
</div>
