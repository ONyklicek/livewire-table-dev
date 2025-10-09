<?php

namespace NyonCode\LivewireTable\Builders;

use Illuminate\Database\Eloquent\Builder;

class QueryBuilder
{
    public Builder $query;

    /**
     * Create a new instance of the query builder.
     */
    public function __construct(Builder $query)
    {
        $this->query = $query;
    }

    /**
     * Apply search across multiple columns
     *
     * @return $this
     */
    public function search(array $columns, string $search): static
    {
        $this->query->where(function ($q) use ($columns, $search) {
            foreach ($columns as $column) {
                if (str_contains($column, '.')) {
                    [$relation, $field] = explode('.', $column, 2);
                    $q->orWhereHas($relation, fn ($query) => $query->where($field, 'like', "%{$search}%")
                    );
                } else {
                    $q->orWhere($column, 'like', "%{$search}%");
                }
            }
        });

        return $this;
    }

    /**
     * Apply multiple sorts
     *
     * @return $this
     */
    public function multiSort(array $sorts): static
    {
        foreach ($sorts as $column => $direction) {
            if (str_contains($column, '.')) {
                [$relation, $field] = explode('.', $column, 2);
                $this->query->with([
                    $relation => fn ($q) => $q->orderBy($field, $direction),
                ]);
            } else {
                $this->query->orderBy($column, $direction);
            }
        }

        return $this;
    }

    /**
     * Apply filters
     *
     * @return $this
     */
    public function applyFilters(array $filters): static
    {
        foreach ($filters as $filter) {
            $this->query = $filter->apply($this->query, $filter->getValue());
        }

        return $this;
    }

    /**
     * Get the query
     */
    public function get(): Builder
    {
        return $this->query;
    }
}
