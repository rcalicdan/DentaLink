<div>
    <x-flash-session />
    <x-flash-message />

    <!-- Filter Section -->
    <div
        class="bg-white dark:bg-slate-800 rounded-lg shadow-md border border-slate-200 dark:border-slate-700 mb-6 overflow-hidden">
        <div class="bg-gradient-to-r from-blue-600 to-indigo-600 px-4 py-3">
            <h3 class="text-lg font-semibold text-white">Filters</h3>
        </div>
        <div class="p-4">
            <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-4">
                <div class="group">
                    <label class="flex items-center text-xs font-semibold text-slate-600 dark:text-slate-400 mb-2">
                        <i class="fas fa-building text-blue-600 dark:text-blue-400 mr-1.5"></i>
                        Branch
                    </label>
                    <select wire:model.live="searchBranch"
                        class="w-full pl-3 pr-8 py-2 text-sm rounded-lg border border-slate-300 dark:border-slate-600 bg-white dark:bg-slate-700 text-slate-900 dark:text-slate-300 focus:border-blue-500 focus:ring-2 focus:ring-blue-500/20 hover:border-slate-400 dark:hover:border-slate-500 transition-all duration-150 shadow-sm appearance-none cursor-pointer">
                        @foreach ($branches as $branch)
                            <option value="{{ $branch->id }}">{{ $branch->name }}</option>
                        @endforeach
                    </select>
                </div>
            </div>
        </div>
    </div>

    <x-partials.table-header title="Inventory" />
    <x-data-table :data="$this->rows" :headers="$dataTable['headers']" :showActions="$dataTable['showActions']" :showSearch="$dataTable['showSearch']" :showCreate="$dataTable['showCreate']"
        :createRoute="$dataTable['createRoute']" :createButtonName="$dataTable['createButtonName']" :editRoute="$dataTable['editRoute']" :viewRoute="$dataTable['viewRoute']" :deleteAction="$dataTable['deleteAction']" :searchPlaceholder="$dataTable['searchPlaceholder']"
        :emptyMessage="$dataTable['emptyMessage']" :searchQuery="$search" :sortColumn="$sortColumn" :sortDirection="$sortDirection" :showBulkActions="$dataTable['showBulkActions']"
        :bulkDeleteAction="$dataTable['bulkDeleteAction']" :selectedRowsCount="$selectedRowsCount" :selectAll="$selectAll" :selectPage="$selectPage" :selectedRows="$selectedRows" />
</div>
