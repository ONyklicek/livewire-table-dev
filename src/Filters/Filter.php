<?php

namespace App\Support\Tables\Filters;

use Illuminate\Contracts\Support\Htmlable;
use Illuminate\Database\Eloquent\Builder;
use Throwable;

abstract class Filter implements Htmlable
{
    protected string $name;
    protected string $column;
    protected ?string $label = null;
    protected mixed $default = null;
    protected ?string $operator = null;
    protected ?string $placeholder = null;

    protected bool $isGlobal = false;

    /**
     * Create a new filter.
     *
     * @param  string       $name
     * @param  string|null  $column
     */
    public function __construct(string $name, ?string $column = null)
    {
        $this->name = $name;
        $this->column = $column ?? $name;
        $this->label = str($name)->headline()->toString();
    }

    /**
     * Create a new filter.
     *
     * @param  string       $name
     * @param  string|null  $column
     *
     * @return static
     */
    public static function make(string $name, ?string $column = null): static
    {
        return new static($name, $column);
    }

    /**
     * Set the label.
     *
     * @param  string  $label
     *
     * @return $this
     */
    public function label(string $label): static
    {
        $this->label = $label;
        return $this;
    }

    /**
     * Set the default value.
     *
     * @param  mixed  $default
     *
     * @return $this
     */
    public function default(mixed $default): static
    {
        $this->default = $default;
        return $this;
    }

    /**
     * Set the filter to be global.
     *
     * @param  bool  $isGlobal
     *
     * @return $this
     */
    public function global(bool $isGlobal = true): static
    {
        $this->isGlobal = $isGlobal;
        return $this;
    }

    /**
     * Apply the filter to the query.
     *
     * @param  Builder  $query
     * @param  mixed    $value
     *
     * @return Builder
     */
    abstract public function apply(Builder $query, mixed $value): Builder;

    /**
     * Set the placeholder.
     *
     * @param  string  $placeholder
     *
     * @return $this
     */
    public function placeholder(string $placeholder): static
    {
        $this->placeholder = $placeholder;
        return $this;
    }

    /**
     * Set the operator.
     *
     * @param  string  $operator
     *
     * @return $this
     */
    public function operator(string $operator): static
    {
        $this->operator = $operator;
        return $this;
    }

    /**
     * Get the name.
     *
     * @return string
     */
    public function getName(): string
    {
        return $this->name;
    }

    /**
     * Get the column.
     *
     * @return string
     */
    public function getColumn(): string
    {
        return $this->column;
    }

    /**
     * Get the label.
     *
     * @return string
     */
    public function getLabel(): string
    {
        return $this->label;
    }

    /**
     * Get the default value.
     *
     * @return mixed
     */
    public function getDefault(): mixed
    {
        return $this->default;
    }

    /**
     * Get the placeholder.
     *
     * @return string|null
     */
    public function getPlaceholder(): ?string
    {
        return $this->placeholder;
    }

    /**
     * Check if the filter is global.
     *
     * @return bool
     */
    public function isGlobal(): bool
    {
        return $this->isGlobal;
    }

    /**
     * Convert the filter to a string.
     *
     * @throws Throwable
     *
     * @return string
     */
    public function __toString(): string
    {
        return $this->toHtml();
    }

    /**
     * Render the filter.
     *
     * @throws Throwable
     *
     * @return string
     */
    public function toHtml(): string
    {
        return $this->render();
    }

    /**
     * Render the filter.
     *
     * @throws Throwable
     *
     * @return string
     */
    abstract public function render(): string;

    /**
     * Apply a condition to the query.
     *
     * @param  Builder  $query
     * @param  string   $column
     * @param  mixed    $value
     *
     * @return Builder
     */
    public function applyCondition(Builder $query, string $column, mixed $value): Builder
    {
        return match ($this->operator) {
            'like' => $query->where($column, 'like', "%$value%"),
            '=' => $query->where($column, '=', $value),
            '!=' => $query->where($column, '!=', $value),
            '>' => $query->where($column, '>', $value),
            '>=' => $query->where($column, '>=', $value),
            '<' => $query->where($column, '<', $value),
            '<=' => $query->where($column, '<=', $value),
            default => $query,
        };
    }
}
