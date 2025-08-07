<?php
// app/Livewire/Branches/Table.php

namespace App\Livewire\Branches;

use App\DataTable\DataTableFactory;
use App\Models\Branch;
use App\Traits\Livewire\WithDataTable;
use Illuminate\Support\Facades\Auth;
use Livewire\Attributes\Layout;
use Livewire\Component;
use Livewire\WithPagination;

#[Layout('components.layouts.app')]
class Table extends Component
{
    use WithDataTable, WithPagination;

    public function boot()
    {
        $this->deleteAction = 'deleteBranch';
        $this->routeIdColumn = 'id';
        $this->setDataTableFactory($this->getDataTableConfig());
    }

    private function getDataTableConfig(): DataTableFactory
    {
        return DataTableFactory::make()
            ->model(Branch::class)
            ->headers([
                [
                    'key' => 'id',
                    'label' => 'ID',
                    'sortable' => true
                ],
                [
                    'key' => 'name',
                    'label' => 'Branch Name',
                    'sortable' => true
                ],
                [
                    'key' => 'users_count',
                    'label' => 'Users',
                    'sortable' => true,
                    'accessor' => true,
                ],
            ])
            ->deleteAction('deleteBranch')
            ->searchPlaceholder('Search branches...')
            ->emptyMessage('No branches found')
            ->searchQuery($this->search)
            ->sortColumn($this->sortColumn)
            ->sortDirection($this->sortDirection)
            ->showBulkActions(true)
            ->showCreate(true)
            ->createRoute('branches.create')
            ->editRoute('branches.edit')
            ->bulkDeleteAction('bulkDelete');
    }

    public function rowsQuery()
    {
        $query = Branch::withCount('users');
        $dataTable = $this->getDataTableConfig();

        return $this->applySearchAndSort($query, ['name', 'address', 'phone', 'email'], $dataTable);
    }

    public function getRowsProperty()
    {
        return $this->rowsQuery()->paginate($this->perPage);
    }

    public function render()
    {
        $this->authorize('viewAny', Branch::class);
        $dataTable = $this->getDataTableConfig()->toArray();
        $selectedRowsCount = $this->getSelectedRowsCountProperty();

        return view('livewire.branches.table', [
            'dataTable' => $dataTable,
            'selectedRowsCount' => $selectedRowsCount,
        ]);
    }

    public function bulkDelete()
    {
        $query = Branch::query();
        if ($this->selectAll) {
            $query = $this->rowsQuery();
        } else {
            $query->whereIn('id', $this->selectedRows);
        }
        
        $branchesWithUsers = $query->withCount('users')->having('users_count', '>', 0)->exists();
        
        if ($branchesWithUsers) {
            $this->dispatch('show-message', [
                'message' => 'Cannot delete branches that have users assigned to them.',
                'type' => 'error'
            ]);
            return;
        }

        $query->delete();
        $this->clearSelection();
        $this->dispatch('show-message', [
            'message' => 'Branches deleted successfully.',
            'type' => 'success'
        ]);
    }

    public function deleteBranch($id)
    {
        $branch = Branch::withCount('users')->findOrFail($id);
        $this->authorize('delete', $branch);
        
        if ($branch->users_count > 0) {
            $this->dispatch('show-message', [
                'message' => 'Cannot delete branch that has users assigned to it.',
                'type' => 'error'
            ]);
            return;
        }

        $branch->delete();

        $this->dispatch('show-message', [
            'message' => 'Branch deleted successfully.',
            'type' => 'success'
        ]);
    }
}