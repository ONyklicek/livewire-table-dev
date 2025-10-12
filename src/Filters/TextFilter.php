<?php

namespace NyonCode\LivewireTable\Filters;

use Illuminate\Database\Eloquent\Builder;

class TextFilter extends Filter
{
    protected ?string $operator = 'like';

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
                return $this->applyCondition($q, $column, $value);
            });
        }

        return $this->applyCondition($query, $this->column, $value);
    }

    /**
     * {@inheritDoc}
     */
    public function render(): string
    {
        return view('livewire-table::components.table.filters.text', [
            'filter' => $this,
        ])->render();
    }
}
