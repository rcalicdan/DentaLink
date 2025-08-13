<?php

namespace App\Livewire\AuditLogs;

use App\DataTable\DataTableFactory;
use App\Models\AuditLog;
use App\Models\User;
use App\Traits\Livewire\WithDataTable;
use Livewire\Attributes\Layout;
use Livewire\Component;
use Livewire\WithPagination;

#[Layout('components.layouts.app')]
class Table extends Component
{
    use WithDataTable, WithPagination;

    public $searchEvent = '';
    public $searchUser = '';

    public function boot()
    {
        $this->setDataTableFactory($this->getDataTableConfig());
    }

    private function getDataTableConfig(): DataTableFactory
    {
        return DataTableFactory::make()
            ->model(AuditLog::class)
            ->headers([
                [
                    'key' => 'event',
                    'label' => 'Event',
                    'sortable' => true,
                    'type' => 'badge',
                ],
                [
                    'key' => 'message',
                    'label' => 'Action Description',
                    'sortable' => true,
                ],
                [
                    'key' => 'user_name',
                    'label' => 'Performed By',
                    'accessor' => true,
                    'sort_columns' => ['users.first_name', 'users.last_name'],
                ],
                [
                    'key' => 'ip_address',
                    'label' => 'IP Address',
                    'sortable' => true
                ],
                [
                    'key' => 'created_at',
                    'label' => 'Timestamp',
                    'sortable' => true,
                    'type' => 'datetime'
                ],
            ])
            ->searchPlaceholder('Search descriptions...')
            ->emptyMessage('No audit logs found')
            ->searchQuery($this->search)
            ->sortColumn($this->sortColumn)
            ->sortDirection($this->sortDirection)
            ->showCreate(false) 
            ->viewRoute('audit-logs.view'); 
    }

    public function rowsQuery()
    {
        $query = AuditLog::with('user');

        $query->when($this->searchEvent, function ($q) {
            return $q->where('event', $this->searchEvent);
        })
        ->when($this->searchUser, function ($q) {
            return $q->where('user_id', $this->searchUser);
        });

        $dataTable = $this->getDataTableConfig();
        return $this->applySearchAndSort($query, ['message', 'auditable_type'], $dataTable);
    }

    public function getRowsProperty()
    {
        return $this->rowsQuery()
            ->orderBy($this->sortColumn, $this->sortDirection)
            ->paginate($this->perPage);
    }

    public function render()
    {
        $this->authorize('viewAny', AuditLog::class);
        $dataTable = $this->getDataTableConfig()->toArray();

        return view('livewire.audit-logs.table', [
            'dataTable' => $dataTable,
            'eventTypes' => ['created', 'updated', 'deleted', 'login', 'logout'],
            'users' => User::orderBy('first_name')->get(),
        ]);
    }
}