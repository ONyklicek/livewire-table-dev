<?php

namespace NyonCode\LivewireTable\Columns;

use Illuminate\Database\Eloquent\Model;

class ImageColumn extends Column
{
    protected string $size = 'md';

    protected bool $circular = false;

    protected ?string $defaultImage = null;

    /**
     * Set size.
     *
     *
     * @return $this
     */
    public function size(string $size): static
    {
        $this->size = $size;

        return $this;
    }

    /**
     * Set circular.
     *
     *
     * @return $this
     */
    public function circular(bool $circular = true): static
    {
        $this->circular = $circular;

        return $this;
    }

    /**
     * Set default image.
     *
     *
     * @return $this
     */
    public function defaultImage(string $url): static
    {
        $this->defaultImage = $url;

        return $this;
    }

    /**
     * Get size.
     */
    public function getSize(): string
    {
        return $this->size;
    }

    /**
     * Get circular.
     */
    public function isCircular(): bool
    {
        return $this->circular;
    }

    /**
     * Format the value.
     */
    public function formatValue(mixed $value, Model $record): mixed
    {
        return $value ?? $this->defaultImage ?? 'https://ui-avatars.com/api/?name='.urlencode($record->name ?? 'User');
    }
}
