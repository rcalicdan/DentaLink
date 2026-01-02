<?php

namespace App\Livewire\Appointments;

use App\Actions\Appointments\GenerateAppointmentsPdfAction;
use App\Actions\Appointments\GenerateAppointmentsCsvAction;
use App\DataTable\DataTableFactory;
use App\Models\Appointment;
use App\Enums\AppointmentStatuses;
use App\Models\Branch;
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
    public $searchStatus = '';
    public $searchPatient = '';
    public $searchBranch = '';
    public $showMyAppointments = false;

    public function boot()
    {
        $this->deleteAction = 'deleteAppointment';
        $this->routeIdColumn = 'id';
        $this->setDataTableFactory($this->getDataTableConfig());

        if (request()->has('date')) {
            $this->searchDate = request()->get('date');
            $this->searchDateRange = 'single';
        }

        if (empty($this->searchDate) && $this->searchDateRange === 'single') {
            $this->searchDate = Carbon::today()->format('Y-m-d');
        }

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
            ->model(Appointment::class)
            ->headers([
                [
                    'key' => 'queue_number',
                    'label' => 'Queue #',
                    'sortable' => true
                ],
                [
                    'key' => 'patient_name',
                    'label' => 'Patient',
                    'sortable' => true,
                    'accessor' => true,
                    'search_columns' => ['patient.first_name', 'patient.last_name'],
                ],
                [
                    'key' => 'appointment_date',
                    'label' => 'Date',
                    'sortable' => true,
                    'type' => 'date'
                ],
                [
                    'key' => 'status',
                    'label' => 'Status',
                    'sortable' => true,
                    'type' => 'enum_badge'
                ],
                [
                    'key' => 'reason',
                    'label' => 'Reason',
                    'sortable' => true
                ],
                [
                    'key' => 'created_at',
                    'label' => 'Created',
                    'sortable' => true,
                    'type' => 'datetime'
                ],
            ])
            ->deleteAction('deleteAppointment')
            ->searchPlaceholder('Search appointments...')
            ->emptyMessage('No appointments found')
            ->searchQuery($this->search)
            ->sortColumn($this->sortColumn)
            ->sortDirection($this->sortDirection)
            ->showBulkActions(true)
            ->showCreate(!Auth::user()->isDentist())
            ->showActions(!Auth::user()->isDentist())
            ->createRoute('appointments.create')
            ->editRoute('appointments.edit')
            ->viewRoute('appointments.view')
            ->bulkDeleteAction('bulkDelete');
    }

    public function rowsQuery()
    {
        $query = Appointment::with(['patient', 'branch']);

        if ($this->searchDateRange === 'single' && $this->searchDate) {
            $query->whereDate('appointment_date', $this->searchDate);
        } elseif ($this->searchDateRange !== 'single' && $this->searchDateFrom && $this->searchDateTo) {
            $query->whereBetween('appointment_date', [$this->searchDateFrom, $this->searchDateTo]);
        }

        $query->when($this->searchStatus, function ($q) {
            return $q->where('status', $this->searchStatus);
        })
            ->when($this->searchBranch, function ($q) {
                return $q->where('branch_id', $this->searchBranch);
            });

        if (Auth::user()->isDentist() && $this->showMyAppointments) {
            $query->where('dentist_id', Auth::id());
        }

        $dataTable = $this->getDataTableConfig();
        return $this->applySearchAndSort($query, ['reason', 'notes'], $dataTable);
    }

    public function getRowsProperty()
    {
        return $this->rowsQuery()
            ->orderBy('appointment_date', 'desc')
            ->orderBy('queue_number')
            ->paginate($this->perPage);
    }

    public function clearFilters()
    {
        $this->searchDateRange = 'single';
        $this->searchDate = Carbon::today()->format('Y-m-d');
        $this->searchDateFrom = '';
        $this->searchDateTo = '';
        $this->searchStatus = '';

        $this->showMyAppointments = false;

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
        $this->authorize('export', Appointment::class);

        $filters = [
            'branch_id' => $this->searchBranch,
            'status' => $this->searchStatus,
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

        if ($this->searchStatus) {
            $status = AppointmentStatuses::from($this->searchStatus);
            $filters['status_name'] = $status->getDisplayName();
        }

        $action = new GenerateAppointmentsPdfAction();
        $pdfContent = $action->execute($filters);

        $fileName = 'appointments_' . date('Y-m-d_His') . '.pdf';

        return Response::streamDownload(function () use ($pdfContent) {
            echo $pdfContent;
        }, $fileName, [
            'Content-Type' => 'application/pdf',
        ]);
    }

    public function downloadCsv()
    {
        $this->authorize('export', Appointment::class);

        $filters = [
            'branch_id' => $this->searchBranch,
            'status' => $this->searchStatus,
        ];

        if ($this->searchDateRange === 'single') {
            $filters['date'] = $this->searchDate;
        } else {
            $filters['date_from'] = $this->searchDateFrom;
            $filters['date_to'] = $this->searchDateTo;
        }

        $action = new GenerateAppointmentsCsvAction();
        $csvContent = $action->execute($filters);

        $fileName = 'appointments_' . date('Y-m-d_His') . '.csv';

        return Response::streamDownload(function () use ($csvContent) {
            echo $csvContent;
        }, $fileName, [
            'Content-Type' => 'text/csv',
            'Content-Disposition' => 'attachment; filename="' . $fileName . '"',
        ]);
    }

    public function updateStatus($appointmentId, $newStatus)
    {
        $appointment = Appointment::findOrFail($appointmentId);
        $this->authorize('update', $appointment);

        $status = AppointmentStatuses::from($newStatus);

        if (!$appointment->updateStatus($status, Auth::user())) {
            $this->dispatch('show-message', [
                'message' => 'Invalid status transition.',
                'type' => 'error'
            ]);
            return;
        }

        $this->dispatch('show-message', [
            'message' => 'Appointment status updated successfully.',
            'type' => 'success'
        ]);
    }

    public function getDynamicTitleProperty()
    {
        $title = 'Appointments';
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

        if ($this->searchStatus) {
            $status = AppointmentStatuses::from($this->searchStatus);
            $indicators[] = $status->getDisplayName();
        }

        if ($this->searchBranch) {
            $branch = Branch::find($this->searchBranch);
            if ($branch) {
                $indicators[] = $branch->name;
            }
        }

        if (Auth::user()->isDentist() && $this->showMyAppointments) {
            $indicators[] = 'My Appointments';
        }

        if (!empty($indicators)) {
            $title .= ' for ' . implode(' - ', $indicators);
        }

        return $title;
    }

    public function render()
    {
        $this->authorize('viewAny', Appointment::class);
        $dataTable = $this->getDataTableConfig()->toArray();
        $selectedRowsCount = $this->getSelectedRowsCountProperty();

        return view('livewire.appointments.table', [
            'dataTable' => $dataTable,
            'selectedRowsCount' => $selectedRowsCount,
            'availableStatuses' => AppointmentStatuses::cases(),
            'branches' => Branch::orderBy('name')->get(),
            'dynamicTitle' => $this->dynamicTitle
        ]);
    }

    public function bulkDelete()
    {
        $query = Appointment::query();

        if ($this->selectAll) {
            $query = $this->rowsQuery();
        } else {
            $query->whereIn('id', $this->selectedRows);
        }
        $query->delete();
        $this->clearSelection();
        $this->dispatch('show-message', [
            'message' => 'Appointments deleted successfully.',
            'type' => 'success'
        ]);
    }

    public function deleteAppointment($id)
    {
        $appointment = Appointment::findOrFail($id);
        $this->authorize('delete', $appointment);

        if (!Auth::user()->isSuperadmin() && !$appointment->canBeModified()) {
            $this->dispatch('show-message', [
                'message' => 'Cannot delete appointment with current status.',
                'type' => 'error'
            ]);
            return;
        }

        $appointment->delete();

        $this->dispatch('show-message', [
            'message' => 'Appointment deleted successfully.',
            'type' => 'success'
        ]);
    }
}
