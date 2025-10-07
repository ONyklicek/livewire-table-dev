<?php

namespace App\Support\Tables\Filters;

use Illuminate\Database\Eloquent\Builder;

class SelectFilter extends Filter
{

    protected array $options = [];
    protected ?string $placeholder = null;

    public function options(array $options): static
    {
        $this->options = $options;
        return $this;
    }

    /**
     * @inheritDoc
     */
    public function apply(Builder $query, mixed $value): Builder
    {
        if ($value === null || $value === '') {
            return $query;
        }

        if (str_contains($this->column, '.')) {
            [$relation, $column] = explode('.', $this->column, 2);

            return $query->whereHas($relation, fn($q) => $q->where($column, $value));
        }

        return $query->where($this->column, $value);
    }

    public function getOptions(): array
    {
        return $this->options;
    }

    /**
     * @inheritDoc
     */
    public function render(): string
    {
        return view('components.table.filters.select', [
            'filter' => $this
        ])->render();
    }
}
