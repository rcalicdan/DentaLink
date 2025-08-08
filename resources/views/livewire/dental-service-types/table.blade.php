<div>
    <x-flash-session/>
    <x-flash-message/>
    <x-partials.table-header title="Dental Service Types" />
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