<?php

namespace App\Livewire\PatientVisits;

use App\Actions\PatientVisits\GeneratePatientVisitsPdfAction;
use App\Actions\PatientVisits\GeneratePatientVisitsCsvAction;
use App\DataTable\DataTableFactory;
use App\Models\Branch;
use App\Models\PatientVisit;
use App\Models\Patient;
use App\Traits\Livewire\WithDataTable;
use Illuminate\Support\Facades\Auth;
use Livewire\Attributes\Layout;
use Livewire\Component;
use Livewire\WithPagination;
use Carbon\Carbon;
use Illuminate\Support\Facades\Response; 

#[Layout('components.layouts.app')]
class Table extends Component
{
    use WithDataTable, WithPagination;

    public $searchDate = '';
    public $searchDateFrom = '';
    public $searchDateTo = '';
    public $searchDateRange = 'single'; 
    public $searchBranch = '';
    public $searchVisitType = '';
    public $showMyVisits = false;

    public function boot()
    {
        $this->deleteAction = 'deletePatientVisit';
        $this->routeIdColumn = 'id';
        $this->setDataTableFactory($this->getDataTableConfig());

        if (!Auth::user()->isSuperadmin() && empty($this->searchBranch)) {
            $this->searchBranch = Auth::user()->branch_id;
        }
        
        $this->applyDateRange();
    }

    public function updatedSearchDateRange()
    {
        $this->applyDateRange();
        $this->resetPage();
    }

    public function updatedSearchDateFrom()
    {
        if ($this->searchDateRange === 'custom') $this->resetPage();
    }

    public function updatedSearchDateTo()
    {
        if ($this->searchDateRange === 'custom') $this->resetPage();
    }

    private function applyDateRange()
    {
        $today = Carbon::today();

        switch ($this->searchDateRange) {
            case 'single':
                $this->searchDateFrom = '';
                $this->searchDateTo = '';
                if (empty($this->searchDate)) {
                    $this->searchDate = $today->format('Y-m-d');
                }
                break;
            case '7days':
                $this->searchDateFrom = $today->format('Y-m-d');
                $this->searchDateTo = $today->copy()->addDays(6)->format('Y-m-d');
                $this->searchDate = '';
                break;
            case '15days':
                $this->searchDateFrom = $today->format('Y-m-d');
                $this->searchDateTo = $today->copy()->addDays(14)->format('Y-m-d');
                $this->searchDate = '';
                break;
            case '30days':
                $this->searchDateFrom = $today->format('Y-m-d');
                $this->searchDateTo = $today->copy()->addDays(29)->format('Y-m-d');
                $this->searchDate = '';
                break;
            case '3months':
                $this->searchDateFrom = $today->format('Y-m-d');
                $this->searchDateTo = $today->copy()->addMonths(3)->format('Y-m-d');
                $this->searchDate = '';
                break;
            case 'custom':
                $this->searchDate = '';
                if (empty($this->searchDateFrom)) {
                    $this->searchDateFrom = $today->format('Y-m-d');
                }
                if (empty($this->searchDateTo)) {
                    $this->searchDateTo = $today->copy()->addDays(7)->format('Y-m-d');
                }
                break;
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
                    'accessor' => true,
                    'sortable' => true
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
                    'key' => 'branch_name',
                    'label' => 'Branch',
                    'sortable' => true,
                    'accessor' => true
                ],
                [
                    'key' => 'total_amount_paid',
                    'label' => 'Amount Paid',
                    'sortable' => true,
                    'type' => 'currency'
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
        $query = PatientVisit::with(['patient', 'branch', 'appointment']);

        if ($this->searchDateRange === 'single' && $this->searchDate) {
            $query->whereDate('visit_date', $this->searchDate);
        } elseif ($this->searchDateRange !== 'single' && $this->searchDateFrom && $this->searchDateTo) {
            $query->whereBetween('visit_date', [$this->searchDateFrom, $this->searchDateTo]);
        }

        $query->when($this->searchBranch, function ($q) {
                return $q->where('branch_id', $this->searchBranch);
            })
            ->when($this->searchVisitType, function ($q) {
                if ($this->searchVisitType === 'walk-in') {
                    return $q->whereNull('appointment_id');
                } elseif ($this->searchVisitType === 'appointment') {
                    return $q->whereNotNull('appointment_id');
                }
            });
            
        if (Auth::user()->isDentist() && $this->showMyVisits) {
            $query->where('dentist_id', Auth::id());
        }

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
        $this->searchDateRange = 'single';
        $this->searchDate = Carbon::today()->format('Y-m-d');
        $this->searchDateFrom = '';
        $this->searchDateTo = '';
        $this->searchVisitType = '';
        
        $this->showMyVisits = false;

        if (Auth::user()->isSuperadmin()) {
            $this->searchBranch = '';
        } else {
            $this->searchBranch = Auth::user()->branch_id;
        }

        $this->search = '';
        $this->resetPage();
    }

    public function downloadPdf()
    {
        $filters = [
            'branch_id' => $this->searchBranch,
            'visit_type' => $this->searchVisitType,
        ];

        if ($this->searchDateRange === 'single') {
            $filters['date'] = $this->searchDate;
        } else {
            $filters['date_from'] = $this->searchDateFrom;
            $filters['date_to'] = $this->searchDateTo;
        }

        if ($this->searchBranch) {
            $branch = Branch::find($this->searchBranch);
            $filters['branch_name'] = $branch?->name;
        }

        $action = new GeneratePatientVisitsPdfAction();
        $pdfContent = $action->execute($filters);

        $fileName = 'patient_visits_' . date('Y-m-d_His') . '.pdf';

        return Response::streamDownload(function () use ($pdfContent) {
            echo $pdfContent;
        }, $fileName, [
            'Content-Type' => 'application/pdf',
        ]);
    }

    public function downloadCsv()
    {
        $filters = [
            'branch_id' => $this->searchBranch,
            'visit_type' => $this->searchVisitType,
        ];

        if ($this->searchDateRange === 'single') {
            $filters['date'] = $this->searchDate;
        } else {
            $filters['date_from'] = $this->searchDateFrom;
            $filters['date_to'] = $this->searchDateTo;
        }

        $action = new GeneratePatientVisitsCsvAction();
        $csvContent = $action->execute($filters);

        $fileName = 'patient_visits_' . date('Y-m-d_His') . '.csv';

        return Response::streamDownload(function () use ($csvContent) {
            echo $csvContent;
        }, $fileName, [
            'Content-Type' => 'text/csv',
            'Content-Disposition' => 'attachment; filename="' . $fileName . '"',
        ]);
    }

    public function getDynamicTitleProperty()
    {
        $title = 'Patient Visits';
        $indicators = [];

        if ($this->searchDateRange === 'single' && $this->searchDate) {
            $date = Carbon::parse($this->searchDate);
            if ($date->isToday()) {
                $indicators[] = 'Today';
            } elseif ($date->isYesterday()) {
                $indicators[] = 'Yesterday';
            } elseif ($date->isTomorrow()) {
                $indicators[] = 'Tomorrow';
            } else {
                $indicators[] = $date->format('M j, Y');
            }
        } elseif ($this->searchDateRange !== 'single' && $this->searchDateFrom && $this->searchDateTo) {
            $dateFrom = Carbon::parse($this->searchDateFrom);
            $dateTo = Carbon::parse($this->searchDateTo);
            $indicators[] = $dateFrom->format('M j') . ' - ' . $dateTo->format('M j, Y');
        }

        if ($this->searchVisitType) {
            $visitTypes = [
                'walk-in' => 'Walk-in',
                'appointment' => 'Appointment'
            ];
            $indicators[] = $visitTypes[$this->searchVisitType] ?? $this->searchVisitType;
        }

        if ($this->searchBranch) {
            $branch = Branch::find($this->searchBranch);
            if ($branch) {
                $indicators[] = $branch->name;
            }
        }
        
        if (Auth::user()->isDentist() && $this->showMyVisits) {
            $indicators[] = 'My Visits';
        }

        if (!empty($indicators)) {
            $title .= ' for ' . implode(' - ', $indicators);
        }

        return $title;
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
            'branches' => Branch::orderBy('name')->get(),
            'dynamicTitle' => $this->dynamicTitle
        ]);
    }

    public function bulkDelete()
    {
        $query = PatientVisit::query();

        if (!Auth::user()->isSuperadmin()) {
            $query->where('branch_id', Auth::user()->branch_id);
        }

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