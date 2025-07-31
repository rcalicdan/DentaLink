<div>
    <x-data-table :headers="$dataTableFactory->getHeaders()" :data="$users" :showActions="true" :showBulkActions="true"
        :bulkDeleteAction="'bulkDeleteUsers'" :showSearch="true" :showCreate="$this->canCreateRecord()"
        :createRoute="$this->getCreateRoute()" createButtonName="Add User" :editRoute="'users.edit'"
        :viewRoute="'users.show'" :deleteAction="'deleteUser'" searchPlaceholder="Search users..."
        emptyMessage="No users found" :searchQuery="$search" :sortColumn="$sortColumn" :sortDirection="$sortDirection"
        :selectedRowsCount="$this->selectedRowsCount" :selectAll="$selectAll" :selectPage="$selectPage"
        :selectedRows="$selectedRows" :dataTableFactory="$dataTableFactory" routeIdColumn="id" />
</div>