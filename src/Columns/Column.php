<?php

namespace App\Support\Tables\Columns;

use BackedEnum;
use Closure;
use Illuminate\Contracts\Support\Htmlable;
use Illuminate\Database\Eloquent\Model;
use Str;
use Throwable;
use UnitEnum;

abstract class Column implements Htmlable
{
    public string $field;
    public ?string $label = null;
    public bool $sortable = false;
    public bool $searchable = false;
    public bool $hidden = false;
    protected array $responsiveHidden = [];
    protected ?Closure $formatCallback = null;
    protected ?string $view = null;
    protected ?Model $record = null;

    /**
     * Create a new column instance.
     *
     * @param  string  $field
     */
    public function __construct(string $field)
    {
        $this->field = $field;
        $this->label = Str::headline($field);
    }

    /**
     * Create a new column instance.
     *
     * @param  string  $field  The field name to create the column for.
     *
     * @return static  A new column instance.
     */
    public static function make(string $field): static
    {
        return new static($field);
    }

    /**
     * Set column label.
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
     * Set column sortable.
     *
     * @param  bool  $sortable
     *
     * @return $this
     */
    public function sortable(bool $sortable = true): static
    {
        $this->sortable = $sortable;
        return $this;
    }

    /**
     * @param  bool  $searchable
     *
     * @return $this
     */
    public function searchable(bool $searchable = true): static
    {
        $this->searchable = $searchable;
        return $this;
    }

    /**
     * Set column hidden.
     *
     * @param  bool|Closure  $hidden
     *
     * @return $this
     */
    public function hidden(bool|Closure $hidden = true): static
    {
        $this->hidden = value($hidden);
        return $this;
    }

    /**
     * Set column hidden on breakepoints.
     * Example: hideOn(['sm', 'md'])
     *
     * @param  array  $breakpoints
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
     * @param  Closure  $callback
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
     * @param  Model  $record
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
     * @param  string  $view
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

    public function isHidden(): bool
    {
        return $this->hidden;
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
     *
     * @return string
     */
    public function __toString(): string
    {
        return $this->toHtml();
    }

    /**
     * Convert the column to HTML.
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
     * Render the column.
     *
     * @throws Throwable
     * @return string
     */
    public function render(): string
    {
        if (!$this->record) {
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

        return view('components.table.columns.text', [
            'column' => $this,
            'record' => $this->record,
            'value' => $this->getFormattedValue($this->record),
            'rawValue' => $this->getValue($this->record),
        ])->render();
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
        //Support relationships with dot notation
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
     *
     * @param  mixed  $value
     * @param  Model  $record
     *
     * @return mixed
     */
    abstract public function formatValue(mixed $value, Model $record): mixed;
}
