<?php

namespace App\Livewire\Appointments;

use App\DataTable\DataTableFactory;
use App\Models\Appointment;
use App\Models\Patient;
use App\Enums\AppointmentStatuses;
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
    public $searchStatus = '';
    public $searchPatient = '';

    public function boot()
    {
        $this->deleteAction = 'deleteAppointment';
        $this->routeIdColumn = 'id';
        $this->setDataTableFactory($this->getDataTableConfig());

        if (empty($this->searchDate)) {
            $this->searchDate = Carbon::today()->format('Y-m-d');
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
            ->showCreate(true)
            ->createRoute('appointments.create')
            ->editRoute('appointments.edit')
            ->viewRoute('appointments.view')
            ->bulkDeleteAction('bulkDelete');
    }

    public function rowsQuery()
    {
        $query = Appointment::with(['patient', 'branch'])
            ->when($this->searchDate, function ($q) {
                return $q->whereDate('appointment_date', $this->searchDate);
            })
            ->when($this->searchStatus, function ($q) {
                return $q->where('status', $this->searchStatus);
            })
            ->when($this->searchPatient, function ($q) {
                return $q->whereHas('patient', function ($query) {
                    $query->whereRaw("CONCAT(first_name, ' ', last_name) LIKE ?", ["%{$this->searchPatient}%"]);
                });
            });

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
        $this->searchDate = Carbon::today()->format('Y-m-d');
        $this->searchStatus = '';
        $this->searchPatient = '';
        $this->search = '';
        $this->resetPage();
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

    public function render()
    {
        $this->authorize('viewAny', Appointment::class);
        $dataTable = $this->getDataTableConfig()->toArray();
        $selectedRowsCount = $this->getSelectedRowsCountProperty();

        return view('livewire.appointments.table', [
            'dataTable' => $dataTable,
            'selectedRowsCount' => $selectedRowsCount,
            'availableStatuses' => AppointmentStatuses::cases(),
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
