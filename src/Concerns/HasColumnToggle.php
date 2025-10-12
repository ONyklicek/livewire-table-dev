<?php

namespace NyonCode\LivewireTable\Concerns;

use Illuminate\Support\Collection;

trait HasColumnToggle
{
    protected array $hiddenColumns = [];

    protected bool $columnToggleEnabled = true;

    protected array $alwaysVisibleColumns = [];

    public function enableColumnToggle(bool $enabled = true): static
    {
        $this->columnToggleEnabled = $enabled;

        return $this;
    }

    public function alwaysVisible(array $columns): static
    {
        $this->alwaysVisibleColumns = $columns;

        return $this;
    }

    public function getVisibleColumns(): Collection
    {
        return $this->columns->filter(function ($column) {
            return $column->isVisible() && $this->isColumnVisible($column->getField());
        });
    }

    public function isColumnVisible(string $columnField): bool
    {
        $hiddenColumns = $this->state['hiddenColumns'] ?? [];

        return ! in_array($columnField, $hiddenColumns);
    }

    public function getToggleableColumns(): Collection
    {
        return $this->columns->filter(function ($column) {
            return ! $column->isVisible() && ! in_array($column->getField(), $this->alwaysVisibleColumns);
        });
    }

    public function isColumnToggleEnabled(): bool
    {
        return $this->columnToggleEnabled;
    }

    public function getHiddenColumns(): array
    {
        return $this->state['hiddenColumns'] ?? [];
    }
}
