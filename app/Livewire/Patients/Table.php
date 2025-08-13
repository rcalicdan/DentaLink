<?php

namespace App\Livewire\Patients;

use App\DataTable\DataTableFactory;
use App\Models\Patient;
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
        $this->deleteAction = 'deletePatient';
        $this->routeIdColumn = 'id';
        $this->setDataTableFactory($this->getDataTableConfig());
    }

    private function getDataTableConfig(): DataTableFactory
    {
        return DataTableFactory::make()
            ->model(Patient::class)
            ->headers([
                [
                    'key' => 'id',
                    'label' => 'ID',
                    'sortable' => true
                ],
                [
                    'key' => 'full_name',
                    'label' => 'Full Name',
                    'sortable' => true,
                    'accessor' => true,
                    'search_columns' => ['first_name', 'last_name'],
                    'sort_columns' => ['first_name', 'last_name']
                ],
                [
                    'key' => 'phone',
                    'label' => 'Phone',
                    'sortable' => true
                ],
                [
                    'key' => 'email',
                    'label' => 'Email',
                    'sortable' => true
                ],
                [
                    'key' => 'date_of_birth',
                    'label' => 'Date of Birth',
                    'sortable' => true,
                    'type' => 'date'
                ],
                [
                    'key' => 'registration_branch_name',
                    'label' => 'Registration Branch',
                    'sortable' => true,
                    'accessor' => true,
                ],
                [
                    'key' => 'created_at',
                    'label' => 'Registered',
                    'sortable' => true,
                    'type' => 'datetime'
                ],
            ])
            ->deleteAction('deletePatient')
            ->searchPlaceholder('Search patients...')
            ->emptyMessage('No patients found')
            ->searchQuery($this->search)
            ->sortColumn($this->sortColumn)
            ->sortDirection($this->sortDirection)
            ->showBulkActions(true)
            ->showCreate(true)
            ->createRoute('patients.create')
            ->editRoute('patients.edit')
            ->viewRoute('patients.view')
            ->bulkDeleteAction('bulkDelete');
    }

    public function rowsQuery()
    {
        $query = Patient::with('registrationBranch');
        
        if (Auth::user()->isAdmin()) {
            $query->where('registration_branch_id', Auth::user()->branch_id);
        }
        
        $dataTable = $this->getDataTableConfig();

        return $this->applySearchAndSort($query, ['first_name', 'last_name', 'phone', 'email'], $dataTable);
    }

    public function getRowsProperty()
    {
        return $this->rowsQuery()->paginate($this->perPage);
    }

    public function render()
    {
        $this->authorize('viewAny', Patient::class);
        $dataTable = $this->getDataTableConfig()->toArray();
        $selectedRowsCount = $this->getSelectedRowsCountProperty();

        return view('livewire.patients.table', [
            'dataTable' => $dataTable,
            'selectedRowsCount' => $selectedRowsCount,
        ]);
    }

    public function bulkDelete()
    {
        $query = Patient::query();
        
        if ($this->selectAll) {
            $query = $this->rowsQuery();
        } else {
            $query->whereIn('id', $this->selectedRows);
        }
        
        $patientsWithAppointments = $query->whereHas('appointments')->exists();
        $patientsWithVisits = $query->whereHas('patientVisits')->exists();
        
        if ($patientsWithAppointments || $patientsWithVisits) {
            $this->dispatch('show-message', [
                'message' => 'Cannot delete patients that have appointments or visits.',
                'type' => 'error'
            ]);
            return;
        }

        $query->delete();
        $this->clearSelection();
        $this->dispatch('show-message', [
            'message' => 'Patients deleted successfully.',
            'type' => 'success'
        ]);
    }

    public function deletePatient($id)
    {
        $patient = Patient::with(['appointments', 'patientVisits'])->findOrFail($id);
        $this->authorize('delete', $patient);
        
        if ($patient->appointments()->exists() || $patient->patientVisits()->exists()) {
            $this->dispatch('show-message', [
                'message' => 'Cannot delete patient that has appointments or visits.',
                'type' => 'error'
            ]);
            return;
        }

        $patient->delete();

        $this->dispatch('show-message', [
            'message' => 'Patient deleted successfully.',
            'type' => 'success'
        ]);
    }
}