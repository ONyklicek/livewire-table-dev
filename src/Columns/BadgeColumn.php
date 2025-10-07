<?php

namespace NyonCode\LivewireTable\Columns;

use Illuminate\Database\Eloquent\Model;

class BadgeColumn extends Column
{
    protected ?string $view = 'components.table.columns.badge';

    protected array $colors = [];

    protected array $icons = [];

    protected ?string $size;

    /**
     * Set colors.
     *
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
     */
    public function getColor(mixed $value): string
    {
        return $this->colors[$value] ?? 'gray';
    }

    /**
     * Get icon.
     */
    public function getIcon(mixed $value): ?string
    {
        return $this->icons[$value] ?? null;
    }

    public function getSize(): ?string
    {
        return $this->size ?? null;
    }

    /**
     * Format the value.
     */
    public function formatValue(mixed $value, Model $record): mixed
    {
        return $value;
    }
}
