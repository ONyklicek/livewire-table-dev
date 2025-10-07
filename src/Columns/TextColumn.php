<?php

namespace App\Support\Tables\Columns;

use Illuminate\Database\Eloquent\Model;

class TextColumn extends Column
{

    protected bool $copyable = false;
    protected ?int $limit = null;
    protected ?string $placeholder = null;
    protected ?string $view = 'components.table.columns.text';

    /**
     * Set column copyable.
     *
     * @param  bool  $copyable
     *
     * @return $this
     */
    public function copyable(bool $copyable = true): static
    {
        $this->copyable = $copyable;
        return $this;
    }

    /**
     * Set column limit.
     *
     * @param  int  $limit
     *
     * @return $this
     */
    public function limit(int $limit): static
    {
        $this->limit = $limit;
        return $this;
    }

    /**
     * Set column placeholder.
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
     * Check if the column is copyable.
     *
     * @return bool
     */
    public function isCopyable(): bool
    {
        return $this->copyable;
    }

    /**
     * @inheritDoc
     */
    public function formatValue(mixed $value, Model $record): string
    {
        if ($value === null) {
            return $this->placeholder ?? '—';
        }

        if ($this->limit && strlen($value) > $this->limit) {
            return substr($value, 0, $this->limit).'...';
        }

        return $value;
    }
}
