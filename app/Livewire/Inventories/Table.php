<?php

namespace App\Livewire\Inventories;

use App\DataTable\DataTableFactory;
use App\Models\Branch;
use App\Models\Inventory;
use App\Traits\Livewire\WithDataTable;
use Illuminate\Support\Facades\Auth;
use Livewire\Attributes\Layout;
use Livewire\Component;
use Livewire\WithPagination;

#[Layout('components.layouts.app')]
class Table extends Component
{
    use WithDataTable, WithPagination;

    public $searchBranch = '';

    public function boot()
    {
        $this->deleteAction = 'deleteInventoryItem';
        $this->routeIdColumn = 'id';
        $this->setDataTableFactory($this->getDataTableConfig());

        if (empty($this->searchBranch)) {
            if (Auth::user()->isSuperadmin()) {
                $this->searchBranch = Branch::query()->orderBy('name')->first()?->id ?? '';
            } else {
                $this->searchBranch = Auth::user()->branch_id;
            }
        }
    }

    private function getDataTableConfig(): DataTableFactory
    {
        return DataTableFactory::make()
            ->model(Inventory::class)
            ->headers([
                [
                    'key' => 'name',
                    'label' => 'Item Name',
                    'sortable' => true,
                    'search_columns' => ['name'],
                ],
                [
                    'key' => 'category',
                    'label' => 'Category',
                    'sortable' => true,
                    'type' => 'badge',
                ],
                [
                    'key' => 'stock_status',
                    'label' => 'Stock Status',
                    'accessor' => true,
                ],
                [
                    'key' => 'current_stock',
                    'label' => 'Current Stock',
                    'sortable' => true
                ],
                [
                    'key' => 'minimum_stock',
                    'label' => 'Minimum Stock',
                    'sortable' => true
                ],
                [
                    'key' => 'branch_name',
                    'label' => 'Branch',
                    'sortable' => true,
                    'accessor' => true,
                ],
            ])
            ->deleteAction('deleteInventoryItem')
            ->searchPlaceholder('Search inventory...')
            ->emptyMessage('No inventory items found')
            ->searchQuery($this->search)
            ->sortColumn($this->sortColumn)
            ->sortDirection($this->sortDirection)
            ->showBulkActions(true)
            ->showCreate(!Auth::user()->isDentist())
            ->showActions(!Auth::user()->isDentist())
            ->createRoute('inventory.create')
            ->editRoute('inventory.edit')
            ->bulkDeleteAction('bulkDelete');
    }

    public function rowsQuery()
    {
        $query = Inventory::with('branch');

        if (Auth::user()->isSuperadmin()) {
            $query->when($this->searchBranch, function ($q) {
                return $q->where('branch_id', $this->searchBranch);
            });
        } else {
            $query->where('branch_id', Auth::user()->branch_id);
        }

        $dataTable = $this->getDataTableConfig();
        return $this->applySearchAndSort($query, ['name', 'category'], $dataTable);
    }

    public function getRowsProperty()
    {
        return $this->rowsQuery()->paginate($this->perPage);
    }

    public function render()
    {
        $this->authorize('viewAny', Inventory::class);
        $dataTable = $this->getDataTableConfig()->toArray();
        $selectedRowsCount = $this->getSelectedRowsCountProperty();

        return view('livewire.inventories.table', [
            'dataTable' => $dataTable,
            'selectedRowsCount' => $selectedRowsCount,
            'branches' => Auth::user()->isSuperadmin() ? Branch::orderBy('name')->get() : Branch::where('id', Auth::user()->branch_id)->get(),
        ]);
    }

    public function bulkDelete()
    {
        $this->authorize('delete', new Inventory());
        $query = Inventory::query();
        if ($this->selectAll) {
            $query = $this->rowsQuery();
        } else {
            $query->whereIn('id', $this->selectedRows);
        }
        $query->delete();
        $this->clearSelection();
        $this->dispatch('show-message', [
            'message' => 'Inventory items deleted successfully.',
            'type' => 'success'
        ]);
    }

    public function deleteInventoryItem($id)
    {
        $item = Inventory::findOrFail($id);
        $this->authorize('delete', $item);
        $item->delete();

        $this->dispatch('show-message', [
            'message' => 'Inventory item deleted successfully.',
            'type' => 'success'
        ]);
    }
}
