<?php

namespace App\Livewire\DentalServiceTypes;

use App\DataTable\DataTableFactory;
use App\Models\DentalServiceType;
use App\Traits\Livewire\WithDataTable;
use Illuminate\Support\Facades\Auth;
use Illuminate\Database\QueryException;
use Livewire\Attributes\Layout;
use Livewire\Component;
use Livewire\WithPagination;

#[Layout('components.layouts.app')]
class Table extends Component
{
    use WithDataTable, WithPagination;

    public function boot()
    {
        $this->deleteAction = 'deleteDentalServiceType';
        $this->routeIdColumn = 'id';
        $this->setDataTableFactory($this->getDataTableConfig());
    }

    private function getDataTableConfig(): DataTableFactory
    {
        return DataTableFactory::make()
            ->model(DentalServiceType::class)
            ->headers([
                [
                    'key' => 'id',
                    'label' => 'ID',
                    'sortable' => true
                ],
                [
                    'key' => 'name',
                    'label' => 'Name',
                    'sortable' => true
                ],
                [
                    'key' => 'services_count',
                    'label' => 'Services Count',
                    'sortable' => true,
                    'accessor' => true,
                ],
            ])
            ->deleteAction('deleteDentalServiceType')
            ->searchPlaceholder('Search dental service types...')
            ->emptyMessage('No dental service types found')
            ->searchQuery($this->search)
            ->sortColumn($this->sortColumn)
            ->sortDirection($this->sortDirection)
            ->showBulkActions(true)
            ->showCreate(true)
            ->createRoute('dental-service-types.create')
            ->editRoute('dental-service-types.edit')
            ->bulkDeleteAction('bulkDelete');
    }

    public function rowsQuery()
    {
        $query = DentalServiceType::query()->withCount('dentalServices as services_count');
        $dataTable = $this->getDataTableConfig();

        return $this->applySearchAndSort($query, ['name', 'description'], $dataTable);
    }

    public function getRowsProperty()
    {
        return $this->rowsQuery()->paginate($this->perPage);
    }

    public function render()
    {
        $this->authorize('viewAny', DentalServiceType::class);
        $dataTable = $this->getDataTableConfig()->toArray();
        $selectedRowsCount = $this->getSelectedRowsCountProperty();

        return view('livewire.dental-service-types.table', [
            'dataTable' => $dataTable,
            'selectedRowsCount' => $selectedRowsCount,
        ]);
    }

    public function bulkDelete()
    {
        try {
            $query = DentalServiceType::query();
            if ($this->selectAll) {
                $query = $this->rowsQuery();
            } else {
                $query->whereIn('id', $this->selectedRows);
            }
            
            // Check if any of the selected service types have associated dental services
            $serviceTypesWithServices = $query->withCount('dentalServices')
                ->having('dental_services_count', '>', 0)
                ->get(['id', 'name', 'dental_services_count']);

            if ($serviceTypesWithServices->count() > 0) {
                $serviceTypeNames = $serviceTypesWithServices->pluck('name')->toArray();
                $serviceTypeCounts = $serviceTypesWithServices->sum('dental_services_count');
                
                throw new \Exception(
                    'Cannot delete the following service types because they have associated dental services: ' . 
                    implode(', ', $serviceTypeNames) . '. ' .
                    'These service types are currently being used by ' . $serviceTypeCounts . ' dental service(s). ' .
                    'Please remove or reassign the associated services before deleting these service types.'
                );
            }

            $query->delete();
            $this->clearSelection();
            $this->dispatch('show-message', [
                'message' => 'Dental service types deleted successfully.',
                'type' => 'success'
            ]);

        } catch (QueryException $e) {
            $this->dispatch('show-message', [
                'message' => 'Cannot delete these service types due to database constraints. They may have associated records.',
                'type' => 'error'
            ]);
        } catch (\Exception $e) {
            $this->dispatch('show-message', [
                'message' => $e->getMessage(),
                'type' => 'error'
            ]);
        }
    }

    public function deleteDentalServiceType($id)
    {
        try {
            $dentalServiceType = DentalServiceType::findOrFail($id);
            $this->authorize('delete', $dentalServiceType);
            
            $dentalServiceType->loadCount('dentalServices');
            
            if ($dentalServiceType->dental_services_count > 0) {
                throw new \Exception(
                    'Cannot delete "' . $dentalServiceType->name . '" because it has ' . 
                    $dentalServiceType->dental_services_count . ' associated dental service(s). ' .
                    'Please remove or reassign the associated services before deleting this service type.'
                );
            }

            $dentalServiceType->delete();

            $this->dispatch('show-message', [
                'message' => 'Dental service type "' . $dentalServiceType->name . '" deleted successfully.',
                'type' => 'success'
            ]);

        } catch (QueryException $e) {
            $this->dispatch('show-message', [
                'message' => 'Cannot delete this service type due to database constraints. It may have associated records.',
                'type' => 'error'
            ]);
        } catch (\Exception $e) {
            $this->dispatch('show-message', [
                'message' => $e->getMessage(),
                'type' => 'error'
            ]);
        }
    }
}