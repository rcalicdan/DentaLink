<?php

namespace App\Livewire\DentalServices;

use App\DataTable\DataTableFactory;
use App\Models\DentalService;
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
        $this->deleteAction = 'deleteDentalService';
        $this->routeIdColumn = 'id';
        $this->setDataTableFactory($this->getDataTableConfig());
    }

    private function getDataTableConfig(): DataTableFactory
    {
        return DataTableFactory::make()
            ->model(DentalService::class)
            ->headers([
                [
                    'key' => 'id',
                    'label' => 'ID',
                    'sortable' => true
                ],
                [
                    'key' => 'name',
                    'label' => 'Service Name',
                    'sortable' => true
                ],
                [
                    'key' => 'service_type_name',
                    'label' => 'Service Type',
                    'accessor' => true,
                    "sortable" => false,
                ],
                [
                    'key' => 'price',
                    'label' => 'Price',
                    'sortable' => true,
                    'type' => 'currency'
                ],
                [
                    'key' => 'visits_count',
                    'label' => 'Usage Count',
                    'sortable' => true,
                    'accessor' => true,
                ],
            ])
            ->deleteAction('deleteDentalService')
            ->searchPlaceholder('Search dental services...')
            ->emptyMessage('No dental services found')
            ->searchQuery($this->search)
            ->sortColumn($this->sortColumn)
            ->sortDirection($this->sortDirection)
            ->showBulkActions(true)
            ->showCreate(true)
            ->createRoute('dental-services.create')
            ->editRoute('dental-services.edit')
            ->bulkDeleteAction('bulkDelete');
    }

    public function rowsQuery()
    {
        $query = DentalService::query()
            ->with('dentalServiceType')
            ->withCount('patientVisitServices as visits_count');
        
        $dataTable = $this->getDataTableConfig();

        return $this->applySearchAndSort($query, ['name', 'price'], $dataTable);
    }

    public function getRowsProperty()
    {
        return $this->rowsQuery()->paginate($this->perPage);
    }

    public function render()
    {
        $this->authorize('viewAny', DentalService::class);
        $dataTable = $this->getDataTableConfig()->toArray();
        $selectedRowsCount = $this->getSelectedRowsCountProperty();

        return view('livewire.dental-services.table', [
            'dataTable' => $dataTable,
            'selectedRowsCount' => $selectedRowsCount,
        ]);
    }

    public function bulkDelete()
    {
        $query = DentalService::query();
        if ($this->selectAll) {
            $query = $this->rowsQuery();
        } else {
            $query->whereIn('id', $this->selectedRows);
        }
        $query->delete();
        $this->clearSelection();
        $this->dispatch('show-message', [
            'message' => 'Dental services deleted successfully.',
            'type' => 'success'
        ]);
    }

    public function deleteDentalService($id)
    {
        $dentalService = DentalService::findOrFail($id);
        $this->authorize('delete', $dentalService);
        $dentalService->delete();

        $this->dispatch('show-message', [
            'message' => 'Dental service deleted successfully.',
            'type' => 'success'
        ]);
    }
}