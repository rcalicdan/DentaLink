<div>
    <x-flash-session/>
    <x-flash-message/>
    
    <div class="bg-white dark:bg-slate-800 rounded-lg shadow-sm border border-slate-200 dark:border-slate-700 mb-6">
        <div class="p-6">
            <h3 class="text-lg font-medium text-slate-900 dark:text-slate-100 mb-4">Filter Appointments</h3>
            <div class="grid grid-cols-1 md:grid-cols-4 gap-4">
                <div>
                    <label class="block text-sm font-medium text-slate-700 dark:text-slate-300 mb-2">Date</label>
                    <input type="date" wire:model.live="searchDate"
                        class="w-full rounded-md border-slate-300 dark:border-slate-600 dark:bg-slate-700 dark:text-slate-300">
                </div>
                <div>
                    <label class="block text-sm font-medium text-slate-700 dark:text-slate-300 mb-2">Status</label>
                    <select wire:model.live="searchStatus"
                        class="w-full rounded-md border-slate-300 dark:border-slate-600 dark:bg-slate-700 dark:text-slate-300">
                        <option value="">All Statuses</option>
                        @foreach($availableStatuses as $status)
                            <option value="{{ $status->value }}">{{ $status->getDisplayName() }}</option>
                        @endforeach
                    </select>
                </div>
                <div>
                    <label class="block text-sm font-medium text-slate-700 dark:text-slate-300 mb-2">Patient</label>
                    <input type="text" wire:model.live="searchPatient" placeholder="Search patient..."
                        class="w-full rounded-md border-slate-300 dark:border-slate-600 dark:bg-slate-700 dark:text-slate-300">
                </div>
                <div class="flex items-end">
                    <button wire:click="clearFilters" type="button"
                        class="px-4 py-2 bg-slate-500 hover:bg-slate-600 text-white rounded-md transition-colors">
                        Clear Filters
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