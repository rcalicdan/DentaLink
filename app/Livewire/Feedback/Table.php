<?php

namespace App\Livewire\Feedback;

use App\DataTable\DataTableFactory;
use App\Models\Feedback;
use App\Traits\Livewire\WithDataTable;
use Livewire\Attributes\Layout;
use Livewire\Component;
use Livewire\WithPagination;

#[Layout('components.layouts.app')]
class Table extends Component
{
    use WithDataTable, WithPagination;

    public function boot()
    {
        $this->deleteAction = 'deleteFeedback';
        $this->routeIdColumn = 'id';
        $this->setDataTableFactory($this->getDataTableConfig());
    }

    private function getDataTableConfig(): DataTableFactory
    {
        return DataTableFactory::make()
            ->model(Feedback::class)
            ->headers([
                [
                    'key' => 'rating',
                    'label' => 'Rating',
                    'sortable' => true,
                    'type' => 'custom'
                ],
                [
                    'key' => 'email',
                    'label' => 'Email',
                    'sortable' => true
                ],
                [
                    'key' => 'content',
                    'label' => 'Feedback',
                    'sortable' => false,
                ],
                [
                    'key' => 'created_at',
                    'label' => 'Submitted',
                    'sortable' => true,
                    'type' => 'datetime'
                ],
            ])
            ->deleteAction('deleteFeedback')
            ->searchPlaceholder('Search feedback...')
            ->emptyMessage('No feedback found')
            ->searchQuery($this->search)
            ->sortColumn($this->sortColumn)
            ->sortDirection($this->sortDirection)
            ->showBulkActions(true)
            ->showCreate(false)
            ->bulkDeleteAction('bulkDelete');
    }

    public function rowsQuery()
    {
        $query = Feedback::query();
        $dataTable = $this->getDataTableConfig();
        return $this->applySearchAndSort($query, ['email', 'content'], $dataTable);
    }

    public function getRowsProperty()
    {
        return $this->rowsQuery()
            ->orderBy('created_at', 'desc')
            ->paginate($this->perPage);
    }

    public function render()
    {
        $this->authorize('viewAny', Feedback::class);
        $dataTable = $this->getDataTableConfig()->toArray();
        $selectedRowsCount = $this->getSelectedRowsCountProperty();

        return view('livewire.feedback.table', [
            'dataTable' => $dataTable,
            'selectedRowsCount' => $selectedRowsCount,
        ]);
    }

    public function bulkDelete()
    {
        $query = Feedback::query();

        if ($this->selectAll) {
            $query = $this->rowsQuery();
        } else {
            $query->whereIn('id', $this->selectedRows);
        }
        
        $query->delete();
        $this->clearSelection();
        
        $this->dispatch('show-message', [
            'message' => 'Feedback deleted successfully.',
            'type' => 'success'
        ]);
    }

    public function deleteFeedback($id)
    {
        $feedback = Feedback::findOrFail($id);
        $this->authorize('delete', $feedback);

        $feedback->delete();

        $this->dispatch('show-message', [
            'message' => 'Feedback deleted successfully.',
            'type' => 'success'
        ]);
    }
}