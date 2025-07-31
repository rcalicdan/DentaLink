<?php

namespace App\Livewire\Users;

use App\DataTable\DataTableFactory;
use App\Models\User;
use App\Traits\Livewire\WithDataTable;
use Livewire\Component;

class Table extends Component
{
    use WithDataTable;

    public function mount()
    {
        $this->deleteAction = 'deleteUser';
        
        $dataTable = DataTableFactory::make()
            ->model(User::class)
            ->headers([
                [
                    'key' => 'id',
                    'label' => 'ID',
                    'sortable' => true,
                    'type' => 'text'
                ],
                [
                    'key' => 'first_name',
                    'label' => 'First Name',
                    'sortable' => true,
                    'searchable' => true,
                    'type' => 'text'
                ],
                [
                    'key' => 'last_name',
                    'label' => 'Last Name',
                    'sortable' => true,
                    'searchable' => true,
                    'type' => 'text'
                ],
                [
                    'key' => 'email',
                    'label' => 'Email',
                    'sortable' => true,
                    'searchable' => true,
                    'type' => 'text'
                ],
                [
                    'key' => 'phone',
                    'label' => 'Phone',
                    'sortable' => true,
                    'searchable' => true,
                    'type' => 'text'
                ],
                [
                    'key' => 'role',
                    'label' => 'Role',
                    'sortable' => true,
                    'searchable' => true,
                    'type' => 'badge'
                ],
                [
                    'key' => 'branch.name',
                    'label' => 'Branch',
                    'sortable' => true,
                    'searchable' => true,
                    'accessor' => true,
                    'search_columns' => ['branch.name'],
                    'sort_columns' => ['branch.name'],
                    'type' => 'text',
                    'defaultValue' => 'No Branch'
                ],
                [
                    'key' => 'created_at',
                    'label' => 'Created',
                    'sortable' => true,
                    'type' => 'date'
                ]
            ])
            ->config([
                'showActions' => true,
                'showSearch' => true,
                'showCreate' => true,
                'showBulkActions' => true,
                'createRoute' => 'users.create',
                'createButtonName' => 'Add User',
                'editRoute' => 'users.edit',
                'viewRoute' => 'users.show',
                'deleteAction' => 'deleteUser',
                'bulkDeleteAction' => 'bulkDeleteUsers',
                'searchPlaceholder' => 'Search users...',
                'emptyMessage' => 'No users found',
            ])
            ->searchableColumns([
                'first_name',
                'last_name', 
                'email',
                'phone',
                'role'
            ]);

        $this->setDataTableFactory($dataTable);
    }

    public function getRowsQueryProperty()
    {
        return User::query()
            ->with(['branch'])
            ->when($this->search, function ($query) {
                $query->where(function ($q) {
                    $q->where('first_name', 'like', '%' . $this->search . '%')
                      ->orWhere('last_name', 'like', '%' . $this->search . '%')
                      ->orWhere('email', 'like', '%' . $this->search . '%')
                      ->orWhere('phone', 'like', '%' . $this->search . '%')
                      ->orWhere('role', 'like', '%' . $this->search . '%')
                      ->orWhereHas('branch', function ($branchQuery) {
                          $branchQuery->where('name', 'like', '%' . $this->search . '%');
                      });
                });
            });
    }

    public function getRowsProperty()
    {
        return $this->applySearchAndSort(
            $this->rowsQuery,
            $this->dataTableFactory->getSearchableColumns(),
            $this->dataTableFactory
        )->paginate($this->perPage);
    }

    public function deleteUser($userId)
    {
        $user = User::find($userId);
        
        if (!$user) {
            $this->dispatch('toast', [
                'type' => 'error',
                'message' => 'User not found.'
            ]);
            return;
        }

        if (!$this->canDeleteRow($user)) {
            $this->dispatch('toast', [
                'type' => 'error',
                'message' => 'You do not have permission to delete this user.'
            ]);
            return;
        }

        try {
            $user->delete();
            
            $this->dispatch('toast', [
                'type' => 'success',
                'message' => 'User deleted successfully.'
            ]);
            
            // Reset selection if the deleted user was selected
            $this->selectedRows = array_filter($this->selectedRows, function($id) use ($userId) {
                return $id != $userId;
            });
            
        } catch (\Exception $e) {
            $this->dispatch('toast', [
                'type' => 'error',
                'message' => 'Failed to delete user. Please try again.'
            ]);
        }
    }

    public function bulkDeleteUsers()
    {
        if (empty($this->selectedRows)) {
            $this->dispatch('toast', [
                'type' => 'error',
                'message' => 'No users selected.'
            ]);
            return;
        }

        if (!$this->canBulkDelete()) {
            $this->dispatch('toast', [
                'type' => 'error',
                'message' => 'You do not have permission to delete users.'
            ]);
            return;
        }

        try {
            $query = User::whereIn('id', $this->selectedRows);
            
            if ($this->selectAll) {
                $query = $this->rowsQuery;
            }
            
            $deletedCount = $query->count();
            $query->delete();
            
            $this->dispatch('toast', [
                'type' => 'success',
                'message' => "Successfully deleted {$deletedCount} user(s)."
            ]);
            
            $this->clearSelection();
            
        } catch (\Exception $e) {
            $this->dispatch('toast', [
                'type' => 'error',
                'message' => 'Failed to delete users. Please try again.'
            ]);
        }
    }

    public function render()
    {
        return view('livewire.users.table', [
            'users' => $this->rows,
        ]);
    }
}