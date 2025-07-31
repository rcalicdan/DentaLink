<?php

namespace App\DataTable;

use Illuminate\Support\Facades\Gate;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Str;

class DataTableFactory
{
    protected array $headers = [];
    protected $query;
    protected array $config = [];
    protected array $searchableColumns = [];
    protected array $accessorColumns = [];
    protected ?string $model = null;
    protected ?string $autoDetectedModel = null;
    protected array $relationshipAccessors = [];

    public function __construct()
    {
        $this->config = [
            'showActions' => true,
            'showSearch' => true,
            'showCreate' => true,
            'createRoute' => '#',
            'createButtonName' => 'Add New',
            'editRoute' => null,
            'viewRoute' => null,
            'deleteAction' => null,
            'searchPlaceholder' => 'Search...',
            'emptyMessage' => 'No data available',
            'searchQuery' => '',
            'sortColumn' => '',
            'sortDirection' => 'asc',
            'showBulkActions' => false,
            'bulkDeleteAction' => null,
        ];
    }

    public function model(string $model): self
    {
        $this->model = $model;
        $this->autoDetectRelationshipAccessors();
        return $this;
    }

    public function autoDetectModel(string $componentClass): self
    {
        $componentName = class_basename($componentClass);
        $modelName = str_replace(['Table', 'Component', 'Livewire'], '', $componentName);
        $modelName = Str::singular($modelName);

        $modelClass = "App\\Models\\{$modelName}";

        if (class_exists($modelClass)) {
            $this->autoDetectedModel = $modelClass;
            $this->autoDetectRelationshipAccessors();
        }

        return $this;
    }

    protected function getModelClass(): ?string
    {
        return $this->model ?? $this->autoDetectedModel;
    }

    /**
     * Auto-detect relationship accessors with predefined mappings
     */
    protected function autoDetectRelationshipAccessors(): void
    {
        $modelClass = $this->getModelClass();
        if (!$modelClass || !class_exists($modelClass)) {
            return;
        }

        // Define common accessor patterns for better detection
        $commonPatterns = [
            'client_name' => ['client.first_name', 'client.last_name'],
            'driver_name' => ['driver.user.first_name', 'driver.user.last_name'],
            'driver_full_name' => ['driver.user.first_name', 'driver.user.last_name'],
            'user_name' => ['user.first_name', 'user.last_name'],
            'full_name' => ['first_name', 'last_name'],
            'price_list_name' => ['priceList.name'],
        ];

        $model = new $modelClass;
        $detected = $this->detectRelationshipAccessors($model);
        
        // Merge detected with common patterns
        $this->relationshipAccessors = array_merge($commonPatterns, $detected);
    }

    /**
     * Detect relationship accessors by analyzing accessor methods
     */
    protected function detectRelationshipAccessors($model): array
    {
        $accessors = [];
        $reflection = new \ReflectionClass($model);
        $methods = $reflection->getMethods(\ReflectionMethod::IS_PUBLIC);

        foreach ($methods as $method) {
            $methodName = $method->getName();
            
            if (preg_match('/^get(.+)Attribute$/', $methodName, $matches)) {
                $attributeName = Str::snake($matches[1]);
                $columns = $this->analyzeAccessorForRelationships($method, $model);
                
                if (!empty($columns)) {
                    $accessors[$attributeName] = $columns;
                }
            }
        }

        return $accessors;
    }

    /**
     * Analyze accessor method to determine relationship columns
     */
    protected function analyzeAccessorForRelationships(\ReflectionMethod $method, $model): array
    {
        try {
            $source = $this->getMethodSource($method);
            $relationships = [];

            // Enhanced patterns for relationship detection
            $patterns = [
                // $this->relationship->attribute or $this->relationship?->attribute
                '/\$this->(\w+)\??->(\w+)/' => function($matches) use ($model) {
                    if ($this->isValidRelationship($model, $matches[1])) {
                        return [$matches[1] . '.' . $matches[2]];
                    }
                    return [];
                },
                // Handle full_name patterns specifically
                '/full_name|name/' => function($matches) {
                    return ['first_name', 'last_name'];
                },
            ];

            foreach ($patterns as $pattern => $handler) {
                if (preg_match_all($pattern, $source, $matches, PREG_SET_ORDER)) {
                    foreach ($matches as $match) {
                        $result = $handler($match);
                        $relationships = array_merge($relationships, $result);
                    }
                }
            }

            return array_unique($relationships);
        } catch (\Exception $e) {
            return [];
        }
    }

    protected function getMethodSource(\ReflectionMethod $method): string
    {
        try {
            $filename = $method->getFileName();
            $startLine = $method->getStartLine();
            $endLine = $method->getEndLine();
            
            if ($filename && $startLine && $endLine) {
                $lines = file($filename);
                return implode('', array_slice($lines, $startLine - 1, $endLine - $startLine + 1));
            }
        } catch (\Exception $e) {
            // Ignore errors
        }
        
        return '';
    }

    protected function isValidRelationship($model, string $relationshipName): bool
    {
        try {
            return method_exists($model, $relationshipName);
        } catch (\Exception $e) {
            return false;
        }
    }

    public function headers(array $headers): self
    {
        $this->headers = $this->processHeaders($headers);
        return $this;
    }

    protected function processHeaders(array $headers): array
    {
        foreach ($headers as &$header) {
            $key = $header['key'];
            
            if (isset($this->relationshipAccessors[$key])) {
                $relationships = $this->relationshipAccessors[$key];
                
                if (!isset($header['accessor'])) {
                    $header['accessor'] = true;
                }
                
                if (!isset($header['search_columns'])) {
                    $header['search_columns'] = $relationships;
                }
                
                if (!isset($header['sort_columns'])) {
                    $header['sort_columns'] = $relationships;
                }
                
                if (!isset($header['sortable'])) {
                    $header['sortable'] = true;
                }
            }
        }
        
        return $headers;
    }

    public function addHeader(string $key, string $label, array $options = []): self
    {
        $header = array_merge([
            'key' => $key,
            'label' => $label,
            'sortable' => false,
            'type' => 'text',
            'accessor' => false,
            'searchable' => false,
            'search_columns' => [],
            'sort_columns' => [],
            'defaultValue' => null,
        ], $options);

        if (isset($this->relationshipAccessors[$key])) {
            $relationships = $this->relationshipAccessors[$key];
            
            $header = array_merge($header, [
                'accessor' => true,
                'search_columns' => $relationships,
                'sort_columns' => $relationships,
                'sortable' => true,
            ], $options);
        }

        $this->headers[] = $header;
        return $this;
    }

    // Add a method to manually register accessor mappings
    public function registerAccessor(string $accessor, array $columns): self
    {
        $this->relationshipAccessors[$accessor] = $columns;
        return $this;
    }

    // ... (rest of existing methods remain the same)

    public static function make(): self
    {
        return new static;
    }

    public function canCreate(): bool
    {
        $model = $this->getModelClass();
        return $model ? Gate::allows('create', $model) : true;
    }

    public function canView($model = null): bool
    {
        $modelClass = $this->getModelClass();
        if (!$modelClass) return true;
        return Gate::allows('view', $model ?? $modelClass);
    }

    public function canEdit($model = null): bool
    {
        $modelClass = $this->getModelClass();
        if (!$modelClass) return true;
        return Gate::allows('update', $model ?? $modelClass);
    }

    public function canDelete($model = null): bool
    {
        $modelClass = $this->getModelClass();
        if (!$modelClass) return true;
        return Gate::allows('delete', $model ?? $modelClass);
    }

    public function canBulkDelete(): bool
    {
        $modelClass = $this->getModelClass();
        return $modelClass ? Gate::allows('delete', $modelClass) : true;
    }

    public function searchableColumns(array $columns): self
    {
        $this->searchableColumns = $columns;
        return $this;
    }

    public function accessorColumns(array $columns): self
    {
        $this->accessorColumns = $columns;
        return $this;
    }

    public function query(Builder $query): self
    {
        $this->query = $query;
        return $this;
    }

    public function data($data): self
    {
        if ($data instanceof Builder) {
            $this->query = $data;
        } else {
            $this->query = $data;
        }
        return $this;
    }

    public function config(array $config): self
    {
        $this->config = array_merge($this->config, $config);
        return $this;
    }

    public function showActions(bool $show = true): self
    {
        $this->config['showActions'] = $show;
        return $this;
    }

    public function showBulkActions(bool $show = true): self
    {
        $this->config['showBulkActions'] = $show;
        return $this;
    }

    public function bulkDeleteAction(string $action): self
    {
        $this->config['bulkDeleteAction'] = $action;
        return $this;
    }

    public function showSearch(bool $show = true): self
    {
        $this->config['showSearch'] = $show;
        return $this;
    }

    public function showCreate(bool $show = true): self
    {
        $this->config['showCreate'] = $show;
        return $this;
    }

    public function createRoute(string $route): self
    {
        $this->config['createRoute'] = $route;
        return $this;
    }

    public function createButtonName(string $name): self
    {
        $this->config['createButtonName'] = $name;
        return $this;
    }

    public function editRoute(string $route): self
    {
        $this->config['editRoute'] = $route;
        return $this;
    }

    public function viewRoute(string $route): self
    {
        $this->config['viewRoute'] = $route;
        return $this;
    }

    public function deleteAction(string $action): self
    {
        $this->config['deleteAction'] = $action;
        return $this;
    }

    public function searchPlaceholder(string $placeholder): self
    {
        $this->config['searchPlaceholder'] = $placeholder;
        return $this;
    }

    public function emptyMessage(string $message): self
    {
        $this->config['emptyMessage'] = $message;
        return $this;
    }

    public function searchQuery(string $query): self
    {
        $this->config['searchQuery'] = $query;
        return $this;
    }

    public function sortColumn(string $column): self
    {
        $this->config['sortColumn'] = $column;
        return $this;
    }

    public function sortDirection(string $direction): self
    {
        $this->config['sortDirection'] = $direction;
        return $this;
    }

    public function getQuery(): Builder
    {
        return $this->query;
    }

    public function getSearchableColumns(): array
    {
        return $this->searchableColumns;
    }

    public function getAccessorColumns(): array
    {
        return $this->accessorColumns;
    }

    public function getHeaders(): array
    {
        return $this->headers;
    }

    public function getModel(): ?string
    {
        return $this->model;
    }

    public function getRelationshipAccessors(): array
    {
        return $this->relationshipAccessors;
    }

    public function toArray(): array
    {
        $resolved = $this->resolveRoutes();

        return array_merge($this->config, [
            'headers' => $this->headers,
            'searchableColumns' => $this->searchableColumns,
            'accessorColumns' => $this->accessorColumns,
            'relationshipAccessors' => $this->relationshipAccessors,
            'model' => $this->model,
        ], $resolved);
    }

    protected function resolveRoutes(): array
    {
        $createRoute = $this->config['createRoute'] ?? '';

        return [
            'createRoute' => $this->resolveRoute($createRoute),
            'editRoute' => $this->config['editRoute'],
            'viewRoute' => $this->config['viewRoute'],
        ];
    }

    protected function resolveRoute(string $route): string
    {
        if (empty($route)) {
            return '#';
        }

        if (\Route::has($route)) {
            return route($route);
        }

        return $route;
    }
}