<?php

namespace App\Support\Tables\Concerns;

use Illuminate\Support\Collection;

trait HasSavedFilters
{
    protected ?string $presetModel = TableFilterPreset::class;
    protected ?int $activePresetId = null;
    protected bool $presetsEnabled = false;

    public function enablePresets(bool $enabled = true): static
    {
        $this->presetsEnabled = $enabled;
        return $this;
    }

    public function presetModel(string $model): static
    {
        $this->presetModel = $model;
        return $this;
    }

    public function getPresets(): Collection
    {
        if (!$this->presetsEnabled || !class_exists($this->presetModel)) {
            return collect();
        }

        return $this->presetModel::where('user_id', auth()->id())
            ->where('table_name', $this->livewireComponent)
            ->orderBy('name')
            ->get();
    }

    public function isPresetsEnabled(): bool
    {
        return $this->presetsEnabled;
    }

    public function getActivePresetId(): ?int
    {
        return $this->activePresetId;
    }
}
