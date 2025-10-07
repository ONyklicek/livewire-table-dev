<?php

namespace NyonCode\LivewireTable\Concerns;

use Closure;

trait HasSubRows
{
    protected ?string $subRowRelation = null;

    protected ?Closure $subRowView = null;

    protected array $expandedRows = [];

    protected bool $lazyLoadSubRows = true;

    public function subRows(string $relation, Closure $viewCallback): static
    {
        $this->subRowRelation = $relation;
        $this->subRowView = $viewCallback;

        return $this;
    }

    public function lazyLoadSubRows(bool $lazy = true): static
    {
        $this->lazyLoadSubRows = $lazy;

        return $this;
    }

    /**
     * Render sub row.
     */
    public function renderSubRow($record): ?string
    {
        if (! $this->subRowView || ! $this->isRowExpanded($record->id)) {
            return null;
        }

        $subData = $this->getSubRowsData($record);

        return ($this->subRowView)($subData, $record);
    }

    public function isRowExpanded($recordId): bool
    {
        return in_array($recordId, $this->state['expandedRows'] ?? []);
    }

    public function getSubRowsData($record): mixed
    {
        if (! $this->subRowRelation) {
            return null;
        }

        return $record->{$this->subRowRelation};
    }

    public function hasSubRows(): bool
    {
        return $this->subRowRelation !== null;
    }

    public function getExpandedRows(): array
    {
        return $this->state['expandedRows'] ?? [];
    }
}
