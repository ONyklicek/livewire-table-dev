<?php

namespace App\Support\Tables\Concerns;

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
     * @param  array  $columns
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
     *
     * @return Collection
     */
    public function getColumns(): Collection
    {
        return $this->columns;
    }
}
