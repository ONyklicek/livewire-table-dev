<?php

namespace App\Support\Tables;

use App\Support\Tables\Builders\QueryBuilder;
use App\Support\Tables\Builders\RelationshipResolver;
use App\Support\Tables\Concerns\HasActions;
use App\Support\Tables\Concerns\HasColumns;
use App\Support\Tables\Concerns\HasColumnToggle;
use App\Support\Tables\Concerns\HasFilters;
use App\Support\Tables\Concerns\HasGrouping;
use App\Support\Tables\Concerns\HasPagination;
use App\Support\Tables\Concerns\HasResponsiveScheme;
use App\Support\Tables\Concerns\HasSavedFilters;
use App\Support\Tables\Concerns\HasSubRows;
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
    protected ?string $livewireComponent = null;
    protected array $data = [];
    protected ?int $liveUpdateInterval = null;
    protected array $state = [];

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
     * Set the live update interval for the table.
     *
     * @param  int  $seconds
     *
     * @return $this
     */
    public function liveUpdate(int $seconds): static
    {
        $this->liveUpdateInterval = $seconds;
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

    /**
     * Eager load the relationships.
     *
     * @param  Builder  $query
     *
     * @return Builder
     *
     * @deprecated Use RelationshipResolver::eagerLoad() instead
     */
    public function eagerLoadRelationships(Builder $query): Builder
    {
        $relationships = RelationshipResolver::extractRelationships($this->columns);
        return RelationshipResolver::eagerLoad($query, $relationships);
    }

    /**
     * Apply the filters to the query.
     *
     * @param  Builder  $query
     *
     * @return Builder
     *
     * @deprecated Use QueryBuilder instead
     */
    private function applyFilters(Builder $query): Builder
    {
        foreach ($this->filters as $filter) {
            $value = $this->state['filters'][$filter->getName()] ?? null;
            if ($value !== null && $value !== '') {
                $query = $filter->apply($query, $value);
            }
        }

        foreach ($this->globalFilters as $filter) {
            $value = $this->state['filters'][$filter->getName()] ?? null;
            if ($value !== null && $value !== '') {
                $query = $filter->apply($query, $value);
            }
        }

        return $query;
    }

    /**
     * Apply the sorting to the query.
     *
     * @param  Builder  $query
     *
     * @return Builder
     *
     * @deprecated Use QueryBuilder::multiSort() instead
     */
    private function applySorting(Builder $query): Builder
    {
        $sortColumn = $this->state['sortColumn'] ?? '';
        $sortDirection = $this->state['sortDirection'] ?? 'asc';

        if (empty($sortColumn)) {
            return $query;
        }

        if (str_contains($sortColumn, '.')) {
            [$relation, $relatedField] = explode('.', $sortColumn, 2);
            return $query->with([
                $relation => function ($q) use ($relatedField, $sortDirection) {
                    $q->orderBy($relatedField, $sortDirection);
                }
            ]);
        }

        return $query->orderBy($sortColumn, $sortDirection);
    }

    /**
     * Apply the search to the query.
     *
     * @param  Builder  $query
     *
     * @return Builder
     *
     * @deprecated Use QueryBuilder::search() instead
     */
    private function applySearch(Builder $query): Builder
    {
        $search = $this->state['search'] ?? '';

        if (empty($search)) {
            return $query;
        }

        $searchableColumns = $this->columns->filter(fn($col) => $col->isSearchable());

        if ($searchableColumns->isEmpty()) {
            return $query;
        }

        return $query->where(function ($q) use ($searchableColumns, $search) {
            foreach ($searchableColumns as $column) {
                $field = $column->getField();

                if (str_contains($field, '.')) {
                    [$relation, $relatedField] = explode('.', $field, 2);
                    $q->orWhereHas($relation, function ($query) use ($relatedField, $search) {
                        $query->where($relatedField, 'like', "%$search%");
                    });
                } else {
                    $q->orWhere($field, 'like', "%$search%");
                }
            }
        });
    }
}
