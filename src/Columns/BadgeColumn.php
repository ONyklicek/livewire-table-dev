<?php

namespace App\Support\Tables\Columns;

use Illuminate\Database\Eloquent\Model;

class BadgeColumn extends Column
{
    protected ?string $view = 'components.table.columns.badge';
    protected array $colors = [];
    protected array $icons = [];
    protected string|null $size;

    /**
     * Set colors.
     *
     * @param  array  $colors
     *
     * @return $this
     */
    public function colors(array $colors): static
    {
        $this->colors = $colors;
        return $this;
    }

    /**
     * Set icons.
     *
     * @param  array  $icons
     *
     * @return $this
     */
    public function icons(array $icons): static
    {
        $this->icons = $icons;
        return $this;
    }

    /**
     * Set size.
     *
     * @param  string  $size
     *
     * @return $this
     */
    public function size(string $size = 'lg'): static
    {
        $this->size = $size;
        return $this;
    }

    /**
     * Get color.
     *
     * @param  mixed  $value
     *
     * @return string
     */
    public function getColor(mixed $value): string
    {
        return $this->colors[$value] ?? 'gray';
    }

    /**
     * Get icon.
     *
     * @param  mixed  $value
     *
     * @return string|null
     */
    public function getIcon(mixed $value): ?string
    {
        return $this->icons[$value] ?? null;
    }

    public function getSize(): string|null
    {
        return $this->size ?? null;
    }

    /**
     * Format the value.
     *
     * @param  mixed  $value
     * @param  Model  $record
     *
     * @return mixed
     */
    public function formatValue(mixed $value, Model $record): mixed
    {
        return $value;
    }
}
