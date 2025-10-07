<?php

namespace NyonCode\LivewireTable\Concerns;

use Illuminate\Support\Collection;

trait HasColumns
{
    protected Collection $columns;

    /**
     * Set columns.
     * Example: columns([
     *     Column::make('name'),
     *     Column::make('email'),
     * ])
     *
     *
     * @return $this
     */
    public function columns(array $columns): static
    {
        $this->columns = collect($columns);

        return $this;
    }

    /**
     * Get all columns.
     */
    public function getColumns(): Collection
    {
        return $this->columns;
    }
}
