<?php

namespace App\Livewire\Patients;

use App\DataTable\DataTableFactory;
use App\Models\Patient;
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

    public $searchBranch = '';

    public function boot()
    {
        $this->deleteAction = 'deletePatient';
        $this->routeIdColumn = 'id';
        $this->setDataTableFactory($this->getDataTableConfig());

        if (!Auth::user()->isSuperadmin() && empty($this->searchBranch)) {
            $this->searchBranch = Auth::user()->branch_id;
        }
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
                    'key' => 'registration_branch_name',
                    'label' => 'Registration Branch',
                    'sortable' => true,
                    'accessor' => true,
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

        $query->when($this->searchBranch, function ($q) {
            return $q->where('registration_branch_id', $this->searchBranch);
        });

        if (!Auth::user()->isSuperadmin() && !$this->searchBranch) {
            $query->where('registration_branch_id', Auth::user()->branch_id);
        }

        $dataTable = $this->getDataTableConfig();

        return $this->applySearchAndSort($query, ['first_name', 'last_name', 'phone', 'email'], $dataTable);
    }

    public function getRowsProperty()
    {
        return $this->rowsQuery()->paginate($this->perPage);
    }

    public function clearFilters()
    {
        if (Auth::user()->isSuperadmin()) {
            $this->searchBranch = '';
        } else {
            $this->searchBranch = Auth::user()->branch_id;
        }

        $this->search = '';
        $this->resetPage();
    }

    public function render()
    {
        $this->authorize('viewAny', Patient::class);
        $dataTable = $this->getDataTableConfig()->toArray();
        $selectedRowsCount = $this->getSelectedRowsCountProperty();

        return view('livewire.patients.table', [
            'dataTable' => $dataTable,
            'selectedRowsCount' => $selectedRowsCount,
            'branches' => Branch::orderBy('name')->get()
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
