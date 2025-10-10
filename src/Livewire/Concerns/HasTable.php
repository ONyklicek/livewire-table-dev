<?php

namespace NyonCode\LivewireTable\Livewire\Concerns;

use Illuminate\Database\Eloquent\Builder;
use Livewire\Attributes\On;
use Livewire\WithPagination;
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

    public function getTableProperty(): Table
    {
        $table = $this->table(Table::make());

        $table->setLivewireComponent(static::class);
        $table->setState([
            'filters' => $this->tableFilters,
            'search' => $this->tableSearch,
            'sortColumn' => $this->tableSortColumn,
            'sortDirection' => $this->tableSortDirection,
            'perPage' => $this->tablePerPage,
            'selected' => $this->tableSelected,
            'expandedGroups' => $this->expandedGroups,
            'expandedRows' => $this->expandedRows,
            'hiddenColumns' => $this->hiddenColumns,
        ]);

        return $table;
    }

    public function updatedTableSearch(): void
    {
        $this->resetPage();
    }

    public function updatedTableFilters(): void
    {
        $this->resetPage();
    }

    public function sortBy(string $column): void
    {
        if ($this->tableSortColumn === $column) {
            $this->tableSortDirection = $this->tableSortDirection === 'asc' ? 'desc' : 'asc';
        } else {
            $this->tableSortColumn = $column;
            $this->tableSortDirection = 'asc';
        }
    }

    public function selectAll(): void
    {
        $this->tableSelectAll = ! $this->tableSelectAll;

        if ($this->tableSelectAll) {
            $this->tableSelected = $this->table->getData()->pluck('id')->toArray();
        } else {
            $this->tableSelected = [];
        }
    }

    public function executeAction(string $action, $recordId): void
    {
        $table = $this->table(Table::make());
        $actionObject = $table->getActions()->firstWhere('name', $action);

        if ($actionObject) {
            $model = $table->getModel();
            $record = $model instanceof Builder
                ? $model->find($recordId)
                : $model::find($recordId);
            $actionObject->execute($record);
        }
    }

    public function executeBulkAction(string $action): void
    {
        $table = $this->table(Table::make());
        $bulkActionObject = $table->getBulkActions()->firstWhere('name', $action);

        if ($bulkActionObject && ! empty($this->tableSelected)) {
            $model = $table->getModel();
            $records = $model instanceof Builder
                ? $model->findMany($this->tableSelected)
                : $model::findMany($this->tableSelected);
            $bulkActionObject->execute($records);
            $this->tableSelected = [];
            $this->tableSelectAll = false;
        }
    }

    public function updateCell($recordId, string $columnField, $value): void
    {
        $table = $this->table(Table::make());
        $column = $table->getColumns()->firstWhere('field', $columnField);

        if (! $column instanceof \App\Tables\Columns\EditableColumn) {
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

    public function toggleGroup(string $groupKey): void
    {
        if (in_array($groupKey, $this->expandedGroups)) {
            $this->expandedGroups = array_filter(
                $this->expandedGroups,
                fn ($k) => $k !== $groupKey
            );
        } else {
            $this->expandedGroups[] = $groupKey;
        }
    }

    public function toggleRow($recordId): void
    {
        if (in_array($recordId, $this->expandedRows)) {
            $this->expandedRows = array_filter(
                $this->expandedRows,
                fn ($id) => $id !== $recordId
            );
        } else {
            $this->expandedRows[] = $recordId;
        }
    }

    public function toggleColumn(string $columnField): void
    {
        if (in_array($columnField, $this->hiddenColumns)) {
            $this->hiddenColumns = array_filter(
                $this->hiddenColumns,
                fn ($field) => $field !== $columnField
            );
        } else {
            $this->hiddenColumns[] = $columnField;
        }
    }

    public function showAllColumns(): void
    {
        $this->hiddenColumns = [];
    }

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

    public function clearFilters(): void
    {
        $this->tableFilters = [];
        $this->tableSearch = '';
        $this->activePresetId = null;
        $this->resetPage();
    }
}
