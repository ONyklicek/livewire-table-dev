<?php

namespace NyonCode\LivewireTable\Concerns;

use Illuminate\Support\Collection;
use NyonCode\LivewireTable\Models\TableFilterPreset;

trait HasSavedFilters
{
    protected ?string $presetModel = TableFilterPreset::class;

    protected ?int $activePresetId = null;

    protected bool $presetsEnabled = false;

    /**
     * Set presets enabled.
     *
     *
     * @return $this
     */
    public function enablePresets(bool $enabled = true): static
    {
        $this->presetsEnabled = $enabled;

        return $this;
    }

    /**
     * Set preset model.
     *
     *
     * @return $this
     */
    public function presetModel(string $model): static
    {
        $this->presetModel = $model;

        return $this;
    }

    /**
     * Get presets.
     */
    public function getPresets(): Collection
    {
        if (! $this->presetsEnabled || ! class_exists($this->presetModel)) {
            return collect();
        }

        return $this->presetModel::where('user_id', auth()->id())
            ->where('table_name', $this->livewireComponent)
            ->orderBy('name')
            ->get();
    }

    /**
     * Check if presets are enabled.
     */
    public function isPresetsEnabled(): bool
    {
        return $this->presetsEnabled;
    }

    /**
     * Get active preset id.
     */
    public function getActivePresetId(): ?int
    {
        return $this->activePresetId;
    }
}
