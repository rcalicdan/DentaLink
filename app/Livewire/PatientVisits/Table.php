<?php

namespace App\Livewire\PatientVisits;

use App\DataTable\DataTableFactory;
use App\Models\PatientVisit;
use App\Models\Patient;
use App\Traits\Livewire\WithDataTable;
use Illuminate\Support\Facades\Auth;
use Livewire\Attributes\Layout;
use Livewire\Component;
use Livewire\WithPagination;
use Carbon\Carbon;

#[Layout('components.layouts.app')]
class Table extends Component
{
    use WithDataTable, WithPagination;

    public $searchDate = '';
    public $searchPatient = '';
    public $searchVisitType = '';

    public function boot()
    {
        $this->deleteAction = 'deletePatientVisit';
        $this->routeIdColumn = 'id';
        $this->setDataTableFactory($this->getDataTableConfig());

        if (empty($this->searchDate)) {
            $this->searchDate = Carbon::today()->format('Y-m-d');
        }
    }

    private function getDataTableConfig(): DataTableFactory
    {
        return DataTableFactory::make()
            ->model(PatientVisit::class)
            ->headers([
                [
                    'key' => 'visit_type',
                    'label' => 'Type',
                    'sortable' => false
                ],
                [
                    'key' => 'patient_name',
                    'label' => 'Patient',
                    'sortable' => true,
                    'accessor' => true,
                    'search_columns' => ['patients.first_name', 'patients.last_name'],
                ],
                [
                    'key' => 'visit_date',
                    'label' => 'Visit Date',
                    'sortable' => true,
                    'type' => 'datetime'
                ],
                [
                    'key' => 'appointment_info',
                    'label' => 'Appointment',
                    'sortable' => false
                ],
                [
                    'key' => 'total_amount_paid',
                    'label' => 'Amount Paid',
                    'sortable' => true,
                    'type' => 'currency'
                ],
                [
                    'key' => 'branch_name',
                    'label' => 'Branch',
                    'sortable' => true,
                    'accessor' => true
                ],
                [
                    'key' => 'created_at',
                    'label' => 'Created',
                    'sortable' => true,
                    'type' => 'datetime'
                ],
            ])
            ->deleteAction('deletePatientVisit')
            ->searchPlaceholder('Search visits...')
            ->emptyMessage('No patient visits found')
            ->searchQuery($this->search)
            ->sortColumn($this->sortColumn)
            ->sortDirection($this->sortDirection)
            ->showBulkActions(true)
            ->showCreate(true)
            ->createRoute('patient-visits.create')
            ->editRoute('patient-visits.edit')
            ->viewRoute('patient-visits.view')
            ->bulkDeleteAction('bulkDelete');
    }

    public function rowsQuery()
    {
        $query = PatientVisit::with(['patient', 'branch', 'appointment'])
            ->when($this->searchDate, function ($q) {
                return $q->whereDate('visit_date', $this->searchDate);
            })
            ->when($this->searchPatient, function ($q) {
                return $q->whereHas('patient', function ($query) {
                    $query->where(function ($subQuery) {
                        $subQuery->where('first_name', 'like', "%{$this->searchPatient}%")
                            ->orWhere('last_name', 'like', "%{$this->searchPatient}%")
                            ->orWhere('id', 'like', "%{$this->searchPatient}%")
                            ->orWhereRaw("CONCAT(first_name, ' ', last_name) LIKE ?", ["%{$this->searchPatient}%"]);
                    });
                });
            })
            ->when($this->searchVisitType, function ($q) {
                if ($this->searchVisitType === 'walk-in') {
                    return $q->whereNull('appointment_id');
                } elseif ($this->searchVisitType === 'appointment') {
                    return $q->whereNotNull('appointment_id');
                }
            });

        $dataTable = $this->getDataTableConfig();
        return $this->applySearchAndSort($query, ['notes'], $dataTable);
    }

    public function getRowsProperty()
    {
        return $this->rowsQuery()
            ->orderBy('visit_date', 'desc')
            ->orderBy('created_at', 'desc')
            ->paginate($this->perPage);
    }

    public function clearFilters()
    {
        $this->searchDate = Carbon::today()->format('Y-m-d');
        $this->searchPatient = '';
        $this->searchVisitType = '';
        $this->search = '';
        $this->resetPage();
    }

    public function render()
    {
        $this->authorize('viewAny', PatientVisit::class);
        $dataTable = $this->getDataTableConfig()->toArray();
        $selectedRowsCount = $this->getSelectedRowsCountProperty();

        return view('livewire.patient-visits.table', [
            'dataTable' => $dataTable,
            'selectedRowsCount' => $selectedRowsCount,
            'visitTypes' => [
                'walk-in' => 'Walk-in',
                'appointment' => 'Appointment'
            ],
        ]);
    }

    public function bulkDelete()
    {
        $query = PatientVisit::query();
        if ($this->selectAll) {
            $query = $this->rowsQuery();
        } else {
            $query->whereIn('id', $this->selectedRows);
        }
        $query->delete();
        $this->clearSelection();
        $this->dispatch('show-message', [
            'message' => 'Patient visits deleted successfully.',
            'type' => 'success'
        ]);
    }

    public function deletePatientVisit($id)
    {
        $patientVisit = PatientVisit::findOrFail($id);
        $this->authorize('delete', $patientVisit);

        $patientVisit->delete();

        $this->dispatch('show-message', [
            'message' => 'Patient visit deleted successfully.',
            'type' => 'success'
        ]);
    }
}