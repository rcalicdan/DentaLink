<div>
    <x-flash-session/>
    <x-flash-message/>
    
    <!-- Modern Filter Section -->
    <div class="bg-gradient-to-r from-white to-slate-50 dark:from-slate-800 dark:to-slate-900 rounded-xl shadow-lg border border-slate-200 dark:border-slate-700 mb-8 overflow-hidden">
        <!-- Header with gradient background -->
        <div class="bg-gradient-to-r from-blue-600 to-indigo-600 px-6 py-4">
            <div class="flex items-center">
                <div class="bg-white/20 p-2 rounded-lg mr-3">
                    <i class="fas fa-filter text-white text-lg"></i>
                </div>
                <h3 class="text-xl font-semibold text-white">Filter Appointments</h3>
                <div class="ml-auto">
                    <span class="bg-white/20 px-3 py-1 rounded-full text-white text-sm font-medium">
                        Advanced Filters
                    </span>
                </div>
            </div>
        </div>
        
        <!-- Filter Controls -->
        <div class="p-8">
            <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-6">
                <!-- Date Filter -->
                <div class="group">
                    <label class="flex items-center text-sm font-semibold text-slate-700 dark:text-slate-300 mb-3">
                        <div class="bg-blue-100 dark:bg-blue-900/50 p-1.5 rounded-lg mr-2 group-hover:bg-blue-200 dark:group-hover:bg-blue-800/50 transition-colors">
                            <i class="fas fa-calendar-alt text-blue-600 dark:text-blue-400 text-xs"></i>
                        </div>
                        Appointment Date
                    </label>
                    <div class="relative">
                        <input type="date" wire:model.live="searchDate"
                            class="w-full pl-4 pr-10 py-3 rounded-xl border-2 border-slate-200 dark:border-slate-600 
                                   bg-white dark:bg-slate-700 text-slate-900 dark:text-slate-300
                                   focus:border-blue-500 focus:ring-4 focus:ring-blue-500/10 
                                   hover:border-slate-300 dark:hover:border-slate-500
                                   transition-all duration-200 shadow-sm">
                        <div class="absolute right-3 top-1/2 transform -translate-y-1/2 pointer-events-none">
                            <i class="fas fa-calendar text-slate-400 text-sm"></i>
                        </div>
                    </div>
                </div>

                <!-- Status Filter -->
                <div class="group">
                    <label class="flex items-center text-sm font-semibold text-slate-700 dark:text-slate-300 mb-3">
                        <div class="bg-green-100 dark:bg-green-900/50 p-1.5 rounded-lg mr-2 group-hover:bg-green-200 dark:group-hover:bg-green-800/50 transition-colors">
                            <i class="fas fa-check-circle text-green-600 dark:text-green-400 text-xs"></i>
                        </div>
                        Status
                    </label>
                    <div class="relative">
                        <select wire:model.live="searchStatus"
                            class="w-full pl-4 pr-10 py-3 rounded-xl border-2 border-slate-200 dark:border-slate-600 
                                   bg-white dark:bg-slate-700 text-slate-900 dark:text-slate-300
                                   focus:border-green-500 focus:ring-4 focus:ring-green-500/10 
                                   hover:border-slate-300 dark:hover:border-slate-500
                                   transition-all duration-200 shadow-sm appearance-none cursor-pointer">
                            <option value="">All Statuses</option>
                            @foreach($availableStatuses as $status)
                                <option value="{{ $status->value }}">{{ $status->getDisplayName() }}</option>
                            @endforeach
                        </select>
                        <div class="absolute right-3 top-1/2 transform -translate-y-1/2 pointer-events-none">
                            <i class="fas fa-chevron-down text-slate-400 text-sm"></i>
                        </div>
                    </div>
                </div>

                <!-- Patient Filter -->
                <div class="group">
                    <label class="flex items-center text-sm font-semibold text-slate-700 dark:text-slate-300 mb-3">
                        <div class="bg-purple-100 dark:bg-purple-900/50 p-1.5 rounded-lg mr-2 group-hover:bg-purple-200 dark:group-hover:bg-purple-800/50 transition-colors">
                            <i class="fas fa-user text-purple-600 dark:text-purple-400 text-xs"></i>
                        </div>
                        Patient
                    </label>
                    <div class="relative">
                        <input type="text" wire:model.live="searchPatient" placeholder="Search patient name..."
                            class="w-full pl-10 pr-4 py-3 rounded-xl border-2 border-slate-200 dark:border-slate-600 
                                   bg-white dark:bg-slate-700 text-slate-900 dark:text-slate-300
                                   placeholder:text-slate-400 dark:placeholder:text-slate-500
                                   focus:border-purple-500 focus:ring-4 focus:ring-purple-500/10 
                                   hover:border-slate-300 dark:hover:border-slate-500
                                   transition-all duration-200 shadow-sm">
                        <div class="absolute left-3 top-1/2 transform -translate-y-1/2">
                            <i class="fas fa-search text-slate-400 text-sm"></i>
                        </div>
                    </div>
                </div>

                <!-- Clear Filters -->
                <div class="flex items-end">
                    <button wire:click="clearFilters" type="button"
                        class="w-full px-6 py-3 bg-gradient-to-r from-slate-600 to-slate-700 hover:from-slate-700 hover:to-slate-800 
                               text-white font-semibold rounded-xl shadow-lg hover:shadow-xl 
                               transform hover:-translate-y-0.5 transition-all duration-200
                               flex items-center justify-center space-x-2 group">
                        <i class="fas fa-times-circle group-hover:rotate-90 transition-transform duration-200"></i>
                        <span>Clear All</span>
                    </button>
                </div>
            </div>
        </div>
    </div>

    <x-partials.table-header title="Appointments" />
    <x-data-table 
        :data="$this->rows" 
        :headers="$dataTable['headers']"
        :showActions="$dataTable['showActions']" 
        :showSearch="$dataTable['showSearch']"
        :showCreate="$dataTable['showCreate']" 
        :createRoute="$dataTable['createRoute']"
        :createButtonName="$dataTable['createButtonName']" 
        :editRoute="$dataTable['editRoute']"
        :viewRoute="$dataTable['viewRoute']"
        :deleteAction="$dataTable['deleteAction']"
        :searchPlaceholder="$dataTable['searchPlaceholder']" 
        :emptyMessage="$dataTable['emptyMessage']"
        :searchQuery="$search"
        :sortColumn="$sortColumn"
        :sortDirection="$sortDirection" 
        :showBulkActions="$dataTable['showBulkActions']"
        :bulkDeleteAction="$dataTable['bulkDeleteAction']" 
        :selectedRowsCount="$selectedRowsCount"
        :selectAll="$selectAll" 
        :selectPage="$selectPage" 
        :selectedRows="$selectedRows" />
</div>