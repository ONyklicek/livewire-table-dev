<?php

namespace NyonCode\LivewireTable\Livewire\Concerns;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Pagination\LengthAwarePaginator;
use Livewire\Attributes\On;
use Livewire\WithPagination;
use NyonCode\LivewireTable\Columns\EditableColumn;
use NyonCode\LivewireTable\Models\TableFilterPreset;
use NyonCode\LivewireTable\Table;

trait HasTable
{
    use WithPagination;

    public array $tableFilters = [];

    public string $tableSearch = '';

    public string $tableSortColumn = '';

    public string $tableSortDirection = 'asc';

    public int $tablePerPage = 10;

    public array $tableSelected = [];

    public bool $tableSelectAll = false;

    public array $expandedGroups = [];

    public array $expandedRows = [];

    public array $hiddenColumns = [];

    public ?int $activePresetId = null;

    public bool $showPresetModal = false;

    public string $presetName = '';

    public string $selectedBulkAction = '';

    public function mountHasTable(): void
    {
        $table = $this->table(Table::make());
        $this->tablePerPage = $table->getPerPage();

        if ($table->isPresetsEnabled()) {
            $defaultPreset = TableFilterPreset::where('user_id', auth()->id())
                ->where('table_name', static::class)
                ->where('is_default', true)
                ->first();

            if ($defaultPreset) {
                $this->loadPreset($defaultPreset->id);
            }
        }
    }

    abstract public function table(Table $table): Table;

    /**
     * Load preset
     */
    #[On('load-preset')]
    public function loadPreset(int $presetId): void
    {
        $preset = TableFilterPreset::find($presetId);

        if (! $preset || $preset->user_id !== auth()->id()) {
            return;
        }

        $this->activePresetId = $presetId;
        $this->tableFilters = $preset->filters;

        $this->dispatch('preset-loaded');
    }

    /**
     * Get table property - vždy fresh instance s aktuálním state
     */
    public function getTableProperty(): Table
    {
        $table = $this->table(Table::make());

        $table->setLivewireComponent(static::class);

        // KRITICKÉ: State se musí nastavit s AKTUÁLNÍMI hodnotami
        $table->setState([
            'filters' => $this->tableFilters,
            'search' => $this->tableSearch,
            'sortColumn' => $this->tableSortColumn,
            'sortDirection' => $this->tableSortDirection,
            'perPage' => $this->tablePerPage,  // ← Tady se předává aktuální hodnota
            'selected' => $this->tableSelected,
            'expandedGroups' => $this->expandedGroups,
            'expandedRows' => $this->expandedRows,
            'hiddenColumns' => $this->hiddenColumns,
        ]);

        return $table;
    }

    /**
     * Updated hook - search
     */
    public function updatedTableSearch(): void
    {
        $this->resetPage();
    }

    /**
     * Updated hook - filters
     */
    public function updatedTableFilters(): void
    {
        $this->resetPage();
    }

    /**
     * Updated hook - per page
     */
    public function updatedTablePerPage(): void
    {
        $this->resetPage();
    }

    /**
     * Sort by column
     */
    public function sortBy(string $column): void
    {
        if ($this->tableSortColumn === $column) {
            $this->tableSortDirection = $this->tableSortDirection === 'asc' ? 'desc' : 'asc';
        } else {
            $this->tableSortColumn = $column;
            $this->tableSortDirection = 'asc';
        }

        $this->resetPage();
    }

    /**
     * Updated hook - select all
     */
    public function updatedTableSelectAll(): void
    {
        if ($this->tableSelectAll) {
            // Get all IDs from current page data
            $table = $this->table(Table::make());
            $data = $table->getData();

            if ($data instanceof LengthAwarePaginator) {
                $this->tableSelected = $data->pluck('id')->toArray();
            } elseif ($data instanceof \Illuminate\Support\Collection) {
                $this->tableSelected = $data->pluck('id')->toArray();
            }
        } else {
            $this->tableSelected = [];
        }
    }

    /**
     * Updated hook - individual selection changed
     */
    public function updatedTableSelected(): void
    {
        $table = $this->table(Table::make());
        $data = $table->getData();

        if ($data instanceof LengthAwarePaginator) {
            $allIds = $data->pluck('id')->toArray();
            $this->tableSelectAll = !empty($allIds) && count(array_intersect($this->tableSelected, $allIds)) === count($allIds);
        }
    }

    /**
     * Execute single action
     */
    public function executeAction(string $action, $recordId): void
    {
        $table = $this->table(Table::make());
        $actionObject = $table->getActions()->firstWhere('name', $action);

        if ($actionObject) {
            $model = $table->getModel();
            $record = $model instanceof Builder
                ? $model->find($recordId)
                : $model::find($recordId);

            if ($record) {
                $actionObject->execute($record);
                $this->dispatch('action-executed', ['action' => $action]);
            }
        }
    }

    /**
     * Execute bulk action
     */
    public function executeBulkAction(?string $action = null): void
    {
        $actionName = $action ?? $this->selectedBulkAction;

        if (empty($actionName)) {
            return;
        }

        $table = $this->table(Table::make());


        $bulkActionObject = $table->getBulkActions()->first(function($a) use ($actionName) {
            return $a->getName() === $actionName;
        });

        if ($bulkActionObject && !empty($this->tableSelected)) {
            $model = $table->getModel();
            $records = $model instanceof Builder
                ? $model->findMany($this->tableSelected)
                : $model::findMany($this->tableSelected);

            $bulkActionObject->execute($records);

            $this->tableSelected = [];
            $this->tableSelectAll = false;
            $this->selectedBulkAction = '';

            $this->dispatch('bulk-action-executed', ['action' => $actionName]);
        }
    }

    /**
     * Update cell (for editable columns)
     */
    public function updateCell($recordId, string $columnField, $value): void
    {
        $table = $this->table(Table::make());
        $column = $table->getColumns()->firstWhere('field', $columnField);

        if (! $column instanceof EditableColumn) {
            return;
        }

        $model = $table->getModel();
        $record = $model instanceof Builder
            ? $model->find($recordId)
            : $model::find($recordId);

        if (! $record) {
            return;
        }

        if ($rules = $column->getRules()) {
            $this->validate([
                'value' => $rules,
            ]);
        }

        $column->save($record, $value);

        $this->dispatch('cell-updated', [
            'recordId' => $recordId,
            'column' => $columnField,
        ]);
    }

    /**
     * Toggle group (for collapsible groups)
     */
    public function toggleGroup(string $groupKey): void
    {
        if (in_array($groupKey, $this->expandedGroups)) {
            $this->expandedGroups = array_values(
                array_filter($this->expandedGroups, fn($k) => $k !== $groupKey)
            );
        } else {
            $this->expandedGroups[] = $groupKey;
        }
    }

    /**
     * Toggle row (for sub-rows)
     */
    public function toggleRow($recordId): void
    {
        if (in_array($recordId, $this->expandedRows)) {
            $this->expandedRows = array_values(
                array_filter($this->expandedRows, fn($id) => $id !== $recordId)
            );
        } else {
            $this->expandedRows[] = $recordId;
        }
    }

    /**
     * Toggle column visibility
     */
    public function toggleColumn(string $columnField): void
    {
        if (in_array($columnField, $this->hiddenColumns)) {
            $this->hiddenColumns = array_values(
                array_filter($this->hiddenColumns, fn($field) => $field !== $columnField)
            );
        } else {
            $this->hiddenColumns[] = $columnField;
        }
    }

    /**
     * Show all columns
     */
    public function showAllColumns(): void
    {
        $this->hiddenColumns = [];
    }

    /**
     * Save preset
     */
    public function savePreset(): void
    {
        $this->validate([
            'presetName' => 'required|string|max:255',
        ]);

        TableFilterPreset::create([
            'user_id' => auth()->id(),
            'table_name' => static::class,
            'name' => $this->presetName,
            'filters' => $this->tableFilters,
            'is_default' => false,
        ]);

        $this->showPresetModal = false;
        $this->presetName = '';

        $this->dispatch('preset-saved');
    }

    /**
     * Delete preset
     */
    public function deletePreset(int $presetId): void
    {
        $preset = TableFilterPreset::find($presetId);

        if ($preset && $preset->user_id === auth()->id()) {
            $preset->delete();

            if ($this->activePresetId === $presetId) {
                $this->activePresetId = null;
                $this->tableFilters = [];
            }
        }

        $this->dispatch('preset-deleted');
    }

    /**
     * Clear all filters
     */
    public function clearFilters(): void
    {
        $this->tableFilters = [];
        $this->tableSearch = '';
        $this->activePresetId = null;
        $this->resetPage();
    }
}