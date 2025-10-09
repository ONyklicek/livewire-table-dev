<?php

namespace NyonCode\LivewireTable\Concerns;

use Closure;
use Illuminate\Support\Collection;

trait HasGrouping
{
    protected ?string $groupBy = null;

    protected ?Closure $groupByCallback = null;

    protected bool $collapsibleGroups = true;

    protected array $expandedGroups = [];

    protected ?Closure $groupHeaderCallback = null;

    /**
     * Set collapsible groups.
     * Example: collapsibleGroups(false)
     *
     *
     * @return $this
     */
    public function collapsibleGroups(bool $collapsible = true): static
    {
        $this->collapsibleGroups = $collapsible;

        return $this;
    }

    /**
     * Set group header.
     * Example: groupHeader(fn($key, $items) => "{$key} ({$items->count()})")
     *
     *
     * @return $this
     */
    public function groupHeader(Closure $callback): static
    {
        $this->groupHeaderCallback = $callback;

        return $this;
    }

    /**
     * Get grouped data.
     */
    public function getGroupedData(): Collection
    {
        $data = $this->getData();

        if (! $this->groupBy) {
            return collect([['items' => $data, 'group' => null, 'key' => null]]);
        }

        $grouped = $data->groupBy(function ($item) {
            if ($this->groupByCallback) {
                return ($this->groupByCallback)($item);
            }

            return data_get($item, $this->groupBy);
        });

        return $grouped->map(function ($items, $groupKey) {
            return [
                'key' => $groupKey,
                'label' => $this->formatGroupLabel($groupKey, $items),
                'items' => $items,
                'count' => $items->count(),
                'collapsed' => ! in_array($groupKey, $this->state['expandedGroups'] ?? []),
            ];
        })->values();
    }

    /**
     * Set group by.
     * Example: groupBy('column', fn($item) => $item->column)
     *
     *
     * @return $this
     */
    public function groupBy(string $column, ?Closure $callback = null): static
    {
        $this->groupBy = $column;
        $this->groupByCallback = $callback;

        return $this;
    }

    /**
     * Format group label.
     * Example: formatGroupLabel(fn($key, $items) => "{$key} ({$items->count()})")
     */
    protected function formatGroupLabel($key, $items): string
    {
        if ($this->groupHeaderCallback) {
            return ($this->groupHeaderCallback)($key, $items);
        }

        return "$key ({$items->count()})";
    }

    /**
     * Get group by.
     */
    public function getGroupBy(): ?string
    {
        return $this->groupBy;
    }

    /**
     * Get collapsible groups.
     */
    public function isCollapsible(): bool
    {
        return $this->collapsibleGroups;
    }
}
