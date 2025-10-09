<?php

namespace NyonCode\LivewireTable\Concerns;

trait HasResponsiveScheme
{
    protected array $scheme = [];

    /**
     * Set responsive scheme.
     *
     *
     * @return $this
     */
    public function scheme(array $scheme): static
    {
        $this->scheme = $scheme;

        return $this;
    }

    /**
     * Get responsive scheme.
     */
    public function getScheme(): array
    {
        if (empty($this->scheme)) {
            return [
                'mobile' => ['stack'],
                'tablet' => ['scroll'],
                'desktop' => ['full'],
                'responsive_classes' => '',
            ];
        }

        return $this->scheme;
    }
}
