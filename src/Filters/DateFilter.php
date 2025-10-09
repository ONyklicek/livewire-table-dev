<?php

namespace NyonCode\LivewireTable\Filters;

use Illuminate\Database\Eloquent\Builder;

class DateFilter extends Filter
{
    protected ?string $operator = '=';

    /**
     * {@inheritDoc}
     */
    public function apply(Builder $query, mixed $value): Builder
    {
        if (empty($value)) {
            return $query;
        }

        if (str_contains($this->column, '.')) {
            [$relation, $column] = explode('.', $this->column, 2);

            return $query->whereHas($relation, function ($q) use ($column, $value) {
                return $q->whereDate($column, $this->operator, $value);
            });
        }

        return $query->whereDate($this->column, $this->operator, $value);
    }

    /**
     * {@inheritDoc}
     */
    public function render(): string
    {
        return view('components.table.filters.date', [
            'filter' => $this,
        ])->render();
    }
}
