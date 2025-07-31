<?php

namespace App\Traits\Livewire;

use App\DataTable\DataTableFactory;
use Illuminate\Support\Carbon;
use Livewire\WithPagination;

trait WithDataTable
{
    use WithPagination;

    public $search = '';

    public $sortColumn = '';

    public $sortDirection = 'asc';

    public $perPage = 10;

    public ?string $deleteAction = null;

    public array $selectedRows = [];

    public bool $selectPage = false;

    public bool $selectAll = false;

    public string $routeIdColumn = 'id';

    protected ?DataTableFactory $dataTableFactory = null;

    protected $queryString = [
        'search' => ['except' => ''],
        'sortColumn' => ['except' => ''],
        'sortDirection' => ['except' => 'asc'],
        'perPage' => ['except' => 10],
    ];

    public function setDataTableFactory(DataTableFactory $factory): void
    {
        $factory->autoDetectModel(static::class);
        $this->dataTableFactory = $factory;
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

    public function canCreateRecord(): bool
    {
        return $this->dataTableFactory ? $this->dataTableFactory->canCreate() : true;
    }

    public function canBulkDelete(): bool
    {
        return $this->dataTableFactory ? $this->dataTableFactory->canBulkDelete() : true;
    }

    public function getDeleteAction($row): string
    {
        $idValue = $this->getIdValue($row);
        return $this->deleteAction . '(' . ($idValue ?? '') . ')';
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
     * Get view route with ID
     */
    public function getViewRoute($row): string
    {
        $factory = $this->dataTableFactory;
        if (!$factory) {
            return '#';
        }

        $config = $factory->toArray();
        $viewRoute = $config['viewRoute'] ?? '';

        if (empty($viewRoute)) {
            return '#';
        }

        $id = $this->getIdValue($row);

        if (\Route::has($viewRoute)) {
            try {
                return route($viewRoute, $id);
            } catch (\Exception $e) {
                return '#';
            }
        }

        return str_replace(':id', $id ?? '', $viewRoute);
    }

    /**
     * Get edit route with ID
     */
    public function getEditRoute($row): string
    {
        $factory = $this->dataTableFactory;
        if (!$factory) {
            return '#';
        }

        $config = $factory->toArray();
        $editRoute = $config['editRoute'] ?? '';

        if (empty($editRoute)) {
            return '#';
        }

        $id = $this->getIdValue($row);

        if (\Route::has($editRoute)) {
            try {
                return route($editRoute, $id);
            } catch (\Exception $e) {
                return '#';
            }
        }

        return str_replace(':id', $id ?? '', $editRoute);
    }

    /**
     * Get create route
     */
    public function getCreateRoute(): string
    {
        $factory = $this->dataTableFactory;
        if (!$factory) {
            return '#';
        }

        $config = $factory->toArray();
        $createRoute = $config['createRoute'] ?? '';

        if (empty($createRoute)) {
            return '#';
        }

        if (\Route::has($createRoute)) {
            return route($createRoute);
        }

        return $createRoute;
    }

    public function updatedSearch()
    {
        $this->resetPage();
    }

    public function updatedPerPage()
    {
        $this->resetPage();
    }

    public function sortBy($column)
    {
        if ($this->sortColumn === $column) {
            $this->sortDirection = $this->sortDirection === 'asc' ? 'desc' : 'asc';
        } else {
            $this->sortColumn = $column;
            $this->sortDirection = 'asc';
        }
        $this->resetPage();
    }

    public function updatedSelectPage($value)
    {
        // This requires the component to have a computed 'rows' property.
        $this->selectedRows = $value
            ? $this->rows->pluck('id')->map(fn($id) => (string) $id)->toArray()
            : [];
        $this->selectAll = false;
    }

    public function updatedSelectedRows()
    {
        $this->selectAll = false;
        $this->selectPage = false;
    }

    public function selectAll()
    {
        $this->selectAll = true;
    }

    public function clearSelection()
    {
        $this->selectedRows = [];
        $this->selectPage = false;
        $this->selectAll = false;
    }

    public function getSelectedRowsCountProperty()
    {
        if ($this->selectAll) {
            // This requires the component to have a computed 'rowsQuery' property.
            return $this->rowsQuery->count();
        }

        return count($this->selectedRows);
    }

    public function getHeaderValue($header, $row)
    {
        $value = null;

        if (isset($header['accessor']) && $header['accessor'] === true) {
            $value = $this->getAccessorValue($row, $header['key']);
        } else {
            $value = $row[$header['key']] ?? null;
        }

        if (($value === null || $value === '') && isset($header['defaultValue'])) {
            return $header['defaultValue'];
        }

        return $value ?? '';
    }

    protected function getAccessorValue($model, $accessor)
    {
        if (str_contains($accessor, '.')) {
            $parts = explode('.', $accessor);
            $value = $model;
            foreach ($parts as $part) {
                $value = $value->{$part} ?? null;
                if ($value === null) {
                    break;
                }
            }

            return $value;
        }

        return $model->{$accessor} ?? null;
    }

    public function formatValue($header, $value)
    {
        if (($value === null || $value === '') && isset($header['defaultValue'])) {
            $value = $header['defaultValue'];
        }

        if (! isset($header['type'])) {
            return $value;
        }

        return match ($header['type']) {
            'date' => $this->formatDateValue($value),
            'datetime' => $this->formatDateTimeValue($value),
            'currency' => number_format((float) ($value ?? 0), 2, ',', ' ') . ' zł',
            'boolean' => $value ? 'Yes' : 'No',
            default => $value
        };
    }

    private function formatDateValue($value): string
    {
        if (!$value) {
            return '';
        }

        if (is_string($value) && !$this->isValidDateString($value)) {
            return $value;
        }

        try {
            return Carbon::parse($value)->format('M j, Y');
        } catch (\Exception $e) {
            return $value;
        }
    }

    private function formatDateTimeValue($value): string
    {
        if (!$value) {
            return '';
        }

        if (is_string($value) && !$this->isValidDateString($value)) {
            return $value;
        }

        try {
            return Carbon::parse($value)->format('M j, Y');
        } catch (\Exception $e) {
            return $value;
        }
    }

    /**
     * Check if a string is a valid date string
     */
    private function isValidDateString($value): bool
    {
        if (!is_string($value)) {
            return false;
        }

        $datePatterns = [
            '/^\d{4}-\d{2}-\d{2}/',
            '/^\d{2}\/\d{2}\/\d{4}/',
            '/^\d{2}-\d{2}-\d{4}/',
            '/^\d{4}-\d{2}-\d{2} \d{2}:\d{2}/',
        ];

        foreach ($datePatterns as $pattern) {
            if (preg_match($pattern, $value)) {
                return true;
            }
        }

        try {
            Carbon::parse($value);
            return true;
        } catch (\Exception $e) {
            return false;
        }
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

    public function getBooleanBadgeClass($value): string
    {
        return $value ? 'bg-green-100 text-green-800' : 'bg-red-100 text-red-800';
    }

    public function shouldShowOnMobile($header): bool
    {
        return ! in_array($header['key'], ['id']);
    }

    public function getRouteWithId($route, $id): string
    {
        return str_replace(':id', $id ?? '', $route);
    }

    public function getPaginationRange($paginator): array
    {
        $onEachSide = 1;
        $window = $onEachSide * 2;
        if ($paginator->lastPage() < $window + 4) {
            $start = 1;
            $end = $paginator->lastPage();
        } else {
            $start = max(1, $paginator->currentPage() - $onEachSide);
            $end = min($paginator->lastPage(), $paginator->currentPage() + $onEachSide);
            if ($end - $start < $window) {
                if ($start === 1) {
                    $end = min($paginator->lastPage(), $start + $window);
                } else {
                    $start = max(1, $end - $window);
                }
            }
        }

        return [
            'start' => $start,
            'end' => $end,
            'showStartEllipsis' => $start > 2,
            'showEndEllipsis' => $end < $paginator->lastPage() - 1,
        ];
    }

    protected function applySearchAndSort($query, $searchableColumns = [], $dataTable = null)
    {
        if (!empty($this->search)) {
            $query->where(function ($q) use ($searchableColumns, $dataTable) {
                foreach ($searchableColumns as $column) {
                    $q->orWhere($column, 'like', '%' . $this->search . '%');
                }
                if ($dataTable) {
                    $headers = $dataTable->getHeaders();
                    foreach ($headers as $header) {
                        if (isset($header['accessor'], $header['search_columns']) && $header['accessor'] === true) {
                            foreach ($header['search_columns'] as $searchColumn) {
                                $this->addNestedSearch($q, $searchColumn);
                            }
                        }
                    }
                }
            });
        }

        if (!empty($this->sortColumn)) {
            $this->applySorting($query, $dataTable);
        }

        return $query;
    }

    /**
     * Add nested relationship search
     */
    protected function addNestedSearch($query, string $searchColumn): void
    {
        $parts = explode('.', $searchColumn);

        if (count($parts) === 1) {
            $query->orWhere($searchColumn, 'like', '%' . $this->search . '%');
        } else {
            $relationship = array_shift($parts);
            $column = implode('.', $parts);

            $query->orWhereHas($relationship, function ($q) use ($column, $parts) {
                if (count($parts) === 1) {
                    $q->where($parts[0], 'like', '%' . $this->search . '%');
                } else {
                    $this->addNestedSearch($q, $column);
                }
            });
        }
    }

    /**
     * Add joins for sorting by relationship columns
     */
    protected function addSortJoin($query, array $parts): void
    {
        $model = $query->getModel();
        $currentTable = $model->getTable();
        $joinedTables = [];

        for ($i = 0; $i < count($parts) - 1; $i++) {
            $relationship = $parts[$i];

            if (!method_exists($model, $relationship)) {
                continue;
            }

            $relationInstance = $model->$relationship();
            $relatedModel = $relationInstance->getRelated();
            $relatedTable = $relatedModel->getTable();

            $joinKey = $currentTable . '_' . $relatedTable;
            if (in_array($joinKey, $joinedTables)) {
                $currentTable = $relatedTable;
                $model = $relatedModel;
                continue;
            }

            $joinedTables[] = $joinKey;

            if (method_exists($relationInstance, 'getForeignKeyName')) {
                $foreignKey = $relationInstance->getForeignKeyName();
                $ownerKey = $relationInstance->getOwnerKeyName();

                $query->leftJoin($relatedTable, $currentTable . '.' . $foreignKey, '=', $relatedTable . '.' . $ownerKey);
            } elseif (method_exists($relationInstance, 'getForeignKey')) {
                $foreignKey = $relationInstance->getForeignKey();
                $localKey = $relationInstance->getLocalKeyName();

                $query->leftJoin($relatedTable, $currentTable . '.' . $localKey, '=', $relatedTable . '.' . $foreignKey);
            }

            $currentTable = $relatedTable;
            $model = $relatedModel;
        }

        $finalColumn = end($parts);
        $query->orderBy($currentTable . '.' . $finalColumn, $this->sortDirection);
    }

    /**
     * Apply sorting with relationship support
     */
    protected function applySorting($query, $dataTable = null): void
    {
        $isAccessorSort = false;

        if ($dataTable) {
            $headers = $dataTable->getHeaders();
            foreach ($headers as $header) {
                if ($header['key'] === $this->sortColumn && isset($header['accessor'], $header['sort_columns']) && $header['accessor'] === true) {
                    $isAccessorSort = true;

                    // Sort by multiple columns for names (first_name, then last_name)
                    foreach ($header['sort_columns'] as $index => $sortColumn) {
                        if ($index === 0) {
                            $this->addNestedSort($query, $sortColumn, true); // Primary sort
                        } else {
                            $this->addNestedSort($query, $sortColumn, false); // Secondary sort
                        }
                    }
                    break;
                }
            }
        }

        if (!$isAccessorSort) {
            $query->orderBy($this->sortColumn, $this->sortDirection);
        }
    }

    /**
     * Add nested relationship sorting with join support
     */
    protected function addNestedSort($query, string $sortColumn, bool $isPrimary = true): void
    {
        $parts = explode('.', $sortColumn);

        if (count($parts) === 1) {
            // Simple column
            $query->orderBy($sortColumn, $this->sortDirection);
        } else {
            // Complex relationship - use subquery approach for better reliability
            $this->addSortSubquery($query, $parts, $isPrimary);
        }
    }

    /**
     * Add sorting using subqueries (more reliable than joins for complex relationships)
     */
    protected function addSortSubquery($query, array $parts, bool $isPrimary = true): void
    {
        $model = $query->getModel();
        $currentModel = $model;
        $relationships = [];

        // Build relationship chain
        for ($i = 0; $i < count($parts) - 1; $i++) {
            $relationshipName = $parts[$i];

            if (!method_exists($currentModel, $relationshipName)) {
                return; // Skip if relationship doesn't exist
            }

            $relationship = $currentModel->$relationshipName();
            $relationships[] = [
                'name' => $relationshipName,
                'instance' => $relationship,
                'model' => $relationship->getRelated()
            ];

            $currentModel = $relationship->getRelated();
        }

        $finalColumn = end($parts);

        // Use raw SQL with subquery for sorting
        if (!empty($relationships)) {
            $subquery = $this->buildSortSubquery($relationships, $finalColumn, $model);
            if ($subquery) {
                $query->orderByRaw("({$subquery}) {$this->sortDirection}");
            }
        }
    }

    /**
     * Build subquery for sorting
     */
    /**
     * Build subquery for sorting
     */
    protected function buildSortSubquery(array $relationships, string $finalColumn, $baseModel): ?string
    {
        try {
            $currentTable = $baseModel->getTable();
            $finalTable = end($relationships)['model']->getTable();

            $subquery = "SELECT {$finalTable}.{$finalColumn}";
            $joins = [];
            $currentTableAlias = $currentTable;

            foreach ($relationships as $index => $rel) {
                $relationInstance = $rel['instance'];
                $relatedModel = $rel['model'];
                $relatedTable = $relatedModel->getTable();

                if (method_exists($relationInstance, 'getForeignKeyName')) {
                    $foreignKey = $relationInstance->getForeignKeyName();
                    $ownerKey = $relationInstance->getOwnerKeyName();

                    if ($index === 0) {
                        $subquery .= " FROM {$relatedTable}";
                        $whereCondition = "{$relatedTable}.{$ownerKey} = {$currentTable}.{$foreignKey}";
                    } else {
                        $prevTable = $relationships[$index - 1]['model']->getTable();
                        $joins[] = "INNER JOIN {$relatedTable} ON {$relatedTable}.{$ownerKey} = {$prevTable}.{$foreignKey}";
                    }
                } elseif (method_exists($relationInstance, 'getForeignKey')) {
                    $foreignKey = $relationInstance->getForeignKey();
                    $localKey = $relationInstance->getLocalKeyName();

                    if ($index === 0) {
                        $subquery .= " FROM {$relatedTable}";
                        $whereCondition = "{$relatedTable}.{$foreignKey} = {$currentTable}.{$localKey}";
                    } else {
                        $prevTable = $relationships[$index - 1]['model']->getTable();
                        $joins[] = "INNER JOIN {$relatedTable} ON {$relatedTable}.{$foreignKey} = {$prevTable}.{$localKey}";
                    }
                }
            }

            if (!empty($joins)) {
                $subquery .= " " . implode(" ", $joins);
            }

            if (isset($whereCondition)) {
                $subquery .= " WHERE {$whereCondition}";
            }

            $subquery .= " LIMIT 1";

            return $subquery;
        } catch (\Exception $e) {
            \Log::error('Subquery build error: ' . $e->getMessage());
            return null;
        }
    }
}
