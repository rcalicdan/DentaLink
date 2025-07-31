<?php

namespace App\View\Components;

use App\DataTable\DataTableFactory;
use Illuminate\View\Component;
use Illuminate\Contracts\View\View;
use Carbon\Carbon;

class DataTable extends Component
{
    public array $headers;
    public $data;
    public bool $showActions;
    public bool $showBulkActions;
    public ?string $bulkDeleteAction;
    public bool $showSearch;
    public bool $showCreate;
    public string $createRoute;
    public string $createButtonName;
    public ?string $editRoute;
    public ?string $viewRoute;
    public ?string $deleteAction;
    public string $searchPlaceholder;
    public string $emptyMessage;
    public string $searchQuery;
    public string $sortColumn;
    public string $sortDirection;
    public int $selectedRowsCount;
    public bool $selectAll;
    public bool $selectPage;
    public array $selectedRows;
    public ?DataTableFactory $dataTableFactory;
    public array $permissions;
    public string $routeIdColumn; // New property

    public function __construct(
        array $headers = [],
        $data = null,
        bool $showActions = true,
        bool $showBulkActions = false,
        ?string $bulkDeleteAction = null,
        bool $showSearch = true,
        bool $showCreate = true,
        string $createRoute = '#',
        string $createButtonName = 'Add New',
        ?string $editRoute = null,
        ?string $viewRoute = null,
        ?string $deleteAction = null,
        string $searchPlaceholder = 'Search...',
        string $emptyMessage = 'No data available',
        string $searchQuery = '',
        string $sortColumn = '',
        string $sortDirection = 'asc',
        int $selectedRowsCount = 0,
        bool $selectAll = false,
        bool $selectPage = false,
        array $selectedRows = [],
        ?DataTableFactory $dataTableFactory = null,
        array $permissions = [],
        string $routeIdColumn = 'id' // New parameter
    ) {
        $this->headers = $headers;
        $this->data = $data;
        $this->showActions = $showActions;
        $this->showBulkActions = $showBulkActions;
        $this->bulkDeleteAction = $bulkDeleteAction;
        $this->showSearch = $showSearch;
        $this->showCreate = $showCreate;
        $this->createRoute = $createRoute;
        $this->createButtonName = $createButtonName;
        $this->editRoute = $editRoute;
        $this->viewRoute = $viewRoute;
        $this->deleteAction = $deleteAction;
        $this->searchPlaceholder = $searchPlaceholder;
        $this->emptyMessage = $emptyMessage;
        $this->searchQuery = $searchQuery;
        $this->sortColumn = $sortColumn;
        $this->sortDirection = $sortDirection;
        $this->selectedRowsCount = $selectedRowsCount;
        $this->selectAll = $selectAll;
        $this->selectPage = $selectPage;
        $this->selectedRows = $selectedRows;
        $this->dataTableFactory = $dataTableFactory;
        $this->permissions = $permissions;
        $this->routeIdColumn = $routeIdColumn;
    }

    public function canViewRow($row): bool
    {
        return $this->dataTableFactory ? $this->dataTableFactory->canView($row) : true;
    }

    public function canEditRow($row): bool
    {
        return $this->dataTableFactory ? $this->dataTableFactory->canEdit($row) : true;
    }

    public function canDeleteRow($row): bool
    {
        return $this->dataTableFactory ? $this->dataTableFactory->canDelete($row) : true;
    }

    public function shouldShowCreateButton(): bool
    {
        if (!$this->showCreate) return false;

        return $this->dataTableFactory ? $this->dataTableFactory->canCreate() : true;
    }

    public function shouldShowBulkActions(): bool
    {
        if (!$this->showBulkActions) return false;

        return $this->dataTableFactory ? $this->dataTableFactory->canBulkDelete() : true;
    }

    public function getHeaderValue($header, $row)
    {
        $value = '';
        if (isset($header['accessor']) && $header['accessor'] === true) {
            if (str_contains($header['key'], '.')) {
                $parts = explode('.', $header['key']);
                $tempValue = $row;
                foreach ($parts as $part) {
                    $tempValue = $tempValue->{$part} ?? null;
                    if ($tempValue === null) {
                        break;
                    }
                }
                $value = $tempValue;
            } else {
                $value = $row->{$header['key']} ?? '';
            }
        } else {
            $value = $row[$header['key']] ?? '';
        }
        return $value;
    }

    /**
     * Format value based on header type
     */
    public function formatValue($header, $value)
    {
        if (!isset($header['type'])) {
            return $value;
        }
        return match ($header['type']) {
            'date' => $value ? Carbon::parse($value)->format('M d, Y') : '',
            'datetime' => $value ? Carbon::parse($value)->format('M d, Y H:i') : '',
            'currency' => '$' . number_format($value ?? 0, 2),
            'boolean' => $value ? 'Yes' : 'No',
            default => $value
        };
    }

    public function getBadgeClass($value): string
    {
        if (is_array($value)) {
            return $value['class'] ?? 'bg-gray-100 text-gray-800';
        }

        return match (strtolower((string) $value)) {
            'active', 'published', 'approved', 'completed' => 'bg-green-100 text-green-800',
            'inactive', 'draft', 'pending' => 'bg-yellow-100 text-yellow-800',
            'deleted', 'rejected', 'cancelled' => 'bg-red-100 text-red-800',
            default => 'bg-gray-100 text-gray-800'
        };
    }

    public function getBadgeText($value): string
    {
        if (is_array($value)) {
            return $value['text'] ?? (string) $value;
        }

        return (string) $value;
    }

    public function getBooleanBadgeClass($value): string
    {
        return $value ? 'bg-green-100 text-green-800' : 'bg-red-100 text-red-800';
    }

    public function shouldShowOnMobile($header): bool
    {
        return !in_array($header['key'], ['id']);
    }

    public function getRouteWithId($route, $row): string
    {
        if (empty($route)) {
            return '#';
        }

        $id = $this->getIdValue($row);

        if (\Route::has($route)) {
            try {
                return route($route, $id);
            } catch (\Exception $e) {
                return '#';
            }
        }

        return str_replace(':id', $id ?? '', $route);
    }

    /**
     * Get the ID value based on the configured route ID column
     */
    protected function getIdValue($row)
    {
        $column = $this->routeIdColumn;

        if (is_object($row)) {
            return $row->{$column} ?? $row->id ?? '';
        }

        if (is_array($row)) {
            return $row[$column] ?? $row['id'] ?? '';
        }

        return '';
    }

    /**
     * Get view action with ID
     */
    public function getViewAction($row): string
    {
        if (empty($this->viewRoute)) {
            return '#';
        }
        return $this->getRouteWithId($this->viewRoute, $row);
    }

    /**
     * Get edit action with ID  
     */
    public function getEditAction($row): string
    {
        if (empty($this->editRoute)) {
            return '#';
        }
        return $this->getRouteWithId($this->editRoute, $row);
    }

    /**
     * Get create action
     */
    public function getCreateAction(): string
    {
        if (empty($this->createRoute)) {
            return '#';
        }
        
        if (\Route::has($this->createRoute)) {
            return route($this->createRoute);
        }
        
        return $this->createRoute;
    }

    /**
     * Get delete action with ID
     */
    public function getDeleteAction($row): string
    {
        $idValue = $this->getIdValue($row);
        return $this->deleteAction . '(' . ($idValue ?? '') . ')';
    }

    /**
     * Calculate pagination range
     */
    public function getPaginationRange($paginator): array
    {
        $start = max(1, $paginator->currentPage() - 2);
        $end = min($paginator->lastPage(), $paginator->currentPage() + 2);
        return [
            'start' => $start,
            'end' => $end,
            'showStartEllipsis' => $start > 2,
            'showEndEllipsis' => $end < $paginator->lastPage() - 1
        ];
    }

    public function render(): View
    {
        return view('components.data-table', [
            'data' => $this->data,
            'headers' => $this->headers,
            'showActions' => $this->showActions,
            'showBulkActions' => $this->shouldShowBulkActions(),
            'bulkDeleteAction' => $this->bulkDeleteAction,
            'showSearch' => $this->showSearch,
            'showCreate' => $this->shouldShowCreateButton(),
            'createRoute' => $this->createRoute,
            'createButtonName' => $this->createButtonName,
            'editRoute' => $this->editRoute,
            'viewRoute' => $this->viewRoute,
            'deleteAction' => $this->deleteAction,
            'searchPlaceholder' => $this->searchPlaceholder,
            'emptyMessage' => $this->emptyMessage,
            'searchQuery' => $this->searchQuery,
            'sortColumn' => $this->sortColumn,
            'sortDirection' => $this->sortDirection,
            'selectedRowsCount' => $this->selectedRowsCount,
            'selectAll' => $this->selectAll,
            'selectPage' => $this->selectPage,
            'selectedRows' => $this->selectedRows,
            'dataTableFactory' => $this->dataTableFactory,
            'permissions' => $this->permissions,
            'routeIdColumn' => $this->routeIdColumn,
        ]);
    }
}
