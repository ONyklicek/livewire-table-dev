<?php

namespace App\Support\Tables\Concerns;

use Illuminate\Support\Collection;

trait HasActions
{
    protected Collection $actions;
    protected Collection $bulkActions;

    /**
     * Set actions.
     *
     * @param  array  $actions
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
     * @param  array  $actions
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
     *
     * @return Collection
     */
    public function getActions(): Collection
    {
        return $this->actions;
    }

    /**
     * Get bulk actions.
     *
     * @return Collection
     */
    public function getBulkActions(): Collection
    {
        return $this->bulkActions;
    }
}
