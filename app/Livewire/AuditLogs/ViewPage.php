<?php

namespace App\Livewire\AuditLogs;

use App\Models\AuditLog;
use Livewire\Attributes\Layout;
use Livewire\Component;

#[Layout('components.layouts.app')]
class ViewPage extends Component
{
    public AuditLog $auditLog;

    public function mount(AuditLog $auditLog)
    {
        $this->authorize('view', $auditLog);
        $this->auditLog = $auditLog->load('user', 'auditable');
    }

    /**
     * Prepare a structured array of changes for easy display.
     */
    public function getChangesProperty(): array
    {
        $old = $this->auditLog->old_values ?? [];
        $new = $this->auditLog->new_values ?? [];
        $changes = [];
        $keys = array_unique(array_merge(array_keys($old), array_keys($new)));

        foreach ($keys as $key) {
            $oldValue = $old[$key] ?? null;
            $newValue = $new[$key] ?? null;

            if ($oldValue !== $newValue) {
                $changes[] = [
                    'field' => str_replace('_', ' ', \Illuminate\Support\Str::title($key)),
                    'old' => $oldValue,
                    'new' => $newValue,
                ];
            }
        }

        return $changes;
    }

    public function render()
    {
        return view('livewire.audit-logs.view-page');
    }
}