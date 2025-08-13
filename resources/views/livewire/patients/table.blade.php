<div>
    <x-flash-session />
    <x-flash-message />

    <!-- Compact Filter Section -->
    <div class="bg-white dark:bg-slate-800 rounded-lg shadow-md border border-slate-200 dark:border-slate-700 mb-6 overflow-hidden">
        <!-- Compact Header -->
        <div class="bg-gradient-to-r from-emerald-600 to-teal-600 px-4 py-3">
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
            <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-3 gap-4">

                <!-- Branch Filter -->
                <div class="group">
                    <label class="flex items-center text-xs font-semibold text-slate-600 dark:text-slate-400 mb-2">
                        <div class="bg-emerald-100 dark:bg-emerald-900/50 p-1 rounded-md mr-1.5 group-hover:bg-emerald-200 dark:group-hover:bg-emerald-800/50 transition-colors">
                            <i class="fas fa-building text-emerald-600 dark:text-emerald-400 text-xs"></i>
                        </div>
                        Registration Branch
                    </label>
                    <div class="relative">
                        <select wire:model.live="searchBranch"
                            class="w-full pl-3 pr-8 py-2 text-sm rounded-lg border border-slate-300 dark:border-slate-600 
                               bg-white dark:bg-slate-700 text-slate-900 dark:text-slate-300
                               focus:border-emerald-500 focus:ring-2 focus:ring-emerald-500/20 
                               hover:border-slate-400 dark:hover:border-slate-500
                               transition-all duration-150 shadow-sm appearance-none cursor-pointer">
                            @if(Auth::user()->isSuperadmin())
                                <option value="">All Branches</option>
                            @endif
                            @foreach ($branches as $branch)
                                <option value="{{ $branch->id }}">{{ $branch->name }}</option>
                            @endforeach
                        </select>
                        <div class="absolute right-2.5 top-1/2 transform -translate-y-1/2 pointer-events-none">
                            <i class="fas fa-chevron-down text-slate-400 text-xs"></i>
                        </div>
                    </div>
                </div>

                <!-- Empty space to maintain grid layout -->
                <div></div>

                <!-- Clear Filters -->
                <div class="flex items-end">
                    <button wire:click="clearFilters" type="button"
                        class="w-full px-4 py-2 bg-gradient-to-r from-slate-500 to-slate-600 hover:from-slate-600 hover:to-slate-700 
                           text-white font-medium text-sm rounded-lg shadow-md hover:shadow-lg 
                           transform hover:-translate-y-0.5 transition-all duration-150
                           flex items-center justify-center space-x-1.5 group">
                        <i class="fas fa-times-circle text-xs group-hover:rotate-90 transition-transform duration-150"></i>
                        <span>Clear</span>
                    </button>
                </div>
            </div>
        </div>
    </div>

    <x-partials.table-header title="Patients" />
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