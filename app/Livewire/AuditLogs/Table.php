<?php

namespace App\Livewire\AuditLogs;

use App\Models\AuditLog;
use App\Models\User;
use Carbon\Carbon;
use Livewire\Attributes\Layout;
use Livewire\Component;
use Livewire\WithPagination;

#[Layout('components.layouts.app')]
class Table extends Component
{
    use WithPagination;

    public $search = '';
    public $searchEvent = '';
    public $searchUser = '';
    public $searchDate = '';
    public $perPage = 10;
    public $sortColumn = 'created_at';
    public $sortDirection = 'desc';

    public function sortBy($column)
    {
        if ($this->sortColumn === $column) {
            $this->sortDirection = $this->sortDirection === 'asc' ? 'desc' : 'asc';
        } else {
            $this->sortColumn = $column;
            $this->sortDirection = 'asc';
        }
    }

    public function getAuditLogsProperty()
    {
        $query = AuditLog::with('user')
            ->when($this->search, function ($q) {
                $q->where('message', 'like', '%' . $this->search . '%')
                  ->orWhere('auditable_type', 'like', '%' . $this->search . '%');
            })
            ->when($this->searchEvent, function ($q) {
                return $q->where('event', $this->searchEvent);
            })
            ->when($this->searchUser, function ($q) {
                return $q->where('user_id', $this->searchUser);
            })
            ->when($this->searchDate, function ($q) {
                return $q->whereDate('created_at', $this->searchDate);
            });

        return $query->orderBy($this->sortColumn, $this->sortDirection)
            ->paginate($this->perPage);
    }

    public function clearFilters()
    {
        $this->reset('search', 'searchEvent', 'searchUser', 'searchDate');
        $this->resetPage();
    }
    
    public function getEventBadgeClass(string $event): string
    {
        return match ($event) {
            'created' => 'bg-green-100 text-green-800 dark:bg-green-900 dark:text-green-200',
            'updated' => 'bg-amber-100 text-amber-800 dark:bg-amber-900 dark:text-amber-200',
            'deleted' => 'bg-red-100 text-red-800 dark:bg-red-900 dark:text-red-200',
            default => 'bg-slate-100 text-slate-800 dark:bg-slate-700 dark:text-slate-200',
        };
    }

    public function render()
    {
        $this->authorize('viewAny', AuditLog::class);

        return view('livewire.audit-logs.table', [
            'auditLogs' => $this->auditLogs,
            'eventTypes' => ['created', 'updated', 'deleted', 'login', 'logout'],
            'users' => User::orderBy('first_name')->get(),
        ]);
    }
}