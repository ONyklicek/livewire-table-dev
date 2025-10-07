<?php

namespace NyonCode\LivewireTable;

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
use Illuminate\Contracts\Pagination\LengthAwarePaginator;
use Illuminate\Contracts\View\View;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Database\Eloquent\Model;
use Throwable;

class Table
{
    use HasColumns;
    use HasFilters;
    use HasActions;
    use HasPagination;
    use HasResponsiveScheme;
    use HasGrouping;
    use HasSubRows;
    use HasColumnToggle;
    use HasSavedFilters;

    public Model|Builder|Collection $model;
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
        return new static();
    }

    public function model(Model|Builder|Collection $model): static
    {
        $this->model = $model;
        return $this;
    }

    /**
     * Set the Livewire component for the table.
     *
     * @param  string  $component
     *
     * @return $this
     */
    public function setLivewireComponent(string $component): static
    {
        $this->livewireComponent = $component;
        return $this;
    }

    /**
     * Set the state for the table.
     *
     * @param  array  $state
     *
     * @return $this
     */
    public function setState(array $state): static
    {
        $this->state = $state;
        return $this;
    }

    /**
     * Get the model for the table.
     *
     * @return Model|Builder
     */
    public function getModel(): Model|Builder
    {
        return $this->model;
    }

    /**
     * Convert the table to a string.
     *
     * @throws Throwable
     *
     * @return string
     */
    public function __toString(): string
    {
        return $this->toHtml();
    }

    /**
     * Convert the table to HTML.
     *
     * @throws Throwable
     *
     * @return string
     */
    public function toHtml(): string
    {
        return $this->render()->render();
    }

    /**
     * Render the table.
     *
     * @return View
     */
    public function render(): View
    {
        $data = $this->groupBy
            ? $this->getGroupedData()
            : collect([['items' => $this->getData(), 'group' => null, 'key' => null]]);

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
            'expandedGroups' => $this->expandedGroups,
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

        return view('components.table.table', $this->data);
    }

    /**
     * Get the data for the table.
     *
     * @return LengthAwarePaginator|Collection
     */
    public function getData(): LengthAwarePaginator|Collection
    {
        if ($this->model instanceof Collection) {
            return $this->model;
        }

        $query = $this->model instanceof Model
            ? $this->model->query()
            : $this->model;

        // Eager load relationships FIRST (optimization)
        $relationships = RelationshipResolver::extractRelationships($this->columns);
        $query = RelationshipResolver::eagerLoad($query, $relationships);

        // Create QueryBuilder instance
        $queryBuilder = new QueryBuilder($query);

        // Apply filters
        foreach ($this->filters as $filter) {
            $value = $this->state['filters'][$filter->getName()] ?? null;
            if ($value !== null && $value !== '') {
                $query = $filter->apply($queryBuilder->get(), $value);
                $queryBuilder = new QueryBuilder($query);
            }
        }

        foreach ($this->globalFilters as $filter) {
            $value = $this->state['filters'][$filter->getName()] ?? null;
            if ($value !== null && $value !== '') {
                $query = $filter->apply($queryBuilder->get(), $value);
                $queryBuilder = new QueryBuilder($query);
            }
        }

        // Apply search
        $search = $this->state['search'] ?? '';
        if (!empty($search)) {
            $searchableFields = $this->columns
                ->filter(fn($col) => $col->isSearchable())
                ->map(fn($col) => $col->getField())
                ->toArray();

            if (!empty($searchableFields)) {
                $queryBuilder->search($searchableFields, $search);
            }
        }

        // 5. Apply sorting
        $sortColumn = $this->state['sortColumn'] ?? '';
        $sortDirection = $this->state['sortDirection'] ?? 'asc';

        if (!empty($sortColumn)) {
            $queryBuilder->multiSort([$sortColumn => $sortDirection]);
        }

        // 6. Get final query and paginate
        return $queryBuilder->get()->paginate($this->perPage);
    }
}
