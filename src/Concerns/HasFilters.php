<?php

namespace App\Support\Tables\Concerns;

use Illuminate\Support\Collection;

trait HasFilters
{
    protected Collection $filters;
    protected Collection $globalFilters;

    /**
     * Set filters.
     * Example: filters([
     *     Filter::make('name'),
     *     Filter::make('email'),
     * ])
     *
     * @param  array  $filters
     *
     * @return $this
     */
    public function filters(array $filters): static
    {
        $this->filters = collect($filters);
        return $this;
    }

    /**
     * Set global filters.
     * Example: globalFilters([
     *     Filter::make('name'),
     *     Filter::make('email'),
     * ])
     *
     * @param  array  $filters
     *
     * @return $this
     */
    public function globalFilters(array $filters): static
    {
        $this->globalFilters = collect($filters);
        return $this;
    }

    /**
     * Get all filters.
     *
     * @return Collection
     */
    public function getFilters(): Collection
    {
        return $this->filters;
    }

    /**
     * Get all global filters.
     *
     * @return Collection
     */
    public function getGlobalFilters(): Collection
    {
        return $this->globalFilters;
    }
}
