<?php

namespace NyonCode\LivewireTable\Concerns;

use Illuminate\Support\Collection;

trait HasActions
{
    protected Collection $actions;

    protected Collection $bulkActions;

    /**
     * Set actions.
     *
     *
     * @return $this
     */
    public function actions(array $actions): static
    {
        $this->actions = collect($actions);

        return $this;
    }

    /**
     * Set bulk actions.
     *
     *
     * @return $this
     */
    public function bulkActions(array $actions): static
    {
        $this->bulkActions = collect($actions);

        return $this;
    }

    /**
     * Get actions.
     */
    public function getActions(): Collection
    {
        return $this->actions;
    }

    /**
     * Get bulk actions.
     */
    public function getBulkActions(): Collection
    {
        return $this->bulkActions;
    }
}
