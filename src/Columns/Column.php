<?php

namespace NyonCode\LivewireTable\Columns;

use BackedEnum;
use Closure;
use Illuminate\Contracts\Support\Htmlable;
use Illuminate\Contracts\Support\Renderable;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Str;
use InvalidArgumentException;
use NyonCode\LivewireTable\Builders\RelationshipResolver;
use Throwable;
use UnitEnum;

abstract class Column implements Renderable, Htmlable
{
    public string $field;

    public ?string $label = null;

    public bool $sortable = false;

    public bool $searchable = false;

    public bool $visible = true;
    protected ?Closure $visibleCallback = null;

    protected array $responsiveHidden = [];

    protected ?Closure $formatCallback = null;

    protected ?string $view = null;

    protected ?Model $record = null;

    /**
     * Create a new column instance.
     *
     * @throws InvalidArgumentException
     */
    public function __construct(string $field)
    {
        if (Str::contains($field, '.')) {
            try {
                RelationshipResolver::validateField($field);
            } catch (InvalidArgumentException $e) {
                throw new InvalidArgumentException("Invalid field format for column: {$e->getMessage()}");
            }
        }

        $this->field = $field;
        $this->label = Str::headline($field);
    }

    /**
     * Create a new column instance.
     *
     * @param  string  $field  The field name to create the column for.
     * @return static A new column instance.
     */
    public static function make(string $field): static
    {
        return new static($field);
    }

    /**
     * Set column label.
     *
     *
     * @return $this
     */
    public function label(string $label): static
    {
        $this->label = $label;

        return $this;
    }

    /**
     * Set column sortable.
     *
     *
     * @return $this
     */
    public function sortable(bool $sortable = true): static
    {
        $this->sortable = $sortable;

        return $this;
    }

    /**
     * @return $this
     */
    public function searchable(bool $searchable = true): static
    {
        $this->searchable = $searchable;

        return $this;
    }

    /**
     * Set column global visibility.
     *
     * @param bool|Closure $visible True to show, false to hide
     * @return $this
     */
    public function visible(bool|Closure $visible = true): static
    {
        if ($visible instanceof Closure) {
            $this->visibleCallback = $visible;
            $this->visible = true; // Will be evaluated later
        } else {
            $this->visible = $visible;
            $this->visibleCallback = null;
        }

        return $this;
    }

    /**
     * Alias for visible(false) for better readability.
     *
     * @return $this
     */
    public function hidden(bool|Closure $hidden = true): static
    {
        if ($hidden instanceof Closure) {
            return $this->visible(fn() => !$hidden());
        }

        return $this->visible(!$hidden);
    }

    /**
     * Set column hidden on breakepoints.
     * Example: hideOn(['sm', 'md'])
     *
     *
     * @return $this
     */
    public function hideOn(array $breakpoints): static
    {
        $this->responsiveHidden = $breakpoints;

        return $this;
    }

    /**
     * Set column format.
     * Example: format(fn($value) => $value * 2)
     *
     *
     * @return $this
     */
    public function format(Closure $callback): static
    {
        $this->formatCallback = $callback;

        return $this;
    }

    /**
     * Set column record.
     *
     *
     * @return $this
     */
    public function for(Model $record): static
    {
        $clone = clone $this;
        $clone->record = $record;

        return $clone;
    }

    /**
     * Set column view.
     * Example: view('components.table.columns.text')
     *
     *
     * @return $this
     */
    public function view(string $view): static
    {
        $this->view = $view;

        return $this;
    }

    public function getField(): string
    {
        return $this->field;
    }

    public function getLabel(): string
    {
        return $this->label;
    }

    public function isSortable(): bool
    {
        return $this->sortable;
    }

    public function isSearchable(): bool
    {
        return $this->searchable;
    }

    /**
     * Check if column is globally visible.
     */
    public function isVisible(): bool
    {
        if ($this->visibleCallback !== null) {
            try {
                return (bool) ($this->visibleCallback)();
            } catch (Throwable $e) {
                logger()->warning('Column visibility callback failed', [
                    'column' => $this->field,
                    'error' => $e->getMessage(),
                ]);
                return false; // Hide on error
            }
        }

        return $this->visible;
    }

    /**
     * Alias for !isVisible() for better readability.
     */
    public function isHidden(): bool
    {
        return !$this->isVisible();
    }

    public function getResponsiveClasses(): string
    {
        if (empty($this->responsiveHidden)) {
            return '';
        }

        $classes = [];
        foreach ($this->responsiveHidden as $breakpoint) {
            $classes[] = "hidden $breakpoint:table-cell";
        }

        return implode(' ', $classes);
    }

    /**
     * Convert the column to a string.
     *
     * @throws Throwable
     */
    public function __toString(): string
    {
        return $this->toHtml();
    }

    /**
     * Convert the column to HTML.
     *
     * @throws Throwable
     */
    public function toHtml(): string
    {
        return $this->render();
    }

    /**
     * Render the column.
     *
     * @throws Throwable
     */
    public function render(): string
    {
        if (! $this->record) {
            return '';
        }

        if ($this->view) {
            return view($this->view, [
                'column' => $this,
                'record' => $this->record,
                'value' => $this->getFormattedValue($this->record),
                'rawValue' => $this->getValue($this->record),
            ])->render();
        }

        return $this->getFormattedValue($this->record);
    }

    public function getFormattedValue(Model $record): mixed
    {
        $value = $this->getValue($record);

        if ($this->formatCallback) {
            return ($this->formatCallback)($value, $record);
        }

        return $this->formatValue($value, $record);
    }

    public function getValue(Model $record): mixed
    {
        // Support relationships with dot notation
        if (str_contains($this->field, '.')) {
            return data_get($record, $this->field);
        }

        $value = $record->{$this->field};

        // Support enums casts
        if ($value instanceof BackedEnum) {
            return $value->value;
        }

        if ($value instanceof UnitEnum) {
            return $value->name;
        }

        return $value;
    }

    /**
     * Format the value.
     */
    abstract public function formatValue(mixed $value, Model $record): mixed;
}
