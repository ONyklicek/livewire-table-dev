<?php

namespace NyonCode\LivewireTable;

use Illuminate\Contracts\Support\Htmlable;
use Illuminate\Contracts\Support\Renderable;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Pagination\LengthAwarePaginator;
use InvalidArgumentException;
use NyonCode\LivewireTable\Builders\QueryBuilder;
use NyonCode\LivewireTable\Builders\RelationshipResolver;
use NyonCode\LivewireTable\Concerns\HasActions;
use NyonCode\LivewireTable\Concerns\HasColumns;
use NyonCode\LivewireTable\Concerns\HasColumnToggle;
use NyonCode\LivewireTable\Concerns\HasFilters;
use NyonCode\LivewireTable\Concerns\HasGrouping;
use NyonCode\LivewireTable\Concerns\HasPagination;
use NyonCode\LivewireTable\Concerns\HasResponsiveScheme;
use NyonCode\LivewireTable\Concerns\HasSavedFilters;
use NyonCode\LivewireTable\Concerns\HasSubRows;

class Table implements Renderable, Htmlable
{
    use HasActions;
    use HasColumns;
    use HasColumnToggle;
    use HasFilters;
    use HasGrouping;
    use HasPagination;
    use HasResponsiveScheme;
    use HasSavedFilters;
    use HasSubRows;

    public Model|Builder|Collection|string $model;

    public ?string $livewireComponent = null;

    public array $data = [];

    public ?int $liveUpdateInterval = null;

    public array $state = [];

    public function __construct()
    {
        $this->columns = collect();
        $this->filters = collect();
        $this->globalFilters = collect();
        $this->actions = collect();
        $this->bulkActions = collect();
        $this->perPage = 10;
        $this->pageOptions = [10, 25, 50, 100];
        $this->hiddenColumns = [];
        $this->expandedGroups = [];
        $this->expandedRows = [];
    }

    public static function make(): static
    {
        return new static;
    }

    public function model(Model|Builder|Collection|string $model): static
    {
        if (is_string($model)){
            if (!is_subclass_of($model, Model::class)) {
                throw new InvalidArgumentException("Class [$model] must be an instance of ".Model::class);
            }

            $this->model = (new $model())->newQuery();
            return $this;
        }

        if ($model instanceof Model) {
            $this->model = $model->newQuery();
            return $this;
        }

        if ($model instanceof Builder) {
            $this->model = $model;
            return $this;
        }

        if ($model instanceof Collection) {
            $this->model = $model;
            return $this;
        }

        throw new InvalidArgumentException("Model must be an instance of " . Model::class . " or Builder or Collection");
    }

    /**
     * Set the live update interval for the table.
     */
    public function liveUpdate(int $seconds): static
    {
        $this->liveUpdateInterval = $seconds;

        return $this;
    }

    /**
     * Set the Livewire component for the table.
     */
    public function setLivewireComponent(string $component): static
    {
        $this->livewireComponent = $component;

        return $this;
    }

    /**
     * Set the state for the table.
     */
    public function setState(array $state): static
    {
        $this->state = $state;

        return $this;
    }

    /**
     * Get the model for the table.
     */
    public function getModel(): Model|Builder
    {
        return $this->model;
    }

    /**
     * Convert the table to a string.
     */
    public function __toString(): string
    {
        return $this->toHtml();
    }

    /**
     * Convert the table to HTML.
     */
    public function toHtml(): string
    {
        return $this->render();
    }

    /**
     * Render the table.
     */
    public function render(): string
    {
        // Get data FIRST
        $rawData = $this->getData();

        // Then apply grouping if needed
        $data = $this->groupBy
            ? $this->applyGrouping($rawData)
            : collect([['items' => $rawData, 'group' => null, 'key' => null]]);

        $this->data = [
            'columns' => $this->getVisibleColumns(),
            'allColumns' => $this->columns,
            'filters' => $this->filters,
            'globalFilters' => $this->globalFilters,
            'actions' => $this->actions,
            'bulkActions' => $this->bulkActions,
            'data' => $data,
            'perPage' => $this->perPage,
            'pageOptions' => $this->pageOptions,
            'liveUpdateInterval' => $this->liveUpdateInterval,
            'scheme' => $this->getScheme(),
            'livewireComponent' => $this->livewireComponent,
            'groupBy' => $this->groupBy,
            'isCollapsible' => $this->isCollapsible(),
            'hasSubRows' => $this->hasSubRows(),
            'expandedRows' => $this->getExpandedRows(),
            'expandedGroups' => $this->state['expandedGroups'] ?? [],
            'columnToggleEnabled' => $this->isColumnToggleEnabled(),
            'toggleableColumns' => $this->getToggleableColumns(),
            'hiddenColumns' => $this->getHiddenColumns(),
            'presetsEnabled' => $this->isPresetsEnabled(),
            'presets' => $this->isPresetsEnabled() ? $this->getPresets() : collect(),
            'activePresetId' => $this->getActivePresetId(),
            'table' => $this,
            'tableSortColumn' => $this->state['sortColumn'] ?? '',
            'tableSortDirection' => $this->state['sortDirection'] ?? 'asc',
            'tableSelected' => $this->state['selected'] ?? [],
            'tableSearch' => $this->state['search'] ?? '',
            'tableFilters' => $this->state['filters'] ?? [],
        ];

        return view('livewire-table::components.table.table', $this->data)->render();
    }

    /**
     * Apply grouping to paginated data
     */
    public function applyGrouping($data): Collection
    {
        // If data is paginator, group only current page items
        $items = $data instanceof LengthAwarePaginator ? $data->items() : $data;

        $grouped = collect($items)->groupBy(function ($item) {
            if ($this->groupByCallback) {
                return ($this->groupByCallback)($item);
            }
            return data_get($item, $this->groupBy);
        });

        return $grouped->map(function ($groupItems, $groupKey) use ($data) {
            return [
                'key' => $groupKey,
                'label' => $this->formatGroupLabel($groupKey, $groupItems),
                'items' => $data instanceof LengthAwarePaginator ? $data->setCollection($groupItems) : $groupItems,
                'count' => $groupItems->count(),
                'collapsed' => !in_array($groupKey, $this->state['expandedGroups'] ?? []),
            ];
        })->values();
    }

    /**
     * Get the data for the table.
     */
    public function getData(): LengthAwarePaginator|Collection
    {
        // Validate all column fields
        $this->validateColumnFields();

        if ($this->model instanceof Collection) {
            return $this->model;
        }

        $query = $this->model instanceof Model
            ? $this->model->query()
            : clone $this->model;

        // Eager load relationships FIRST (optimization)
        $relationships = RelationshipResolver::extractRelationships($this->columns);
        $query = RelationshipResolver::eagerLoad($query, $relationships);

        // Apply column filters
        foreach ($this->filters as $filter) {
            $value = $this->state['filters'][$filter->getName()] ?? null;
            if ($value !== null && $value !== '') {
                $query = $filter->apply($query, $value);
            }
        }

        // Apply global filters
        foreach ($this->globalFilters as $filter) {
            $value = $this->state['filters'][$filter->getName()] ?? null;
            if ($value !== null && $value !== '') {
                $query = $filter->apply($query, $value);
            }
        }

        // Apply global search
        $search = $this->state['search'] ?? '';
        if (!empty($search)) {
            $searchableFields = $this->columns
                ->filter(fn($col) => $col->isSearchable())
                ->map(fn($col) => $col->getField())
                ->toArray();

            if (!empty($searchableFields)) {
                $queryBuilder = new QueryBuilder($query);
                $queryBuilder->search($searchableFields, $search);
                $query = $queryBuilder->get();
            }
        }

        // Apply sorting
        $sortColumn = $this->state['sortColumn'] ?? '';
        $sortDirection = $this->state['sortDirection'] ?? 'asc';

        if (!empty($sortColumn)) {
            if (str_contains($sortColumn, '.')) {
                // Relationship sorting - use join
                $parts = explode('.', $sortColumn);
                $relationName = $parts[0];
                $relationField = $parts[1];

                // Get the relationship
                $relation = $query->getModel()->{$relationName}();
                $relatedTable = $relation->getRelated()->getTable();
                $foreignKey = $relation->getForeignKeyName();
                $ownerKey = $relation->getOwnerKeyName();

                $query->leftJoin(
                    $relatedTable,
                    $query->getModel()->getTable() . '.' . $foreignKey,
                    '=',
                    $relatedTable . '.' . $ownerKey
                )->orderBy($relatedTable . '.' . $relationField, $sortDirection)
                    ->select($query->getModel()->getTable() . '.*');
            } else {
                $query->orderBy($sortColumn, $sortDirection);
            }
        }

        // Get perPage from state OR use default
        $perPage = isset($this->state['perPage']) ? (int) $this->state['perPage'] : $this->perPage;

        // Count before pagination
        $totalCount = $query->count();

        // Paginate
        $result = $query->paginate($perPage);

        // Ochrana proti prázdné stránce
        if ($result->isEmpty() && $result->currentPage() > 1) {
            $lastPage = max(1, $result->lastPage());

            $result = $query->paginate($perPage, ['*'], 'page', $lastPage);
        }

        return $result;
    }

    /**
     * Validate all column fields before processing
     *
     * @throws InvalidArgumentException
     */
    protected function validateColumnFields(): void
    {
        foreach ($this->columns as $column) {
            $field = $column->getField();

            if (RelationshipResolver::isRelationship($field)) {
                RelationshipResolver::validateField($field);
            }
        }
    }
}