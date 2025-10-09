<?php

namespace NyonCode\LivewireTable\Columns;

use Closure;
use Illuminate\Database\Eloquent\Model;

class EditableColumn extends Column
{
    protected string $inputType = 'text';

    protected array $options = [];

    protected string|null $rules = null;

    protected Closure|null $onSave = null;

    public function inputType(string $type): static
    {
        $this->inputType = $type;

        return $this;
    }

    public function options(array $options): static
    {
        $this->options = $options;

        return $this;
    }

    public function rules(string $rules): static
    {
        $this->rules = $rules;

        return $this;
    }

    public function onSave(Closure $callback): static
    {
        $this->onSave = $callback;

        return $this;
    }

    public function getInputType(): string
    {
        return $this->inputType;
    }

    /**
     * Get options.
     *
     * @return array
     */
    public function getOptions(): array
    {
        return $this->options;
    }

    /**
     * Get rules.
     *
     * @return string|null
     */
    public function getRules(): ?string
    {
        return $this->rules;
    }

    /**
     * Save the value.
     *
     * @param  Model   $record
     * @param  string  $value
     *
     * @return void
     */
    public function save(Model $record, string $value): void
    {
        if ($this->onSave) {
            ($this->onSave)($record, $value);
        } else {
            $record->update([
                $this->field => $value
            ]);
        }
    }

    /**
     * @inheritDoc
     */
    public function render(): string
    {
        return view('livewire-table::components.table.columns.editable', [
            'column' => $this,
        ])->render();
    }

    /**
     * @inheritDoc
     */
    public function formatValue(mixed $value, Model $record): mixed
    {
        return $value;
    }
}
