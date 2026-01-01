<div>
    <x-flash-session/>
    <x-flash-message/>
    
    <div class="mb-6 flex justify-between items-center">
        <x-partials.table-header title="Customer Feedback" />
        <a href="{{ route('feedback.public') }}" target="_blank"
            class="px-4 py-2 bg-blue-600 hover:bg-blue-700 text-white rounded-lg transition-colors duration-200 flex items-center gap-2">
            <i class="fas fa-external-link-alt"></i>
            <span>Public Page</span>
        </a>
    </div>

    <x-data-table 
        :data="$this->rows" 
        :headers="$dataTable['headers']"
        :showActions="$dataTable['showActions']" 
        :showSearch="$dataTable['showSearch']"
        :showCreate="$dataTable['showCreate']" 
        :createRoute="$dataTable['createRoute'] ?? null"
        :createButtonName="$dataTable['createButtonName'] ?? null" 
        :editRoute="$dataTable['editRoute'] ?? null"
        :viewRoute="$dataTable['viewRoute'] ?? null" 
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
        :selectedRows="$selectedRows">
    </x-data-table>
</div>